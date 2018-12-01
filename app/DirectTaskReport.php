<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DirectTaskReport extends Model
{
    public function directTask() {
        return $this->belongsTo('App\DirectTask', 'direct_task_id', 'id');
    }

    public static function writeStatistics(array $data)
    {
        $report = new DirectTaskReport();

        $report->direct_task_id = $data['direct_task_id'];
        $report->response = $data['response'];
        $report->success = $data['success'];
        $report->error_message = $data['error_message'];

        $report->save();
    }

    public static function getTodayFriendDirectMessagesCount(int $directTaskId): int
    {
        $res = DB::select("SELECT COUNT(1) AS cnt 
            FROM direct_task_reports
            WHERE direct_task_id = ? 
                AND DATE(created_at) = CURDATE()
            LIMIT 1", [$directTaskId]);

        if (is_null($res) or count($res) == 0) {
            return 0;
        }

        return (int) $res[0]->cnt;
    }

    public static function getLastHourFriendDirectMessagesCount(int $directTaskId): int
    {
        $res = DB::select("SELECT COUNT(1) AS cnt
            FROM direct_task_reports r
            WHERE r.direct_task_id = ? 
                AND DATE(r.created_at) = CURDATE() 
                AND r.created_at > NOW() - INTERVAL 1 HOUR
            LIMIT 1", [$directTaskId]);

        if (is_null($res) or count($res) == 0) {
            return 0;
        }

        return (int) $res[0]->cnt;
    }
}
