<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TariffList extends Model
{
    const TYPE_DIRECT = 'direct';
    const TYPE_UNSUBSCRIBE = 'unsubscribe';
    const TYPE_CHATBOT = 'chatbot';

    public static function getListByTariff(Tariff $tariff)
    {
        return self::find($tariff->tariff_list_id);
    }

    public static function getAvaliableTypes(Tariff $tariff)
    {
        $res = TariffList::getListByTariff($tariff);

        return explode(',', $res->description);
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
