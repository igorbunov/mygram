<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskList extends Model
{
    public function directTasks() {
        return $this->hasMany('App\DirectTask', 'task_list_id', 'id');
    }

    public function tariffList() {
        return $this->hasMany('App\TariffList', 'tariff_list_id', 'id');
    }
}
