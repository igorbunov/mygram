<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    public function tariffList() {
        return $this->belongsTo('App\TariffList', 'tariff_list_id', 'id');
    }

    public function user() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public static function getUserCurrentTariff(int $userId)
    {
        return Tariff::where([
            'is_active' => 1
            , 'user_id' => $userId
        ])
        ->orderBy('id', 'DESC')
        ->first();
    }
}
