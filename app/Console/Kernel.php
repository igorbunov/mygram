<?php

namespace App\Console;

use App\FastTask;
use App\Http\Controllers\TaskGenerator\AllTasksGenerator;
use App\Http\Controllers\TaskGenerator\FastTaskGenerator;
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
            if (env('PROJECT_PATH') != '/home/pata/projects/myinst/') {
                AllTasksGenerator::everyDayGenerator();
            } else {
                AllTasksGenerator::everyDayLocalGenerator();
            }
        })->daily();

        $schedule->call(function() {
            AllTasksGenerator::everyTenMinuteDBCleaner();
        })->everyTenMinutes();

        $schedule->call(function() {
            if (env('PROJECT_PATH') != '/home/pata/projects/myinst/') {
                AllTasksGenerator::everyMinuteGenerator();
            } else {
                AllTasksGenerator::everyMinuteLocalGenerator();
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
