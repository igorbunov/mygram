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
use App\Http\Controllers\MyInstagram\MyInstagram;
use Illuminate\Support\Facades\Log;
use InstagramAPI\Instagram;

class DirectToSubsTasksRunner
{
    public static function runDirectTasks(int $directTaskId, int $accountId)
    {
        $account = account::getAccountById($accountId);

        if (is_null($account)) {
            Log::debug('account not found');
            return;
        }

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
            AccountSubscribers::addUniqueArray($followersAsArray);
            Log::debug('done');
            return;
        }

        $followersDiff = AccountSubscribers::getNewFollowers($accountId, $followersAsArray);

        Log::debug('new followers count: ' . count($followersDiff));

        AccountSubscribers::deleteOldFollowers($accountId);

        AccountSubscribers::addUniqueArray($followersAsArray);

        Log::debug('readded followers: ' . count($followersAsArray));

        if (count($followersDiff) > 0) {
            self::sendDirectToSubscribers($directTaskId, $accountId, $followersDiff);
        }

        DirectTask::updateStatistics($directTaskId);
        Log::debug('done');
    }

    public static function sendDirectToSubscribers(int $directTaskId, int $accountId, array $newFollowers)
    {
        $directTask = DirectTask::getDirectTaskById($directTaskId, $accountId, true);

        if (is_null($directTask)) {
            return;
        }

        $account = account::getAccountById($accountId, true);

        if (is_null($account)) {
            return;
        }

        MyInstagram::getInstanse()->login($account);

        foreach ($newFollowers as $newFollower) {
            sleep(rand(10, 30));

            $response = MyInstagram::getInstanse()->sendDirect($newFollower['pk'], $directTask->message);

            $resultArr = [
                'direct_task_id' => $directTaskId,
                'response' => \json_encode($response),
                'success' => 1,
                'error_message' => ''
            ];

            if (!$response->isOk()) {
                $resultArr['success'] = 0;
                $resultArr['error_message'] = $response->getMessage();
                Log::error('error send message to: ' . $newFollower['username'] . ' ' . $resultArr['error_message']);
            } else {
                Log::debug('message sended to: ' . $newFollower['username']);
            }

            DirectTaskReport::writeStatistics($resultArr);
        }
    }
}