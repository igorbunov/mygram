<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class account extends Model
{
    public function user() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function directTasks() {
        return $this->hasMany('App\DirectTask', 'account_id', 'id');
    }

//    public function unsubscribeTasks() {
//        return $this->hasMany('App\UnsubscribeTask', 'account_id', 'id');
//    }
}
