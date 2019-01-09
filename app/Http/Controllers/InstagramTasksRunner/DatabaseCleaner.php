<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 09.01.19
 * Time: 20:09
 */

namespace App\Http\Controllers\InstagramTasksRunner;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseCleaner {
    const FAST_TASK_LIMIT_PER_ACCOUNT = 100;

    public static function clearFastTasks()
    {
        Log::debug('== Run db cleaner task == ');

        $res = DB::select("SELECT 
                account_id
                , COUNT(1) AS cnt
            FROM fast_tasks
            GROUP BY account_id
            HAVING cnt > ?", [self::FAST_TASK_LIMIT_PER_ACCOUNT]);

        if (is_null($res) or !is_array($res)) {
            return;
        }

        foreach($res as $row) {
            $limit = abs(self::FAST_TASK_LIMIT_PER_ACCOUNT - $row->cnt);

            if ($limit > 0) {
                DB::delete("DELETE FROM fast_tasks 
                WHERE account_id = :accountId AND `status` = 'executed' 
                ORDER BY id ASC 
                LIMIT :limit", [':accountId' => $row->account_id, ':limit' => $limit]);

                Log::debug("Removed {$limit} rows from fast_task for account_id = {$row->account_id}");
            }
        }

        Log::debug('== db cleaner task done == ');
    }
}