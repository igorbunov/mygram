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

        $sleepTime = rand(10, 180); // спим от 10 сек до 3 мин
        Log::debug('sleep time: ' . $sleepTime);
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
            AccountSubscribers::addUniqueArray($followersAsArray);
            Log::debug('done');
            return;
        }

        $followersDiff = AccountSubscribers::getNewFollowers($accountId, $followersAsArray);

        Log::debug('new followers count: ' . count($followersDiff));

        if (count($followersDiff) > 0) {
            AccountSubscribers::addUniqueArray($followersDiff);
            Log::debug('re-added followers: ' . count($followersDiff));
        }

        $sendedFollowersArr = self::sendDirectToSubscribers($directTaskId, $accountId);

        Log::debug('sendedFollowersArr: ' . count($sendedFollowersArr));

//            AccountSubscribers::deleteOldFollowers($accountId);

        DirectTask::updateStatistics($directTaskId);

        Log::debug('=== done ===');
    }

    public static function sendDirectToSubscribers(int $directTaskId, int $accountId)
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

        $sendedFollowersArr = [];

        $unsendedFollowers = AccountSubscribers::getUnsendedFollowers($accountId);

        foreach ($unsendedFollowers as $newFollower) {
            sleep(rand(10, 30));

            if (AccountSubscribers::isSended($newFollower->id)) { //TODO: сделать проверку за последние 5 мин
                Log::debug('дубль ' . $newFollower->id);
                continue;
            }

            $todayDirectCount = DirectTaskReport::getTodayFriendDirectMessagesCount($directTask->id);

            if ($todayDirectCount >= env('FRIEND_DIRECT_LIMITS_BY_DAY')) {
                Log::debug('direct limits per day achieved: ' . $todayDirectCount);
                break;
            }

            $lastHourDirectCount = DirectTaskReport::getLastHourFriendDirectMessagesCount($directTask->id);

            if ($lastHourDirectCount >= env('FRIEND_DIRECT_LIMITS_BY_HOUR')) {
                Log::debug('direct limits per hour: ' . $lastHourDirectCount);
                break;
            }

            $response = MyInstagram::getInstanse()->sendDirect($newFollower->pk, $directTask->message);

            $resultArr = [
                'direct_task_id' => $directTaskId,
                'response' => \json_encode($response),
                'success' => 1,
                'error_message' => ''
            ];

            $sendedFollowersArr[] = $newFollower;
            AccountSubscribers::setSended($newFollower->id, true);

            if (!$response->isOk()) {
                $resultArr['success'] = 0;
                $resultArr['error_message'] = $response->getMessage();
                Log::error('error send message to: ' . $newFollower->username . ' ' . $resultArr['error_message']);
            } else {
                Log::debug('message sended to: ' . $newFollower->username);
            }

            DirectTaskReport::writeStatistics($resultArr);
        }

        return $sendedFollowersArr;
    }
}