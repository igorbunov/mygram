<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public function accounts()
    {
        return $this->hasMany('App\account', 'user_id', 'id');
    }

    public function tariffs() {
        return $this->hasMany('App\Tariff', 'user_id', 'id');
    }
}
