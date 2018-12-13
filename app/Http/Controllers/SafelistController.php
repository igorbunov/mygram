<?php

namespace App\Http\Controllers;

use App\account;
use App\AccountSubscriptions;
use App\FastTask;
use App\Safelist;
use App\Tariff;
use App\User;
use Illuminate\Http\Request;

class SafelistController extends Controller
{
    public function toggleUser(Request $req)
    {
        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return response()->json(['success' => false, 'message' => 'Потеряна сессия авторизации']);
        }

        $tariff = Tariff::getUserCurrentTariff($userId);

        if (is_null($tariff)) {
            return response()->json(['success' => false, 'message' => 'Не удалось получить тариф']);
        }

        $accountId = (int) $req->post('account_id', 0);

        if (!account::isAccountBelongsToUser($userId, $accountId)) {
            return response()->json(['success' => false, 'message' => 'Это не ваш аккаунт']);
        }

        $safeList = Safelist::getByAccountId($accountId);

        if (is_null($safeList)) {
            return response()->json(['success' => false, 'message' => 'Ошибка получения списка']);
        }



        return response()->json(['success' => true, 'accountId' => $accountId]);
    }

    public function updateList(Request $req)
    {
        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return response()->json(['success' => false, 'message' => 'Потеряна сессия авторизации']);
        }

        $tariff = Tariff::getUserCurrentTariff($userId);

        if (is_null($tariff)) {
            return response()->json(['success' => false, 'message' => 'Не удалось получить тариф']);
        }

        $accountId = (int) $req->post('account_id', 0);

        if (!account::isAccountBelongsToUser($userId, $accountId)) {
            return response()->json(['success' => false, 'message' => 'Это не ваш аккаунт']);
        }

        $safeList = Safelist::getByAccountId($accountId);

        if (is_null($safeList)) {
            return response()->json(['success' => false, 'message' => 'Ошибка получения списка']);
        }

        Safelist::setStatus($safeList->id, Safelist::STATUS_UPDATING);

        FastTask::addTask($accountId, FastTask::TYPE_REFRESH_WHITELIST, $safeList->id);

        return response()->json(['success' => true]);
    }

    public function index()
    {
        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return view('main_not_logined');
        }

        $accounts = User::getAccountsByUser($userId, true);

        foreach ($accounts as $i => $account) {
            $accounts[$i]->safelistInfo = Safelist::getOrCreate($account->id);
        }

        $res = [
            'title' => 'Белый список'
            , 'activePage' => 'safelist'
            , 'accounts' => $accounts
            , 'currentTariff' => Tariff::getUserCurrentTariffForMainView($userId)
            , 'accountPicture' => User::getAccountPictureUrl($userId)
        ];

        return view('safelist.accounts_list', $res);
    }

    public function getSafelist(int $accountId)
    {
        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return view('main_not_logined');
        }

        if (!account::isAccountBelongsToUser($userId, $accountId)) {
            return redirect('/safelist');
        }

        $account = account::getAccountById($accountId);

        if ($account->is_active == 0 or $account->is_confirmed == 0) {
            return redirect('/safelist');
        }

        $safelist = Safelist::getOrCreate($accountId);

        $allSubscibtions = AccountSubscriptions::getAll($accountId);

        $res = [
            'title' => 'Белый список @' . $account->nickname
            , 'accountId' => $accountId
            , 'activePage' => 'safelist'
            , 'totalSubscriptions' => $safelist->total_subscriptions
            , 'selectedAccounts' => $safelist->selected_accounts
            , 'status' => $safelist->status
            , 'safelist' => $allSubscibtions
            , 'accountPicture' => User::getAccountPictureUrl($userId, $accountId)
            , 'currentTariff' => Tariff::getUserCurrentTariffForMainView($userId)
        ];

        return view('safelist.main', $res);
    }
}
