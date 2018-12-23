<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UnsubscribeTask extends Model
{
    const STATUS_ACTIVE = 'active';
    const STATUS_DEACTIVATED = 'deactivated';
    const STATUS_PAUSED = 'paused';

    public static function getUnsubscribeTaskById(int $taskId, int $accountId, bool $onlyActive = true, bool $asArray = false)
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

    public static function getActiveUnsubscribeTaskByTaskListId(int $taskListId, int $accountId, bool $activeAndPaused = false, bool $asArray = false)
    {
        $res = null;
        $filter = [
            'account_id' => $accountId,
            'task_list_id' => $taskListId
        ];

        if ($activeAndPaused) {
            $res = self::where($filter)->whereIn('status', array(self::STATUS_ACTIVE, self::STATUS_PAUSED))->first();
        } else {
            $filter['status'] = self::STATUS_ACTIVE;

            $res = self::where($filter)->first();
        }

        if (!$asArray) {
            return $res;
        }

        return (is_null($res)) ? null : $res->toArray();
    }

    public static function updateStatistics(int $directTaskId)
    {
        $res = DB::select("SELECT COUNT(1) AS total
                , SUM(IF(success = 1, 1, 0)) AS success
                , SUM(IF(success = 0, 1, 0)) AS failures
            FROM unsubscribe_task_reports 
            WHERE unsubscribe_task_id = ?
            LIMIT 1"
            , [$directTaskId]);

        if (is_null($res)) {
            return;
        }

        $task = self::find($directTaskId);

        if (is_null($task)) {
            return;
        }

        $task->total = $res[0]->total;
        $task->success_count = $res[0]->success;
        $task->failure_count = $res[0]->failures;

        $task->save();
    }
}
