<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
}
