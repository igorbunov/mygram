<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
}
