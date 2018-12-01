<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class User extends Model
{
    public function accounts()
    {
        return $this->hasMany('App\account', 'user_id', 'id');
    }

    public function tariffs() {
        return $this->hasMany('App\Tariff', 'user_id', 'id');
    }

    public static function getAccountPictureUrl(int $userId = 0)
    {
        if ($userId == 0) {
            $userId = (int) session('user_id', 0);
        }

        if ($userId == 0) {
            throw new \Exception('no user');
        }

        $accounts = account::getActiveAccountsByUser($userId);

        if (!is_null($accounts)) {
            foreach ($accounts as $account) {
                if (!is_null($account->picture) and !empty($account->picture)) {
                    return $account->picture;
                }
            }
        }

        return '';
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
