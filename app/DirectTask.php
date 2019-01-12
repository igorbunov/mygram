<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DirectTask extends Model
{
    const STATUS_ACTIVE = 'active';
    const STATUS_DEACTIVATED = 'deactivated';
    const STATUS_PAUSED = 'paused';

    public static function isValidStatus($status)
    {
        return in_array($status, [self::STATUS_ACTIVE, self::STATUS_DEACTIVATED, self::STATUS_PAUSED]);
    }

    public static function getActiveTasksCountByAccountId(int $accountId)
    {
        $filter = [
            'account_id' => $accountId,
            'status' => self::STATUS_ACTIVE
        ];

        $res = self::where($filter)->get();

        return count($res);
    }

    public static function getDirectTaskById(int $taskId, int $accountId, bool $onlyActive = true, bool $asArray = false)
    {
        $filter = [
            'id' => $taskId,
            'account_id' => $accountId
        ];

        if ($onlyActive) {
            $filter['status'] = self::STATUS_ACTIVE;
        }

        $res = self::where($filter)->first();

        if (!$asArray) {
            return $res;
        }

        return (is_null($res)) ? null : $res->toArray();
    }


    private static function getActiveAndPausedDirectTasksForAccount(account $account)
    {
        $filter = [
            'account_id' => $account->id
        ];

        $res = self::where($filter)->whereIn('status', array(self::STATUS_ACTIVE, self::STATUS_PAUSED))->get();

        return $res;
    }
    private static function getAllDirectTasksForAccount(account $account)
    {
        $filter = [
            'account_id' => $account->id
        ];

        $res = self::where($filter)->get();

        return $res;
    }

    public static function getDirectTasksByForAccount(account $account, bool $activeAndPaused = false)
    {
        if ($activeAndPaused) {
            return self::getActiveAndPausedDirectTasksForAccount($account);
        }

        return self::getAllDirectTasksForAccount($account);
    }

    public static function updateStatistics(int $directTaskId)
    {
        $res = DB::select("SELECT COUNT(1) AS total
                , SUM(IF(success = 1, 1, 0)) AS success
                , SUM(IF(success = 0, 1, 0)) AS failures
            FROM direct_task_reports 
            WHERE direct_task_id = ?
            LIMIT 1"
        , [$directTaskId]);

        if (is_null($res)) {
            return;
        }

        $task = self::find($directTaskId);

        if (is_null($task)) {
            return;
        }

        $task->total_messages = $res[0]->total;
        $task->success_count = $res[0]->success;
        $task->failure_count = $res[0]->failures;

        $task->save();
    }
}
