<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AccountSubscribers extends Model
{
    public static function clearQueue(int $accountId)
    {
        self::where([
            'owner_account_id' => $accountId,
            'is_sended' => 0
        ])->delete();
    }

    public static function isSended(int $followerId)
    {
        $res = self::find($followerId);

        if (is_null($res)) {
            Log::error('не смог найти субскрайбера по айди: ' . $followerId);
            return true;
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

    public static function getUnsendedFollowers(int $accountId)
    {
        $followers = self::where([
            'owner_account_id' => $accountId,
            'is_sended' => 0
        ])->orderBy('id', 'ASC')->get();

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
