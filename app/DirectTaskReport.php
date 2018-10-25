<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DirectTaskReport extends Model
{
    public function directTask() {
        return $this->belongsTo('App\DirectTask', 'direct_task_id', 'id');
    }
}
