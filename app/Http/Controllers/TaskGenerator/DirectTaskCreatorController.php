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
use App\Tariff;
use App\TaskList;
use App\User;
use Illuminate\Support\Facades\Log;

class DirectTaskCreatorController
{
    public static function generateDirectTasks()
    {
        Log::debug('generate tasks');
        $users = User::where(['is_confirmed' => 1])->get();

        foreach ($users as $user) {
            $tariff = Tariff::getUserCurrentTariff($user->id);

            if (is_null($tariff)) {
                continue;
            }

            $tasksTypes = TaskList::getAvaliableTasksForTariffListId($tariff->tariff_list_id);

            if (count($tasksTypes) > 0) {
                $accounts = account::getActiveAccountsByUser($user->id);

                foreach ($accounts as $account) {
                    foreach ($tasksTypes as $taskType) {
                        if ('direct' == $taskType->type) {
                            $taskListId = $taskType->id;
                            $directTask = DirectTask::getActiveDirectTaskByTaskListId($taskListId, $account->id);

                            if (is_null($directTask)) {
                                continue;
                            }

                            $todayDirectCount = DirectTaskReport::getTodayFriendDirectMessagesCount($directTask->id);

                            if ($todayDirectCount >= env('FRIEND_DIRECT_LIMITS_BY_DAY')) {
                                Log::debug('direct limits per day achieved: ' . $todayDirectCount);
                                continue;
                            }

                            if ($directTask->work_only_in_night > 0 and !self::isNight()) {
//                                Log::debug('This is night task and now is not night');
                                continue;
                            }

                            $preCommand = "cd " . env('PROJECT_PATH');
                            $command = " && " . env('PHP_PATH') . " artisan direct:send " . $directTask->id . ' ' . $account->id;
                            $runInBackground = " > /dev/null 2>&1";

                            Log::debug('command: ' . $preCommand . $command . $runInBackground);

                            exec($preCommand . $command . $runInBackground);
                        } else if ('unfollowing' == $taskType->type) {

                        }
                    }

                }
            }
        }
    }

    public static function isNight()
    {
        date_default_timezone_set('Europe/Kiev');

        $nightStartTime = 23;
        $nightEndTime = 6;

        $curHour = date("H");

        return ($curHour >= $nightStartTime or $curHour <= $nightEndTime);
    }
}