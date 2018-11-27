<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 26.11.18
 * Time: 19:41
 */

namespace App\Http\Controllers\TaskGenerator;

use App\account;
use Illuminate\Support\Facades\Log;

class ValidateAccountTaskCreator
{
//    public static function generateFastLoginTasksForNonCheckedUsers()
//    {
//        $accounts = account::getNonConfirmedAccounts();
//
//        if (is_null($accounts) or count($accounts) == 0) {
//            Log::debug('not found accounts for validating');
//            return;
//        }
//
//        Log::debug('validate accounts '.count($accounts));
//
//        foreach ($accounts as $account) {
//            self::generateFirstLoginTask($account->id);
//        }
//    }

    public static function generateFirstLoginTask(int $accountId, int $fastTaskId)
    {
        $preCommand = "cd " . env('PROJECT_PATH');
        $command = " && " . env('PHP_PATH') . " artisan fast_tasks:login " .  $accountId . ' ' . $fastTaskId;
        $runInBackground = " > /dev/null 2>/dev/null &";

        Log::debug('fast task command: ' . $preCommand . $command . $runInBackground);
        exec($preCommand . $command . $runInBackground);
    }
}