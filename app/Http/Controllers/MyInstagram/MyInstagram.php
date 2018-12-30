<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 25.11.18
 * Time: 16:09
 */

namespace App\Http\Controllers\MyInstagram;

use App\account;
use App\Chatbot;
use App\ChatbotAccounts;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use InstagramAPI\Exception\ChallengeRequiredException;

class MyInstagram
{
    private static $instanse = null;
    private $account = null;
    private $rankToken = '';
    private $instagram = null;
    private $accountPK = '';
    private $accountId = 0;

    private function __construct()
    {
        if (!self::includeLibrary()) {
            return;
        }
    }

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


    public static function getInstanse()
    {
        if (is_null(self::$instanse)) {
            self::$instanse = new MyInstagram();
        }

        return self::$instanse;
    }

    public function setAccount(account $account)
    {
        $this->account = $account;

        $this->setRankToken();
    }

    private function setRankToken()
    {
        $this->rankToken = account::getRankToken($this->account->id);
    }

    public function getRankToken()
    {
        return $this->rankToken;
    }

    public function isLogined(account $account)
    {
        return (!is_null($this->account) and $this->accountId == $account->id and !is_null($this->instagram));
    }

    public function logout()
    {
        $this->account = null;
        $this->rankToken = '';
        $this->instagram = null;
        $this->accountPK = '';
        $this->accountId = 0;
    }

    public function loginByAccountId(int $accountId)
    {
        $account = account::getAccountById($accountId, false);

        return $this->login($account);
    }

    public function login(account $account)
    {
        if ($this->isLogined($account)) {
            return $this->instagram;
        } else {
            $this->logout();
        }

        $response = '';
        $verifyCode = '';
        $checkApiPath = '';

        try {
            $this->account = $account;

            $this->instagram = new ExtendedInstagram(true);

            if ($account->nickname == 'houpek_nadin') {
                $this->instagram->setProxy('https://Etv8v1:mgsgxz@213.232.69.203:8000');
            }
            

//            // HTTP proxy needing authentication.
//            $ig->setProxy('http://user:pass@iporhost:port');
//
//            // HTTP proxy without authentication.
//                        $ig->setProxy('http://iporhost:port');
//
//            // Encrypted HTTPS proxy needing authentication.
//                        $ig->setProxy('https://user:pass@iporhost:port');
//
//            // Encrypted HTTPS proxy without authentication.
//                        $ig->setProxy('https://iporhost:port');
//
//            // SOCKS5 Proxy needing authentication:
//                        $ig->setProxy('socks5://user:pass@iporhost:port');
//
//            // SOCKS5 Proxy without authentication:
//                        $ig->setProxy('socks5://iporhost:port');


            if (!empty($this->account->verify_code) and 'sended' != $this->account->verify_code
                and $this->account->check_api_path != '') {

                $checkApiPath = $this->account->check_api_path;

                try {
                    $this->instagram->changeUser($this->account->nickname, Crypt::decryptString($this->account->password));
                    $customResponse = $this->instagram->request($checkApiPath)
                        ->setNeedsAuth(false)
                        ->addPost('security_code', $this->account->verify_code)
                        ->addPost('_uuid', $this->instagram->uuid)
                        ->addPost('guid', $this->instagram->uuid)
                        ->addPost('device_id', $this->instagram->device_id)
                        ->addPost('_uid', $this->instagram->account_id)
                        ->addPost('_csrftoken', $this->instagram->client->getToken())
                        ->getDecodedResponse();

                    if ($customResponse['status'] === 'ok' ) {
                        Log::debug("Finished, logged in successfully! Run this file again to validate that it works.");
                    } else {
                        Log::debug("Probably finished...");
                        Log::debug(\json_encode($customResponse));
                    }
                } catch ( \Exception $ex ) {
                    $verifyCode = 'error';
                    Log::error($ex->getMessage());
                }
            }


            try {
                $response = $this->instagram->login($this->account->nickname, Crypt::decryptString($this->account->password));
            } catch (\Exception $err0) {
                Log::error('error when login: ' . $this->account->nickname . ' ' . $err0->getMessage());

                if (empty($checkApiPath)) {

                    $response = $err0->getResponse();
                    Log::debug('login response: ' . \json_encode($response));
                    if ($err0 instanceof ChallengeRequiredException) {
//                        && $response->getErrorType() === 'checkpoint_challenge_required') {

                        sleep(3);

                        $checkApiPath = substr( $response->getChallenge()->getApiPath(), 1);
                        $verification_method = 0; 	//0 = SMS, 1 = Email

                        $customResponse = $this->instagram->request($checkApiPath)
                            ->setNeedsAuth(false)
                            ->addPost('choice', $verification_method)
                            ->addPost('_uuid', $this->instagram->uuid)
                            ->addPost('guid', $this->instagram->uuid)
                            ->addPost('device_id', $this->instagram->device_id)
                            ->addPost('_uid', $this->instagram->account_id)
                            ->addPost('_csrftoken', $this->instagram->client->getToken())
                            ->getDecodedResponse();

                        Log::debug('$customResponse: ' . \json_encode($customResponse));

                        if ($customResponse['status'] === 'ok') {
                            Log::debug('SMS SENDED');
                            $verifyCode = 'sended';
                        } else {
                            Log::debug('bad status: ' . $customResponse['status']);
                        }
                    } else {
                        Log::error("Not a challenge required exception...");
                    }
                }
            }

            $this->setRankToken();

            $this->accountPK = $this->instagram->account_id;
            $this->accountId = $this->account->id;
            $curUserInfo = $this->instagram->account->getCurrentUser();
            $profilePictureUrl = $curUserInfo->getUser()->getProfilePicUrl();

            account::setInfo($account->id, [
                'picture' => $profilePictureUrl,
                'pk' => $this->accountPK,
                'verify_code' => $verifyCode,
                'check_api_path' => $checkApiPath,
                'is_confirmed' => 1,
                'is_active' => 1,
                'message' => (is_null($response)) ? 'session exists' : \json_encode($response)
            ]);

            return $this->instagram;
        } catch (\Exception $err) {
            account::setInfo($account->id, [
                'verify_code' => $verifyCode,
                'check_api_path' => $checkApiPath,
                'is_confirmed' => 0,
                'is_active' => 0,
                'message' => $err->getMessage()
            ]);

            Log::error('Ошибка логина в инстаграм: ' . $err->getMessage().' '.$err->getTraceAsString());
        }

        return null;
    }

    public function getInstagram()
    {
        return $this->instagram;
    }

    public function getLast200Followers()
    {
        $response = $this->instagram->people->getFollowers($this->accountPK, $this->rankToken);

        if (!$response->isOk()) {
            throw new \Exception('Cant get followers: ' . $response->getMessage());
        }

        return $response->getUsers();
    }

    public function convertFollowersToArray(array $followers)
    {
        $followersAsArray = [];

        foreach ($followers as $follower) {
            $followersAsArray[] = [
                'owner_account_id' => $this->accountId,
                'username' => $follower->getUsername(),
                'pk' => $follower->getPk(),
                'json' => \json_encode($follower)
            ];
        }

        return $followersAsArray;
    }

    public function sendDirect(string $receiverPK, string $message)
    {
        return $this->instagram->direct->sendText(['users' => [$receiverPK]], $message);
    }

    public function unsubscribe(string $userPK)
    {
        return $this->instagram->people->unfollow($userPK);
    }

    public function getInfo()
    {
        $result = [];

        $curUser = $this->instagram->people->getInfoById($this->accountPK);
        $curUserInfo = $curUser->getUser();
//        Log::debug('class ' . get_class($curUserInfo));

        $result['followers'] = $curUserInfo->getFollowerCount();
        $result['following'] = $curUserInfo->getFollowingCount();
        $result['posts'] = $curUserInfo->getMediaCount();
        $result['picture'] = $curUserInfo->getProfilePicUrl();

        return $result;
    }

    public function getAllSelfFollowing()
    {
        $followersAsArray = array();
        $nextMaxId = null;
        $userIds = [];

        try {
            do {
                $userFeed = $this->instagram->people->getSelfFollowing($this->rankToken, null, $nextMaxId);
//                Log::debug('user feed: ' . \json_encode($userFeed));
                $followings = $userFeed->getUsers();

//                Log::debug('$followings: ' . \json_encode($followings));

                foreach($followings as $user) {
                    $userName = $user->getUsername();
                    $userPk = $user->getPk();
                    $userIds[] = $userPk;

                    $followersAsArray[] = [
                        'owner_account_id' => $this->accountId,
                        'username' => $userName,
                        'pk' => $userPk,
                        'json' => \json_encode($user),
                        'is_my_subscriber' => 0,
                        'is_in_safelist' => 0,
                        'picture' => $user->getProfilePicUrl()
                    ];
                }
                $sleepTime = rand(20, 50); // спим от 10 сек до 3 мин
                Log::debug('sleep getAllSelfFollowing: ' . $sleepTime);
                sleep($sleepTime);
            } while($nextMaxId=$userFeed->getNextMaxId());

            Log::debug('$nextMaxId: ' . $nextMaxId);

        } catch (\Exception $err) {
            Log::error('error get all following: ' . $err->getMessage());
        }

        return $followersAsArray;
    }

    public function findUsersByHashtag(array $hashtags, int $limit, int $chatbotId, int $userId)
    {
        $results = [];

        Log::debug('limit = ' . $limit . ' count(hashtags) = ' . count($hashtags));

        foreach($hashtags as $i => $tag) {
            $maxId = null;
            $count = 0;

            do {
                Log::debug('search hashtag: ' . $tag);
                $subResult = [];
                $isBreak = false;
                $response = $this->instagram->hashtag->getFeed($tag, $this->rankToken, $maxId);
                $items = ($response->isItems()) ? $response->getItems() : $response->getRankedItems();

                if (!is_null($items)) {
                    foreach ($items as $item) {
                        $user = $item->getUser();
                        $userPK = $user->getPk();

                        if (array_key_exists($userPK, $results)) {
                            Log::debug('already in list: ' . $user->getUsername());
                            continue;
                        }

                        $isPrivate = $user->getIsPrivate();

                        if ($isPrivate) {
                            Log::debug('private profile: ' . $user->getUsername());
                            continue;
                        }

                        if (!ChatbotAccounts::canBeAdded($chatbotId, $userPK, $userId)) {
                            Log::debug('cantBeAdded: ' . $user->getUsername());
                            continue;
                        }

                        $profilePictureUrl = $user->getProfilePicUrl();

                        $userRow = [
                            'chatbot_id' => $chatbotId,
                            'username' => $user->getUsername(),
                            'pk' => $userPK,
                            'json' => \json_encode($item),
                            'picture' => $profilePictureUrl,
                            'is_private_profile' => $isPrivate
                        ];

                        $results[$userPK] = $userRow;
                        $subResult[] = $userRow;

                        $count++;

                        if ($count >= $limit) {
                            Log::debug('limit done, count: ' . $count);
                            $isBreak = true;
                            break;
                        }
                    }

                    Log::debug('count($results): ' . count($results));
                } else {
                    Log::debug('items is null ' . \json_encode($response));
                }

                if ($isBreak) {
                    Log::debug('isBreak = true');
                    $maxId = null;
                } else {
                    $maxId = $response->getNextMaxId();
                }

                Log::debug('count $results: ' . count($results));

                if (count($subResult) > 0) {
                    foreach($subResult as $user) {
                        ChatbotAccounts::add($user);
                    }

                    Chatbot::edit([
                        'id' => $chatbotId,
                        'total_chats' => ChatbotAccounts::getCount($chatbotId)
                    ]);

                    $subResult = [];
                }

                sleep(rand(5, 15));
            } while ($maxId !== null);
        }

        return $results;
    }
}
