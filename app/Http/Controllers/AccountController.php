<?php

namespace App\Http\Controllers;

use App\account;
use App\FastTask;
use App\Tariff;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
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
            'title' => 'Ваши аккаунты'
            , 'activePage' => 'accounts'
            , 'onlyActiveAccounts' => $onlyActive
            , 'accounts' => $accounts
            , 'accountPicture' => User::getAccountPictureUrl($userId)
        ];

        if ($error != '') {
            $res['error'] = $error;
        }

        $res['currentTariff'] = Tariff::getUserCurrentTariffForMainView($userId);

        return view('accounts', $res);
    }

    public function create(Request $req)
    {
        $userId = (int) session('user_id', 0);

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

        $accountId = account::addNew([
            'user_id' => $userId,
            'nickname' => $nickname,
            'password' => Crypt::encryptString($password)
        ]);

        if ($accountId == 0) {
            return response()->json(['success' => false, 'message' => 'Не удалось создать аккаунт']);
        }

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
