<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ChatbotAccounts extends Model
{
    public static function updateStatistics(Chatbot $chatBot, account $account)
    {
        $res = DB::selectOne("SELECT 
                COUNT(1) AS total,
                IFNULL(SUM(IF(sender_account_id = :senderId, 1, 0)), 0) AS `sended`
            FROM chatbot_accounts 
            WHERE chatbot_id = :botId", [':botId' => $chatBot->id, ':senderId' => $account->id]);

        if (is_null($res)) {
            return;
        }

        Chatbot::edit([
            'id' => $chatBot->id,
            'total_chats' => $res->total,
            'chats_in_progress' => $res->sended
        ]);
    }

    public static function getLastHourDirectMessagesCount(Chatbot $chatBot, account $account): int
    {
        $res = DB::selectOne("SELECT COUNT(1) AS cnt 
            FROM chatbot_accounts
            WHERE chatbot_id = ? 
                AND sender_account_id = ? 
                AND is_sended = 1 
                AND DATE(updated_at) = CURDATE() 
                AND updated_at > NOW() - INTERVAL 1 HOUR"
        , [$chatBot->id, $account->id]);

        if (is_null($res)) {
            return 0;
        }

        return $res->cnt;
    }

    public static function getTodayDirectMessagesCount(Chatbot $chatBot, account $account): int
    {
        $res = DB::selectOne("SELECT COUNT(1) AS cnt 
            FROM chatbot_accounts
            WHERE chatbot_id = ? AND sender_account_id = ? AND is_sended = 1 AND DATE(updated_at) = CURDATE()"
        , [$chatBot->id, $account->id]);

        if (is_null($res)) {
            return 0;
        }

        return $res->cnt;
    }

    public static function setSended(Chatbot $chatBot, string $pk, bool $isSended, int $accountId)
    {
        $res = self::where([
            'chatbot_id' => $chatBot->id,
            'pk' => $pk
        ])->first();

        if (is_null($res)) {
            return false;
        }

        $res->is_sended = ($isSended) ? 1 : 0;
        $res->sender_account_id = $accountId;

        return $res->save();
    }

    public static function isSended(Chatbot $chatBot, string $pk)
    {
        $res = (int) DB::selectOne("SELECT COUNT(1) AS cnt 
            FROM chatbot_accounts
            WHERE chatbot_id = ? AND pk = ? AND is_sended = 1", [$chatBot->id, $pk])->cnt;

        return $res > 0;
    }

    public static function getWaitingSendAccounts(Chatbot $chatBot, int $limit = 5)
    {
        return self::where([
            'chatbot_id' => $chatBot->id,
            'is_sended' => 0
        ])->orderBy('id', 'ASC')->limit($limit)->get();
    }

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
