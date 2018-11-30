<?php

namespace App\Console;

use App\FastTask;
use App\Http\Controllers\InstagramTasksRunner\AccountFirstLoginRunner;
use App\Http\Controllers\InstagramTasksRunner\DirectToSubsTasksRunner;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskGenerator\DirectTaskCreatorController;
use App\Http\Controllers\TaskGenerator\FastTaskGenerator;
use App\Tariff;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if (!env('SITE_ENABLED')) {
            return;
        }
//
//        $schedule->call(function() {
//            Log::debug('== Run chedule tariff changer == ');
//            Tariff::tariffTick();
//            TaskController::disableAccountsAndTasksByEndTariff();
//        })->daily();
//
        $schedule->call(function() {
            Log::debug('== Run chedule direct task generator == ');
            DirectTaskCreatorController::generateDirectTasks();
        })->everyTenMinutes();
//
//        $schedule->call(function() {
//            Log::debug('== Run schedule fast task generator == ');
//            FastTaskGenerator::generateFastTask();
//        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');

        Artisan::command('test:run {name}', function(string $name) {
            Log::debug('run test task: ' . $name);
            DirectTaskCreatorController::runTestTask($name);
        });

        Artisan::command('fastTask:run', function() {
            Log::debug('run fast task');
            FastTask::runTask();
        });

        Artisan::command('direct:send {directTaskId} {accountId}', function ($directTaskId, $accountId) {
            try {
                DirectToSubsTasksRunner::runDirectTasks($directTaskId, $accountId);
            } catch (\Exception $err) {
                Log::error('Error running task DirectToSubsTasksRunner::getAccountSubscribers: ' . $err->getMessage());
            }
        });
    }
}
