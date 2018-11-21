<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 20.11.18
 * Time: 22:38
 */
namespace App\Http\Controllers\InstagramTasksRunner;
use App\account;
use App\DirectTask;
use Illuminate\Support\Facades\Log;
use InstagramAPI\Instagram;

class DirectToSubsTasksRunner
{
    public static function sendDirectToSubscribers(int $directTaskId, int $accountId)
    {
        Log::debug('sendDirectToSubscribers '.$directTaskId. ' '.$accountId);
        $path =__DIR__.'/../../../../instagram_lib/vendor/autoload.php';

        Log::debug('file exists '. file_exists($path));

        if (!file_exists($path)) {

            Log::error('cant find path: '.$path);
            return;
        }

        try {
            require_once $path;

//            Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;

            $ig = new Instagram();
            Log::debug('inst created');
            $directTask = DirectTask::getActiveDirectTaskById($directTaskId, $accountId);

            if (is_null($directTask)) {
                Log::debug('direct task not found');
                return;
            }
            Log::debug('direct task polu4en');
            $account = account::getAccountById($accountId);

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