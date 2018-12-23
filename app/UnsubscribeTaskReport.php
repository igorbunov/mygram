<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UnsubscribeTaskReport extends Model
{
    public static function writeStatistics(array $data)
    {
        $report = new UnsubscribeTaskReport();

        $report->unsubscribe_task_id = $data['unsubscribe_task_id'];
        $report->response = $data['response'];
        $report->success = $data['success'];
        $report->error_message = $data['error_message'];

        $report->save();
    }

    public static function getTodayUnsubscribesCount(int $unsubscribeTaskId): int
    {
        $res = DB::select("SELECT COUNT(1) AS cnt 
            FROM unsubscribe_task_reports
            WHERE unsubscribe_task_id = ? 
                AND DATE(created_at) = CURDATE()
            LIMIT 1", [$unsubscribeTaskId]);

        if (is_null($res) or count($res) == 0) {
            return 0;
        }

        return (int) $res[0]->cnt;
    }

    public static function getLastHourUnsubscribeCount(int $unsubscribeTaskId): int
    {
        $res = DB::select("SELECT COUNT(1) AS cnt
            FROM unsubscribe_task_reports r
            WHERE r.unsubscribe_task_id = ? 
                AND DATE(r.created_at) = CURDATE() 
                AND r.created_at > NOW() - INTERVAL 1 HOUR
            LIMIT 1", [$unsubscribeTaskId]);

        if (is_null($res) or count($res) == 0) {
            return 0;
        }

        return (int) $res[0]->cnt;
    }
}
