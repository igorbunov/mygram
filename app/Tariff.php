<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Tariff extends Model
{
    public function tariffList() {
        return $this->belongsTo('App\TariffList', 'tariff_list_id', 'id');
    }

    public function user() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public static function getUserCurrentTariff(int $userId = 0, bool $asArray = false)
    {
        if ($userId == 0) {
            $userId = (int) session('user_id');
        }

        $res = Tariff::where([
            'is_active' => 1
            , 'is_payed' => 1
            , 'user_id' => $userId
        ])
        ->orderBy('id', 'DESC')
        ->first();

        if (!$asArray) {
            return $res;
        }

        return (is_null($res)) ? null : $res->toArray();
    }

    public static function getUserCurrentTariffForMainView(int $userId = 0)
    {
        $tariff = self::getUserCurrentTariff($userId);

        if (is_null($tariff)) {
            return null;
        }

        $tariffList = $tariff->tariffList;

        return [
            'name' => $tariffList->name,
            'accounts_count' => $tariff->accounts_count,
            'dt_end' => date("d.m.Y", strtotime($tariff->dt_end)),
            'tariff_list_id' => $tariff->tariff_list_id
        ];
    }

    public static function tariffTick()
    {
        DB::update("UPDATE tariffs SET is_active = 0 WHERE is_active = 1 AND DATE(dt_end) < CURDATE()");
    }
}
