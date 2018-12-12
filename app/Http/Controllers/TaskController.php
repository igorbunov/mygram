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
    public function getAllTasks(int $accountId)
    {
        return $this->getTasks($accountId, false);
    }

    public function getTasks(int $accountId, $onlyActiveTasks = true)
    {
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

        $avaliableTaskList = TaskList::getAvaliableTasksForTariffListId($tariff->tariff_list_id);

        $directTasks = [];

        foreach ($avaliableTaskList as $taskListItem) {
            if ('direct' == $taskListItem->type) {
                $directTasks = DirectTask::getDirectTasksByTaskListId($taskListItem->id,$accountId, $onlyActiveTasks);

                foreach ($directTasks as $i => $directTask) {
                    $directTasks[$i]->sendedToday = DirectTaskReport::getTodayFriendDirectMessagesCount($directTask->id);
                    $unsendedFollowers = AccountSubscribers::getUnsendedFollowers($accountId);
                    $directTasks[$i]->inQueue = count($unsendedFollowers);
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
            'onlyActiveTasks' => $onlyActiveTasks,
            'currentTariff' => Tariff::getUserCurrentTariffForMainView($userId),
            'accountPicture' => User::getAccountPictureUrl($userId, $accountId)
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

        if ($userId == 0) {
            return response()->json(['success' => false, 'message' => 'Потеряна сессия авторизации']);
        }

        try {

            $accountId = (int) $req->post('account_id', 0);
            $taskListId = (int) $req->post('task_list_id', 0);
            $directText = $req->post('direct_text', '');

            if ($accountId == 0) {
                return response()->json(['success' => false, 'message' => 'Не найден аккаунт']);
            }
            if ($taskListId == 0) {
                return response()->json(['success' => false, 'message' => 'Не указан тип задания']);
            }

            $tariff = Tariff::getUserCurrentTariff($userId);

            if (is_null($tariff)) {
                return response()->json(['success' => false, 'message' => 'Не удалось получить тариф']);
            }

            if (!account::isAccountBelongsToUser($userId, $accountId)) {
                return response()->json(['success' => false, 'message' => 'Это не ваш аккаунт']);
            }

            $taskList = TaskList::getAvaliableTasksForTariffListId($tariff->tariff_list_id);

            if (count($taskList) == 0) {
                return response()->json(['success' => false, 'message' => 'Это задание недоступно для вашего тарифа']);
            }

            foreach ($taskList as $taskListItem) {
                if ($taskListItem->id == $taskListId) {
                    if ('direct' == $taskListItem->type) {
                        $directTask = DirectTask::getActiveDirectTaskByTaskListId($taskListId, $accountId);

                        if (!is_null($directTask)) {
                            return response()->json(['success' => false, 'message' => 'Нельзя создавать два директ задания']);
                        } else {
                            $direct = new DirectTask();
                            $direct->account_id = $accountId;
                            $direct->is_active = 1;
                            $direct->task_list_id = $taskListId;
                            $direct->message = $directText;
                            $direct->save();

                            return response()->json(['success' => true]);
                        }
                    }
                }
            }
        } catch (\Exception $err) {
            return response()->json(['success' => false, 'message' => $err->getMessage()]);
        }

        return response()->json(['success' => false, 'message' => 'Другая ошибка']);
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
