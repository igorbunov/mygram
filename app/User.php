<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class User extends Model
{
    public static function getActiveAndConrifmed()
    {
        $res = User::where([
            'is_confirmed' => 1
        ])->get();

        if (is_null($res)) {
            return [];
        }

        return $res;
    }

    public static function sendEmail(int $userId, string $subject, string $message)
    {
        $user = self::getUserById($userId);

        if (is_null($user)) {
            return;
        }

        $headers = "From: mygram.in.ua\r\nReply-To: igorbunov.ua@gmail.com\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8";

        \mail($user->email, $subject, $message, $headers);
    }

    public static function getUserById(int $userId)
    {
        $res = self::find($userId);

        return $res;
    }

    public static function getAccountPictureUrl(int $userId = 0, int $accountId = 0)
    {
        if ($userId == 0) {
            $userId = (int) session('user_id', 0);
        }

        if ($userId == 0) {
            throw new \Exception('no user');
        }

        $accounts = account::getActiveAccountsByUser($userId);
        $picture = '';

        if (!is_null($accounts)) {
            foreach ($accounts as $account) {
                if (!is_null($account->picture) and !empty($account->picture)) {
                    $picture = $account->picture;
                }

                if ($accountId == $account->id and $picture != '') {
                    break;
                }
            }
        }

        return $picture;
    }

    public static function getAccountsByUser(int $userId, bool $activeOnly = false, bool $asArray = false)
    {
        $filter = [
            'user_id' => $userId
        ];

        if ($activeOnly) {
            $filter['is_active'] = 1;
        }
        
        $res = account::where($filter)->get();

        if (!$asArray) {
            return $res;
        }

        $result = [];

        foreach ($res as $row) {
            $result[] = $row->toArray();
        }

        return $result;
    }
}
