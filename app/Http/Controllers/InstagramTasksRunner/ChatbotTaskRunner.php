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
//        Log::debug('=== start async method: ChatbotTaskRunner->runRefreshList ' . $chatbotId . ' ===');
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
/*
        $hashtags = explode('|', $chatbot->hashtags);

        Log::debug('$hashtags: ' . \json_encode($hashtags));

        MyInstagram::getInstanse()->login($account);

        $newUsers = MyInstagram::getInstanse()->findUsersByHashtag($hashtags, $chatbot->max_accounts, $chatbot->id, $account->user_id);
*/
        // TODO: find users by likes
        $userAccounts = explode('|', $chatbot->hashtags);

        Log::debug('userAccounts: ' . \json_encode($userAccounts));

        MyInstagram::getInstanse()->login($account);

        $newUsers = MyInstagram::getInstanse()->findUsersByLikes($userAccounts, $chatbot->id, $account->user_id);

        $allAccountsSafelist = Safelist::getSafelistForAllAccounts($account->user_id);

        $allAccountsSubsDirect = AccountSubscribers::getAllAccountsUsersPKS($account->user_id);

        $blackListNicknames = [
            'ua', 'store', 'shop', 'ukraine', 'salon', 'busines', 'salon', 'nails', 'bissnes', 'dress',
            'style', 'moda', 'clothes', 'odezhda', 'look', 'apple', 'watch', 'design', 'pharmacy', 'agency',
            'fitness', 'sport', 'doll', 'toys', 'parfume', 'shoes' , 'mag', 'official', 'avon', 'smm',
            'fashion', 'cosmet', 'shugar', 'babe', 'eco', 'food', 'ru', 'russia', 'travel', 'master',
            'box', 'decor', 'jewelry', 'galler', 'iphone', 'ipad', 'service', 'beauty', 'showroom', 'catalog',
            'marketing', 'free', 'tkani', 'child', 'baby', 'skidki', 'lingerie', 'nogotki'
        ];

        $manNames = ['Aleksandr','Alexander','Sasha','Aleksey','Alexey','Alyosha','Anatoly','Anatoliy','Tolya','Andrey','Andrei','Andryusha','Anton','Arkady','Arkadiy','Arkasha','Artem','Artyom','Tyoma','Artur','Boris','Borya','Vadim','Vadik','Valentin','Valya','Valeriy','Valera','Vasily','Vasiliy','Vasya','Viktor','Victor','Vitya','Vitaly','Vitaliy','Vitya','Vladimir','Vova','Volodya','Vladislav','Vlad','Slava','Vyacheslav','Viacheslav','Slava','Gennady','Gennadiy','Gena','Georgy','Georgiy','Gosha','Gleb','Grigory','Grigoriy','Grisha','Daniil','Danila','Denis','Dmitry','Dmitriy','Dima','Yevgeny','Yevgeniy','Zhenya','Yegor','Egor','Gosha','Zakhar','Zahar','Ivan','Vanya','Igor','Ilya','Ilia','Ilyusha','Innokenty','Innokentiy','Kesha','Iosif','Kirill','Konstantin','Kostya','Lev','Lyova','Leonid','Lyonya','Maksim','Maxim','Max','Matvey','Matvei','Mikhail','Misha','Moisey','Nikita','Nikolay','Nikolai','Kolya','Oleg','Pavel','Pasha','Pyotr','Petr','Petya','Roman','Roma','Ruslan','Svyatoslav','Sviatoslav','Slava','Semyon','Senya','Syoma','Sergey','Sergei','Seryozha','Stanislav','Stas','Stepan','Styopa','Timofey','Timofei','Tima','Timur','Timour','Fedor','Fyodor','Fedya','Filipp','Philipp','Filya','Eduard','Edward','Edik','Yuri','Yuriy','Yury','Yura','Yakov','Iakov','Yasha','Yan','Ian','Yaroslav'];

        foreach($newUsers as $userPk => $userRow) {
            if (!array_key_exists($userPk, $allAccountsSafelist)
                and !array_key_exists($userPk, $allAccountsSubsDirect)) {

                $isBad = false;

                foreach($blackListNicknames as $badNickname) {
                    if (strpos($userRow['username'], $badNickname) !== false) {
                        $isBad = true;
                        break;
                    }
                }

                if ($isBad) {
                    continue;
                }

                foreach($manNames as $manNickname) {
                    if (strpos(strtolower($userRow['username']), strtolower($manNickname)) !== false) {
                        $isBad = true;
                        break;
                    }
                }

                if (!$isBad) {
                    ChatbotAccounts::add($userRow);
                }
            }
        }

        ChatbotAccounts::updateStatistics($chatbot);
        Chatbot::setStatus($chatbot->id, Chatbot::STATUS_SYNCHRONIZED);

//        Log::debug('done');
    }

    public static function getDirectInbox(int $accountId)
    {
//        Log::debug('=== start async method: ChatbotTaskRunner->getDirectInbox ' . $accountId . ' ===');
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

        $allAccountsSafelist = Safelist::getSafelistForAllAccounts($account->user_id);

        MyInstagram::getInstanse()->login($account);

        if (ChatHeader::isChatExists($chatBot, $account)) {
            $cursorId = null;
            $counter = 0;

            while($counter++ < 1) { // 40 dialogs
                $messages = MyInstagram::getInstanse()->getDirectInbox($cursorId);

                if ($messages->getStatus() == 'ok') {
                    $inbox = $messages->getInbox();

                    $threads = $inbox->getThreads();
//                    Log::debug('found threads for (' . $account->nickname . '): ' . count($threads));

                    foreach($threads as $thread) {
                        $threadId = $thread->getThreadId();
                        $threadTitle = $thread->getThreadTitle();
                        $lastMessageId = $thread->getNewestCursor();

                        $users = $thread->getUsers();

                        if (count($users) == 0) {
                            Log::debug('['.$account->nickname.']не получил юзеров в диалоге: ' . $threadTitle);
                            Log::debug('dialog: ' . \json_encode($thread));
                            continue;
                        }

                        $companionPK = $users[0]->getPk();
                        $inSafeList = false;

                        if (array_key_exists($companionPK, $allAccountsSafelist)) {
                            continue;
                        }

                        $chatHeader = ChatHeader::getHeaderByThreadId($chatBot, $threadId);

                        if (!is_null($chatHeader)) {
                            if ($inSafeList) {
//                                Log::debug('chat header in safelist, skip');
                                continue;
                            }
                            if ($chatHeader->status == ChatHeader::STATUS_DIALOG_FINISHED) {
//                                Log::debug('dialog ' . $threadTitle . ' finished, skip');
                                continue;
                            }

//                            Log::debug('chat header exists: ' . $threadId);


                            if ($chatHeader->last_message_id == $lastMessageId) {
//                                Log::debug('isLastMessageSame not changed');
                                continue;
                            }

                            Log::debug('['.$account->nickname.'] isLastMessageSame changed, threadTitle: ' . $threadTitle);

                            ChatHeader::edit([
                                'thread_id' => $threadId,
                                'status' => ChatHeader::STATUS_DIALOG_NEED_ANALIZE,
                                'last_message_id' => $lastMessageId
                            ]);

                            MyInstagram::getInstanse()->markItemSeen($threadId, $lastMessageId);
                        } else {
                            Log::debug('['.$account->nickname.'] chat header not exists, adding: ' . $threadTitle);

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

                            MyInstagram::getInstanse()->markItemSeen($threadId, $lastMessageId);
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

//                $allAccountsSafelist = Safelist::getSafelistForAllAccounts($account->user_id);
//                Log::debug('found threads for 1 (' . $account->nickname . '): ' . count($threads));

                foreach($threads as $thread) {
                    $status = ChatHeader::STATUS_WAITING_ANSWER;
                    $users = $thread->getUsers();

                    if (is_null($users) or count($users) == 0) {
                        continue;
                    }

                    $companionPK = $thread->getUsers()[0]->getPk();

                    if (array_key_exists($companionPK, $allAccountsSafelist)) {
                        continue;
                    }

                    Log::debug('['.$account->nickname.'] первое добавление диалога ' . $thread->getThreadTitle() );

                    $threadId = $thread->getThreadId();
                    $lastMessageId = $thread->getNewestCursor();

                    ChatHeader::add([
                        'account_id' => $account->id,
                        'chatbot_id' => $chatBot->id,
                        'thread_id' => $threadId,
                        'thread_title' => $thread->getThreadTitle(),
                        'my_pk' => $account->pk,
                        'companion_pk' => $companionPK,
                        'last_message_id' => $lastMessageId,
                        'status' => $status
                    ]);

                    MyInstagram::getInstanse()->markItemSeen($threadId, $lastMessageId);
                }
            }
        }

//        Log::debug('getDirectInbox done');
    }

    public static function sendFirstMessage(int $accountId)
    {
//        Log::debug('=== start async method: ChatbotTaskRunner->sendFirstMessage accountId: ' . $accountId . ' ===');
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

        if ($chatBot->status != Chatbot::STATUS_IN_PROGRESS) {
            return;
        }


        if (Chatbot::getInQueueChats($chatBot) == 0) {
            return;
        }

//        Log::debug('chatbot: ' . \json_encode($chatBot->toArray()));

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

            try {
//                if (!in_array($account->nickname, $errorList)) {
//                    if ($newUser->is_private_profile == 0) {
//                        MyInstagram::getInstanse()->likeSomePosts($account->nickname, $newUser->pk);
//                    }
//                }

                $subRes = MyInstagram::getInstanse()->subscribe($newUser->pk);

                if (!is_null($subRes) AND $subRes->isOk()) {
                    Log::debug('['.$account->nickname.'] подписался (chatbot): ' . $newUser->username);
                } else {
                    Log::debug('['.$account->nickname.'] не удалось подписатся (chatbot): ' . \json_encode($subRes));
                }

                sleep(rand(5, 10));
            } catch (\Exception $err0) {
                Log::debug('ошибка подписки ' . $newUser->username . ' ' . \json_encode($err0->getMessage()));
            }

            $response = MyInstagram::getInstanse()->sendDirect($newUser->pk, 'Доброго времени суток. Предлагаю работу в Инстаграм. Интересно?');

            if ($response->isOk()) {
                Log::debug('['.$account->nickname.'] message sended to (chatbot): ' . $newUser->username);
            } else {
                Log::error('['.$account->nickname.'] error send message to (chatbot): ' . $newUser->username . ' error: ' . \json_encode($response));
            }

            break;
        }

        ChatbotAccounts::updateStatistics($chatBot);

        if (Chatbot::getInQueueChats($chatBot) == 0) {
            AccountController::mailToClient($accountId, 'Закончена очередь для чатбота', 'Очередь для чатбота закончена, необходимо составить новый список рассылки');
            FastTask::mailToDeveloper('Закончена очередь для чатбота', 'Чатбот ' . $chatBot->id);
        }

//        Log::debug('done');
    }

    public static function analizeDialogAndAnswer(int $chatbotId, int $accountId)
    {
//        Log::debug('=== start async method: ChatbotTaskRunner->analizeDialogAndAnswer ' . $chatbotId . ' === ' . $accountId);
        $account = account::getAccountById($accountId);

        if (is_null($account)) {
//            Log::debug('account not found');
            return;
        }

        $chatBot = Chatbot::getByUserId($account->user_id);

        if (is_null($chatBot) or $chatBot->id != $chatbotId) {
//            Log::debug('chatbot by user_id not found');
            return;
        }

//        Log::debug('chatBot: ' . \json_encode($chatBot->toArray()));

        $waitingDialogs = ChatHeader::getWaitingAnalisys($chatBot, $account, ChatHeader::STATUS_DIALOG_NEED_ANALIZE);

        if (is_null($waitingDialogs)) {
//            Log::debug('no waiting dialogs for account: ' . $account->nickname);
            return;
        }

//        Log::debug('waiting analisys: ' . count($waitingDialogs));

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

                        if (count($messages) == 0) {
                            Log::debug('['.$account->nickname.'] ===!!! нет сообщений в диалоге: ' . $threadTitle);
                            continue;
                        }

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
                        $otvet = null;

                        try {
                            $otvet = $bot->getAnswer($messArr);
                        } catch (\Exception $err2) {
                            Log::error('бот задублировал сообщение');
                            FastTask::mailToDeveloper('Чатбот (задублировал сообщение)', \json_encode($messArr));
                            $otvet = ['status' => '', 'txt' => '', 'phone' => ''];
                        }

                        Log::debug('['.$account->nickname.'] == ответ ' . $threadTitle . ' == ' . $otvet['txt'] . ' ' . $otvet['status']);

                        if ($otvet['ori']) {
                            $emailMessage = view('chatbot.mail_chat', [
                                'account' => $account->nickname,
                                'dialog' => $messArr,
                                'threadTitle' => $threadTitle,
                                'phone' => $otvet['phone']
                            ]);

                            Log::debug('['.$account->nickname.'] чат с: '.$threadTitle.' вопрос про ори, написали имейл');

                            ChatHeader::edit([
                                'thread_id' => $threadId,
                                'status' => ChatHeader::STATUS_DIALOG_FINISHED
                            ]);

                            AccountController::mailToClient($account->id, 'Чатбот (вопрос про орифлейм)', $emailMessage);

                            FastTask::mailToDeveloper('Чатбот (вопрос про орифлейм)', $emailMessage);
                        } else if ($otvet['status'] != '') {
                            if ($otvet['phone'] != '') {
                                $phoneTaken = $otvet['phone'];

                                if (mb_strlen($phoneTaken) > 240) {
                                    $phoneTaken = substr($phoneTaken, '0','240');
                                }

                                ChatHeader::edit([
                                    'thread_id' => $threadId,
                                    'status' => ChatHeader::STATUS_DIALOG_FINISHED,
                                    'taken_phone' => $phoneTaken
                                ]);

                                try {
                                    Log::debug('['.$account->nickname.'] На аккаунте: ' . $account->nickname. ', чат с: '.$threadTitle.', получен телефон: ' . $otvet['phone']);

                                    $emailMessage = view('chatbot.mail_chat', [
                                        'account' => $account->nickname,
                                        'dialog' => $messArr,
                                        'threadTitle' => $threadTitle,
                                        'phone' => $otvet['phone']
                                    ]);

                                    AccountController::mailToClient($account->id, 'Чатбот (получен номер телефона)', $emailMessage);

                                    FastTask::mailToDeveloper('Чатбот (получен номер телефона)', $emailMessage);
                                } catch (\Exception $err1) {
                                    Log::error('Ошибка отправки чата клиента ' . $err1->getMessage() . ' ' . $err1->getTraceAsString());
                                }
                            } else if ($otvet['txt'] != '') {
                                $sendResp = MyInstagram::getInstanse()->sendDirectThread($threadId, $otvet['txt']);

                                if ($sendResp->getStatus() == 'ok') {
                                    Log::debug('['.$account->nickname.']  == sended == ');
                                }

                                ChatHeader::edit([
                                    'thread_id' => $threadId,
                                    'status' => ChatHeader::STATUS_WAITING_ANSWER
                                ]);
                            } else {
                                if ($otvet['status'] == BotController::STATUS_DIALOG_FINISHED) {
                                    Log::debug('['.$account->nickname.'] пользователь '. $threadTitle . ' отказался');

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

                            if ($otvet['txt'] == '') {
                                try {
                                    $emailMessage = view('chatbot.mail_chat', [
                                        'account' => $account->nickname,
                                        'dialog' => $messArr,
                                        'threadTitle' => $threadTitle,
                                        'phone' => $otvet['phone']
                                    ]);

                                    FastTask::mailToDeveloper('Чатбот (ответ не ясен)', $emailMessage);
                                    AccountController::mailToClient($account->id, 'Чатбот (ответ не ясен)', $emailMessage);
                                } catch (\Exception $err2) {
                                    Log::error('Ошибка отправки чата ' . $err2->getMessage() . ' ' . $err2->getTraceAsString());
                                }
                            }
                        }

//                        Log::debug('messages: ' . \json_encode($messages));
                    }
                }
            } else {
                Log::error('чет не пошло: ' . \json_encode($response));
            }

            sleep(rand(5, 10));
        }

//        Log::debug('done');
    }

    private static function isMyMessage($myPk, $senderPk)
    {
        return $myPk == $senderPk;
    }

}