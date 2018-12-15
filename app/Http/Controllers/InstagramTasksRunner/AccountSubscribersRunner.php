<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 15.12.18
 * Time: 16:59
 */
namespace App\Http\Controllers\InstagramTasksRunner;

use App\account;
use App\AccountSubscribers;
use App\Http\Controllers\MyInstagram\MyInstagram;
use Illuminate\Support\Facades\Log;

class AccountSubscribersRunner
{
    public static function runGetSubscribers(int $accountId)
    {
        Log::debug('=== start async method: runGetSubscribers ' . $accountId . ' ===');
        $account = account::getAccountById($accountId);

        if (is_null($account)) {
            Log::debug('account not found');
            return;
        }

        $sleepTime = rand(5, 25);
        Log::debug('Sleep: ' . $sleepTime);
        sleep($sleepTime);

        MyInstagram::getInstanse()->login($account);

        Log::debug('account_id ' . MyInstagram::getInstanse()->getInstagram()->account_id .
            ', get rank token ' . MyInstagram::getInstanse()->getRankToken());

        $followersAsArray = [];

        try {
            $followers = MyInstagram::getInstanse()->getLast200Followers();
            $followersAsArray = MyInstagram::getInstanse()->convertFollowersToArray($followers);

            Log::debug('Received new: ' . count($followersAsArray) . ' followers from instagram');
        } catch (\Exception $err) {
            Log::error($err->getMessage());
            return;
        }

        $followersCountInDB = AccountSubscribers::getCurrentFollowersCount($accountId);

        Log::debug('followers count in DB: ' . $followersCountInDB);

        if ($followersCountInDB == 0) {
            foreach($followersAsArray as $i => $follower) {
                $followersAsArray[$i]['is_sended'] = 1;
            }

            AccountSubscribers::addUniqueArray($followersAsArray);
            Log::debug('done create first subs list');
            return;
        }

        $followersDiff = AccountSubscribers::getNewFollowers($accountId, $followersAsArray);

        Log::debug('new followers count: ' . count($followersDiff));

        if (count($followersDiff) > 0) {
            AccountSubscribers::addUniqueArray($followersDiff);
            Log::debug('re-added followers: ' . count($followersDiff));
        }

        Log::debug('=== done ===');
    }
}