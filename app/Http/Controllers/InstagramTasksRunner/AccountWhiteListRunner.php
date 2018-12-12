<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 11.12.18
 * Time: 20:23
 */

namespace App\Http\Controllers\InstagramTasksRunner;

use App\account;
use App\AccountSubscriptions;
use App\Http\Controllers\MyInstagram\MyInstagram;
use App\Safelist;
use Illuminate\Support\Facades\Log;

class AccountWhiteListRunner
{
    public static function runRefresh(int $safeListId)
    {
        Log::debug('runned runRefresh account whitelist task for safelist id: ' . $safeListId);

        $safeList = Safelist::getById($safeListId);

        if (is_null($safeList)) {
            Log::error('cant find safelist id');
            return;
        }

        $account = account::getAccountById($safeList->account_id);

        if (is_null($account)) {
            Log::error('account not found');
            return;
        }

        MyInstagram::getInstanse()->login($account);

//        $info = MyInstagram::getInstanse()->getInfo();
//
//        account::setInfo($safeList->account_id, $info);
//
//        $sleepTime = rand(10, 180); // спим от 10 сек до 3 мин
//        Log::debug('sleep time: ' . $sleepTime);
//        sleep($sleepTime);

        $myFollowing = MyInstagram::getInstanse()->getAllSelfFollowing();
//        Log::debug('$myFollowing: ' . \json_encode($myFollowing));
        AccountSubscriptions::addUniqueArray($myFollowing);
        $selected = AccountSubscriptions::getSelected($safeList->account_id);

        Safelist::updateSafelist($safeListId, count($myFollowing), count($selected),Safelist::STATUS_SYNCHRONIZED);

        Log::debug('done');
    }
}
