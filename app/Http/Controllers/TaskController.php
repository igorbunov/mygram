<?php

namespace App\Http\Controllers;

use App\account;
use App\AccountSubscribers;
use App\DirectTask;
use App\DirectTaskReport;
use App\Http\Controllers\InstagramTasksRunner\DirectToSubsTasksRunner;
use App\Http\Controllers\TaskGenerator\DirectTaskCreatorController;
use App\Tariff;
use App\TariffList;
use App\Task;
use App\TaskList;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InstagramAPI\Instagram;
use Mockery\Exception;

class TaskController extends Controller
{
    public function check()
    {
        self::disableAccountsAndTasksByEndTariff();
//        DirectTaskCreatorController::generateDirectTasks();
//        DirectToSubsTasksRunner::sendDirectToSubscribers(1);
    }

    public function getTasks(int $accountId) {
        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return view('main_not_logined');
        }

        $tariff = Tariff::getUserCurrentTariff($userId);

        if (is_null($tariff)) {
            throw new \Exception('У пользователя нет тарифа');
        }

        $taskList = TaskList::getAvaliableTasksForTariffListId($tariff->tariff_list_id);

        $account = account::getAccountById($accountId, false);

        if (is_null($account)) {
            throw new \Exception('Не найден аккаунт');
        }
//dd($account);
        $avaliableTaskList = TaskList::getAvaliableTasksForTariffListId($tariff->tariff_list_id);
//dd($avaliableTaskList);
        $directTasks = [];

        foreach ($avaliableTaskList as $taskListItem) {
            if ('direct' == $taskListItem->type) {
                $directTasks = DirectTask::getDirectTasksByTaskListId($taskListItem->id,$accountId);

                foreach ($directTasks as $i => $directTask) {
                    $directTasks[$i]->sendedToday = DirectTaskReport::getTodayFriendDirectMessagesCount($directTask->id);
                    $directTasks[$i]->taskType = $taskListItem->type;
                }
            } else if ('unfollowing' == $taskListItem->type) {

            }
        }

        return view('account_task', [
            'title' => 'Задачи @' . $account->nickname,
            'activePage' => 'tasks',
            'directTasks' => $directTasks,
            'account' => $account,
            'taskList' => $taskList,
            'currentTariff' => Tariff::getUserCurrentTariffForMainView($userId),
            'accountPicture' => User::getAccountPictureUrl($userId)
        ]);
    }

    public function index($error = '')
    {
        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return view('main_not_logined');
        }

        $accounts = User::getAccountsByUser($userId);

        $res = [
            'title' => 'Задачи'
            , 'activePage' => 'tasks'
            , 'accounts' => $accounts
            , 'currentTariff' => Tariff::getUserCurrentTariffForMainView($userId)
            , 'accountPicture' => User::getAccountPictureUrl($userId)
        ];

        if ($error != '') {
            $res['error'] = $error;
        }

        return view('tasks', $res);
    }

    public function createTask(Request $req)
    {
        $userId = (int) session('user_id', 0);
        $accountId = (int) $req->post('account_id', 0);
        $taskListId = (int) $req->post('task_list_id', 0);
        $directText = $req->post('direct_text', '');

        $workOnlyInNight = $req->post('work_only_in_night', 'off');
        $workOnlyInNight = filter_var($workOnlyInNight, FILTER_VALIDATE_BOOLEAN, array('flags' => FILTER_NULL_ON_FAILURE));

        if ($userId == 0) {
            return redirect('account/' . $accountId);
        }

        if ($accountId == 0) {
            throw new Exception('account not set');
        }
        if ($taskListId == 0) {
            throw new Exception('task type not set');
        }

        $tariff = Tariff::getUserCurrentTariff($userId);

        if (is_null($tariff)) {
            return redirect('account/' . $accountId);
        }

        if (!account::isAccountBelongsToUser($userId, $accountId)) {
            return redirect('account/' . $accountId);
        }

        $taskList = TaskList::getAvaliableTasksForTariffListId($tariff->tariff_list_id);

        if (count($taskList) == 0) {
            return redirect('account/' . $accountId);
        }

        foreach ($taskList as $taskListItem) {
            if ($taskListItem->id == $taskListId) {
                if ('direct' == $taskListItem->type) {
                    $directTask = DirectTask::getActiveDirectTaskByTaskListId($taskListId, $accountId);

                    if (!is_null($directTask)) {
                        throw new \Exception('У вас уже есть активное задание с таким типом');
                    } else {
                        //TODO: $taskListId узнать тип таска и в зависимости от него делать инсерт в нужную таблицу
                        // сейчас реализовано только директ таск

                        $direct = new DirectTask();
                        $direct->account_id = $accountId;
                        $direct->is_active = 1;
                        $direct->task_list_id = $taskListId;
                        $direct->message = $directText;
                        $direct->work_only_in_night = $workOnlyInNight ? 1 : 0;
                        $direct->save();

                        return redirect('account/' . $accountId);
                    }
                }
            }
        }

        return redirect('account/' . $accountId);
    }

    public function changeStatus(Request $req)
    {
        $taskId = (int) $req->post('task_id', 0);
        $isActive = (int) $req->post('is_active', -1);
        $accountId = (int) $req->post('account_id', 0);
        $taskType = $req->post('task_type', '');

        if ($isActive == -1) {
            return response()->json(['success' => false, 'error' => 'not set status']);
        }
        if ($accountId == 0) {
            return response()->json(['success' => false, 'error' => 'not set account id']);
        }

        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return response()->json(['success' => false, 'error' => 'Необходимо авторизоваться']);
        }

        if (!account::isAccountBelongsToUser($userId, $accountId)) {
            return response()->json(['success' => false, 'error' => 'Это не ваш аккаунт']);
        }

        if ($taskType == 'direct') {
            $activeTaskCount = DirectTask::getActiveTasksCountByAccountId($accountId);

            if ($isActive > 0 and $activeTaskCount > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'У аккаунта уже есть одно активное задание. Сначала деактивируйте его.'
                ]);
            }

            $direct = DirectTask::getDirectTaskById($taskId, $accountId, false);

            if (is_null($direct)) {
                return response()->json(['success' => false, 'error' => 'Задание не найдено']);
            }

            $direct->is_active = $isActive;
            $direct->save();

            if (0 == $isActive) {
                AccountSubscribers::deleteOldFollowers($accountId);
            }

            return response()->json([
                'success' => true,
                'accountId' => $direct->account_id
            ]);
        }

        return response()->json(['success' => false, 'error' => 'not set task type']);
    }

    public static function disableAccountsAndTasksByEndTariff()
    {
        $users = User::where(['is_confirmed' => 1])->get();

        foreach ($users as $user) {
            $tariff = Tariff::getUserCurrentTariff($user->id);

            if (is_null($tariff)) {
                DB::statement("UPDATE direct_tasks
                    SET is_active = 0
                    WHERE account_id IN (SELECT id FROM accounts WHERE user_id = {$user->id})");

                DB::statement("UPDATE accounts SET is_active = 0 WHERE user_id = {$user->id}");
            }
        }
    }
}
