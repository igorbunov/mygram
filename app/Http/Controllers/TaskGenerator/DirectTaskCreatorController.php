<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 20.11.18
 * Time: 20:49
 */

namespace App\Http\Controllers\TaskGenerator;

use App\AccountSubscribers;
use App\DirectTask;
use App\DirectTaskReport;
use App\FastTask;
use Illuminate\Support\Facades\Log;

class DirectTaskCreatorController
{
    /*
     * Вызывает генератор получения новых подпищиков и генератор директ ответов
     * генератор подпищиков работает только если есть активный директ таск
     */
    public static function tasksGenerator($accounts)
    {
        try {
            foreach ($accounts as $account) {
                $directTasks = DirectTask::getDirectTasksByForAccount($account, true);

                if (is_null($directTasks) or count($directTasks) == 0) { continue; }

                $directTask = $directTasks[0];

                GetNewSubsTaskCreatorController::generateGetSubsTask($directTask);

                if ($directTask->status == DirectTask::STATUS_ACTIVE) {
                    $count = AccountSubscribers::getUnsendedFollowers($account->id);

                    if ($count > 0) {
                        self::generateDirectTask($directTask);
                    }
                }
            }
        } catch (\Exception $err) {
            Log::error('error: ' . $err->getMessage());
        }
    }

    private static function generateDirectTask(DirectTask $directTask): bool
    {
        if (is_null($directTask)) {
            return false;
        }

        if (FastTask::isNight()) {
            return false;
        }

        $todayDirectCount = DirectTaskReport::getTodayFriendDirectMessagesCount($directTask->id);

        if ($todayDirectCount >= env('FRIEND_DIRECT_LIMITS_BY_DAY')) {
            return false;
        }

        if (!FastTask::isCanRun($directTask->account_id, FastTask::TYPE_DIRECT_ANSWER)) {
            return false;
        }

        $lastTask = FastTask::getLastTask($directTask->account_id, FastTask::TYPE_DIRECT_ANSWER);
        $lastDelay = 0;

        if (!is_null($lastTask)) {
            $lastDelay = $lastTask->delay;
        }

        $lastHourDirectCount = DirectTaskReport::getLastHourFriendDirectMessagesCount($directTask->id);

        if ($lastHourDirectCount >= env('FRIEND_DIRECT_LIMITS_BY_HOUR')) {
            return false;
        }

        for($i = 0; $i < 20; $i++) {
            $randomDelayMinutes = rand(env('MESSAGE_DELAY_MIN_SLEEP', '3'), env('MESSAGE_DELAY_MAX_SLEEP', '5'));

            if ($lastDelay != $randomDelayMinutes) {
                break;
            }
        }

        if (!FastTask::isHadRestInLastOneAndHalfHoursDirectTasks($directTask->account_id)) {
            $randomDelayMinutes = rand(40, 50);
        }

//        Log::debug('Delay time (minutes): ' . $randomDelayMinutes);

        FastTask::addTask($directTask->account_id,
            FastTask::TYPE_DIRECT_ANSWER,
            $directTask->id,
            $randomDelayMinutes);

        return true;
    }
}