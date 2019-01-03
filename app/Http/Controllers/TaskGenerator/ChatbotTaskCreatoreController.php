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
use App\Tariff;
use App\TaskList;
use App\User;
use Illuminate\Support\Facades\Log;

class ChatbotTaskCreatoreController
{
    public static function tasksGenerator()
    {
        Log::debug('======== generate chatbot tasks =======');
        $users = User::where(['is_confirmed' => 1])->get();

//        Log::debug('found users: ' . count($users));

        foreach ($users as $user) {
            $tariff = Tariff::getUserCurrentTariff($user->id);

            if (is_null($tariff)) {
                Log::debug('tariff is not valid for user: ' . $user->id);
                continue;
            }

            $tasksTypes = TaskList::getAvaliableTasksForTariffListId($tariff->tariff_list_id);

            if (count($tasksTypes) == 0) {
                Log::debug('task types not found: ' . $tariff->tariff_list_id);
                continue;
            }

            $accounts = account::getActiveAccountsByUser($user->id);

//            Log::debug("found active accounts: " . count($accounts));

            try {
                foreach ($accounts as $account) {
                    if ($account->nickname == 'houpek_nadin') { //TODO: remove in future
                        continue;
                    }

//                    Log::debug('$account ' . $account);
//                    Log::debug('$tasksTypes: ' . \json_encode($tasksTypes));

                    foreach ($tasksTypes as $taskType) {
//                        Log::debug('$taskType->type ' . $taskType->type);
                        if (TaskList::TYPE_CHATBOT == $taskType->type) {
//                            $taskListId = $taskType->id;

                            $chatBot = Chatbot::getByUserId($user->id);

                            if (is_null($chatBot)) {
                                Log::debug("no chatbot exists");
                            }

                            if (self::generateGetInboxTask($chatBot, $account)) {
                                Log::debug("chatbot get inbox task added to fast tasks: " . $chatBot->id);
                            }

                            //TODO: временно отключено
//                            if (self::generateFirstMessageTask($chatBot, $account)) {
//                                Log::debug("chatbot first message task added to fast tasks: " . $chatBot->id);
//                            }

                            if (self::generateBotAnswerTask($chatBot, $account)) {
                                Log::debug("chatbot answer task added to fast tasks: " . $chatBot->id);
                            }
                        }
                    }
                }
            } catch (\Exception $err) {
                Log::error('error: ' . $err->getMessage());
            }
        }

        Log::debug('======== done generate chatbot tasks =======');
    }

    private static function generateGetInboxTask(Chatbot $chatBot, account $account)
    {
        if (is_null($chatBot) or is_null($account)) {
            return false;
        }

        if (FastTask::isNight()) {
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

//        if (!FastTask::isHadRestInLastOneAndHalfHoursUnsubscribeTasks($account->id)) {
//            $randomDelayMinutes = rand(20, 30);
//        }

        Log::debug('GetInbox delay time (minutes): ' . $randomDelayMinutes);

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

        $directTask = DirectTask::getActiveDirectTaskByTaskListId(0, $account->id, true);

        if (!is_null($directTask)) {
            $todayDirectCount += DirectTaskReport::getTodayFriendDirectMessagesCount($directTask->id);
        }

        if ($todayDirectCount >= env('FRIEND_DIRECT_LIMITS_BY_DAY')) {
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

        if (!is_null($directTask)) {
            $lastHourDirectCount += DirectTaskReport::getLastHourFriendDirectMessagesCount($directTask->id);
        }

        if ($lastHourDirectCount >= env('FRIEND_DIRECT_LIMITS_BY_HOUR')) {
            return false;
        }

        $randomDelayMinutes = 3;

        for($i = 0; $i < 20; $i++) {
            $randomDelayMinutes = rand(env('MESSAGE_DELAY_MIN_SLEEP', '3'), env('MESSAGE_DELAY_MAX_SLEEP', '5'));

            if ($lastDelay != $randomDelayMinutes) {
                break;
            }
        }

        if (!FastTask::isHadRestInLastOneAndHalfHoursDirectTasks($account->id)) {
            $randomDelayMinutes = rand(40, 50);
        }

        Log::debug('Delay time (minutes): ' . $randomDelayMinutes);

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

        if (FastTask::isNight()) {
            return false;
        }

        if (!FastTask::isCanRun($account->id, FastTask::TYPE_CHATBOT_ANALIZE_AND_ANSWER)) {
            return false;
        }

        $waiting = ChatHeader::getWaitingAnalisysCount($chatBot, $account, ChatHeader::STATUS_DIALOG_NEED_ANALIZE);

        if ($waiting > 0) {
        Log::debug('waiting analisys: ' . $waiting . ', add fast task');
            $randomDelayMinutes = rand(1,2);

            FastTask::addTask($account->id,
                FastTask::TYPE_CHATBOT_ANALIZE_AND_ANSWER,
                $chatBot->id,
                $randomDelayMinutes);

            return true;
        }

        return false;
    }
}