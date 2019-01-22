<?php

namespace App;

use App\Http\Controllers\AccountController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Tariff extends Model
{
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

        $tariffList = TariffList::getTariffType($tariff);

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

    public static function notifyEndingTariffs()
    {
        sleep(5);

        $res = DB::select("SELECT DISTINCT
                t.user_id
                , DATE_FORMAT(t.dt_end, '%d.%m.%Y') AS end_dt
                , u.email
            FROM tariffs t
            INNER JOIN users u ON u.id = t.user_id AND u.is_confirmed = 1
            INNER JOIN accounts a ON a.user_id = u.id AND a.is_confirmed = 1 AND a.is_active = 1
            WHERE t.is_active = 1 AND DATE(t.dt_end) = CURDATE() + INTERVAL 5 DAY");

        if (is_null($res)) {
            return;
        }

        foreach ($res as $row) {
            AccountController::mailToUser($row->user_id,
                'Уведомление об окончании тарифа',
                'Срок действия вашего тарифа закончится в ' . $row->end_dt);

            FastTask::mailToDeveloper('Уведомление об окончании тарифа',
                '[' . $row->email . '] Срок действия вашего тарифа закончится через 5 дней (' . $row->end_dt . ')');
        }
    }
}
