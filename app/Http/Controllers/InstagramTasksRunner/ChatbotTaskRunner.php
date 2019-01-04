<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 29.12.18
 * Time: 14:46
 */
namespace App\Http\Controllers\InstagramTasksRunner;

use App\account;
use App\AccountSubscribers;
use App\Chatbot;
use App\ChatbotAccounts;
use App\ChatHeader;
use App\FastTask;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\MyInstagram\MyInstagram;
use App\Safelist;
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

    public static function getDirectInbox(int $accountId)
    {
        Log::debug('=== start async method: ChatbotTaskRunner->getDirectInbox ' . $accountId . ' ===');
        $account = account::getAccountById($accountId);

        if (is_null($account)) {
            Log::debug('account not found');
            return;
        }

        $chatBot = Chatbot::getByUserId($account->user_id);

        if (is_null($chatBot)) {
            Log::debug('no chatbot exists');
            return;
        }

        MyInstagram::getInstanse()->login($account);

        if (ChatHeader::isChatExists($chatBot, $account)) {
            $cursorId = null;
            $counter = 0;

            $allAccountsSafelist = Safelist::getSafelistForAllAccounts($account->user_id);

            while($counter++ < 1) { // 40 dialogs
                $messages = MyInstagram::getInstanse()->getDirectInbox($cursorId);

                if ($messages->getStatus() == 'ok') {
                    $inbox = $messages->getInbox();

                    $threads = $inbox->getThreads();
                    Log::debug('found threads for (' . $account->nickname . '): ' . count($threads));

                    foreach($threads as $thread) {
                        $threadId = $thread->getThreadId();
                        $threadTitle = $thread->getThreadTitle();
//                        Log::debug('thread title: ' . $threadTitle);
                        $lastMessageId = $thread->getNewestCursor();
                        $companionPK = $thread->getUsers()[0]->getPk();
                        $inSafeList = false;

                        if (array_key_exists($companionPK, $allAccountsSafelist)) {
                            $inSafeList = true;
                        }

                        $chatHeader = ChatHeader::getHeaderByThreadId($chatBot, $threadId);

                        if (!is_null($chatHeader)) {
                            if ($inSafeList) {
//                                Log::debug('chat header in safelist, skip');
                                continue;
                            }
                            if ($chatHeader->status == ChatHeader::STATUS_DIALOG_FINISHED) {
                                Log::debug('dialog ' . $threadTitle . ' finished, skip');
                                continue;
                            }

//                            Log::debug('chat header exists: ' . $threadId);


                            if ($chatHeader->last_message_id == $lastMessageId) {
//                                Log::debug('isLastMessageSame not changed');
                                continue;
                            }

                            Log::debug('isLastMessageSame changed, threadTitle: ' . $threadTitle);

                            ChatHeader::edit([
                                'thread_id' => $threadId,
                                'status' => ChatHeader::STATUS_DIALOG_NEED_ANALIZE,
                                'last_message_id' => $lastMessageId
                            ]);
                        } else {
                            Log::debug('chat header not exists, adding: ' . $threadTitle);

                            ChatHeader::add([
                                'account_id' => $account->id,
                                'chatbot_id' => $chatBot->id,
                                'thread_id' => $threadId,
                                'thread_title' => $threadTitle,
                                'my_pk' => $account->pk,
                                'companion_pk' => $companionPK,
                                'last_message_id' => $lastMessageId,
                                'status' => ChatHeader::STATUS_DIALOG_NEED_ANALIZE
                            ]);
                        }
                    }

                    if ($inbox->getHasOlder()) {
                        $cursorId = $inbox->getOldestCursor();
                    } else {
                        break;
                    }
                } else {
                    break;
                }

                sleep(rand(3,7));
            }
        } else {
            $messages = MyInstagram::getInstanse()->getDirectInbox();
//            Log::debug('messages: ' . \json_encode($messages));

            if ($messages->getStatus() == 'ok') {
                $inbox = $messages->getInbox();
//                Log::debug('inbox: ' . \json_encode($inbox));
                $threads = $inbox->getThreads();

                $allAccountsSafelist = Safelist::getSafelistForAllAccounts($account->user_id);
                Log::debug('found threads for (' . $account->nickname . '): ' . count($threads));

                foreach($threads as $thread) {
                    $status = ChatHeader::STATUS_WAITING_ANSWER;
                    $companionPK = $thread->getUsers()[0]->getPk();

                    if (array_key_exists($companionPK, $allAccountsSafelist)) {
                        $status = ChatHeader::STATUS_DIALOG_FINISHED;
                    }

                    Log::debug('первое добавление диалога ' . $thread->getThreadTitle() );

                    ChatHeader::add([
                        'account_id' => $account->id,
                        'chatbot_id' => $chatBot->id,
                        'thread_id' => $thread->getThreadId(),
                        'thread_title' => $thread->getThreadTitle(),
                        'my_pk' => $account->pk,
                        'companion_pk' => $companionPK,
                        'last_message_id' => $thread->getNewestCursor(),
                        'status' => $status
                    ]);
                }
            }
        }

        Log::debug('getDirectInbox done');
    }

    public static function sendFirstMessage(int $accountId)
    {
        Log::debug('=== start async method: ChatbotTaskRunner->sendFirstMessage accountId: ' . $accountId . ' ===');
        $account = account::getAccountById($accountId);

        if (is_null($account)) {
            Log::debug('account not found');
            return;
        }

        $chatBot = Chatbot::getByUserId($account->user_id);

        if (is_null($chatBot)) {
            Log::debug('chatbot by user_id not found');
            return;
        }

        Log::debug('chatbot: ' . \json_encode($chatBot->toArray()));

//        chatbot_accounts is_sended
        $waitingSend = ChatbotAccounts::getWaitingSendAccounts($chatBot, 10);

        if (count($waitingSend) > 0) {
            MyInstagram::getInstanse()->login($account);
        }

        foreach ($waitingSend as $newUser) {
            if (ChatbotAccounts::isSended($chatBot, $newUser->pk)) {
                Log::debug('дубль ' . $newUser->pk);
                continue;
            }
            if (AccountSubscribers::isSendedByPK($account->user_id, $newUser->pk)) {
                $directSender = AccountSubscribers::getSendedByPk($account->user_id, $newUser->pk);

                if (is_null($directSender)) {
                    ChatbotAccounts::setSended($chatBot, $newUser->pk, true, $accountId);
                } else {
                    ChatbotAccounts::setSended($chatBot, $newUser->pk, true, $directSender->owner_account_id);
                }

                Log::debug('дубль в директ подпищикам ' . $newUser->pk);
                continue;
            }

            ChatbotAccounts::setSended($chatBot, $newUser->pk, true, $accountId);

            $response = MyInstagram::getInstanse()->sendDirectThread($newUser->pk, 'Добрый день. Предлагаю работу в Инстаграм. Интересно?');

            if ($response->isOk()) {
                Log::debug('message sended to (chatbot): ' . $newUser->username);
            } else {
                Log::error('error send message to (chatbot): ' . $newUser->username . ' error: ' . \json_encode($response));
            }

//            $sleepTime = rand(5, 25);
//            Log::debug('Sleep: ' . $sleepTime);
//            sleep($sleepTime);

            break;
        }

        Log::debug('done');
    }

    public static function analizeDialogAndAnswer(int $chatbotId, int $accountId)
    {
        Log::debug('=== start async method: ChatbotTaskRunner->analizeDialogAndAnswer ' . $chatbotId . ' === ' . $accountId);
        $account = account::getAccountById($accountId);

        if (is_null($account)) {
            Log::debug('account not found');
            return;
        }

        $chatBot = Chatbot::getByUserId($account->user_id);

        if (is_null($chatBot) or $chatBot->id != $chatbotId) {
            Log::debug('chatbot by user_id not found');
            return;
        }

        Log::debug('chatBot: ' . \json_encode($chatBot->toArray()));

        $waitingDialogs = ChatHeader::getWaitingAnalisys($chatBot, $account, ChatHeader::STATUS_DIALOG_NEED_ANALIZE);

        if (is_null($waitingDialogs)) {
            Log::debug('no waiting dialogs for account: ' . $account->nickname);
            return;
        }

        Log::debug('waiting analisys: ' . count($waitingDialogs));

        if (count($waitingDialogs) > 0) {
            MyInstagram::getInstanse()->login($account);
        }

        foreach($waitingDialogs as $dialog) {
//            Log::debug('waiting dialog: ' . \json_encode($dialog));

            $threadId = $dialog->thread_id;

            $response = MyInstagram::getInstanse()->getThreadMessages($threadId);

            if ($response->getStatus() == 'ok') {
//                Log::debug('пошло: ' . \json_encode($response));

                if ($response->isThread()) {
                    $thread = $response->getThread();

                    if ($thread->isItems()) {
                        $messages = $thread->getItems();
                        $threadTitle = $thread->getThreadTitle();

                        $messArr = [];

                        foreach ($messages as $i => $message) {
                            if ($message->getItemType() == 'text') {
                                $text = $message->getText();
                                $text = trim($text);

                                $messArr[] = [
                                    'isMy' => self::isMyMessage($account->pk, $message->getUserId()),
                                    'text' => $text
                                ];
                            } else if ($message->getItemType() == 'like') {
                                $messArr[] = [
                                    'isMy' => self::isMyMessage($account->pk, $message->getUserId()),
                                    'text' => 'like'
                                ];
                            }
                        }

                        $bot = new BotController();
                        $messArr = array_reverse($messArr);
                        $otvet = $bot->getAnswer($messArr);

                        Log::debug('== ответ ' . $threadTitle . ' == ' . $otvet['txt'] . ' ' . $otvet['status']);

                        if ($otvet['status'] != '') {
                            if ($otvet['phone'] != '') {
                                ChatHeader::edit([
                                    'thread_id' => $threadId,
                                    'status' => ChatHeader::STATUS_DIALOG_FINISHED
                                ]);

                                try {
                                    Log::debug('На аккаунте: ' . $account->nickname. ', чат с: '.$threadTitle.', получен телефон: ' . $otvet['phone']);

                                    $emailMessage = view('chatbot.mail_chat', [
                                        'account' => $account->nickname,
                                        'dialog' => $messArr,
                                        'threadTitle' => $threadTitle,
                                        'phone' => $otvet['phone']
                                    ]);

                                    AccountController::mailToClient($account->id, 'Чатбот (получен номер телефона)', $emailMessage);

                                    FastTask::mailToDeveloper('Чатбот (получен номер телефона)', $emailMessage);
                                } catch (\Exception $err1) {
                                    Log::error('Ошибка отправки чата клиенту ' . $err1->getMessage() . ' ' . $err1->getTraceAsString());
                                }
                            } else if ($otvet['txt'] != '') {
                                $sendResp = MyInstagram::getInstanse()->sendDirectThread($threadId, $otvet['txt']);

                                if ($sendResp->getStatus() == 'ok') {
                                    Log::debug(' == sended == ');
                                }

                                ChatHeader::edit([
                                    'thread_id' => $threadId,
                                    'status' => ChatHeader::STATUS_WAITING_ANSWER
                                ]);
                            } else {
                                if ($otvet['status'] == BotController::STATUS_DIALOG_FINISHED) {
                                    Log::debug('пользователь '. $threadTitle . ' отказался');

                                    ChatHeader::edit([
                                        'thread_id' => $threadId,
                                        'status' => ChatHeader::STATUS_DIALOG_FINISHED
                                    ]);
                                } else if ($otvet['status'] == BotController::STATUS_WAITING_ANSWER) {
                                    ChatHeader::edit([
                                        'thread_id' => $threadId,
                                        'status' => ChatHeader::STATUS_WAITING_ANSWER
                                    ]);
                                }
                            }
                        } else {
                            ChatHeader::edit([
                                'thread_id' => $threadId,
                                'status' => ChatHeader::STATUS_WAITING_ANSWER
                            ]);
                        }

//                        Log::debug('messages: ' . \json_encode($messages));
                    }
                }
            } else {
                Log::error('чет не пошло: ' . \json_encode($response));
            }

            sleep(rand(5, 10));
        }

        Log::debug('done');
    }

    private static function isMyMessage($myPk, $senderPk)
    {
        return $myPk == $senderPk;
    }

}