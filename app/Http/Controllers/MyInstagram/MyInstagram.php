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
use App\FastTask;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use InstagramAPI\Exception\ChallengeRequiredException;
use InstagramAPI\Exception\CheckpointRequiredException;

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

    public function login(account $account, int $loginTryCount = 0)
    {
        /*
        // HTTP proxy needing authentication.
        $ig->setProxy('http://user:pass@iporhost:port');
        // HTTP proxy without authentication.
        $ig->setProxy('http://iporhost:port');
        // Encrypted HTTPS proxy needing authentication.
        $ig->setProxy('https://user:pass@iporhost:port');
        // Encrypted HTTPS proxy without authentication.
        $ig->setProxy('https://iporhost:port');
        // SOCKS5 Proxy needing authentication:
        $ig->setProxy('socks5://user:pass@iporhost:port');
        // SOCKS5 Proxy without authentication:
        $ig->setProxy('socks5://iporhost:port');
        */

        try {
            $this->account = $account;
            $this->instagram = new ExtendedInstagram(true);

            if (!empty($account->proxy_ip)) {
                $this->instagram->setProxy($account->proxy_ip);
            }

            $response = $this->instagram->login($this->account->nickname, Crypt::decryptString($this->account->password));

            $this->setRankToken();
            $this->accountPK = $this->instagram->account_id;
            $this->accountId = $this->account->id;
            $curUserInfo = $this->instagram->account->getCurrentUser();
            $profilePictureUrl = $curUserInfo->getUser()->getProfilePicUrl();

            account::setInfo($account->id, [
                'picture' => $profilePictureUrl,
                'pk' => $this->accountPK,
                'verify_code' => '',
                'check_api_path' => '',
                'is_confirmed' => 1,
                'is_active' => 1,
                'response' => (is_null($response)) ? 'Сессия существует' : \json_encode($response)
            ]);

            return $this->instagram;
//        } catch (CheckpointRequiredException $err0) {
//            $errorMessage = $err0->getMessage();
//            Log::error('CheckpointRequiredException error when login: ' . $this->account->nickname . ' ' . $errorMessage);
//
//            $response = $err0->getResponse();
//            $checkApiPath = substr($response->getChallenge()->getApiPath(), 1);
//            Log::debug('checkApiPath: '. $checkApiPath);
//            $usrPK = str_replace('challenge/' , '', $checkApiPath);
//            $pos = strpos($usrPK, '/');
//            $usrPK = substr($usrPK, 0, $pos);
//
//            $this->instagram->account_id = $usrPK;
//            $this->account->pk = $usrPK;
//            Log::debug('set account id ' . $this->instagram->account_id . ' ' .$usrPK);
//
//            account::setInfo($account->id, [
//                'verify_code' => '',
//                'pk' => $this->account->pk,
//                'check_api_path' => '',
//                'is_confirmed' => 0,
//                'is_active' => 0,
//                'response' => 'В приложении инстаграм, укажите что это вы сделали попытку входа. Потом повторите вход здесь'
//            ]);
        } catch (ChallengeRequiredException $err0) {
            $errorMessage = $err0->getMessage();
            Log::error('ChallengeRequiredException error when login: ' . $this->account->nickname . ' ' . $errorMessage);

            $response = $err0->getResponse();
            Log::debug('login response: ' . \json_encode($response));
            Log::debug('this->instagram->account_id: ' . \json_encode($this->instagram->account_id));
            Log::debug('inst params: ' . \json_encode(['_uuid' => $this->instagram->uuid,'guid' => $this->instagram->uuid,'device_id' => $this->instagram->device_id,'_uid' => $this->instagram->account_id,'_csrftoken' => $this->instagram->client->getToken()]));

            $checkApiPath = '';

            if (empty($this->account->check_api_path)) {
                $checkApiPath = substr($response->getChallenge()->getApiPath(), 1);
                Log::debug('checkApiPath: '. $checkApiPath);
            } else {
                $checkApiPath = $this->account->check_api_path;
                Log::debug('checkApiPath2: '. $checkApiPath);
                if (is_null($this->instagram->account_id)) {
                    $usrPK = str_replace('challenge/' , '', $checkApiPath);
                    $pos = strpos($usrPK, '/');
                    $usrPK = substr($usrPK, 0, $pos);

                    $this->instagram->account_id = $usrPK;
                    $this->account->pk = $usrPK;
                    Log::debug('set account id ' . $this->instagram->account_id . ' ' .$usrPK);
                }
            }

            sleep(3);

            $customResponse = $this->instagram->request($checkApiPath)
                ->setNeedsAuth(false)
                ->addPost('_uuid', $this->instagram->uuid)
                ->addPost('guid', $this->instagram->uuid)
                ->addPost('device_id', $this->instagram->device_id)
                ->addPost('_uid', $this->instagram->account_id)
                ->addPost('_csrftoken', $this->instagram->client->getToken());

            if (empty($this->account->verify_code)) {
                $customResponse = $customResponse->addPost('choice', 0); //0 = SMS, 1 = Email
            } else {
                $customResponse = $customResponse->addPost('security_code', $this->account->verify_code);
            }

            $customResponse = $customResponse->getDecodedResponse();

            Log::debug('customResponse: ' . \json_encode($customResponse));

            if ($customResponse['status'] === 'ok') {
                if (isset($customResponse['user_id'])) {
                    $this->account->pk = $customResponse['user_id'];
                    Log::debug('customResponse->user_id: ' . $this->account->pk);
                } else if (!is_array($customResponse) and property_exists($customResponse, 'user_id')) {
                    $this->account->pk = $customResponse->user_id;
                    Log::debug('customResponse->user_id: ' . $this->account->pk);
                } else if (is_object($customResponse) and $customResponse->isLoggedInUser()) {
                    $this->account->pk = $customResponse->getLoggedInUser()->getPk();
                    Log::debug('customResponse->getLoggedInUser->getPK(): ' . $this->account->pk);
                }

                if (empty($this->account->verify_code)) {
                    Log::debug('SMS SENDED');

                    account::setInfo($account->id, [
                        'verify_code' => 'sended',
                        'pk' => $this->account->pk,
                        'check_api_path' => $checkApiPath,
                        'is_confirmed' => 0,
                        'is_active' => 0,
                        'response' => 'Введите код подтверждения из смс'
                    ]);
                } else {
                    Log::debug("Finished, logged in successfully! Run this file again to validate that it works.");
                    $this->instagram->afterChallengeRelogin($this->account->pk);

                    account::setInfo($account->id, [
                        'pk' => $this->account->pk,
                        'verify_code' => '',
                        'check_api_path' => '',
                        'is_confirmed' => 1,
                        'is_active' => 1,
                        'response' => 'Вы залогинелись'
                    ]);

                    return $this->instagram;
                }
            } else {
                Log::debug('bad status: ' . \json_encode($customResponse));
            }
        } catch (\Exception $err0) {
            $errorMessage = $err0->getMessage();
            Log::error('error when login: ' . $this->account->nickname . ' ' . $errorMessage);

            $response = $err0->getResponse();
            Log::debug('login response: ' . \json_encode($response));
            Log::debug('this->instagram->account_id: ' . \json_encode($this->instagram->account_id));
            Log::error("Not a challenge required exception...");

            if (strpos($errorMessage, 'Network: CURL error') !== false
                or strpos($errorMessage, 'No response from server. Either a connection or configuration error') !== false) {
                Log::debug("Network CURL error, do nothing");

                if ($loginTryCount >= 3) {
                    throw new \Exception('Не удалось залогинется и перелогинется за 3 раза. Ошибка связи');
                }

                sleep(rand(5, 10));
                $loginTryCount++;
                return $this->login($account, $loginTryCount);
            } else if (strpos($errorMessage, 'InstagramAPI\Response\BootstrapUsersResponse: <built-in method cancelled') !== false) {
                Log::debug("BootstrapUsers error, do nothing");

                if ($loginTryCount >= 3) {
                    throw new \Exception('Не удалось залогинется и перелогинется за 3 раза. Ошибка BootstrapUsers');
                }

                sleep(rand(5, 10));
                $loginTryCount++;
                return $this->login($account, $loginTryCount);
            }

            FastTask::mailToDeveloper('Ошибка входа в инст', $account->nickname . ' не смог перелогинется: ' . $errorMessage);

            if ($account->is_confirmed > 0) {
                throw new \Exception('Ошибка входа в инст', $account->nickname . ' не смог перелогинется');
            } else {
                Log::debug('Не смог залогинется. Отключаем аккаунт ' . $account->nickname);
                account::setInfo($account->id, ['verify_code' => ''
                    , 'check_api_path' => ''
                    , 'is_confirmed' => 0
                    , 'is_active' => 0
                    , 'response' => $errorMessage
                ]);
            }
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
    public function sendDirectThread(string $threadId, string $message)
    {
        return $this->instagram->direct->sendText(['thread' => $threadId], $message);
    }

    public function unsubscribe(string $userPK)
    {
        try {
            return $this->instagram->people->unfollow($userPK);
        } catch (\Exception $err) {
            Log::error('error unsubscribe: ' . \json_encode($err->getMessage()));
        }
    }
    public function subscribe(string $userPK)
    {
        try {
            return $this->instagram->people->follow($userPK);
        } catch (\Exception $err) {
            Log::error('error subscribe: ' . \json_encode($err->getMessage()));
        }
    }

    public function likeSomePosts($nickname, string $userPK)
    {
        try {
            $response = $this->instagram->timeline->getUserFeed($userPK, null);
//            Log::debug('getUserFeed: ' . \json_encode($response));

            if ($response->getStatus() != 'ok') {
                Log::debug('bad status: ' . $response->getStatus());
                return;
            }

            $posts = ($response->isItems()) ? $response->getItems() : [];

            $counter = 0;
            foreach ($posts as $num => $post) {
                if ($counter >= 2) {
                    break;
                }

                $counter++;

                $this->instagram->media->like($post->getId());

                Log::debug('['.$nickname.'] media liked');

                sleep(rand(4,9));
            }
        } catch (\Exception $err) {
            Log::error('['.$nickname.'] error like some posts: ' . \json_encode($err->getMessage()));
        }
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

    public function findUsersByLikes(array $userAccounts, int $chatbotId, int $userId)
    {
        $results = [];

        Log::debug(' count(userAccounts) = ' . count($userAccounts));

        foreach($userAccounts as $i => $userAccount) {
            if (trim($userAccount) == '') {
                continue;
            }
//            Log::debug('search userAccount: ' . $userAccount);

            $response = null;

            try {
                $userId = $this->instagram->people->getUserIdForName($userAccount);

                $response = $this->instagram->timeline->getUserFeed($userId, null);
//                Log::debug('getUserFeed: ' . \json_encode($response));

                if ($response->getStatus() != 'ok') {
                    Log::debug('bad status: ' . $response->getStatus());
                    continue;
                }

                $posts = ($response->isItems()) ? $response->getItems() : [];

                foreach ($posts as $num => $post) {
                    $likers = $this->instagram->media->getLikers($post->getId());

//                    Log::debug('$likers: ' . \json_encode($likers));
                    if ($likers->isUserCount() and $likers->getUserCount() > 0) {
                        $users = $likers->getUsers();

                        foreach($users as $user) {
                            $userPk = $user->getPk();
                            $isPrivate = ($user->getIsPrivate()) ? 1 : 0;

                            if ($user->isGender()) {
                                $gender = $user->getGender();
//                                (1 = male, 2 = female, 3 = unknown).

                                 if ($gender == 1) {
                                     Log::debug('skip male [' . $user->getUsername() . ']: ' . $gender);
                                     continue;
                                 }
                            }

                            if (!array_key_exists($userPk, $results)) {
                                $results[$userPk] = [
                                    'chatbot_id' => $chatbotId,
                                    'username' => $user->getUsername(),
                                    'pk' => $userPk,
                                    'json' => \json_encode($user),
                                    'picture' => $user->getProfilePicUrl(),
                                    'is_private_profile' => $isPrivate
                                ];
                            }
                        }

                        if ($num > 3) {
                            break;
                        }
                    }

                    if ($num > 5) {
                        break;
                    }
                }
            } catch(\Exception $err1) {
                sleep(4);
                Log::error('error: ' . $err1->getMessage() . ' ' . $err1->getTraceAsString());
                continue;
            }

            sleep(rand(5, 15));
        }

        return $results;
    }

    public function getDirectInbox($cursorId = null)
    {
        return $this->instagram->direct->getInbox($cursorId);
    }

    public function getThreadMessages(string $threadId)
    {
        return $this->instagram->direct->getThread($threadId);
    }

    public function markItemSeen(string $threadId, string $lastMessageId)
    {
        try {
            return $this->instagram->direct->markItemSeen($threadId, $lastMessageId);
        } catch (\Exception $err) {
            Log::error('error mark as seen');
        }
    }
}
