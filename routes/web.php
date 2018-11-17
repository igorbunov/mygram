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


Route::get('/', 'AccountController@index');

Route::post('activationsuccess', 'TariffController@paymentCallback');

Route::group(['middleware' => 'myauth'], function () {
    Route::get('accounts', 'AccountController@index');
    Route::get('account/{id}', 'TaskController@getTasks');
    Route::get('tasks', 'TaskController@index');
    Route::post('accounts', 'AccountController@create');
    Route::post('account_sync', 'AccountController@async');
    Route::delete('accounts', 'AccountController@destroy');
    Route::get('tariffs', 'TariffController@index');
    Route::post('checkout', 'TariffController@checkout');

    Route::get('limits', function () {
        return view('limits', ['title' => 'Лимиты', 'activePage' => 'limits']);
    });

    Route::get('about', function () {
        return view('about', ['title' => 'О компании', 'activePage' => 'about']);
    });

    Route::get('support', function () {
        return view('support', ['title' => 'Поддержка', 'activePage' => 'support']);
    });

    Route::get('contacts', function () {
        return view('contacts', ['title' => 'Контакты', 'activePage' => 'contacts']);
    });

    Route::get('agreement', function () {
        return view('agreement', ['title' => 'Соглашение', 'activePage' => 'agreement']);
    });

    Route::get('return', function () {
        return view('return', ['title' => 'Условия возврата', 'activePage' => 'return']);
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
