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
use App\DirectTaskReport;
use App\FastTask;
use App\Http\Controllers\MyInstagram\MyInstagram;
use App\Tariff;
use App\TaskList;
use App\User;
use Illuminate\Support\Facades\Log;
use InstagramAPI\Instagram;

class DirectToSubsTasksRunner
{
    public static function runDirectTasks(int $directTaskId, int $accountId)
    {
        Log::debug('=== start async method: runDirectTasks ' . $directTaskId . ' ===');
        $account = account::getAccountById($accountId);

        if (is_null($account)) {
            Log::debug('account not found');
            return;
        }

        $directTask = DirectTask::getDirectTaskById($directTaskId, $accountId, true);

        if (is_null($directTask)) {
            return;
        }

        MyInstagram::getInstanse()->login($account);

        $unsendedFollowers = AccountSubscribers::getUnsendedFollowers($accountId, 3);

        foreach ($unsendedFollowers as $newFollower) {
            $sleepTime = rand(5, 25);
            Log::debug('Sleep: ' . $sleepTime);
            sleep($sleepTime);

            if (AccountSubscribers::isSended($newFollower->id)) { //TODO: сделать проверку за последние 5 мин
                Log::debug('дубль ' . $newFollower->id);
                continue;
            }

            $response = MyInstagram::getInstanse()->sendDirect($newFollower->pk, $directTask->message);

            $resultArr = [
                'direct_task_id' => $directTaskId,
                'response' => \json_encode($response),
                'success' => 1,
                'error_message' => ''
            ];

            AccountSubscribers::setSended($newFollower->id, true);

            if (!$response->isOk()) {
                $resultArr['success'] = 0;
                $resultArr['error_message'] = $response->getMessage();
                Log::error('error send message to: ' . $newFollower->username . ' ' . $resultArr['error_message']);
            } else {
                Log::debug('message sended to: ' . $newFollower->username);
            }

            DirectTaskReport::writeStatistics($resultArr);

            break;
        }

        DirectTask::updateStatistics($directTaskId);

        Log::debug('=== done ===');
    }
}