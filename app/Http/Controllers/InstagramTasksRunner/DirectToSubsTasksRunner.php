<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 20.11.18
 * Time: 22:38
 */
namespace App\Http\Controllers\InstagramTasksRunner;
use App\account;
use App\AccountSubscribers;
use App\DirectTask;
use Illuminate\Support\Facades\Log;
use InstagramAPI\Instagram;
use InstagramAPI\Signatures;

class DirectToSubsTasksRunner
{
    private static function includeLibrary()
    {
        $path =__DIR__.'/../../../../instagram_lib/vendor/autoload.php';

        if (!file_exists($path)) {
            Log::error('cant find path: '.$path);
            return false;
        }

        require_once $path;

        return true;
    }

    public static function getAccountSubscribers(int $accountId)
    {
        if (!self::includeLibrary()) {
            return;
        }

        $account = account::getAccountById($accountId);

        if (is_null($account)) {
            Log::debug('account not found');
            return;
        }

        $ig = new Instagram();
        Log::debug('inst created');

        $ig->login($account->nickname, $account->password);

        $maxId = null;
        $rankToken = Signatures::generateUUID();
        Log::debug('account_id '.$ig->account_id.' $rankToken: '.$rankToken);

        $response = $ig->people->getFollowers($ig->account_id, $rankToken);

//        Log::debug('response: '.$response);

        if ($response->isOk()) {
            $followers = $response->getUsers();
//            Log::debug('followers: '.\json_encode($followers));

            foreach ($followers as $follower) {
//                Log::debug('follower', $follower);

                AccountSubscribers::addUnique([
                    'owner_account_id' => $accountId,
                    'username' => $follower->getUsername(),
                    'pk' => $follower->getPk(),
                    'json' => \json_encode($follower)
                ]);
            }
        } else {
            Log::error('cant get response: '.$response->getMessage());
        }
    }

    public static function sendDirectToSubscribers(int $directTaskId, int $accountId)
    {
        Log::debug('sendDirectToSubscribers '.$directTaskId. ' '.$accountId);

        if (!self::includeLibrary()) {
            return;
        }

        try {
//            Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;

            $ig = new Instagram();
            Log::debug('inst created');
            $directTask = DirectTask::getDirectTaskById($directTaskId, $accountId, true);

            if (is_null($directTask)) {
                Log::debug('direct task not found');
                return;
            }
            Log::debug('direct task polu4en');
            $account = account::getAccountById($accountId, true);

            if (is_null($account)) {
                Log::debug('account not found');
                return;
            }
            Log::debug('account polu4en');

            $ig->login($account->nickname, $account->password);
            $userId = $ig->people->getUserIdForName('pata.ua');
            $response = $ig->direct->sendText(['users' => [$userId]], $directTask->message);

            if ($response->isOk()) {
                Log::debug('message sended');
            } else {
                Log::error('message not sended');
            }
        } catch (\Exception $err) {
            Log::error('error: ' . $err->getMessage().' '.$err->getTraceAsString());
        }
    }
}