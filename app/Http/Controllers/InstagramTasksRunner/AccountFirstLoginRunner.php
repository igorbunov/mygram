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
    public static function tryLogin(int $accountId, int $fastTaskId)
    {
        FastTask::setStatus($fastTaskId, FastTask::STATUS_EXECUTED);
        Log::debug('runned tryLogin task for account id: ' . $accountId . ' fast task id: ' . $fastTaskId);
        $account = account::getAccountById($accountId);

        if (is_null($account)) {
            Log::error('account not found');
            return;
        }

        MyInstagram::getInstanse()->login($account);
    }
}