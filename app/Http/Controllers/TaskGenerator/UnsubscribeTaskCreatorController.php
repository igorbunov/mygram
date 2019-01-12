<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 23.12.18
 * Time: 11:20
 */
namespace App\Http\Controllers\TaskGenerator;

use App\FastTask;
use App\UnsubscribeTask;
use App\UnsubscribeTaskReport;
use Illuminate\Support\Facades\Log;

class UnsubscribeTaskCreatorController
{
    /*
     * Вызывает генератор массовой отписки
     */
    public static function tasksGenerator(array $accounts)
    {
        try {
            foreach ($accounts as $account) {
                $unsubscribeTasks = UnsubscribeTask::getUnsubscribeTasksByAccount($account, true);

                if (is_null($unsubscribeTasks) or count($unsubscribeTasks) == 0) {
                    continue;
                }

                if ($unsubscribeTasks[0]->status == UnsubscribeTask::STATUS_ACTIVE) {
                    self::generateUnsubscribeTask($unsubscribeTasks[0]);
                }
            }
        } catch (\Exception $err) {
            Log::error('error: ' . $err->getMessage());
        }
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