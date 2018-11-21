<?php

namespace App\Console;

use App\Http\Controllers\InstagramTasksRunner\DirectToSubsTasksRunner;
use App\Http\Controllers\TaskGenerator\DirectTaskCreatorController;
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
        // $schedule->command('inspire')
        //          ->hourly();

        $schedule->call(function() {
//            Log::debug('runFastTask run');
//            DirectTaskCreatorController::generateDirectTasks();
        })->everyMinute();

        $schedule->call(function() {
            Tariff::tariffTick();
        })->daily();
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

        Artisan::command('direct:send {directTaskId} {accountId}', function ($directTaskId, $accountId) {
            DirectToSubsTasksRunner::sendDirectToSubscribers($directTaskId, $accountId);
        });
    }
}
