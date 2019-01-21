<?php

namespace App\Http\Controllers;

use App\Tariff;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    private function isValidCaptcha($code) {
        $data = array(
            'secret' => env('CAPTCHA_SECRET'),
            'response' => $code
        );

        $verify = curl_init();
        curl_setopt($verify, CURLOPT_URL, env('CAPTCHA_URL'));
        curl_setopt($verify, CURLOPT_POST, true);
        curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($verify);

        $res = json_decode($response, true);

        return (isset($res['success']) AND $res['success']);
    }

    private function isUserExists($email) {
        $emailCheck = DB::table('users')
            ->select([DB::raw('count(1) as cnt')])
            ->where('email', $email)
            ->first();

        return ($emailCheck->cnt > 0);
    }

    public static function getHashPassword($pass) {
        return md5(md5(env('SOLT').$pass));
    }

    private function getUserByEmailPass($email, $pass) {
        $user = User::where([
            'is_confirmed' => 1
            , 'email' => $email
            , 'password' => self::getHashPassword($pass)
        ])->first();

        if (!is_null($user)) {
            return $user->toArray();
        }

        return $user;
    }

    private function isCodeExists($code) {
        $codeCheck = DB::table('users')
            ->select([DB::raw('count(1) as cnt')])
            ->where([
                'confirm_code' => $code
                , 'is_confirmed' => 0
            ])
            ->first();

        return ($codeCheck->cnt > 0);
    }

    private function isForgotCodeExists($code) {
        $codeCheck = DB::table('users')
            ->select([DB::raw('count(1) as cnt')])
            ->where([
                'forgot_code' => $code
                , 'is_confirmed' => 1
            ])
            ->first();

        return ($codeCheck->cnt > 0);
    }

    public function register(Request $req) {
        if (!env('ENABLE_REGISTRATION')) {
            return '<h3>Регистрация временно отключена</h3>';
        }

        $catpchaCode = $req->post("g-recaptcha-response");

        if (empty($catpchaCode)) {
            return view('auth.register', ['error' => 'Не верная капча']);
        }

        $email = $req->post('email', '');

        if (empty($email) OR trim($email) == '') {
            return view('auth.register', ['error' => 'Вы не указали email']);
        }

        $email = trim($email);

        if ($this->isUserExists($email)) {
            return view('auth.register', ['error' => 'Такой пользователь уже существует']);
        }

        if ($this->isValidCaptcha($catpchaCode)) {
            $code = Str::random(16);

            $user = new User();
            $user->email = $email;
            $user->confirm_code = $code;
            $user->save();

            if (env('ENABLE_EMAIL')) {
                mail($email, 'Регистрация на сайте ' . env('APP_URL'),
                    'Ссылка для завершения регистрации: ' . env('APP_URL') . 'register/' . $code);
            }

            return view('auth.register_confirm_sended', ['email' => $email]);
        } else {
            return view('auth.register');
        }
    }

    public function registerConfirm($code) {
        if (!env('ENABLE_REGISTRATION')) {
            return '<h3>Регистрация временно отключена</h3>';
        }

        $code = trim($code);

        if ($code == '' OR empty($code)) {
            return view('auth.register_confirm_form', ['error' => 'Не верный или устаревший код подтверждения']);
        }

        if ($this->isCodeExists($code)) {
            return view('auth.register_confirm_form', ['code' => $code]);
        }

        return view('auth.register_confirm_form', ['error' => 'Не верный или устаревший код подтверждения']);
    }

    public function registerFinish(Request $req) {
        if (!env('ENABLE_REGISTRATION')) {
            return '<h3>Регистрация временно отключена</h3>';
        }

        $code = trim($req->post('code', ''));
        $pass1 = trim($req->post('pass1', ''));
        $pass2 = trim($req->post('pass2', ''));

        if ($code == '' OR empty($code)) {
            return view('auth.register_confirm_form', ['error' => 'Не верный или устаревший код подтверждения']);
        }

        if ($this->isCodeExists($code)) {
            if ($pass1 == '' OR $pass2 == '' OR $pass1 != $pass2) {
                return view('auth.register_confirm_form', ['error' => 'Пароли не совпадают', 'code' => $code]);
            }

            $user = User::where('confirm_code', $code)->first();

            $user->password = self::getHashPassword($pass1);
            $user->is_confirmed = 1;
            $user->confirm_code = '';
            $user->save();

            session(['user_id' => $user->id]);
            session(['user_email' => $user->email]);

            $tariff = new TariffController();
//            $tariff->activateMaximumTariff();
            $tariff->activateTrialTariff();

            return redirect('/');
        }

        return view('auth.register_confirm_form', ['error' => 'Не верный или устаревший код подтверждения']);
    }

    public function registerView() {
        if (!env('ENABLE_REGISTRATION')) {
            return '<h3>Регистрация временно отключена</h3>';
        }

        return view('auth.register');
    }

    public function loginView() {
        return view('auth.login');
    }

    public function login(Request $req) {
        $email = trim($req->post('email', ''));
        $password = trim($req->post('password', ''));

        if ($password == '' OR $email == '') {
            return view('auth.login', ['error' => 'Пароль или имейл не верны']);
        }

        $user = $this->getUserByEmailPass($email, $password);

        if (is_null($user)) {
            return view('auth.login', ['error' => 'Пользователь не найден']);
        }

        session(['user_id' => $user['id']]);
        session(['user_email' => $user['email']]);

        return redirect('/');
    }

    public function logout() {
        session()->flush();

        return redirect('/');
    }

    public function forgotView() {
        return view('auth.forgot');
    }

    public function forgot(Request $req) {
        $catpchaCode = $req->post("g-recaptcha-response");

        if (empty($catpchaCode)) {
            return view('auth.forgot', ['error' => 'Не верная капча']);
        }

        $email = $req->post('email', '');

        if (empty($email) OR trim($email) == '') {
            return view('auth.forgot', ['error' => 'Вы не указали email']);
        }

        $email = trim($email);

        if (!$this->isUserExists($email)) {
            return view('auth.forgot', ['error' => 'Такой пользователь не существует']);
        }

        if ($this->isValidCaptcha($catpchaCode)) {
            $code = Str::random(16);

            $user = User::where('email', $email)->first();

            $user->is_forgot_password = 1;
            $user->forgot_code = $code;
            $user->save();

            if (env('ENABLE_EMAIL')) {
                mail($email, 'Смена пароля на сайте ' . env('APP_URL'),
                    'Если вы не нажимали на восстановление пароля, то игнорируйте это сообщение .Ссылка для смены пароля: '
                    . env('APP_URL') . 'forgot/' . $code);
            }

            return view('auth.forgot_sended', ['email' => $email]);
        }
    }

    public function forgotConfirm($code) {
        $code = trim($code);

        if ($code == '' OR empty($code)) {
            return view('auth.forgot_confirm_form', ['error' => 'Не верный или устаревший код подтверждения']);
        }

        if ($this->isForgotCodeExists($code)) {
            return view('auth.forgot_confirm_form', ['code' => $code]);
        }

        return view('auth.forgot_confirm_form', ['error' => 'Не верный или устаревший код подтверждения']);
    }

    public function forgotFinish(Request $req) {
        $code = trim($req->post('code', ''));
        $pass1 = trim($req->post('pass1', ''));
        $pass2 = trim($req->post('pass2', ''));

        if ($code == '' OR empty($code)) {
            return view('auth.forgot_confirm_form', ['error' => 'Не верный или устаревший код подтверждения']);
        }

        if ($this->isForgotCodeExists($code)) {
            if ($pass1 == '' OR $pass2 == '' OR $pass1 != $pass2) {
                return view('auth.forgot_confirm_form', ['error' => 'Пароли не совпадают', 'code' => $code]);
            }

            $user = User::where(['forgot_code' => $code, 'is_confirmed' => 1])->first();

            $user->password = self::getHashPassword($pass1);
            $user->forgot_code = '';
            $user->is_forgot_password = 0;
            $user->save();

            return redirect('/');
        }

        return view('auth.forgot_confirm_form', ['error' => 'Не верный или устаревший код подтверждения']);
    }
}
