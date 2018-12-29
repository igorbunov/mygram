<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ChatbotAccounts extends Model
{
    public static function getCount(int $chatbotId)
    {
        return (int) DB::selectOne("SELECT COUNT(1) AS cnt 
            FROM chatbot_accounts
            WHERE chatbot_id = ?", [$chatbotId])->cnt;
    }

    public static function add($data)
    {
        try {
            $res = new ChatbotAccounts();

            $res->chatbot_id = $data['chatbot_id'];
            $res->username = $data['username'];
            $res->pk = $data['pk'];
            $res->json = isset($data['json']) ? $data['json'] : '';
            $res->picture = isset($data['picture']) ? $data['picture'] : '';

            if (isset($data['is_private_profile'])) {
                $res->is_private_profile = $data['is_private_profile'];
            }

            return $res->save();
        } catch (\Exception $err) {
            return false;
        }
    }

    public static function canBeAdded(int $chatbotId, string $userPK, int $userId)
    {
        $count = (int) DB::selectOne("SELECT COUNT(1) AS cnt 
            FROM chatbot_accounts
            WHERE chatbot_id = ? AND pk = ?", [$chatbotId, $userPK])->cnt;

        if ($count > 0) {
            return false;
        }

        $accounts = account::getActiveAccountsByUser($userId);
        $accountIds = [];

        foreach ($accounts as $account) {
            $accountIds[] = (int) $account->id;
        }

        $accountIds = implode(',', $accountIds);

        $countInSafelist = (int) DB::selectOne("SELECT COUNT(1) AS cnt
            FROM account_subscriptions
            WHERE owner_account_id IN({$accountIds}) AND pk = ? AND is_in_safelist = 1", [$userPK])->cnt;

        if ($countInSafelist > 0) {
            return false;
        }

        return true;
    }
}
