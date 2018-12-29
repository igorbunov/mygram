<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chatbot extends Model
{
    const STATUS_EMPTY = 'empty';
    const STATUS_UPDATING = 'updating';
    const STATUS_SYNCHRONIZED = 'synchronized';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_FINISHED = 'finished';

    public static function setStatus(int $id, string $status)
    {
        $res = self::find($id);

        $res->status = $status;
        return $res->save();
    }

    public static function getByUserId(int $userId)
    {
        $res = self::where([
            'user_id' => $userId
        ])->first();

        return $res;
    }

    public static function add($data)
    {
        $res = new Chatbot();

        if (isset($data['user_id'])) {
            $res->user_id = $data['user_id'];
        }
        if (isset($data['hashtags'])) {
            $res->hashtags = $data['hashtags'];
        }
        if (isset($data['status'])) {
            $res->status = $data['status'];
        }
        if (isset($data['work_with_direct_answer_task'])) {
            $res->work_with_direct_answer_task = $data['work_with_direct_answer_task'];
        }
        if (isset($data['max_accounts'])) {
            $res->max_accounts = $data['max_accounts'];
        }
        if (isset($data['total_chats'])) {
            $res->total_chats = $data['total_chats'];
        }
        if (isset($data['chats_in_progress'])) {
            $res->chats_in_progress = $data['chats_in_progress'];
        }
        if (isset($data['chats_finished'])) {
            $res->chats_finished = $data['chats_finished'];
        }

        $res->save();

        return $res->id;
    }

    public static function edit($data)
    {
        $res = self::find($data['id']);

        if (isset($data['hashtags'])) {
            $res->hashtags = $data['hashtags'];
        }
        if (isset($data['status'])) {
            $res->status = $data['status'];
        }
        if (isset($data['work_with_direct_answer_task'])) {
            $res->work_with_direct_answer_task = $data['work_with_direct_answer_task'];
        }
        if (isset($data['max_accounts'])) {
            $res->max_accounts = $data['max_accounts'];
        }
        if (isset($data['total_chats'])) {
            $res->total_chats = $data['total_chats'];
        }
        if (isset($data['chats_in_progress'])) {
            $res->chats_in_progress = $data['chats_in_progress'];
        }
        if (isset($data['chats_finished'])) {
            $res->chats_finished = $data['chats_finished'];
        }

        return $res->save();
    }
}
