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

    public static function setProfilePictureUrl(int $accountId, $profileUrl)
    {
        $account = account::getAccountById($accountId);

        $user = self::find($account->user_id)->first();

        if (is_null($user->picture) or empty($user->picture)) {
            $user->picture = $profileUrl;
            $user->save();
        }
    }

    public static function getAccountPictureUrl(int $userId = 0)
    {
        if ($userId == 0) {
            $userId = (int) session('user_id', 0);
        }

        if ($userId == 0) {
            throw new \Exception('no user');
        }

        $user = self::find($userId)->first();

        return $user->picture;
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
