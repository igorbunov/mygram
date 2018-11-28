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
    public static function generateFirstLoginTask(int $accountId, int $fastTaskId)
    {
        $preCommand = "cd " . env('PROJECT_PATH');
        $command = " && /usr/bin/php artisan fast_tasks:login " .  $accountId . ' ' . $fastTaskId;
        $runInBackground = " > /dev/null &";

        Log::debug('fast task command: ' . $preCommand . $command . $runInBackground);
        exec($preCommand . $command . $runInBackground);
    }
}