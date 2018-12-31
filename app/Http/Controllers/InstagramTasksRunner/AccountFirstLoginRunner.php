<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 26.11.18
 * Time: 20:52
 */

namespace App\Http\Controllers\InstagramTasksRunner;

use App\account;
use App\FastTask;
use App\Http\Controllers\MyInstagram\MyInstagram;
use Illuminate\Support\Facades\Log;

class AccountFirstLoginRunner
{
    public static function deleteFiles($src) {
        $dir = opendir($src);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                $full = $src . '/' . $file;
                if ( is_dir($full) ) {
                    rmdir($full);
                }
                else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        rmdir($src);
    }

    public static function tryLogin(int $accountId)
    {
        Log::debug('runned tryLogin task for account id: ' . $accountId);
        $account = account::getAccountById($accountId, false);

        if (is_null($account) or empty($account->nickname)) {
            Log::error('account not found');
            return;
        }

        $sessionDir = realpath(__DIR__ . '/../../../../instagram_lib/sessions/' . $account->nickname . '/');

        if (empty($account->verify_code) and empty($account->check_api_path)) {
            if (is_dir($sessionDir)) {
                Log::debug('Удаляем кукисы: ' . $sessionDir);
                self::deleteFiles($sessionDir);
            } else {
                Log::debug('Нет кукисов');
            }
        } else {
            Log::debug('Попытка ввести код: ' . $account->verify_code);
        }

        MyInstagram::getInstanse()->login($account);
    }

    public static function runRefresh(int $accountId)
    {
        Log::debug('runned runRefresh task for account id: ' . $accountId);
        $account = account::getAccountById($accountId);

        if (is_null($account)) {
            Log::error('account not found');
            return;
        }

        MyInstagram::getInstanse()->login($account);

        $info = MyInstagram::getInstanse()->getInfo();

        account::setInfo($accountId, $info);

    }
}