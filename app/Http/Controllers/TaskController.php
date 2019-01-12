<?php

namespace App\Http\Controllers;

use App\account;
use App\AccountSubscribers;
use App\AccountSubscriptions;
use App\DirectTask;
use App\DirectTaskReport;
use App\FastTask;
use App\Http\Controllers\InstagramTasksRunner\DirectToSubsTasksRunner;
use App\Http\Controllers\TaskGenerator\DirectTaskCreatorController;
use App\Safelist;
use App\Tariff;
use App\TariffList;
use App\Task;
use App\UnsubscribeTask;
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

        $account = account::getAccountById($accountId, false);

        if (is_null($account)) {
            throw new \Exception('Не найден аккаунт');
        }

        $directTasks = [];
        $unsubscribeTasks = [];

        $avaliableTaskList = TariffList::getAvaliableTypes($tariff);

        foreach ($avaliableTaskList as $taskType) {
            switch ($taskType) {
                case TariffList::TYPE_DIRECT:
                    $directTasks = DirectTask::getDirectTasksByForAccount($account, $onlyActiveTasks);

                    if (is_null($directTasks) or count($directTasks) == 0) {
                        continue;
                    }

                    foreach ($directTasks as $i => $directTask) {
                        $directTasks[$i]->sendedToday = DirectTaskReport::getTodayFriendDirectMessagesCount($directTask->id);
                        $unsendedFollowers = AccountSubscribers::getUnsendedFollowers($accountId);
                        $directTasks[$i]->inQueue = count($unsendedFollowers);
                        $directTasks[$i]->taskType = TariffList::TYPE_DIRECT;
                    }

                    break;
                case TariffList::TYPE_UNSUBSCRIBE:
                    $unsubscribeTasks = UnsubscribeTask::getUnsubscribeTasksByAccount($account, $onlyActiveTasks);

                    if (is_null($unsubscribeTasks) or count($unsubscribeTasks) == 0) {
                        continue;
                    }

                    foreach ($unsubscribeTasks as $i => $task) {
                        $unsubscribeTasks[$i]->taskType = TariffList::TYPE_UNSUBSCRIBE;
                        $unsubscribeTasks[$i]->safelistStats = AccountSubscriptions::getStatistics($accountId);
                    }

                    break;
            }
        }

        $subTariffList = TariffList::getAvaliableTypes($tariff);
        $taskList = [];

        foreach ($subTariffList as $item) {
            switch ($item) {
                case TariffList::TYPE_DIRECT:
                    $taskList[] = [
                        'id' => $item,
                        'selected' => 'selected',
                        'title' => 'Директ приветствие'
                    ];
                    break;
                case TariffList::TYPE_UNSUBSCRIBE:
                    $taskList[] = [
                        'id' => $item,
                        'selected' => '',
                        'title' => 'Масс отписка'
                    ];
                    break;
            }

        }

        return view('account_task', [
            'title' => 'Задачи @' . $account->nickname,
            'activePage' => 'tasks',
            'directTasks' => $directTasks,
            'unsubscribeTasks' => $unsubscribeTasks,
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
            $directText = $req->post('direct_text', '');
            $taskType = $req->post('task_type');

            if (empty($taskType)) {
                return response()->json(['success' => false, 'message' => 'Не выбран тип задания']);
            }
            if ($accountId == 0) {
                return response()->json(['success' => false, 'message' => 'Не найден аккаунт']);
            }

            $tariff = Tariff::getUserCurrentTariff($userId);

            if (is_null($tariff)) {
                return response()->json(['success' => false, 'message' => 'Не удалось получить тариф']);
            }

            $taskTypes = TariffList::getAvaliableTypes($tariff);

            if (!in_array($taskType, $taskTypes)) {
                return response()->json(['success' => false, 'message' => 'Не разрешено для вашего тарифа']);
            }

            if (!account::isAccountBelongsToUser($userId, $accountId)) {
                return response()->json(['success' => false, 'message' => 'Это не ваш аккаунт']);
            }

            $account = account::getAccountById($accountId);

            switch ($taskType) {
                case TariffList::TYPE_DIRECT:
                    $directTasks = DirectTask::getDirectTasksByForAccount($account, true);

                    if (!is_null($directTasks) and count($directTasks) > 0) {
                        return response()->json(['success' => false, 'message' => 'Нельзя создавать два директ задания']);
                    } else {
                        $direct = new DirectTask();
                        $direct->account_id = $accountId;
                        $direct->status = DirectTask::STATUS_ACTIVE;
                        $direct->message = $directText;
                        $direct->save();

                        return response()->json(['success' => true]);
                    }

                    break;
                case TariffList::TYPE_UNSUBSCRIBE:
                    $unsubscribeTasks = UnsubscribeTask::getUnsubscribeTasksByAccount($account,true);


                    if (!is_null($unsubscribeTasks) and count($unsubscribeTasks) > 0) {
                        return response()->json(['success' => false, 'message' => 'Нельзя создавать два задания отписки']);
                    } else {
                        $task = new UnsubscribeTask();
                        $task->account_id = $accountId;
                        $task->status = DirectTask::STATUS_ACTIVE;
                        $task->save();

                        return response()->json(['success' => true]);
                    }

                    break;

            }
        } catch (\Exception $err) {
            return response()->json(['success' => false, 'message' => $err->getMessage()]);
        }

        return response()->json(['success' => false, 'message' => 'Другая ошибка']);
    }

    public function changeStatus(Request $req)
    {
        $taskId = (int) $req->post('task_id', 0);
        $status = (string) $req->post('status', 'active');
        $accountId = (int) $req->post('account_id', 0);
        $taskType = $req->post('task_type', '');

        if (!DirectTask::isValidStatus($status)) {
            return response()->json(['success' => false, 'message' => 'not valid status']);
        }

        if ($accountId == 0) {
            return response()->json(['success' => false, 'message' => 'not set account id']);
        }

        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return response()->json(['success' => false, 'message' => 'Необходимо авторизоваться']);
        }

        if (!account::isAccountBelongsToUser($userId, $accountId)) {
            return response()->json(['success' => false, 'message' => 'Это не ваш аккаунт']);
        }

        if ($taskType == TariffList::TYPE_DIRECT) {
            $activeTaskCount = DirectTask::getActiveTasksCountByAccountId($accountId);

            if ($status == DirectTask::STATUS_ACTIVE and $activeTaskCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'У аккаунта уже есть одно активное задание. Сначала деактивируйте его.'
                ]);
            }

            $direct = DirectTask::getDirectTaskById($taskId, $accountId, false);

            if (is_null($direct)) {
                return response()->json(['success' => false, 'message' => 'Задание не найдено']);
            }

            $direct->status = $status;
            $direct->save();

            if ($status == DirectTask::STATUS_DEACTIVATED) {
                AccountSubscribers::deleteOldFollowers($accountId);
            }

            return response()->json([
                'success' => true,
                'accountId' => $direct->account_id
            ]);
        } else if ($taskType == TariffList::TYPE_UNSUBSCRIBE) {
            $activeUnsubscribeTask = UnsubscribeTask::getUnsubscribeTaskById($taskId, $accountId, false);

            if (is_null($activeUnsubscribeTask)) {
                return response()->json(['success' => false, 'message' => 'Задание не найдено']);
            }

            if ($status == UnsubscribeTask::STATUS_ACTIVE) {
                $account = account::getAccountById($accountId);
                $unsubscribeTasks = UnsubscribeTask::getUnsubscribeTasksByAccount($account,true);

                if (!is_null($unsubscribeTasks) and count($unsubscribeTasks) > 0) {
                    if ($activeUnsubscribeTask->status != UnsubscribeTask::STATUS_PAUSED) {
                        return response()->json(['success' => false, 'message' => 'Нельзя создавать два задания отписки']);
                    }
                }
            }

            $activeUnsubscribeTask->status = $status;
            $activeUnsubscribeTask->save();

            return response()->json([
                'success' => true,
                'accountId' => $activeUnsubscribeTask->account_id
            ]);
        }

        return response()->json(['success' => false, 'message' => 'not set task type']);
    }

    public static function disableAccountsAndTasksByEndTariff()
    {
        $users = User::getActiveAndConrifmed();

        foreach ($users as $user) {
            $tariff = Tariff::getUserCurrentTariff($user->id);

            if (is_null($tariff)) {
                DB::statement("UPDATE direct_tasks
                    SET status = 'deactivated'
                    WHERE account_id IN (SELECT id FROM accounts WHERE user_id = {$user->id})");

                DB::statement("UPDATE accounts SET is_active = 0 WHERE user_id = {$user->id}");
            }
        }
    }
}
