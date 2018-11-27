<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FastTask extends Model
{
    const STATUS_SAVED = 'saved';
    const STATUS_IN_PROCESS = 'in_process';
    const STATUS_EXECUTED = 'executed';

    const TYPE_TRY_LOGIN = 'try_login';
    const TYPE_REFRESH_ACCOUNT = 'refresh_account';

    public static function addTask(int $accountId, string $taskType)
    {
        $task = new FastTask();
        $task->task_type = $taskType;
        $task->account_id = $accountId;
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
            ->get();

        return $res;
    }
}
