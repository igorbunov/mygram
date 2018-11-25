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

            $tasks = TaskList::getAvaliableTasksForTariffListId($tariff->tariff_list_id);

            if (count($tasks) > 0) {
                $accounts = account::getActiveAccountsByUser($user->id);

                foreach ($accounts as $account) {
                    foreach ($tasks as $task) {
                        if ('direct' == $task->type) {
                            $taskListId = $task->id;
                            $directTask = DirectTask::getActiveDirectTaskByTaskListId($taskListId, $account->id);

                            if (is_null($directTask)) {
                                continue;
                            }

                            $preCommand = "cd /home/pata/projects/myinst";
                            $command = " && /usr/bin/php artisan direct:send " . $directTask->id . ' ' . $account->id;
                            $runInBackground = " > /dev/null 2>/dev/null &";
                            sleep(rand(1, 15));
                            exec($preCommand . $command . $runInBackground);
                        } else if ('unfollowing' == $task->type) {

                        }
                    }

                }
            }
        }
    }
}