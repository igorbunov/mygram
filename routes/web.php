<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

if (!env('SITE_ENABLED')) {
    echo '<h3>Site disabled</h3>';
    die();
}

Route::get('/', 'AccountController@index');

Route::post('activationsuccess', 'TariffController@paymentCallback');

Route::group(['middleware' => 'myauth'], function () {
    Route::post('add_account', 'AccountController@create');
    Route::get('accounts', 'AccountController@index');
    Route::get('safelist', 'SafelistController@index');
    Route::post('safelist_update', 'SafelistController@updateList');
    Route::post('clear_direct_queue', 'DirectTaskController@clearDirectQueue');
    Route::get('safelist/{id}', 'SafelistController@getSafelist');
    Route::post('safelist_toggle_user', 'SafelistController@toggleUser');
    Route::get('accounts/all', 'AccountController@indexAll');
    Route::get('account/{id}', 'TaskController@getTasks');
    Route::get('account/{id}/all', 'TaskController@getAllTasks');
    Route::get('tasks', 'TaskController@index');
    Route::post('create_task', 'TaskController@createTask');
    Route::post('change_task', 'TaskController@changeStatus');
    Route::post('change_account', 'AccountController@changeStatus');
    Route::post('account_sync', 'AccountController@sync');
    Route::post('fast_task_status', 'FastTaskController@checkTaskStatus');
    Route::get('tariffs', 'TariffController@index');
    Route::post('checkout', 'TariffController@checkout');

    Route::get('limits', function () {
        $curTariff = \App\Tariff::getUserCurrentTariffForMainView();
        return view('limits', ['title' => 'Лимиты', 'activePage' => 'limits', 'currentTariff' => $curTariff
            , 'accountPicture' => '']);
    });

    Route::get('about', function () {
        $curTariff = \App\Tariff::getUserCurrentTariffForMainView();
        return view('about', ['title' => 'О компании', 'activePage' => 'about', 'currentTariff' => $curTariff
            , 'accountPicture' => '']);
    });

    Route::get('support', function () {
        $curTariff = \App\Tariff::getUserCurrentTariffForMainView();
        return view('support', ['title' => 'Поддержка', 'activePage' => 'support', 'currentTariff' => $curTariff
            , 'accountPicture' => '']);
    });

    Route::get('contacts', function () {
        $curTariff = \App\Tariff::getUserCurrentTariffForMainView();
        return view('contacts', ['title' => 'Контакты', 'activePage' => 'contacts', 'currentTariff' => $curTariff
            , 'accountPicture' => '']);
    });

    Route::get('agreement', function () {
        $curTariff = \App\Tariff::getUserCurrentTariffForMainView();
        return view('agreement', ['title' => 'Соглашение', 'activePage' => 'agreement', 'currentTariff' => $curTariff
            , 'accountPicture' => '']);
    });

    Route::get('return', function () {
        $curTariff = \App\Tariff::getUserCurrentTariffForMainView();
        return view('return', ['title' => 'Условия возврата', 'activePage' => 'return', 'currentTariff' => $curTariff
            , 'accountPicture' => '']);
    });
});



Route::get('login', 'UserController@loginView');
Route::post('login', 'UserController@login');
Route::get('logout', 'UserController@logout');

Route::get('forgot', 'UserController@forgotView');
Route::get('forgot/{code}', 'UserController@forgotConfirm');
Route::post('forgot', 'UserController@forgot');
Route::post('forgot_finish', 'UserController@forgotFinish');

Route::get('register', 'UserController@registerView');
Route::get('register/{code}', 'UserController@registerConfirm');

Route::post('register', 'UserController@register');
Route::post('register_finish', 'UserController@registerFinish');
