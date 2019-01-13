<?php

namespace App\Http\Controllers;

use App\Tariff;
use App\TariffList;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;
use LiqPay;

class TariffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list = TariffList::getActiveTariffList();

        foreach($list as $i => $item) {
            $subItems = explode(',', $item->description);

//            dd($subItems);
            $subItemResult = [];
            foreach($subItems as $subItem) {
                $subItemResult[] = TariffList::translateTaskType($subItem);
            }

            $list[$i]->descriptionRus = implode(', ',  $subItemResult);
        }
//dd($list->toArray());
        return view('tariffs', [
            'title' => 'Тарифы',
            'activePage' => 'tariffs',
            'tariffList' => $list,
            'currentTariff' => Tariff::getUserCurrentTariffForMainView(),
            'accountPicture' => User::getAccountPictureUrl()
        ]);
    }

    public function checkout(Request $req)
    {
        $tariffListId = (int) $req->post('tariff_list_id', 0);
        $accountsCount = (int) $req->post('accounts_count', 0);

        if ($tariffListId == 0 or $accountsCount == 0) {
            throw new Exception('bad params');
        }

        $selectedTariff = TariffList::where([
            'is_active' => 1,
            'id' => $tariffListId
        ])->first();

        if (!$selectedTariff) {
            throw new Exception('no tariff found');
        }

        if ($selectedTariff->is_trial == 1) {
            $this->activateTrialTariff();

            return view('tariff_activation_success', [
                'title' => 'Тарифы',
                'activePage' => 'tariffs',
                'tariffList' => [],
                'isError' => false,
                'currentTariff' => Tariff::getUserCurrentTariffForMainView()
            ]);
        }

        $amount = 0;

        switch ($accountsCount) {
            case '1':
                $amount = $selectedTariff->price_one_uah;
                break;
            case '3':
                $amount = $selectedTariff->price_three_uah;
                break;
            case '5':
                $amount = $selectedTariff->price_five_uah;
                break;
            case '10':
                $amount = $selectedTariff->price_ten_uah;
                break;
        }

        if ($amount == 0) {
            throw new Exception('wrong amount');
        }

        $userId = (int) session('user_id');

        if ($userId == 0) {
            throw new Exception('session is dead');
        }

        return view('checkout', [
            'title' => 'Оплата тарифа',
            'activePage' => 'tariffs',
            'selectedTariff' => $selectedTariff,
            'accountsCount' => $accountsCount,
            'payButton' => $this->getPayButton($userId, $amount, $selectedTariff->name, $accountsCount),
            'currentTariff' => Tariff::getUserCurrentTariffForMainView()
        ]);
    }

    public function paymentCallback(Request $request) {
        $result = array('payType' => 'unknown');
        $message = '';
        $isPayed = 0;
        $payResponse = null;
        $privateKey = env('LIQ_PAY_PRIVATE_KEY');
        $postData = $request->post('data');

        $sign = base64_encode( sha1($privateKey .$postData .$privateKey, 1 ));

        if ($sign == $request->post('signature')) {
            $data = json_decode(base64_decode($postData), true);
            $result['payData'] = base64_decode($postData);
            $payResponse = json_decode($result['payData'], true);

            switch($data['status']) {
                case 'error':
                    $message = 'Неуспешный платеж. Некорректно заполнены данные';
                    break;
                case 'failure':
                    $message = 'Неуспешный платеж';
                    break;
                case 'reversed':
                    $message = 'Платеж возвращен';
                    break;
                case 'sandbox':
                    $message = 'Тестовый платеж';
                    $isPayed = 1;
                    break;
                case 'subscribed':
                    $message = 'Подписка успешно оформлена';
                    $isPayed = 1;
                    break;
                case 'success':
                    $message = 'Успешный платеж';
                    $isPayed = 1;
                    break;
                case 'unsubscribed':
                    $message = 'Подписка успешно деактивирована';
                    $isPayed = 1;
                    break;
            }
        } else {
            return 'Не верная подпись';
        }

        if (!is_null($payResponse)) {
            $order = json_decode($payResponse['order_id'], true);

            if (!$this->checkActivation($payResponse['payment_id'])) {
                $dates = $this->getActiveDatesForNewTariff($order['userId']);

                $ac = new Tariff();
                $ac->tariff_list_id = $order['tariffListId'];
                $ac->user_id = $order['userId'];
                $ac->is_active = $isPayed;
                $ac->is_payed = $isPayed;
                $ac->payment_id = $payResponse['payment_id'];
                $ac->currency = $payResponse['currency'];
                $ac->ip = $payResponse['ip'];
                $ac->amount = $payResponse['amount'];
                $ac->accounts_count = $order['accountsCount'];
                $ac->payment_response = $result['payData'];
                $ac->payment_message = $message;
                $ac->dt_start = $dates->dt_start;
                $ac->dt_end = $dates->dt_end;
                $ac->save();
            }

            return view('tariff_activation_success', [
                'title' => 'Тарифы',
                'activePage' => 'tariffs',
                'tariffList' => [],
                'isError' => false,
                'currentTariff' => Tariff::getUserCurrentTariffForMainView($order['userId'])
            ]);
        }

        return view('tariff_activation_success', [
            'title' => 'Тарифы',
            'activePage' => 'tariffs',
            'tariffList' => [],
            'isError' => true,
            'message' => 'Ошибка оплаты тарифа',
            'currentTariff' => Tariff::getUserCurrentTariffForMainView()
        ]);

//        {
//            "action":"pay"
//            ,"payment_id":833777845
//            ,"status":"sandbox"
//            ,"version":3
//            ,"type":"buy"
//            ,"paytype":"privat24"
//            ,"public_key":"i85467765655"
//            ,"acq_id":414963
//            ,"order_id":"{key: \"fwt3434\", payNum: \"21\"}"
//            ,"liqpay_order_id":"P4DHT9JA1538773341079567"
//            ,"description":"description text"
//            ,"ip":"109.206.46.254"
//            ,"amount":1.0
//            ,"currency":"UAH"
//            ,"sender_commission":0.0
//            ,"receiver_commission":0.03
//            ,"agent_commission":0.0
//            ,"amount_debit":1.0
//            ,"amount_credit":1.0
//            ,"commission_debit":0.0
//            ,"commission_credit":0.03
//            ,"currency_debit":"UAH"
//            ,"currency_credit":"UAH"
//            ,"sender_bonus":0.0
//            ,"amount_bonus":0.0
//            ,"mpi_eci":"7"
//            ,"is_3ds":false
//            ,"create_date":1538773341079
//            ,"end_date":1538773341103
//            ,"transaction_id":833777845
//        }
    }

    private function getActiveDatesForNewTariff($userId)
    {
        $res = DB::select("SELECT 
                  dt_end + interval 1 day as dt_start,
                  (dt_end + interval 1 day) + interval 1 month as dt_end
                FROM tariffs
            WHERE user_id = ? AND is_active = 1 AND is_payed = 1 AND DATE(dt_end) > CURDATE()
            ORDER BY dt_end DESC
            LIMIT 1", [$userId]);

        if (is_null($res) or count($res) == 0) {
            $res = DB::select('select now() as dt_start, now() + interval 1 month as dt_end');
        }

        return $res[0];
    }

    private function getPayButton(int $userId, string $amount, string $tariffName, int $accoutsCount)
    {
        $orderId = [
            'userId' => $userId,
            'dt' => date("Y.m.d H:i:s"),
            'tariffListId' => 1,
            'tariffName' => $tariffName,
            'accountsCount' => $accoutsCount,
        ];

        $liqPay = new LiqPay(env('LIQ_PAY_PUBLIC_KEY'), env('LIQ_PAY_PRIVATE_KEY'));

        $html = $liqPay->cnb_form(array(
            'action'      => 'pay',
            'amount'      => $amount,
            'currency'    => 'UAH', //USD, EUR, RUB, UAH
            'description' => 'Активация: ' . $tariffName . ', кол-во аккаунтов: ' . $accoutsCount,
            'order_id'    => json_encode($orderId),
            'version'     => '3',
            'result_url'  => env('LIQ_PAY_RESULT_URL'), // передается через редирект
            'server_url'  => env('LIQ_PAY_SERVER_URL'), // передается через курл
            'sandbox'     => env('LIQ_PAY_SANDBOX')
        ));

        return $html;
    }

    private function checkActivation(string $paymentId): bool
    {
        $result = Tariff::where([
            'is_payed' => 1
            , 'payment_id' => $paymentId
        ])->get();

        return count($result) > 0;
    }

    private function activateTrialTariff()
    {
        $result = TariffList::where([
            'is_active' => 1
            , 'is_trial' => 1
        ])->get()->first();

        if (!is_null($result)) {
            $userId = (int) session('user_id');

            if ($userId == 0) {
                throw new Exception('session is dead');
            }

            $ac = new Tariff();
            $ac->tariff_list_id = $result['id'];
            $ac->user_id = $userId;
            $ac->is_active = 1;
            $ac->is_payed = 1;
            $ac->payment_id = 'trial';
            $ac->currency = 'UAH';
            $ac->ip = $_SERVER['REMOTE_ADDR'];
            $ac->amount = 0;
            $ac->accounts_count = '1';
            $ac->payment_response = '';
            $ac->payment_message = '';
            $ac->dt_start = DB::select('select now() as dt')[0]->dt;
            $ac->dt_end = DB::select('select now() + interval 3 day as dt')[0]->dt;
            $ac->save();
        }
    }
}
