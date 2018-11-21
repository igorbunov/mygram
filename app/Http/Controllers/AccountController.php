<?php

namespace App\Http\Controllers;

use App\account;
use App\Tariff;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($error = '')
    {
        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return view('main_not_logined');
        }

        $accounts = User::find($userId)->accounts;

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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $req)
    {
        $userId = (int) session('user_id', 0);
//        dd($req->all(), $userId);

        $nickname = (string) $req->post('account_name', '');
        $password = (string) $req->post('account_password', '');
//dd($nickname, $password, $req->all());
        if ($nickname == '' OR $password == '') {
            return $this->index('Пустые поля');
        }
//dd('awf');
        $account = new account();
        $account->user_id = $userId;
        $account->nickname = $nickname;
        $account->picture = '';
        $account->password = $password;
        $account->save();

        return redirect('accounts');
    }

    public function destroy(Request $req)
    {
        $userId = (int) session('user_id', 0);
//        dd($req->all(), $userId);

        $nickname = (string) $req->post('account_name', '');

        if ($nickname == '') {
            return response()->json(['success' => false, 'error' => 'Не верный никнейм']);
        }

        DB::table('accounts')->where(['nickname' => $nickname, 'user_id' => $userId])->delete();

        return response()->json(['success' => true]);
    }

    public function async(Request $req) {
        $userId = (int) session('user_id', 0);

        $nickname = (string) $req->post('account_name', '');

        if ($nickname == '') {
            return response()->json(['success' => false, 'error' => 'Не верный никнейм']);
        }
        //TODO: run sync task
//        DB::table('accounts')->where(['nickname' => $nickname, 'user_id' => $userId])->delete();

        return response()->json(['success' => true]);
    }
}
