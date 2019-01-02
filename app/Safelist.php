<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Safelist extends Model
{
    const STATUS_EMPTY = 'empty';
    const STATUS_UPDATING = 'updating';
    const STATUS_SYNCHRONIZED = 'synchronized';

    public static function getSafelistForAllAccounts(int $userId)
    {
        $accounts = account::getActiveAccountsByUser($userId);
        $result = [];

        if (is_null($accounts) or count($accounts) == 0) {
            return $result;
        }

        $accountIds = [];

        foreach ($accounts as $account) {
            $accountIds[] = (int) $account->id;
        }

        $accountIds = implode(',', $accountIds);

        $res = DB::select("SELECT * 
            FROM account_subscriptions 
            WHERE owner_account_id IN ({$accountIds}) AND is_in_safelist = 1");

        foreach ($res as $row) {
            $result[$row->pk] = $row;
        }

        return $result;
    }

    public static function updateStatistics(int $safelistId)
    {
        $res = self::find($safelistId);

        if (is_null($res)) {
            return;
        }

        $selected = AccountSubscriptions::getSelected($res->account_id);

        $res->selected_accounts = count($selected);
        $res->save();
    }

    public static function getOrCreate(int $accountId, bool $asArray = false)
    {
        $res = self::where([
            'account_id' => $accountId
        ])->first();

        if (is_null($res)) {
            $res = new Safelist();
            $res->account_id = $accountId;
            $res->total_subscriptions = 0;
            $res->selected_accounts = 0;
            $res->status = self::STATUS_EMPTY;
            $res->save();

            if (!$asArray) {
                return $res;
            }

            return $res->toArray();
        }

        if (!$asArray) {
            return $res;
        }

        return is_null($res) ? $res : $res->toArray();
    }

    public static function clearAll(int $accountId)
    {
        self::where([
            'account_id' => $accountId
        ])->delete();
    }

    public static function getById(int $id, bool $asArray = false)
    {
        $res = self::find($id);

        if (!$asArray) {
            return $res;
        }

        return is_null($res) ? $res : $res->toArray();
    }

    public static function getByAccountId(int $accountId, bool $asArray = false)
    {
        $res = self::where([
            'account_id' => $accountId
        ])->first();

        if (!$asArray) {
            return $res;
        }

        return is_null($res) ? $res : $res->toArray();
    }

    public static function setStatus(int $id, string $status)
    {
        $res = self::find($id);

        if (is_null($res)) {
            return false;
        }

        $res->status = $status;
        $res->save();
    }

    public static function updateSafelist(int $id, int $totalSubscriptions, int $selectedAccounts, string $status)
    {
        $res = self::find($id);

        Log::debug('update safelist', [$id,  $totalSubscriptions,  $selectedAccounts,  $status]);

        if (is_null($res)) {
            return false;
        }

        $res->total_subscriptions = $totalSubscriptions;
        $res->selected_accounts = $selectedAccounts;
        $res->status = $status;
        $res->save();
    }
}
