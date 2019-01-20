<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatHeader extends Model
{
    const STATUS_NOT_STARTED = 'not_started';
    const STATUS_UPDATING_INBOX = 'updating_inbox';
    const STATUS_INBOX_UPDATED = 'inbox_updated';
    const STATUS_WAITING_SEND_MESSAGE = 'waiting_send_message';
    const STATUS_WAITING_ANSWER = 'waiting_answer';
    const STATUS_DIALOG_FINISHED = 'dialog_finished';
    const STATUS_DIALOG_NEED_ANALIZE = 'dialog_need_analize';
    const STATUS_ERROR = 'error';


    public static function getWaitingAnalisys(Chatbot $chatBot, account $account, string $status)
    {
        return DB::select("SELECT *
            FROM chat_headers 
            WHERE chatbot_id = ? AND account_id = ? AND `status` = ?"
        , [$chatBot->id, $account->id, $status]);
    }

    public static function getWaitingAnalisysCount(Chatbot $chatBot, account $account, string $status)
    {
        return (int) DB::selectOne("SELECT COUNT(1) AS cnt
            FROM chat_headers 
            WHERE chatbot_id = ? AND account_id = ? AND `status` = ?"
        , [$chatBot->id, $account->id, $status])->cnt;
    }


    public static function getHeaderByThreadId(Chatbot $chatBot, string $threadId)
    {
        return self::where([
            'chatbot_id' => $chatBot->id,
            'thread_id' => $threadId
        ])->first();
    }

    public static function isChatHeaderExists(Chatbot $chatBot, string $threadId): bool
    {
        $res = (int) DB::selectOne("SELECT COUNT(1) AS cnt 
            FROM chat_headers 
            WHERE chatbot_id = ? AND thread_id = ?", [$chatBot->id, $threadId])->cnt;

        return $res > 0;
    }

    public static function isLastMessageSame(Chatbot $chatBot, string $threadId, string $lastMessageId): bool
    {
        $last_message_id = DB::selectOne("SELECT last_message_id 
            FROM chat_headers 
            WHERE chatbot_id = ? AND thread_id = ?", [$chatBot->id, $threadId])->last_message_id;

        Log::debug('isLastMessageSame: ' . $last_message_id . ' = ' . $lastMessageId);
        return $last_message_id == $lastMessageId;
    }

    public static function isChatExists(Chatbot $chatBot, account $account): bool
    {
        $res = (int) DB::selectOne("SELECT COUNT(1) AS cnt 
            FROM chat_headers 
            WHERE chatbot_id = ? AND account_id = ?", [$chatBot->id, $account->id])->cnt;

        return $res > 0;
    }

    public static function edit(array $data)
    {
        $filter = [];
        $res = null;

        if (isset($data['thread_id'])) {
            $filter['thread_id'] = $data['thread_id'];
            $res = self::where($filter)->first();
        } else if (isset($data['id'])) {
            $filter['id'] = $data['id'];
            $res = self::find($filter);
        } else {
            return false;
        }

        if (is_null($res)) {
            return false;
        }

        if (isset($data['account_id'])) {
            $res->account_id = $data['account_id'];
        }
        if (isset($data['chatbot_id'])) {
            $res->chatbot_id = $data['chatbot_id'];
        }
        if (isset($data['thread_title'])) {
            $res->thread_title = $data['thread_title'];
        }
        if (isset($data['my_pk'])) {
            $res->my_pk = $data['my_pk'];
        }
        if (isset($data['companion_pk'])) {
            $res->companion_pk = $data['companion_pk'];
        }
        if (isset($data['last_message_id'])) {
            $res->last_message_id = $data['last_message_id'];
        }
        if (isset($data['status'])) {
            $res->status = $data['status'];
        }
        if (isset($data['taken_phone'])) {
            $res->taken_phone = $data['taken_phone'];
        }

        return $res->save();
    }

    public static function add(array $data)
    {
        $res = new ChatHeader();

        if (isset($data['account_id'])) {
            $res->account_id = $data['account_id'];
        }
        if (isset($data['chatbot_id'])) {
            $res->chatbot_id = $data['chatbot_id'];
        }
        if (isset($data['thread_id'])) {
            $res->thread_id = $data['thread_id'];
        }
        if (isset($data['thread_title'])) {
            $res->thread_title = $data['thread_title'];
        }
        if (isset($data['my_pk'])) {
            $res->my_pk = $data['my_pk'];
        }
        if (isset($data['companion_pk'])) {
            $res->companion_pk = $data['companion_pk'];
        }
        if (isset($data['last_message_id'])) {
            $res->last_message_id = $data['last_message_id'];
        }
        if (isset($data['status'])) {
            $res->status = $data['status'];
        }

        $res->save();

        return $res->id;
    }
}
