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
    const SAFELIST_LIMIT = 50;

    public function clearUsers(Request $req)
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

        if (!account::isAccountBelongsToUser($userId, $accountId)) {
            return response()->json(['success' => false, 'message' => 'Это не ваш аккаунт']);
        }

        $safeList = Safelist::getByAccountId($accountId);

        if (is_null($safeList)) {
            return response()->json(['success' => false, 'message' => 'Ошибка получения списка']);
        }

        AccountSubscriptions::setAllNotInSafelist($accountId);


        $allSubscibtions = AccountSubscriptions::getAll($accountId, true);

        $view = view('safelist.safelist_item', [
            'safelist' => $allSubscibtions,
            'accountId' => $accountId
        ]);

        return $view;

//        return response()->json(['success' => true, 'accountId' => $accountId]);
    }

    public function toggleUser(Request $req)
    {
        $userId = (int) session('user_id', 0);
        $accountId = (int) $req->post('account_id', 0);
        $nickname = (string) $req->post('nickname', '');
        $isChecked = (int) $req->post('isChecked', -1);

        if ($userId == 0) {
            return response()->json(['success' => false, 'message' => 'Потеряна сессия авторизации']);
        }

        $tariff = Tariff::getUserCurrentTariff($userId);

        if (is_null($tariff)) {
            return response()->json(['success' => false, 'message' => 'Не удалось получить тариф']);
        }

        if (!account::isAccountBelongsToUser($userId, $accountId)) {
            return response()->json(['success' => false, 'message' => 'Это не ваш аккаунт']);
        }

        $safeList = Safelist::getByAccountId($accountId);

        if (is_null($safeList)) {
            return response()->json(['success' => false, 'message' => 'Ошибка получения списка']);
        }

        if ($nickname == '') {
            return response()->json(['success' => false, 'message' => 'Не указан никнейм']);
        }
        if ($isChecked == -1) {
            return response()->json(['success' => false, 'message' => 'Не указан статус']);
        }

        $res = AccountSubscriptions::setIsInSafelist($accountId, $nickname, $isChecked);

        if (!$res) {
            return response()->json(['success' => false,'message' => 'Ошибка, не удалось изменить статус']);
        }

        Safelist::updateStatistics($safeList->id);

        return response()->json(['success' => true, 'is_checked' => $isChecked, 'accountId' => $accountId]);
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

        $fastTaskId = FastTask::addTask($accountId, FastTask::TYPE_REFRESH_WHITELIST, $safeList->id);

        return response()->json(['success' => true, 'fastTaskId' => $fastTaskId]);
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

    public function getSafelistAjax(Request $req)
    {
        $userId = (int) session('user_id', 0);
        $accountId = (int) $req->post('account_id', 0);
        $isAll = (int) $req->post('is_all', 1);
        $start = (int) $req->post('start', 0);
        $limit = self::SAFELIST_LIMIT;

        if ($userId == 0) {
            return response()->json(['success' => false, 'message' => 'Потеряна сессия авторизации']);
        }

        $tariff = Tariff::getUserCurrentTariff($userId);

        if (is_null($tariff)) {
            return response()->json(['success' => false, 'message' => 'Не удалось получить тариф']);
        }

        if (!account::isAccountBelongsToUser($userId, $accountId)) {
            return response()->json(['success' => false, 'message' => 'Это не ваш аккаунт']);
        }

        $safeList = Safelist::getByAccountId($accountId);

        if (is_null($safeList)) {
            return response()->json(['success' => false, 'message' => 'Ошибка получения списка']);
        }

        $allSubscibtions = AccountSubscriptions::getAll($accountId, ($isAll > 0), false, $start, $limit);

        return view('safelist.safelist_item', [
            'accountId' => $accountId,
            'safelist' => $allSubscibtions['data'],
            'safelistTotal' => $allSubscibtions['total'],
            'start' => $start,
            'limit' => $limit,
            'is_all' => 1
        ]);
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
        $start = 0;
        $limit = self::SAFELIST_LIMIT;

        $allSubscibtions = AccountSubscriptions::getAll($accountId, true, false, $start, $limit);

        $res = [
            'title' => 'Белый список @' . $account->nickname
            , 'accountId' => $accountId
            , 'activePage' => 'safelist'
            , 'totalSubscriptions' => $safelist->total_subscriptions
            , 'selectedAccounts' => $safelist->selected_accounts
            , 'status' => $safelist->status
            , 'safelist' => $allSubscibtions['data']
            , 'safelistTotal' => $allSubscibtions['total']
            , 'start' => $start
            , 'limit' => $limit
            , 'is_all' => 1
            , 'accountPicture' => User::getAccountPictureUrl($userId, $accountId)
            , 'currentTariff' => Tariff::getUserCurrentTariffForMainView($userId)
        ];
//dd($res);
        return view('safelist.main', $res);
    }
}
