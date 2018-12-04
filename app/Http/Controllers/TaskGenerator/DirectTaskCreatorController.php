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
    public static function generateDirectTasks()
    {
        Log::debug('generate tasks');
        $users = User::where(['is_confirmed' => 1])->get();

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

            foreach ($accounts as $account) {
                foreach ($tasksTypes as $taskType) {
                    if ('direct' == $taskType->type) {
                        $taskListId = $taskType->id;
                        $directTask = DirectTask::getActiveDirectTaskByTaskListId($taskListId, $account->id);

                        if (is_null($directTask)) {
                            Log::debug('No direct tasks found ' . $taskListId . ' ' . $account->id);
                            continue;
                        }

                        if ($directTask->work_only_in_night > 0 and !self::isNight()) {
                            continue;
                        } else if (self::isNight()) {
                            $currentMinutes = (int) date('i');
                            if ( $currentMinutes%30 < 10 ) { // once on 30 minutes
                                FastTask::addTask($account->id, FastTask::TYPE_DIRECT_ANSWER, $directTask->id);
                                Log::debug('add fast direct task (at night): ' . $directTask->id . ' ' . $account->id);
                            }
                        } else {
                            FastTask::addTask($account->id, FastTask::TYPE_DIRECT_ANSWER, $directTask->id);
                            Log::debug('add fast direct task (at day): ' . $directTask->id . ' ' . $account->id);
                        }
                    }
                }
            }
        }

        Log::debug('generate tasks end');
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