<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 25.11.18
 * Time: 16:09
 */

namespace App\Http\Controllers\MyInstagram;

use App\account;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use InstagramAPI\Instagram;

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
        } else {
            Log::debug('файл найден');
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
        $account = account::getAccountById($accountId);

        return $this->login($account);
    }

    public function login(account $account)
    {
        if ($this->isLogined($account)) {
            return $this->instagram;
        } else {
            $this->logout();
        }

        try {
            $this->account = $account;

            $this->instagram = new Instagram();
            $respose = '';

            try {
                $respose = $this->instagram->login($this->account->nickname, Crypt::decryptString($this->account->password));
            } catch (\Exception $err0) {
                Log::error('error when login: ' . $this->account->nickname . ' ' . $err0->getMessage());
            }

            $this->setRankToken();

            $this->accountPK = $this->instagram->account_id;
            $this->accountId = $this->account->id;

            account::setLoginStatus([
                'accountId' => $account->id,
                'isError' => false,
                'message' => (is_null($respose)) ? 'session exists' : \json_encode($respose)
            ]);

            return $this->instagram;
        } catch (\Exception $err) {
            account::setLoginStatus([
                'accountId' => $account->id,
                'isError' => true,
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
}
