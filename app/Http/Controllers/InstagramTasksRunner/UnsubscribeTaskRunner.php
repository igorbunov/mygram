<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 23.12.18
 * Time: 13:22
 */

namespace App\Http\Controllers\InstagramTasksRunner;

use App\account;
use App\AccountSubscriptions;
use App\Http\Controllers\MyInstagram\MyInstagram;
use App\Safelist;
use App\UnsubscribeTask;
use App\UnsubscribeTaskReport;
use App\User;
use Illuminate\Support\Facades\Log;

class UnsubscribeTaskRunner
{
    public static function runUnsubscribeTasks(int $unsubscribeTaskId, int $accountId)
    {
        Log::debug('=== start async method: runUnsubscribeTasks ' . $unsubscribeTaskId . ' ===');

        $account = account::getAccountById($accountId);

        if (is_null($account)) {
            Log::debug('account not found');
            return;
        }

        $unsubscribeTask = UnsubscribeTask::getUnsubscribeTaskById($unsubscribeTaskId, $accountId, true);

        if (is_null($unsubscribeTask)) {
            Log::debug('getUnsubscribeTaskById not found');
            return;
        }

        MyInstagram::getInstanse()->login($account);

        $notUnsubscribedFollowings = AccountSubscriptions::getNotUnsubscribedFollowers($accountId, 3);

        $unsubscribeCount = rand(env('UNSUBSCRIBES_COUNT_BY_ONE_TASK_MIN', 1), env('UNSUBSCRIBES_COUNT_BY_ONE_TASK_MAX', 1));
        $unsubscribeCounter = 0;

        foreach ($notUnsubscribedFollowings as $following) {
            if ($unsubscribeCounter >= $unsubscribeCount) {
                break;
            }

            $sleepTime = rand(5, 25);
            Log::debug('Sleep: ' . $sleepTime);
            sleep($sleepTime);

            if (AccountSubscriptions::isUnsubscribed($following->id)) { //TODO: сделать проверку за последние 5 мин
                Log::debug('дубль ' . $following->id);
                continue;
            }

            $response = MyInstagram::getInstanse()->unsubscribe($following->pk);

            $resultArr = [
                'unsubscribe_task_id' => $unsubscribeTaskId,
                'response' => \json_encode($response),
                'success' => 1,
                'error_message' => ''
            ];

            AccountSubscriptions::setUnsubscribed($following->id, true);

            if (!$response->isOk()) {
                $resultArr['success'] = 0;
                $resultArr['error_message'] = $response->getMessage();
                Log::error('error unsubscribing from: ' . $following->username . ' ' . $resultArr['error_message']);
            } else {
                Log::debug('unsubscribed from: ' . $following->username);
            }

            UnsubscribeTaskReport::writeStatistics($resultArr);

            $unsubscribeCounter++;
        }

        if (AccountSubscriptions::isTaskDone($accountId)) {
            UnsubscribeTask::updateStatistics($unsubscribeTaskId, true);

            User::sendEmail($account->user_id,
                'Массовая отписка @' . $account->nickname,
                'Задание массовой отписки закончено!');
        } else {
            UnsubscribeTask::updateStatistics($unsubscribeTaskId);
        }

        Log::debug('=== done ===');
    }
}