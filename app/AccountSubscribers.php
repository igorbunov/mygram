<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountSubscribers extends Model
{
    public static function getAllAccountsUsersPKS(int $userId)
    {
        $res = DB::select("SELECT DISTINCT pk 
            FROM account_subscribers 
            WHERE owner_account_id IN (SELECT id FROM accounts WHERE user_id = ?)", [$userId]);

        if (is_null($res)) {
            return [];
        }

        $result = [];

        foreach($res as $i => $row) {
            $result[$row->pk] = 1;
        }

        Log::debug('getAllAccountsUsersPKS : ' . \json_encode($result));

        return $result;
    }

    public static function clearQueue(int $accountId)
    {
        self::where([
            'owner_account_id' => $accountId,
            'is_sended' => 0
        ])->delete();
    }

    public static function getSendedByPk(int $userId, int $pk)
    {
        return DB::selectOne("SELECT s.*
            FROM account_subscribers s
            INNER JOIN accounts a ON a.id = s.owner_account_id
            WHERE a.user_id = ? AND s.pk = ?
            LIMIT 1", [$userId, $pk]);
    }

    public static function isSendedByPk(int $userId, int $pk)
    {
        $res = (int) DB::selectOne("SELECT COUNT(1) as cnt
            FROM account_subscribers s
            INNER JOIN accounts a ON a.id = s.owner_account_id
            WHERE a.user_id = ? AND s.pk = ?
            LIMIT 1", [$userId, $pk])->cnt;

        return ($res > 0);
    }

    public static function isSended(int $followerId)
    {
        $res = self::find($followerId);

        if (is_null($res)) {
            return false;
        }

        return ($res->is_sended == 1);
    }

    public static function setSended(int $followerId, bool $isSended = true)
    {
        $res = self::find($followerId);

        if (is_null($res)) {
            Log::error('не смог найти субскрайбера по айди: ' . $followerId);
            return;
        }

        $res->is_sended = ($isSended) ? 1 : 0;

        $res->save();
    }

    public static function getUnsendedFollowers(int $accountId, int $limit = -1)
    {
        $followers = null;
        $filter = [
            'owner_account_id' => $accountId,
            'is_sended' => 0
        ];

        if ($limit == -1) {
            $followers = self::where($filter)->get();
        } else {
            $followers = self::where($filter)->orderBy('id', 'ASC')->limit($limit)->get();
        }

        if (is_null($followers)) {
            return [];
        }

        return $followers;
    }

    public static function addUniqueArray(array $followers)
    {
        foreach ($followers as $follower) {
            AccountSubscribers::addUnique($follower);
        }
    }

    public static function getCurrentFollowersCount(int $accountId)
    {
        $followers = self::where([
            'owner_account_id' => $accountId
        ])->get();

        if (is_null($followers)) {
            return 0;
        }

        return count($followers);
    }

    public static function addUnique($data)
    {
        // TODO: решить как доставать новых подпищиков
        
        try {
            $subs = new AccountSubscribers();

            $subs->owner_account_id = $data['owner_account_id'];
            $subs->username =  $data['username'];
            $subs->pk = $data['pk'];
            $subs->json = $data['json'];
            $subs->is_sended = isset($data['is_sended']) ? (int) $data['is_sended'] : 0;

            $subs->save();
        } catch (\Exception $err) {
//            Log::error('dublicate key: '.$data['pk']);
        }
    }

    public static function getNewFollowers(int $accountId, array $newFollowersArr): array
    {
        $followers = self::where([
            'owner_account_id' => $accountId
        ])->get();

        if (is_null($followers)) {
            return [];
        }

        $newFollowers = [];

        foreach ($newFollowersArr as $i => $newFollower) {
            if ($i == 130) {
                break;
            }

            $isFound = false;

            foreach ($followers as $oldFollower) {
                if ($oldFollower->pk == $newFollower['pk']) {
                    $isFound = true;
                    break;
                }
            }

            if (!$isFound) {
                $newFollowers[] = $newFollower;
            }
        }

        return $newFollowers;
    }

    public static function deleteOldFollowers($accountId)
    {
        self::where([
            'owner_account_id' => $accountId
        ])->delete();
    }
}
