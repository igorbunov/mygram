<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class DirectTask extends Model
{
    public function taskList() {
        return $this->belongsTo('App\TaskList', 'task_list_id', 'id');
    }

    public function account() {
        return $this->belongsTo('App\account', 'account_id', 'id');
    }

    public function taskReports() {
        return $this->hasMany('App\DirectTaskReport', 'direct_task_id', 'id');
    }

    public static function getActiveDirectTaskById(int $taskId, int $accountId, bool $asArray = false)
    {
        $res = self::where([
            'account_id' => $accountId,
            'is_active' => 1,
            'id' => $taskId
        ])->first();

        if (!$asArray) {
            return $res;
        }

        return (is_null($res)) ? null : $res->toArray();
    }

    public static function getActiveDirectTaskByTaskListId(int $taskListId, int $accountId, bool $asArray = false)
    {
        $res = self::where([
            'account_id' => $accountId,
            'is_active' => 1,
            'task_list_id' => $taskListId
        ])->first();

        if (!$asArray) {
            return $res;
        }

        return (is_null($res)) ? null : $res->toArray();
    }
}
