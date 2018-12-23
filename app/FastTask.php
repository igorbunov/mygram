<?php

namespace App;

use App\Http\Controllers\AccountController;
use App\Http\Controllers\InstagramTasksRunner\AccountFirstLoginRunner;
use App\Http\Controllers\InstagramTasksRunner\AccountSubscribersRunner;
use App\Http\Controllers\InstagramTasksRunner\AccountWhiteListRunner;
use App\Http\Controllers\InstagramTasksRunner\DirectToSubsTasksRunner;
use App\Http\Controllers\InstagramTasksRunner\UnsubscribeTaskRunner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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
    const TYPE_GET_NEW_SUBSCRIBERS = 'get_new_subscribers';

    public static function isHadRestInLastOneAndHalfHoursDirectTasks(int $accountId): bool
    {
        $res = DB::select("SELECT
                IFNULL(SUM(IF(delay > 30, 1, 0)), 0) AS is_rest
                , COUNT(1) as cnt
            FROM fast_tasks
            WHERE task_type = :type AND account_id = :accountId
                AND updated_at > (NOW() - INTERVAL 90 MINUTE)
            ORDER BY id DESC", [':type' => self::TYPE_DIRECT_ANSWER, ':accountId' => $accountId]);

        if (is_null($res) or count($res) == 0) {
            Log::debug('is had rest: zero');
            return true;
        }

        $row = $res[0];

        Log::debug('res' . \json_encode($row));

        if ($row->cnt >= (env('FRIEND_DIRECT_LIMITS_BY_HOUR', '10') - 1) ) {
            if ($row->cnt > env('FRIEND_DIRECT_LIMITS_BY_HOUR', '10')) {
                return false;
            }

            return ($row->is_rest > 0);
        }

        return true;
    }

    public static function isCanRun(int $accountId, string $taskType)
    {
        $lastTask = self::getLastTask($accountId, $taskType);

        if (is_null($lastTask)) {
            return true;
        }

        if ($lastTask->status == self::STATUS_SAVED or $lastTask->status == self::STATUS_IN_PROCESS) {
            return false;
        }

        if ($lastTask->delay * 1 == -1) {
            return true;
        }

        return $lastTask->need_run * 1 > 0;
    }

    public static function getLastTask(int $accountId, string $taskType)
    {
        $res = self::select([DB::raw("*, IF((updated_at + INTERVAL delay MINUTE) < NOW(), 1, 0) AS need_run")])
            ->where(['account_id' => $accountId, 'task_type' => $taskType])
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->first();

        return $res;
    }

    public static function addTask(int $accountId, string $taskType, int $taskId = 0, int $delayMinutes = -1)
    {
        $task = new FastTask();
        $task->task_type = $taskType;
        $task->account_id = $accountId;
        $task->task_id = $taskId;
        $task->delay = $delayMinutes;
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

        if (is_null($task)) {
            return;
        }

        self::setStatus($task->id, FastTask::STATUS_IN_PROCESS);
        Log::debug('Found fast task # ' . $task->id . ' ' .$task->task_type);
        DirectTaskReport::getNow();

        switch ($task->task_type) {
            case self::TYPE_TRY_LOGIN:
                try {
                    AccountFirstLoginRunner::tryLogin($task->account_id);
                } catch (\Exception $err) {
                    $errorMessage = $err->getMessage();

                    Log::error('Error running task AccountFirstLoginRunner::tryLogin: ' . $errorMessage );

                    self::mailToDeveloper('ошибка выполнения задачи tryLogin', $errorMessage);
                } finally {
                    FastTask::setStatus($task->id, FastTask::STATUS_EXECUTED);
                }

                break;
            case self::TYPE_DIRECT_ANSWER:
                try {
                    DirectToSubsTasksRunner::runDirectTasks($task->task_id, $task->account_id);
                } catch (\Exception $err) {
                    $errorMessage = $err->getMessage();

                    if (strpos($errorMessage, 'Feedback required') !== false) {
                        $direct = DirectTask::getDirectTaskById($task->task_id, $task->account_id, false);

                        if (!is_null($direct)) {
                            $direct->status = DirectTask::STATUS_PAUSED;
                            $direct->save();

                            AccountController::mailToClient($task->account_id, 'Ошибка директ автоответа', 'При попытке отправить директ сообщение возникла ошибка. Задача рассылки автоматически приостановлена.');
                        }
                    }

                    Log::error('Error running task DirectToSubsTasksRunner::runDirectTasks: ' . $errorMessage . ' ' . $err->getTraceAsString());

                    self::mailToDeveloper('ошибка выполнения задачи runDirectTasks', $errorMessage);
                } finally {
                    FastTask::setStatus($task->id, FastTask::STATUS_EXECUTED);
                }

                break;
            case self::TYPE_REFRESH_ACCOUNT:
                try {
                    AccountFirstLoginRunner::runRefresh($task->account_id);
                } catch (\Exception $err) {
                    $errorMessage = $err->getMessage();

                    Log::error('Error running task AccountFirstLoginRunner::runRefresh: ' . $errorMessage);

                    self::mailToDeveloper('ошибка выполнения задачи runRefresh', $errorMessage);
                } finally {
                    FastTask::setStatus($task->id, FastTask::STATUS_EXECUTED);
                }

                break;
            case self::TYPE_REFRESH_WHITELIST:
                try {
                    AccountWhiteListRunner::runRefresh($task->task_id);
                } catch (\Exception $err) {
                    $errorMessage = $err->getMessage();

                    Log::error('Error running task AccountWhiteListRunner::refreshWhitelist: ' . $errorMessage);

                    self::mailToDeveloper('ошибка выполнения задачи refreshWhitelist', $errorMessage);
                } finally {
                    FastTask::setStatus($task->id, FastTask::STATUS_EXECUTED);
                }

                break;
            case self::TYPE_GET_NEW_SUBSCRIBERS:
                try {
                    AccountSubscribersRunner::runGetSubscribers($task->account_id);
                } catch (\Exception $err) {
                    $errorMessage = $err->getMessage();

                    Log::error('Error running task get new subscribers: ' . $errorMessage);

                    self::mailToDeveloper('ошибка выполнения задачи subscribers', $errorMessage);
                } finally {
                    FastTask::setStatus($task->id, FastTask::STATUS_EXECUTED);
                }

                break;
            case self::TYPE_UNSUBSCRIBE:
                try {
                    UnsubscribeTaskRunner::runUnsubscribeTasks($task->task_id, $task->account_id);
                } catch (\Exception $err) {
                    $errorMessage = $err->getMessage();

                    Log::error('Error running task unsubscribe: ' . $errorMessage);

                    self::mailToDeveloper('ошибка выполнения задачи unsubscribe', $errorMessage);
                } finally {
                    FastTask::setStatus($task->id, FastTask::STATUS_EXECUTED);
                }

                break;
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
//        $isNightText = ($isNight) ? 'yes' : 'no';
//        Log::debug('isNight: ' . $isNightText . ' curHour: ' . $curHour .
//            ' NIGHT_TIME_START_HOUR: ' . $nightStartTime . ' NIGHT_TIME_END_HOUR: ' . $nightEndTime);

        return $isNight;
    }

    public static function mailToDeveloper($subject, $message)
    {
        \mail(env('DEVELOPER_EMAIL'), $subject, $message);
    }
}
