<?php

namespace App\Console;

use App\FastTask;
use App\Http\Controllers\InstagramTasksRunner\AccountFirstLoginRunner;
use App\Http\Controllers\InstagramTasksRunner\DirectToSubsTasksRunner;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskGenerator\ChatbotTaskCreatoreController;
use App\Http\Controllers\TaskGenerator\DirectTaskCreatorController;
use App\Http\Controllers\TaskGenerator\FastTaskGenerator;
use App\Http\Controllers\TaskGenerator\UnsubscribeTaskCreatorController;
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
            Log::debug('== Run chedule tariff changer == ');
            Tariff::tariffTick();
            TaskController::disableAccountsAndTasksByEndTariff();
        })->daily();

        if (env('PROJECT_PATH') == '/home/pata/projects/myinst/') {
            $schedule->call(function() {
                Log::debug('== Run schedule tasks generator == ');

                ChatbotTaskCreatoreController::tasksGenerator();

//                DirectTaskCreatorController::tasksGenerator();
//                UnsubscribeTaskCreatorController::tasksGenerator();
            })->everyMinute();
        } else {
            $schedule->call(function() {
                if (env('IS_DIRECT_WORKS', false)) {
                    DirectTaskCreatorController::tasksGenerator();
                }

                if (env('IS_UNSUBSCRIBE_WORKS', false)) {
                    UnsubscribeTaskCreatorController::tasksGenerator();
                }

                if (env('IS_CHATBOT_WORKS', false)) {
                    ChatbotTaskCreatoreController::tasksGenerator();
                }
            })->everyMinute();//TODO: remove
        }
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

        Artisan::command('fastTask:run', function() {
//* * * * * ( cd /home/pata/projects/myinst/ && /usr/bin/php artisan fastTask:run > /dev/null 2>&1 )
//* * * * * ( sleep 10 ; cd /home/pata/projects/myinst/ && /usr/bin/php artisan fastTask:run > /dev/null 2>&1 )
//* * * * * ( sleep 20 ; cd /home/pata/projects/myinst/ && /usr/bin/php artisan fastTask:run > /dev/null 2>&1 )
//* * * * * ( sleep 30 ; cd /home/pata/projects/myinst/ && /usr/bin/php artisan fastTask:run > /dev/null 2>&1 )
//* * * * * ( sleep 40 ; cd /home/pata/projects/myinst/ && /usr/bin/php artisan fastTask:run > /dev/null 2>&1 )
//* * * * * ( sleep 50 ; cd /home/pata/projects/myinst/ && /usr/bin/php artisan fastTask:run > /dev/null 2>&1 )


//***** ( cd /home/fhsjewrv/mygram.in.ua/ && /usr/local/bin/php artisan fastTask:run > /dev/null 2>&1 )
//***** ( sleep 10 ; cd /home/fhsjewrv/mygram.in.ua/ && /usr/local/bin/php artisan fastTask:run > /dev/null 2>&1 )
//***** ( sleep 20 ; cd /home/fhsjewrv/mygram.in.ua/ && /usr/local/bin/php artisan fastTask:run > /dev/null 2>&1 )
//***** ( sleep 30 ; cd /home/fhsjewrv/mygram.in.ua/ && /usr/local/bin/php artisan fastTask:run > /dev/null 2>&1 )
//***** ( sleep 40 ; cd /home/fhsjewrv/mygram.in.ua/ && /usr/local/bin/php artisan fastTask:run > /dev/null 2>&1 )
//***** ( sleep 50 ; cd /home/fhsjewrv/mygram.in.ua/ && /usr/local/bin/php artisan fastTask:run > /dev/null 2>&1 )
//            Log::debug('run fast task');
            FastTask::runTask();
        });
    }
}
