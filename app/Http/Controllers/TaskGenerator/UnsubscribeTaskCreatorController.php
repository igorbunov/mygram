<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 23.12.18
 * Time: 11:20
 */
namespace App\Http\Controllers\TaskGenerator;

use App\account;
use App\AccountSubscriptions;
use App\DirectTask;
use App\DirectTaskReport;
use App\FastTask;
use App\Tariff;
use App\TaskList;
use App\UnsubscribeTask;
use App\UnsubscribeTaskReport;
use App\User;
use Illuminate\Support\Facades\Log;

class UnsubscribeTaskCreatorController
{
    /*
     * Вызывает генератор массовой отписки
     */
    public static function tasksGenerator()
    {
        if (FastTask::isNight()) {
            return false;
        }
//        Log::debug('generate tasks');
        $users = User::where(['is_confirmed' => 1])->get();

//        Log::debug('found users: ' . count($users));

        foreach ($users as $user) {
            $tariff = Tariff::getUserCurrentTariff($user->id);

            if (is_null($tariff)) {
//                Log::debug('tariff is not valid for user: ' . $user->id);
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
                        if (TaskList::TYPE_UNSUBSCRIBE == $taskType->type) {
//                            Log::debug("generate unsubscribe task");

                            $taskListId = $taskType->id;
                            $unsubscribeTask = UnsubscribeTask::getActiveUnsubscribeTaskByTaskListId($taskListId, $account->id, true);

                            if (is_null($unsubscribeTask)) {
//                                Log::debug('No unsubscribe tasks found ' . $taskListId . ' ' . $account->id);
                                continue;
                            }

                            if ($unsubscribeTask->status == UnsubscribeTask::STATUS_ACTIVE) {
                                // run generate unsubscribe task
                                if (self::generateUnsubscribeTask($unsubscribeTask)) {
//                                    Log::debug("unsubscribe task added to fast tasks: " . $unsubscribeTask->id);
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $err) {
                Log::error('error: ' . $err->getMessage());
            }
        }

//        Log::debug('generate tasks end');
    }

    private static function generateUnsubscribeTask(UnsubscribeTask $unsubscribeTask): bool
    {
        if (is_null($unsubscribeTask)) {
            return false;
        }

        if (FastTask::isNight()) {
            return false;
        }

        $todayUnsubscribeCount = UnsubscribeTaskReport::getTodayUnsubscribesCount($unsubscribeTask->id);

        if ($todayUnsubscribeCount >= env('UNSUBSCRIBES_LIMITS_BY_DAY', 700)) {
            return false;
        }

        if (!FastTask::isCanRun($unsubscribeTask->account_id, FastTask::TYPE_UNSUBSCRIBE)) {
            return false;
        }

        $lastTask = FastTask::getLastTask($unsubscribeTask->account_id, FastTask::TYPE_UNSUBSCRIBE);
        $lastDelay = 0;

        if (!is_null($lastTask)) {
            $lastDelay = $lastTask->delay;
        }

        $lastHourDirectCount = UnsubscribeTaskReport::getLastHourUnsubscribeCount($unsubscribeTask->id);

        if ($lastHourDirectCount >= env('UNSUBSCRIBES_LIMITS_BY_HOUR', 50)) {
            return false;
        }

        for($i = 0; $i < 20; $i++) {
            $randomDelayMinutes = rand(env('UNSUBSCRIBE_DELAY_MIN_SLEEP', '3'), env('UNSUBSCRIBE_DELAY_MAX_SLEEP', '5'));

            if ($lastDelay != $randomDelayMinutes) {
                break;
            }
        }

        //TODO:
        if (!FastTask::isHadRestInLastOneAndHalfHoursUnsubscribeTasks($unsubscribeTask->account_id)) {
            $randomDelayMinutes = rand(20, 30);
        }

//        Log::debug('Delay time (minutes): ' . $randomDelayMinutes);

        FastTask::addTask($unsubscribeTask->account_id,
            FastTask::TYPE_UNSUBSCRIBE,
            $unsubscribeTask->id,
            $randomDelayMinutes);

        return true;
    }
}