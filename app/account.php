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

    public static function getAccountById(int $accountId, bool $asArray = false)
    {
        $res = self::where([
            'id' => $accountId,
            'is_active' => 1
        ])->first();

        if (!$asArray) {
            return $res;
        }

        return (is_null($res)) ? null: $res->toArray();
    }
}
