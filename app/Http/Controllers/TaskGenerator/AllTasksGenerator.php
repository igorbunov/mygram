<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 11.01.19
 * Time: 21:01
 */

namespace App\Http\Controllers\TaskGenerator;

use App\account;
use App\Chatbot;
use App\FastTask;
use App\Http\Controllers\InstagramTasksRunner\DatabaseCleaner;
use App\Http\Controllers\TaskController;
use App\Tariff;
use App\TariffList;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AllTasksGenerator
{
    public static function everyMinuteGenerator()
    {
        $users = User::getActiveAndConrifmed();

        foreach ($users as $user) {
            $tariff = Tariff::getUserCurrentTariff($user->id);

            if (is_null($tariff)) {
                continue;
            }

            $accounts = account::getActiveAccountsByUser($user->id);

            if (count($accounts) == 0) {
                continue;
            }

            if (env('IS_DIRECT_WORKS', false)) {
                if (TariffList::isAvaliable($tariff, TariffList::TYPE_DIRECT)) {
                    DirectTaskCreatorController::tasksGenerator($accounts);
                }
            }

            if (env('IS_UNSUBSCRIBE_WORKS', false) and !FastTask::isNight()) {
                if (TariffList::isAvaliable($tariff, TariffList::TYPE_UNSUBSCRIBE)) {
                    UnsubscribeTaskCreatorController::tasksGenerator($accounts);
                }
            }

            if (env('IS_CHATBOT_WORKS', false)) {
                if (TariffList::isAvaliable($tariff, TariffList::TYPE_CHATBOT)) {
                    $chatBot = Chatbot::getByUserId($user->id);

                    if (!is_null($chatBot)) {
                        if ($chatBot->status != Chatbot::STATUS_IN_PROGRESS) {
                            return;
                        }

                        ChatbotTaskCreatoreController::tasksGenerator($chatBot, $accounts);
                    }
                }
            }
        }
    }

    public static function everyTenMinuteDBCleaner()
    {
        DB::delete("UPDATE fast_tasks
            SET `status` = 'error'
            WHERE `status` = 'in_process' AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) > 10");
    }

    public static function everyDayGenerator()
    {
        Tariff::tariffTick();
        TaskController::disableAccountsAndTasksByEndTariff();
        DatabaseCleaner::clearFastTasks();

        Tariff::notifyEndingTariffs();
    }

    public static function everyMinuteLocalGenerator()
    {

    }
    public static function everyDayLocalGenerator()
    {

    }
}