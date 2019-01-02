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
use App\Http\Controllers\MyInstagram\MyInstagram;
use App\Safelist;
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

        if (ChatHeader::isChatExists($chatBot)) {
            $cursorId = null;
            $counter = 0;

            $allAccountsSafelist = Safelist::getSafelistForAllAccounts($account->user_id);

            while($counter++ < 3) { // 60 dialogs
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
                        $status = ChatHeader::STATUS_DIALOG_NEED_ANALIZE;
                        $companionPK = $thread->getUsers()[0]->getPk();
                        $inSafeList = false;

                        if (array_key_exists($companionPK, $allAccountsSafelist)) {
                            $status = ChatHeader::STATUS_DIALOG_FINISHED;
                            $inSafeList = true;
                        }

                        $chatHeader = ChatHeader::getHeaderByThreadId($chatBot, $threadId);

                        if (!is_null($chatHeader)) {
                            if ($inSafeList) {
//                                Log::debug('chat header in safelist, skip');
                                continue;
                            }
                            if ($chatHeader->status == ChatHeader::STATUS_DIALOG_FINISHED) {
//                                Log::debug('dialog finished, skip');
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
                                'status' => $status
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
                    $status = ChatHeader::STATUS_INBOX_UPDATED;
                    $companionPK = $thread->getUsers()[0]->getPk();

                    if (array_key_exists($companionPK, $allAccountsSafelist)) {
                        $status = ChatHeader::STATUS_DIALOG_FINISHED;
                    }

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

            $response = MyInstagram::getInstanse()->sendDirectThread($newUser->pk, 'Привет, я бот. Давай дружить?');

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
            Log::debug('waiting dialog: ' . \json_encode($dialog));

            $threadId = $dialog->thread_id;

            $response = MyInstagram::getInstanse()->getThreadMessages($threadId);

            if ($response->getStatus() == 'ok') {
                Log::debug('пошло: ' . \json_encode($response));

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

                        $otvet = self::getOtvet($messArr);
                        Log::debug('getOtvet: ' . $otvet['txt'] . ' ' . $otvet['status']);
                        if ($otvet['status'] != '') {
                            if ($otvet['txt'] != '') {
                                $sendResp = MyInstagram::getInstanse()->sendDirectThread($threadId, $otvet['txt']);
                                Log::debug('sendResp: ' . \json_encode($sendResp));

                                if ($sendResp->getStatus() == 'ok') {
                                    Log::debug('sended');
                                }
                            }

                            if ($otvet['phone'] != '') {
                                FastTask::mailToDeveloper('Получен телефонный номер', $otvet['phone']);

                                AccountController::mailToClient($account->id
                                    , 'Чатбот (получен номер телефона)'
                                    , 'На аккаунте: ' . $account->nickname. ', чат с: '.$threadTitle.', получен телефон: ' . $otvet['phone']);
                            }

                            ChatHeader::edit([
                                'thread_id' => $threadId,
                                'status' => $otvet['status'],
//                                'last_message_id' => $sendResp->getPayload()->getItemId()
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

    private static function getOtvet(array $messArr)
    {
        $messArr = array_reverse($messArr);

        $myStartMessages = [
            'Привет! Отличный профиль',
            'Привет, как дела?',
            'Предлагаю работу в Instagram. Интересно?'
        ];

        $mySecondMessage = 'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить';
        $myThirdMessage = 'Это Орифлейм. Но это не продажи. Помимо продавцов в компании есть менеджеры, которые всем этим процессом управляют. Вот я, например, не продавец. Я менеджер и занимаюсь набором персонала, который будет помогать мне развивать нашу команду. Работа полностью онлайн. Я всему обучаю.';
        $myFourthMessage = 'ок. успехов';

        $startPositiveOtvet = [
            'да', 'интересно', 'что за работа', 'что нужно делать', 'делать', 'расскажите',
            'подробнее', 'можно', 'суть', 'условия', 'как', 'так', 'реклам', 'возможно', 'не знаю'
        ];

        $okResult = [
            'ok', 'ок'
        ];

        $quesionOtvet1 = [
            'орифлейм', 'ори', 'сетевой', 'продажи', 'эйвон', 'джерел'
        ];

        $startNegativeOtvet = [
            'не интересно', 'нет', 'уже'
        ];

        $isSendNiceProfile = false;
        $isSendFirstJobRequest = false;
        $isSecondJobRequest = false;

        $result = [
            'status' => '',
            'txt' => '',
            'phone' => ''
        ];

        $total = count($messArr)-1;

        Log::debug('total msg: ' . $total);

        foreach($messArr as $number => $msg) {
            $text = $msg['text'];
            Log::debug($number . ' text (is my: '.$msg['isMy'].'):' . $text);

            if ($msg['isMy']) {
                if (strpos($text, $myStartMessages[0]) !== false or strpos($text, $myStartMessages[1]) !== false) {
                    $isSendNiceProfile = true;
                    Log::debug('$isSendNiceProfile = true');
                }
                if (strpos($text, $myStartMessages[2]) !== false) {
                    $isSendFirstJobRequest = true;
                    Log::debug('$isSendFirstJobRequest = true');
                }
                if (strpos($text, $mySecondMessage) !== false) {
                    $isSecondJobRequest = true;
                    Log::debug('$isSecondJobRequest = true');
                }
            } else if (!$msg['isMy'] and $total == $number) {
                Log::debug('last message ' . mb_strtolower($text));

                preg_match('!\d+!', $text, $matches);

                if (count($matches) > 0) {
                    Log::debug('numbers in message ' . \json_encode($matches) . ' count: ' . count($matches) );
                    foreach($matches as $num) {
                        if (strlen($num) > 5) {
                            $result['status'] = ChatHeader::STATUS_DIALOG_FINISHED;
                            $result['phone'] = $num;
                            return $result;
                        }
                    }
                }

                if (self::strposa(mb_strtolower($text), $okResult)) {
                    $result['status'] = ChatHeader::STATUS_WAITING_ANSWER;
                    $result['txt'] = 'Жду номер';
                    break;
                }

                if (self::strposa(mb_strtolower($text), $startNegativeOtvet)) {
                    $result['status'] = ChatHeader::STATUS_DIALOG_FINISHED;
//                    $result['txt'] = $myFourthMessage;
                    break;
                }

                if (self::strposa(mb_strtolower($text), $quesionOtvet1)) {
                    $result['status'] = ChatHeader::STATUS_WAITING_ANSWER;
                    $result['txt'] = $myThirdMessage;
                    break;
                }
                if (self::strposa(mb_strtolower($text), $startPositiveOtvet)) {
                    $result['status'] = ChatHeader::STATUS_WAITING_ANSWER;
                    $result['txt'] = $mySecondMessage;
                    break;
                }

                if ($isSendNiceProfile and self::strposa(mb_strtolower($text), ['спасиб', 'хай'])) {
                    $result['status'] = ChatHeader::STATUS_WAITING_ANSWER;
                    $result['txt'] = $myStartMessages[2];
                    break;
                }
                if ($isSendFirstJobRequest and $text == 'like' and !$isSecondJobRequest) {
                    $result['status'] = ChatHeader::STATUS_WAITING_ANSWER;
                    $result['txt'] = $mySecondMessage;
                    break;
                } else if ($isSecondJobRequest and $text == 'like') {
                    $result['status'] = ChatHeader::STATUS_WAITING_ANSWER;
                    $result['txt'] = 'Жду номер';
                    break;
                }
            }
        }

        return $result;
    }

    private static function strposa($haystack, $needle, $offset=0) {
        if(!is_array($needle)) $needle = array($needle);

        foreach($needle as $query) {
            if(strpos($haystack, $query, $offset) !== false) return true; // stop on first true result
        }

        return false;
    }
}