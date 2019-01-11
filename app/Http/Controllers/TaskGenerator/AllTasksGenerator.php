<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 11.01.19
 * Time: 21:01
 */

namespace App\Http\Controllers\TaskGenerator;

use App\Http\Controllers\InstagramTasksRunner\DatabaseCleaner;
use App\Http\Controllers\TaskController;
use App\Tariff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AllTasksGenerator
{
    public static function everyMinuteGenerator()
    {
        DB::delete("UPDATE fast_tasks
            SET `status` = 'error'
            WHERE `status` = 'in_process' AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) > 5");

        if (env('IS_DIRECT_WORKS', false)) {
            DirectTaskCreatorController::tasksGenerator();
        }

        if (env('IS_UNSUBSCRIBE_WORKS', false)) {
            UnsubscribeTaskCreatorController::tasksGenerator();
        }

        if (env('IS_CHATBOT_WORKS', false)) {
            try {
                ChatbotTaskCreatoreController::tasksGenerator();
            } catch (\Exception $err) {
                Log::debug('error ChatbotTaskCreatoreController ' . $err->getMessage() . ' ' . $err->getTraceAsString());
            }

        }
    }

    public static function everyDayGenerator()
    {
        Tariff::tariffTick();
        TaskController::disableAccountsAndTasksByEndTariff();
        DatabaseCleaner::clearFastTasks();
    }

    public static function everyMinuteLocalGenerator()
    {

    }
    public static function everyDayLocalGenerator()
    {

    }
}