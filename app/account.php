<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use InstagramAPI\Signatures;

class account extends Model
{
    public static function getNonConfirmedAccounts()
    {
        return self::where([
            'is_confirmed' => 0,
            'response' => ''
        ])->get();
    }

    public static function setInfo(int $accountId, array $info)
    {
//        Log::debug('setInfo ' . \json_encode($info));
        $account = account::getAccountById($accountId, false);

        if (!is_null($account)) {
            if (isset($info['picture'])) {
                $account->picture = $info['picture'];
            }
            if (isset($info['followers'])) {
                $account->subscribers = $info['followers'];
            }
            if (isset($info['following'])) {
                $account->subscriptions = $info['following'];
            }
            if (isset($info['posts'])) {
                $account->publications = $info['posts'];
            }
            if (isset($info['pk'])) {
                $account->pk = $info['pk'];
            }
            if (isset($info['verify_code'])) {
                $account->verify_code = $info['verify_code'];
            }
            if (isset($info['check_api_path'])) {
                $account->check_api_path = $info['check_api_path'];
            }
            if (isset($info['is_confirmed'])) {
                $account->is_confirmed = $info['is_confirmed'];
            }
            if (isset($info['is_active'])) {
                $account->is_active = $info['is_active'];
            }
            if (isset($info['response'])) {
                $account->response = $info['response'];
            }

            $account->save();
        }
    }

    public static function setProfilePictureUrl(int $accountId, $profileUrl)
    {
        $account = account::getAccountById($accountId);

        if (!is_null($account) and (is_null($account->picture) or empty($account->picture))) {
            $account->picture = $profileUrl;
            $account->save();
        }
    }

    public static function setLoginStatus(array $data)
    {
        $account = self::getAccountById($data['accountId'], false);

        if (is_null($account)) {
            throw new \Exception('account not found');
        }

        $account->is_confirmed = ($data['isError']) ? 0 : 1;
        $account->is_active = ($data['isError']) ? 0 : 1;
        $account->response = $data['message'];
        $account->save();
    }

    public static function addNew(array $data)
    {
        $account = new account();
        $account->picture = '';
        $account->response = '';

        foreach ($data as $field => $value) {
            $account->$field = $value;
        }

        $account->save();

        return $account->id;
    }

    public static function editById(array $data)
    {
        $account = self::getAccountById($data['account_id'], false);
//dd($data['account_id'], $data);
        if (is_null($account)) {
            return 0;
        }

        if (isset($data['is_active'])) {
            $account->is_active = $data['is_active'];
        }
        if (isset($data['is_confirmed'])) {
            $account->is_confirmed = $data['is_confirmed'];
        }
        if (isset($data['password'])) {
            $account->password = $data['password'];
        }
        if (isset($data['nickname'])) {
            $account->nickname = $data['nickname'];
        }
        if (isset($data['user_id'])) {
            $account->user_id = $data['user_id'];
        }
        if (isset($data['picture'])) {
            $account->picture = $data['picture'];
        }
        if (isset($data['response'])) {
            $account->response = $data['response'];
        }
        if (isset($data['pk'])) {
            $account->pk = $data['pk'];
        }
        if (isset($data['verify_code'])) {
            $account->verify_code = $data['verify_code'];
        }
        if (isset($data['check_api_path'])) {
            $account->check_api_path = $data['check_api_path'];
        }

        $account->save();

        return $account->id;
    }

    public function user() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function directTasks() {
        return $this->hasMany('App\DirectTask', 'account_id', 'id');
    }

    public static function getActiveAccountsByUser(int $userId)
    {
        return self::where([
            'user_id' => $userId,
            'is_active' => 1
        ])->get();
    }

    public static function getAccountById(int $accountId, bool $onlyActive = true, bool $asArray = false)
    {
        $filter = [
            'id' => $accountId
        ];

        if ($onlyActive) {
            $filter['is_active'] = 1;
        }

        $res = self::where($filter)->first();

        if (!$asArray) {
            return $res;
        }

        return (is_null($res)) ? null: $res->toArray();
    }

    public static function isAccountBelongsToUser(int $userId, int $accountId)
    {
        $res = self::where([
            'id' => $accountId,
            'user_id' => $userId
        ])->get();

        return (count($res) > 0);
    }

    public static function changeStatus(int $accountId, int $isActive)
    {
        $acc = self::getAccountById($accountId, false);

        if (is_null($acc)) {
            return;
        }

        $acc->is_active = $isActive;
        $acc->save();
    }

    public static function setToken(int $accountId, string $rankToken)
    {
        $acc = self::getAccountById($accountId, false);

        if (is_null($acc)) {
            return;
        }

        $acc->rank_token = $rankToken;
        $acc->save();
    }

    public static function getRankToken(int $accountId)
    {
        $account = self::getAccountById($accountId, false);

        if (is_null($account)) {
            throw new \Exception('Account id: ' . $accountId . ' not found');
        }

        if (!empty($account->rank_token)) {
            return $account->rank_token;
        }

        $rankToken = Signatures::generateUUID();

        self::setToken($accountId, $rankToken);

        return $rankToken;
    }
}
