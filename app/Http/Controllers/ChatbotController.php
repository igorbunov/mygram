<?php

namespace App\Http\Controllers;

use App\Chatbot;
use App\ChatbotAccounts;
use App\FastTask;
use App\Tariff;
use App\User;
use const Grpc\CHANNEL_CONNECTING;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function toggleAccount(Request $req)
    {
        $userId = (int) session('user_id', 0);
        $nickname = (string) $req->post('nickname', '');
        $isChecked = (int) $req->post('isChecked', -1);

        if ($userId == 0) {
            return response()->json(['success' => false, 'message' => 'Потеряна сессия авторизации']);
        }

        $tariff = Tariff::getUserCurrentTariff($userId);

        if (is_null($tariff)) {
            return response()->json(['success' => false, 'message' => 'Не удалось получить тариф']);
        }

        $chatBot = Chatbot::getByUserId($userId);

        if (is_null($chatBot)) {
            return response()->json(['success' => false, 'message' => 'Ошибка получения чатбота']);
        }

        if ($nickname == '') {
            return response()->json(['success' => false, 'message' => 'Не указан никнейм']);
        }
        if ($isChecked == -1) {
            return response()->json(['success' => false, 'message' => 'Не указан статус']);
        }

        $res = ChatbotAccounts::setIsInSendlist($chatBot, $nickname, $isChecked);

        if (!$res) {
            return response()->json(['success' => false,'message' => 'Ошибка, не удалось изменить статус']);
        }

        //Safelist::updateStatistics($safeList->id);

        return response()->json(['success' => true, 'is_checked' => $isChecked]);
    }

    public function getChatbotAccountsAjax(Request $req)
    {
        $userId = (int) session('user_id', 0);
        $start = (int) $req->post('start', 0);
        $limit = 50;

        if ($userId == 0) {
            return response()->json(['success' => false, 'message' => 'Потеряна сессия авторизации']);
        }

        $tariff = Tariff::getUserCurrentTariff($userId);

        if (is_null($tariff)) {
            return response()->json(['success' => false, 'message' => 'Не удалось получить тариф']);
        }

        $chatBot = Chatbot::getByUserId($userId);

        if (is_null($chatBot)) {
            return response()->json(['success' => false, 'message' => 'Ошибка получения чатбота']);
        }

        $allAccounts = ChatbotAccounts::getAll($chatBot, $start, $limit);

        return view('chatbot.chatbot_account_item', [
            'chatBotAccounts' => $allAccounts['data'],
            'chatBotAccountsTotal' => $allAccounts['total'],
            'start' => $start,
            'limit' => $limit
        ]);
    }

    public function index()
    {
        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return view('main_not_logined');
        }

        $accounts = User::getAccountsByUser($userId, true);

        $chatbot = Chatbot::getByUserId($userId);

        if (is_null($chatbot)) {
            Chatbot::add(['user_id' => $userId, 'hashtags' => '']);

            $chatbot = Chatbot::getByUserId($userId);
        }

        $chatbot->hashtags = str_replace('|', "\n", $chatbot->hashtags);

        switch($chatbot->status) {
            case Chatbot::STATUS_EMPTY:
                $chatbot->statusRus = 'Пусто';
                break;
            case Chatbot::STATUS_UPDATING:
                $chatbot->statusRus = 'Загрузка списка';
                break;
            case Chatbot::STATUS_FINISHED:
                $chatbot->statusRus = 'Выполнен';
                break;
            case Chatbot::STATUS_SYNCHRONIZED:
                $chatbot->statusRus = 'Список загружен';
                break;
            case Chatbot::STATUS_IN_PROGRESS:
                $chatbot->statusRus = 'В процессе';
                break;
        }

        $allAccounts = ChatbotAccounts::getAll($chatbot);

        $res = [
            'title' => 'Чат бот'
            , 'activePage' => 'chatbot'
            , 'accounts' => $accounts
            , 'chatbot' => $chatbot
            , 'currentTariff' => Tariff::getUserCurrentTariffForMainView($userId)
            , 'accountPicture' => User::getAccountPictureUrl($userId)
            , 'chatBotAccounts' => $allAccounts['data']
            , 'chatBotAccountsTotal' => $allAccounts['total']
            , 'start' => 0
            , 'limit' => 50
        ];


        return view('chatbot.main', $res);
    }

    public function setStatus(Request $req)
    {
        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return response()->json(['success' => false, 'error' => 'Необходимо авторизоваться']);
        }

        $status = (string) $req->post('status', '');

        if (!in_array($status, [Chatbot::STATUS_IN_PROGRESS, Chatbot::STATUS_SYNCHRONIZED
            , Chatbot::STATUS_FINISHED, Chatbot::STATUS_UPDATING, Chatbot::STATUS_EMPTY])) {
            return response()->json(['success' => false, 'error' => 'Не верный статус']);
        }

        $chatbot = Chatbot::getByUserId($userId);

        if (is_null($chatbot)) {
            return response()->json(['success' => false, 'error' => 'Не удалось получить чатбота']);
        }

        Chatbot::setStatus($chatbot->id, $status);

        return response()->json(['success' => true]);
    }

    public function updateList(Request $req)
    {
        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return response()->json(['success' => false, 'error' => 'Необходимо авторизоваться']);
        }

        //TODO: проверить тариф и доступность этого таска

        $hashtags = (string) $req->post('hashtags', '');
        $hashtags = trim($hashtags);

        if (empty($hashtags)) {
            return response()->json(['success' => false, 'error' => 'Вы не указали хештеги']);
        }

        $hashtags = explode("|", $hashtags);

        foreach($hashtags as $i => $hashtag) {
            $hashtags[$i] = str_replace("#", "", $hashtag);
        }

        $workWithDirectAnswerTask = (int) $req->post('work_with_direct_answer_task', '0');
        $maxAccounts = (int) $req->post('max_accounts', '100');

        $chatbot = Chatbot::getByUserId($userId);

        if (is_null($chatbot)) {
            Chatbot::add([
                'user_id' => $userId,
                'hashtags' => implode('|', $hashtags),
                'max_accounts' => $maxAccounts,
                'work_with_direct_answer_task' => $workWithDirectAnswerTask,
                'status' => Chatbot::STATUS_UPDATING
            ]);

            $chatbot = Chatbot::getByUserId($userId);
        }

        Chatbot::edit([
            'id' => $chatbot->id,
            'hashtags' => implode('|', $hashtags),
            'max_accounts' => $maxAccounts,
            'work_with_direct_answer_task' => $workWithDirectAnswerTask,
            'status' => Chatbot::STATUS_UPDATING
        ]);


        $chatbotId = $chatbot->id;

        if ($chatbotId == 0) {
            return response()->json(['success' => false, 'error' => 'Ошибка создания чатбота']);
        }

        $accounts = User::getAccountsByUser($userId, true);

        if (count($accounts) == 0) {
            return response()->json(['success' => false, 'error' => 'Ошибка, необходимо чтоб был хотябы один активный аккаунт']);
        }

        $accountId = $accounts[0]->id;

        $fastTaskId = FastTask::addTask($accountId, FastTask::TYPE_REFRESH_CHATBOT_LIST, $chatbotId);

        return response()->json(['success' => true, 'fastTaskId' => $fastTaskId]);
    }
}
