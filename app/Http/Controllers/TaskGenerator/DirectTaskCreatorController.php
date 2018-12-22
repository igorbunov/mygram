<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 20.11.18
 * Time: 20:49
 */

namespace App\Http\Controllers\TaskGenerator;

use App\account;
use App\DirectTask;
use App\DirectTaskReport;
use App\FastTask;
use App\Tariff;
use App\TaskList;
use App\User;
use Illuminate\Support\Facades\Log;

class DirectTaskCreatorController
{
    /*
     * Вызывает генератор получения новых подпищиков и генератор директ ответов
     * генератор подпищиков работает только если есть активный директ таск
     */
    public static function tasksGenerator()
    {
//        Log::debug('generate tasks');
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
                    foreach ($tasksTypes as $taskType) {
                        if ('direct' == $taskType->type) {
                            $taskListId = $taskType->id;
                            $directTask = DirectTask::getActiveDirectTaskByTaskListId($taskListId, $account->id, true);

                            if (is_null($directTask)) {
//                                Log::debug('No direct tasks found ' . $taskListId . ' ' . $account->id);
                                continue;
                            }

                            // run get subs task
                            if (GetNewSubsTaskCreatorController::generateGetSubsTask($directTask)) {
                                Log::debug("get subs task added to fast tasks: " . $directTask->id);
                            }

                            if ($directTask->status == DirectTask::STATUS_ACTIVE) {
                                // run generate direct task
                                if (self::generateDirectTask($directTask)) {
                                    Log::debug("direct task added to fast tasks: " . $directTask->id);
                                }
                            }
                        } else {
                            Log::error("bad task type: " . $taskType->type);
                        }
                    }
                }
            } catch (\Exception $err) {
                Log::error('error: ' . $err->getMessage());
            }
        }

//        Log::debug('generate tasks end');
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

        for($i = 0; $i < 5; $i++) {
            $randomDelayMinutes = rand(env('MESSAGE_DELAY_MIN_SLEEP', '3'), env('MESSAGE_DELAY_MAX_SLEEP', '5'));

            if ($lastDelay != $randomDelayMinutes) {
                break;
            }
        }

        if (!FastTask::isHadRestInLastOneAndHalfHoursDirectTasks($directTask->account_id)) {
            $randomDelayMinutes = rand(40, 50);
        }

        FastTask::addTask($directTask->account_id,
            FastTask::TYPE_DIRECT_ANSWER,
            $directTask->id,
            $randomDelayMinutes);

        return true;
    }
}