<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 20.11.18
 * Time: 20:49
 */

namespace App\Http\Controllers\TaskGenerator;

use App\account;
use App\DirectTask;
use App\Tariff;
use App\TaskList;
use App\User;
use Illuminate\Support\Facades\Log;

class FastTaskGenerator
{
    public static function generateFastTask()
    {
        Log::debug('generate fast tasks');

        $preCommand = "cd " . env('PROJECT_PATH');
        $command = " && " . env('PHP_PATH') . " artisan fastTask:run";
        $runInBackground = " > /dev/null 2>&1 &";

        Log::debug('command: ' . $preCommand . $command . $runInBackground);

        exec($preCommand . $command . $runInBackground);
    }
}