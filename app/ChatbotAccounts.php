<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ChatbotAccounts extends Model
{
    public static function getDirectStats(Chatbot $chatBot)
    {
        return DB::select("SELECT 
                a.sender_account_id
                , k.nickname
                , COUNT(1) AS cnt
            FROM chatbot_accounts a
            INNER JOIN accounts k ON k.id = a.sender_account_id
            WHERE a.chatbot_id = :chatbotId AND a.is_sended = 1 AND DATE(a.updated_at) = CURDATE()
            GROUP BY a.sender_account_id
            ORDER BY cnt DESC", [':chatbotId' => $chatBot->id]);
    }

    public static function setIsInSendlist(Chatbot $chatBot, string $nickname, int $isChecked)
    {
        $res = DB::selectOne("SELECT id
            FROM chatbot_accounts 
            WHERE chatbot_id = :botId AND username = :user
              AND (is_sended = 0 OR (is_sended = 1 AND sender_account_id = -1))
            LIMIT 1"
        , [':botId' => $chatBot->id, ':user' => $nickname]);

        if (is_null($res)) {
            return false;
        }

//dd($res);
        $item = self::find($res->id);

        if (is_null($item)) {
            return false;
        }

        $item->is_sended = (!$isChecked) ? 1 : 0;
        $item->sender_account_id = (!$isChecked) ? -1 : 0;

        return $item->save();
    }

    public static function getAll(Chatbot $chatBot, int $start = 0, int $limit = 50)
    {
        $res = DB::select("SELECT SQL_CALC_FOUND_ROWS *
            FROM chatbot_accounts 
            WHERE chatbot_id = :botId
              AND is_sended = 0 OR (is_sended = 1 AND sender_account_id = -1)
            LIMIT :start, :limit", [':botId' => $chatBot->id, ':start' => $start, ':limit' => $limit]);

        $total = DB::selectOne(DB::raw("SELECT FOUND_ROWS() AS total"))->total;

        return is_null($res) ? ['data' => null, 'total' => 0] : ['data' => $res, 'total' => $total];
    }

    public static function updateStatistics(Chatbot $chatBot)
    {
        $res = DB::selectOne("SELECT 
                COUNT(1) AS total,
                IFNULL(SUM(IF(sender_account_id > 0, 1, 0)), 0) AS `sended`
            FROM chatbot_accounts 
            WHERE chatbot_id = :botId", [':botId' => $chatBot->id]);

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
            'is_sended' => 0,
            'sender_account_id' => 0
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
