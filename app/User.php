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

    public static function getAccountsByUser(int $userId, bool $activeOnly = false, bool $asArray = false)
    {
        $filter = [
            'user_id' => $userId
        ];

        if ($activeOnly) {
            $filter['is_active'] = 1;
        }
        
        $res = account::where($filter)->get();

        if (!$asArray) {
            return $res;
        }

        $result = [];

        foreach ($res as $row) {
            $result[] = $row->toArray();
        }

        return $result;
    }
}
