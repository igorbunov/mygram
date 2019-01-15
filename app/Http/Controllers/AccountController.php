<?php

namespace App\Http\Controllers;

use App\account;
use App\FastTask;
use App\ProxyIps;
use App\Tariff;
use App\User;
use GuzzleHttp\Handler\Proxy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public static function mailToClient(int $accountId, $subject, $message)
    {
        if (!env('ENABLE_EMAIL')) {
            return;
        }

        $account = account::getAccountById($accountId);

        if (is_null($account)) {
            return;
        }

        $message = '['. $account->nickname .'] ' . $message;

        $user = User::getUserById($account->user_id);

        if (is_null($user)) {
            return;
        }

        $headers = "From: mygram.in.ua\nReply-To: {$user->email}\nMIME-Version: 1.\nContent-Type: text/html; charset=UTF-8";

        \mail($user->email, $subject, $message, $headers);
    }

    public function indexAll()
    {
        return $this->index('', false);
    }

    public function index($error = '', $onlyActive = true)
    {
        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return view('main_not_logined');
        }

        $accounts = User::getAccountsByUser($userId, $onlyActive);

        $res = [
            'title' => 'Ваши аккаунты ('.count($accounts).')'
            , 'activePage' => 'accounts'
            , 'onlyActiveAccounts' => $onlyActive
            , 'accounts' => $accounts
            , 'accountPicture' => User::getAccountPictureUrl($userId)
        ];

        if ($error != '') {
            $res['error'] = $error;
        }

        $res['currentTariff'] = Tariff::getUserCurrentTariffForMainView($userId);
//dd($userId, $res['currentTariff']);
        return view('accounts', $res);
    }

    public function create(Request $req)
    {
        $userId = (int) session('user_id', 0);
        $accountId = (int) $req->post('account_id', 0);

        if ($userId == 0) {
            return response()->json(['success' => false, 'message' => 'Потеряна сессия авторизации']);
        }

        $tariff = Tariff::getUserCurrentTariff($userId);

        if (is_null($tariff)) {
            return response()->json(['success' => false, 'message' => 'Не удалось получить тариф']);
        }

        $activeAccounts = account::getActiveAccountsByUser($userId);

        if (count($activeAccounts) >= $tariff->accounts_count) {
            return response()->json(['success' => false, 'message' => 'Вы достигли лимита по активным аккаунтам']);
        }

        $nickname = (string) $req->post('account_name', '');
        $password = (string) $req->post('account_password', '');

        if ($nickname == '' OR $password == '') {
            return response()->json(['success' => false, 'message' => 'Заполните все поля']);
        }

        $proxyIP = ProxyIps::getFreeIp($accountId);

        if (is_null($proxyIP) or empty($proxyIP->proxy_string)) {
            return response()->json(['success' => false, 'message' => 'Добавление не возможно. Нет свободных айпи адресов! Обратитесь в поддержку']);
        }

        if ($accountId == 0) {
            $accountId = account::addNew([
                'user_id' => $userId,
                'nickname' => $nickname,
                'password' => Crypt::encryptString($password),
                'proxy_ip' => $proxyIP->proxy_string
            ]);
        } else {
            $accountId = account::editById([
                'account_id' => $accountId,
                'user_id' => $userId,
                'nickname' => $nickname,
                'password' => Crypt::encryptString($password),
                'proxy_ip' => $proxyIP->proxy_string
            ]);
        }

        if ($accountId == 0) {
            return response()->json(['success' => false, 'message' => 'Не удалось создать аккаунт']);
        }

        try {
            ProxyIps::setAccountId($proxyIP, $accountId);
        } catch (\Exception $err) {
            return response()->json(['success' => false, 'message' => $err->getMessage()]);
        }

        $fastTaskId = FastTask::addTask($accountId, FastTask::TYPE_TRY_LOGIN);

        return response()->json(['success' => true, 'fastTaskId' => $fastTaskId]);
    }

    public function addAccountCode(Request $req)
    {
        $userId = (int) session('user_id', 0);
        $accountId = (int) $req->post('account_id', 0);
        $code = (string) $req->post('code', '');

        if (empty($code) or $accountId == 0) {
            return response()->json(['success' => false, 'message' => 'Заполните все поля']);
        }

        if ($userId == 0) {
            return response()->json(['success' => false, 'message' => 'Потеряна сессия авторизации']);
        }

        $tariff = Tariff::getUserCurrentTariff($userId);

        if (is_null($tariff)) {
            return response()->json(['success' => false, 'message' => 'Не удалось получить тариф']);
        }

        $activeAccounts = account::getActiveAccountsByUser($userId);

        if (count($activeAccounts) >= $tariff->accounts_count) {
            return response()->json(['success' => false, 'message' => 'Вы достигли лимита по активным аккаунтам']);
        }

        account::editById([
            'account_id' => $accountId,
            'verify_code' => $code
        ]);

        $fastTaskId = FastTask::addTask($accountId, FastTask::TYPE_TRY_LOGIN);

        return response()->json(['success' => true, 'fastTaskId' => $fastTaskId]);
    }

    public function sync(Request $req) {
        $userId = (int) session('user_id', 0);

        $accountId = (int) $req->post('account_id', 0);

        if ($accountId == 0) {
            return response()->json(['success' => false, 'error' => 'Не верный аккаунт']);
        }

        if (!account::isAccountBelongsToUser($userId, $accountId)) {
            return response()->json(['success' => false, 'error' => 'Это не ваш аккаунт']);
        }

        $fastTaskId = FastTask::addTask($accountId, FastTask::TYPE_REFRESH_ACCOUNT);

        return response()->json(['success' => true, 'fastTaskId' => $fastTaskId]);
    }

    public function changeStatus(Request $req)
    {
        $accountId = (int) $req->post('account_id', 0);
        $isActive = (int) $req->post('is_active', 1);

        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return response()->json(['success' => false, 'error' => 'Необходимо авторизоваться']);
        }

        if ($accountId == 0) {
            return response()->json(['success' => false, 'error' => 'Не выбран аккаунт']);
        }

        if (!account::isAccountBelongsToUser($userId, $accountId)) {
            return response()->json(['success' => false, 'error' => 'Это не ваш аккаунт']);
        }

        $tariff = Tariff::getUserCurrentTariff($userId);

        if (is_null($tariff)) {
            return response()->json(['success' => false, 'error' => 'У вас закончился тариф. Функция активации аккаунта отключена.']);
        }

        $activeAccounts = account::getActiveAccountsByUser($userId);

        if ($isActive and count($activeAccounts) >= $tariff->accounts_count) {
            return response()->json(['success' => false, 'error' => 'Вы достигли лимита по кол-ву активных аккаунтов для текущего тарифа']);
        }

        account::changeStatus($accountId, $isActive);

        return response()->json(['success' => true]);
    }
}
