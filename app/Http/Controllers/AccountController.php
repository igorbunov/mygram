<?php

namespace App\Http\Controllers;

use App\account;
use App\FastTask;
use App\Http\Controllers\TaskGenerator\ValidateAccountTaskCreator;
use App\Tariff;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index($error = '')
    {
        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return view('main_not_logined');
        }

        $accounts = User::getAccountsByUser($userId);

        $res = [
            'title' => 'Акаунты'
            , 'activePage' => 'accounts'
            , 'accounts' => $accounts
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
            return redirect('accounts');
        }

        $tariff = Tariff::getUserCurrentTariff($userId);

        if (is_null($tariff)) {
            return redirect('accounts');
        }

        $activeAccounts = account::getActiveAccountsByUser($userId);

        if (count($activeAccounts) >= $tariff->accounts_count) {
            return redirect('accounts');
        }

        $nickname = (string) $req->post('account_name', '');
        $password = (string) $req->post('account_password', '');

        if ($nickname == '' OR $password == '') {
            return $this->index('Пустые поля');
        }

        $accountId = account::addNew([
            'user_id' => $userId,
            'nickname' => $nickname,
            'password' => Crypt::encryptString($password)
        ]);

        if ($accountId > 0) {
            FastTask::addTask($accountId, FastTask::TYPE_TRY_LOGIN);
        }

        return redirect('account/' . $accountId);
    }

    public function sync(Request $req) {
        $userId = (int) session('user_id', 0);

        $nickname = (string) $req->post('account_name', '');

        if ($nickname == '') {
            return response()->json(['success' => false, 'error' => 'Не верный никнейм']);
        }
        //TODO: run sync task
//        DB::table('accounts')->where(['nickname' => $nickname, 'user_id' => $userId])->delete();

        return response()->json(['success' => true]);
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
