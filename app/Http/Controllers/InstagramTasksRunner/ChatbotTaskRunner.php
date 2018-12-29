<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 29.12.18
 * Time: 14:46
 */
namespace App\Http\Controllers\InstagramTasksRunner;

use App\account;
use App\Chatbot;
use App\ChatbotAccounts;
use App\Http\Controllers\MyInstagram\MyInstagram;
use const Grpc\CHANNEL_CONNECTING;
use Illuminate\Support\Facades\Log;

class ChatbotTaskRunner
{
    public static function runRefreshList(int $chatbotId, int $accountId)
    {
        Log::debug('=== start async method: ChatbotTaskRunner->runRefreshList ' . $chatbotId . ' ===');
        $account = account::getAccountById($accountId);

        if (is_null($account)) {
            Log::debug('account not found');
            return;
        }

        $chatbot = Chatbot::getByUserId($account->user_id);

        if (is_null($chatbot)) {
            Log::debug('chatbot by user_id not found');
            return;
        }

        Log::debug('$chatbot: ' . \json_encode($chatbot->toArray()));

        $hashtags = explode('|', $chatbot->hashtags);

        Log::debug('$hashtags: ' . \json_encode($hashtags));

        MyInstagram::getInstanse()->login($account);

        $newUsers = MyInstagram::getInstanse()->findUsersByHashtag($hashtags, $chatbot->max_accounts, $chatbot->id, $account->user_id);

        Chatbot::setStatus($chatbot->id, Chatbot::STATUS_SYNCHRONIZED);

        Log::debug('done');
    }
}