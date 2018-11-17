<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TariffList extends Model
{
    public function tariff() {
        return $this->hasMany('App\Tariff', 'tariff_list_id', 'id');
    }

    public static function getActiveTariffList(bool $asArray = false)
    {
        $res = self::where([
            'is_active' => '1'
        ])->get();

        if ($asArray) {
            $result = [];

            foreach ($res as $row) {
                $result[] = $row->toArray();
            }

            return $result;
        }

        return $res;
    }
}
