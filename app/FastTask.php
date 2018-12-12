<?php

namespace App;

use App\Http\Controllers\InstagramTasksRunner\AccountFirstLoginRunner;
use App\Http\Controllers\InstagramTasksRunner\AccountWhiteListRunner;
use App\Http\Controllers\InstagramTasksRunner\DirectToSubsTasksRunner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class FastTask extends Model
{
    const STATUS_SAVED = 'saved';
    const STATUS_IN_PROCESS = 'in_process';
    const STATUS_EXECUTED = 'executed';

    const TYPE_DIRECT_ANSWER = 'direct_answer';
    const TYPE_TRY_LOGIN = 'try_login';
    const TYPE_REFRESH_ACCOUNT = 'refresh_account';
    const TYPE_UNSUBSCRIBE = 'unsubscribe';
    const TYPE_REFRESH_WHITELIST = 'refresh_whitelist';

    public static function addTask(int $accountId, string $taskType, int $taskId = 0)
    {
        $task = new FastTask();
        $task->task_type = $taskType;
        $task->account_id = $accountId;
        $task->task_id = $taskId;
        $task->save();

        return $task->id;
    }

    public static function setStatus(int $taskId, string $status)
    {
        $res = self::find($taskId);

        $res->status = $status;

        $res->save();
    }

    public static function getTask()
    {
        $res = self::where([
            'status' => self::STATUS_SAVED
        ])->orderBy('id', 'ASC')
            ->limit(1)
            ->first();

        return $res;
    }

    public static function runTask()
    {
        $task = self::getTask();

        if (!is_null($task)) {
            self::setStatus($task->id, FastTask::STATUS_IN_PROCESS);
            Log::debug('Found fast task # ' . $task->id . ' ' .$task->task_type);
            DirectTaskReport::getNow();

            switch ($task->task_type) {
                case self::TYPE_TRY_LOGIN:
                    try {
                        AccountFirstLoginRunner::tryLogin($task->account_id);
                    } catch (\Exception $err) {
                        Log::error('Error running task AccountFirstLoginRunner::tryLogin: ' . $err->getMessage());
                    }

                    FastTask::setStatus($task->id, FastTask::STATUS_EXECUTED);

                    break;
                case self::TYPE_DIRECT_ANSWER:
                    try {
                        DirectToSubsTasksRunner::runDirectTasks($task->task_id, $task->account_id);
                    } catch (\Exception $err) {
                        Log::error('Error running task AccountFirstLoginRunner::tryLogin: ' . $err->getMessage());
                    }

                    FastTask::setStatus($task->id, FastTask::STATUS_EXECUTED);

                    break;
                case self::TYPE_REFRESH_ACCOUNT:
                    try {
                        AccountFirstLoginRunner::runRefresh($task->account_id);
                    } catch (\Exception $err) {
                        Log::error('Error running task AccountFirstLoginRunner::runRefresh: ' . $err->getMessage());
                    }

                    FastTask::setStatus($task->id, FastTask::STATUS_EXECUTED);

                    break;
                case self::TYPE_REFRESH_WHITELIST:
                    try {
                        AccountWhiteListRunner::runRefresh($task->id);
                    } catch (\Exception $err) {
                        Log::error('Error running task AccountWhiteListRunner::runRefresh: ' . $err->getMessage());
                    }

                    FastTask::setStatus($task->id, FastTask::STATUS_EXECUTED);

                    break;
            }
        }
    }

    public static function checkStatus(int $taskId)
    {
        $res = self::find($taskId);

        if (is_null($res)) {
            return false;
        }

        return $res->status;
    }

    public static function isNight()
    {
        date_default_timezone_set('Europe/Kiev');

        $nightStartTime = env('NIGHT_TIME_START_HOUR', '23');
        $nightEndTime = env('NIGHT_TIME_END_HOUR', '5');

        $curHour = (int) date("H");

        $isNight = (($nightStartTime >= 21 and $curHour >= $nightStartTime) or $curHour <= $nightEndTime);
        $isNightText = ($isNight) ? 'yes' : 'no';
        Log::debug('isNight: ' . $isNightText . ' curHour: ' . $curHour .
            ' NIGHT_TIME_START_HOUR: ' . $nightStartTime . ' NIGHT_TIME_END_HOUR: ' . $nightEndTime);

        return $isNight;
    }
}
