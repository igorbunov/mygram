<?php

namespace App\Console;

use App\FastTask;
use App\Http\Controllers\InstagramTasksRunner\AccountFirstLoginRunner;
use App\Http\Controllers\InstagramTasksRunner\DirectToSubsTasksRunner;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskGenerator\DirectTaskCreatorController;
use App\Http\Controllers\TaskGenerator\ValidateAccountTaskCreator;
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

        $schedule->call(function() {
//            Log::debug('run task');
            DirectTaskCreatorController::generateDirectTasks();
        })->everyTenMinutes();
//        })->everyMinute(); // ->everyFiveMinutes();

        $schedule->call(function() {
            Tariff::tariffTick();
            TaskController::disableAccountsAndTasksByEndTariff();
        })->daily();

        $schedule->call(function() {
            for ($i = 0; $i < 10; $i++) {
                $tasks = FastTask::getTask();

                if (!is_null($tasks) and count($tasks) > 0) {
//                    Log::debug('found task: ' . json_encode($tasks));

                    foreach ($tasks as $task) {
                        switch ($task->task_type) {
                            case FastTask::TYPE_TRY_LOGIN:
                                FastTask::setStatus($task->id, FastTask::STATUS_IN_PROCESS);
                                ValidateAccountTaskCreator::generateFirstLoginTask($task->account_id, $task->id);

                                break;
                            case FastTask::TYPE_REFRESH_ACCOUNT:

                                break;
                        }
                    }
                }
                sleep(5);
            }
        })->everyMinute();
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
            try {
                DirectToSubsTasksRunner::runDirectTasks($directTaskId, $accountId);
            } catch (\Exception $err) {
                Log::error('Error running task DirectToSubsTasksRunner::getAccountSubscribers: ' . $err->getMessage());
            }
        });

        Artisan::command('fast_tasks:login {accountId} {fastTaskId}', function($accountId, $fastTaskId) {
            try {
                AccountFirstLoginRunner::tryLogin($accountId, $fastTaskId);
            } catch (\Exception $err) {
                Log::error('Error running task AccountFirstLoginRunner::tryLogin: ' . $err->getMessage());
            }
        });
    }
}
