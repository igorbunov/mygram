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

    public static function getActiveAccountsByUser(int $userId, bool $asArray = false)
    {
        $res = self::where([
            'user_id' => $userId,
            'is_active' => 1
        ])->get();

        if (!$asArray) {
            return $res;
        }

        $result = [];

        foreach ($res as $row) {
            $result[] = $row->toArray();
        }

        return $result;
    }

    public static function getAccountById(int $accountId, bool $onlyActive = true, bool $asArray = false)
    {
        $filter = [
            'id' => $accountId
        ];

        if ($onlyActive) {
            $filter['is_active'] = 1;
        }

        $res = self::where($filter)->first();

        if (!$asArray) {
            return $res;
        }

        return (is_null($res)) ? null: $res->toArray();
    }

    public static function isAccountBelongsToUser(int $userId, int $accountId)
    {
        $res = self::where([
            'id' => $accountId,
            'user_id' => $userId
        ])->get();

        return (count($res) > 0);
    }

    public static function changeStatus(int $accountId, int $isActive)
    {
//        dd($accountId);
        $acc = self::getAccountById($accountId, false);
//        dd($acc);
        $acc->is_active = $isActive;
        $acc->save();
    }
}
