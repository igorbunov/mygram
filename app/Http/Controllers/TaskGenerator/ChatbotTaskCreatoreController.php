<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 02.01.19
 * Time: 15:36
 */

namespace App\Http\Controllers\TaskGenerator;

use App\account;
use App\Chatbot;
use App\ChatbotAccounts;
use App\ChatHeader;
use App\DirectTask;
use App\DirectTaskReport;
use App\FastTask;
use Illuminate\Support\Facades\Log;

class ChatbotTaskCreatoreController
{
    public static function tasksGenerator(Chatbot $chatBot, $accounts)
    {
        try {
            foreach ($accounts as $account) {
                self::generateGetInboxTask($chatBot, $account);

                if (env('IS_CHATBOT_FIRST_MESSAGE_WORKS', false)) {
                    $directTasks = DirectTask::getDirectTasksByForAccount($account, true);

                    if (is_null($directTasks) or count($directTasks) == 0) {
                        if (Chatbot::getInQueueChats($chatBot) > 0) {
                            self::generateFirstMessageTask($chatBot, $account);
                        }
                    }
                }

                self::generateBotAnswerTask($chatBot, $account);
            }
        } catch (\Exception $err) {
            Log::error('error: ' . $err->getMessage());
        }
    }

    private static function generateGetInboxTask(Chatbot $chatBot, account $account)
    {
        if (is_null($chatBot) or is_null($account)) {
            return false;
        }

        if (!FastTask::isCanRun($account->id, FastTask::TYPE_GET_DIRECT_INBOX)) {
            return false;
        }

        $lastTask = FastTask::getLastTask($account->id, FastTask::TYPE_GET_DIRECT_INBOX);
        $lastDelay = 0;

        if (!is_null($lastTask)) {
            $lastDelay = $lastTask->delay;
        }

        $randomDelayMinutes = 3;

        for($i = 0; $i < 20; $i++) {
            $randomDelayMinutes = rand(3, 6);

            if ($lastDelay != $randomDelayMinutes) {
                break;
            }
        }

        if (FastTask::isNight()) {
            $randomDelayMinutes+= 10;
        }

        FastTask::addTask($account->id,
            FastTask::TYPE_GET_DIRECT_INBOX,
            $chatBot->id,
            $randomDelayMinutes);

        return true;
    }

    private static function generateFirstMessageTask(Chatbot $chatBot, account $account)
    {
        if (is_null($chatBot) or is_null($account)) {
            return false;
        }

        if (FastTask::isNight()) {
            return false;
        }

        $todayDirectCount = ChatbotAccounts::getTodayDirectMessagesCount($chatBot, $account);
        $directTasks = DirectTask::getDirectTasksByForAccount($account, true);

        if (!is_null($directTasks) and count($directTasks) > 0) {
            $todayDirectCount += DirectTaskReport::getTodayFriendDirectMessagesCount($directTasks[0]->id);
        }

        if ($todayDirectCount >= env('NOT_FRIEND_DIRECT_LIMITS_BY_DAY', 50)) {
            return false;
        }

        if (!FastTask::isCanRun($account->id, FastTask::TYPE_SEND_FIRST_CHATBOT_MESSAGE)) {
            return false;
        }

        $lastTask = FastTask::getLastTask($account->id, FastTask::TYPE_SEND_FIRST_CHATBOT_MESSAGE);
        $lastDelay = 0;

        if (!is_null($lastTask)) {
            $lastDelay = $lastTask->delay;
        }

        $lastHourDirectCount = ChatbotAccounts::getLastHourDirectMessagesCount($chatBot, $account);

        if (!is_null($directTasks) and count($directTasks) > 0) {
            $subres = DirectTaskReport::getLastHourFriendDirectMessagesCount($directTasks[0]->id);
            $lastHourDirectCount += $subres;

        }

        if ($lastHourDirectCount >= env('NOT_FRIEND_DIRECT_LIMITS_BY_HOUR', 8)) {
            return false;
        }

        $randomDelayMinutes = 5;

        for($i = 0; $i < 20; $i++) {
            $randomDelayMinutes = rand(env('NOT_FRIEND_MESSAGE_DELAY_MIN_SLEEP', '6'), env('NOT_FRIEND_MESSAGE_DELAY_MAX_SLEEP', '10'));

            if ($lastDelay != $randomDelayMinutes) {
                break;
            }
        }

        if (FastTask::isReachedHourlyLimitForFirstChatMessage($account->id)) {
            $randomDelayMinutes = rand(120, 180);
        }

        FastTask::addTask($account->id,
            FastTask::TYPE_SEND_FIRST_CHATBOT_MESSAGE,
            $chatBot->id,
            $randomDelayMinutes);

        return true;
    }

    private static function generateBotAnswerTask(Chatbot $chatBot, account $account)
    {
        if (is_null($chatBot) or is_null($account)) {
            return false;
        }

        if (!FastTask::isCanRun($account->id, FastTask::TYPE_CHATBOT_ANALIZE_AND_ANSWER)) {
            return false;
        }

        $waiting = ChatHeader::getWaitingAnalisysCount($chatBot, $account, ChatHeader::STATUS_DIALOG_NEED_ANALIZE);

        if ($waiting > 0) {
            $randomDelayMinutes = rand(1,2);

            if (FastTask::isNight()) {
                $randomDelayMinutes = rand(3, 6);
            }

            FastTask::addTask($account->id,
                FastTask::TYPE_CHATBOT_ANALIZE_AND_ANSWER,
                $chatBot->id,
                $randomDelayMinutes);

            return true;
        }

        return false;
    }
}