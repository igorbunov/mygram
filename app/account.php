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
        Log::debug('setInfo ' . \json_encode($info));
        $account = account::getAccountById($accountId, false);

        if (!is_null($account)) {
            $account->picture = isset($info['picture']) ? $info['picture'] : '';
            $account->subscribers = isset($info['followers']) ? (int) $info['followers'] : 0;
            $account->subscriptions = isset($info['following']) ? (int) $info['following'] : 0;
            $account->publications = isset($info['posts']) ? (int) $info['posts'] : 0;
            $account->pk = isset($info['pk']) ? $info['pk'] : '';
            $account->verify_code = isset($info['verify_code']) ? $info['verify_code'] : '';
            $account->is_confirmed = isset($info['is_confirmed']) ? (int) $info['is_confirmed'] : 0;
            $account->is_active = isset($info['is_active']) ? (int) $info['is_active'] : 0;
            $account->response = isset($info['message']) ? $info['message'] : '';

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

        $account->picture = '';
        $account->response = '';
        $account->user_id = $data['user_id'];
        $account->nickname = $data['nickname'];
        $account->password = $data['password'];

        $account->save();

        return $account->id;
    }

    public function user() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function directTasks() {
        return $this->hasMany('App\DirectTask', 'account_id', 'id');
    }

    public static function getActiveAccountsByUser(int $userId, bool $asArray = false)
    {
        $res = self::where([
            'user_id' => $userId,
            'is_active' => 1
        ])->get();

        if (!$asArray) {
            return $res;
        }

        $result = [];

        foreach ($res as $row) {
            $result[] = $row->toArray();
        }

        return $result;
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
//Log::debug('getAccountById '.\json_encode($res) . ' ' . \json_encode($filter));

//        try {
//            throw new \Exception('aa');
//        } catch (\Exception $err) {
//            Log::error('bad call stack ' . $err->getTraceAsString());
//        }

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
