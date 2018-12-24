<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountSubscriptions extends Model
{
    public static function isTaskDone(int $accountId)
    {
        $res = DB::select("SELECT COUNT(1) AS cnt
            FROM account_subscriptions 
            WHERE owner_account_id = ? 
                AND is_unsubscribed = 0 
                AND is_in_safelist = 0", [$accountId]);

        if (is_null($res) or count($res) == 0) {
            return true;
        }

        return ($res[0]->cnt == 0);
    }

    public static function getStatistics(int $accountId)
    {
        $res = DB::select("SELECT COUNT(1) AS total
                , SUM(is_in_safelist) AS selected
                , SUM(is_unsubscribed) AS unsubscribed 
            FROM account_subscriptions WHERE owner_account_id = ?", [$accountId]);

        if (is_null($res) or count($res) == 0) {
            return $res;
        }

        return $res[0];
    }

    public static function getNotUnsubscribedFollowers(int $accountId)
    {
        $filter = [
            'owner_account_id' => $accountId,
            'is_unsubscribed' => 0,
            'is_in_safelist' => 0
        ];

        $res = self::where($filter)->get();

        return $res;
    }

    public static function isUnsubscribed(int $followerId)
    {
        $res = self::find($followerId);

        if (is_null($res)) {
            Log::error('не смог найти подписку по айди: ' . $followerId);
            return true;
        }

        return ($res->is_unsubscribed == 1);
    }

    public static function setUnsubscribed(int $followerId, bool $isUnsubscribed = true)
    {
        $res = self::find($followerId);

        if (is_null($res)) {
            Log::error('не смог найти субскрайбера по айди: ' . $followerId);
            return;
        }

        $res->is_unsubscribed = ($isUnsubscribed) ? 1 : 0;

        $res->save();
    }

    public static function clearSafelist(int $accountId)
    {
        self::where(['owner_account_id' => $accountId])->delete();
    }

    public static function setIsInSafelist(int $accountId, string $nickname, int $isChecked)
    {
        $res = self::where([
            'owner_account_id' => $accountId,
            'username' => $nickname
        ])->first();

        if (is_null($res)) {
            return false;
        }

        $res->is_in_safelist = $isChecked;
        $res->save();

        return true;
    }

    public static function getAll(int $accountId, bool $isAll, bool $asArray = false, int $start = 0, int $limit = 50)
    {
        $filter = [
            'owner_account_id' => $accountId,
            'is_unsubscribed' => 0
        ];

        if (!$isAll) {
            $filter['is_in_safelist'] = 1;
        }

        $res = self::select(array(DB::raw('SQL_CALC_FOUND_ROWS *')))
            ->where($filter)
            ->offset($start)
            ->limit($limit)
            ->get();

        $total = DB::selectOne(DB::raw("SELECT FOUND_ROWS() AS total"))->total;

        if (!$asArray) {
            return is_null($res) ? ['data' => null, 'total' => 0] : ['data' => $res, 'total' => $total];
        }

        return is_null($res) ? ['data' => null, 'total' => 0] : ['data' => $res->toArray(), 'total' => $total];
    }

    public static function getSelected(int $accountId)
    {
        $res = self::where([
            'owner_account_id' => $accountId,
            'is_in_safelist' => 1
        ])->get();

        return $res;
    }

    public static function addUniqueArray(array $followings)
    {
        foreach ($followings as $follower) {
            self::addUnique($follower);
        }
    }

    public static function addUnique($data)
    {
        try {
            $subs = new AccountSubscriptions();

            $subs->owner_account_id = $data['owner_account_id'];
            $subs->username =  $data['username'];
            $subs->pk = $data['pk'];
            $subs->json = $data['json'];
            $subs->is_my_subscriber = $data['is_my_subscriber'];
            $subs->is_in_safelist = $data['is_in_safelist'];
            $subs->picture = $data['picture'];

            $subs->save();
        } catch (\Exception $err) {
//            Log::error('dublicate key: '.$data['pk']);
        }
    }
}
