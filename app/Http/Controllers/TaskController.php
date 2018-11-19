<?php

namespace App\Http\Controllers;

use App\account;
use App\DirectTask;
use App\Tariff;
use App\Task;
use App\TaskList;
use App\User;
use Illuminate\Http\Request;
use Mockery\Exception;

class TaskController extends Controller
{

    public function getTasks(int $accountId) {
        $res = account::find($accountId);
//TODO: тут какая-то не понятная хрень, переделать!
        foreach($res->directTasks as $i => $task) {
            $res->directTasks[$i]->taskList = $task->taskList;
            $res->directTasks[$i]->taskType = 'direct';
        }
//        SELECT id, title, `type` FROM task_lists WHERE is_active = 1 AND tariff_list_id = 1

        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return view('main_not_logined');
        }

        $tariff = Tariff::getUserCurrentTariff($userId);

        $taskList = TaskList::where([
            'is_active' => 1,
            'tariff_list_id' => $tariff->tariff_list_id
        ])->get();

        return view('account_task', [
            'title' => 'Задачи',
            'activePage' => 'tasks',
            'tasks' => $res->directTasks,
            'account' => $res,
            'taskList' => $taskList,
            'currentTariff' => Tariff::getUserCurrentTariffForMainView($userId)
        ]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($error = '')
    {
        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return view('main_not_logined');
        }

        $accounts = User::find($userId)->accounts;

        $res = [
            'title' => 'Задачи'
            , 'activePage' => 'tasks'
            , 'accounts' => $accounts
            , 'currentTariff' => Tariff::getUserCurrentTariffForMainView($userId)
        ];

        if ($error != '') {
            $res['error'] = $error;
        }

        return view('tasks', $res);
    }

    public function createTask(Request $req)
    {
//        dd($req->all());
        $accountId = (int) $req->post('account_id', 0);
        $taskListId = (int) $req->post('task_list_id', 0);
        $directText = $req->post('direct_text', '');
        $isUseDelay = $req->post('is_use_delay', 'off');
        $isUseDelay = filter_var($isUseDelay, FILTER_VALIDATE_BOOLEAN, array('flags' => FILTER_NULL_ON_FAILURE));

        $workOnlyInMight = $req->post('work_only_in_night', 'off');
        $workOnlyInMight = filter_var($workOnlyInMight, FILTER_VALIDATE_BOOLEAN, array('flags' => FILTER_NULL_ON_FAILURE));

        if ($accountId == 0) {
            throw new Exception('account not set');
        }
        if ($taskListId == 0) {
            throw new Exception('task type not set');
        }

        //TODO: $taskListId узнать тип таска и в зависимости от него делать инсерт в нужную таблицу
        // сейчас реализовано только директ таск

//        dd([
//            '$taskListId' => $taskListId,
//            'accountId' => $accountId,
//            'directText' => $directText,
//            'isUseDelay' => $isUseDelay,
//            'workOnlyInMight' => $workOnlyInMight,
//            'task_list_id' => $taskListId
//        ]);

        $direct = new DirectTask();
        $direct->account_id = $accountId;
        $direct->is_active = 1;
        $direct->task_list_id = $taskListId;
        $direct->message = $directText;
        $direct->delay_time_min = ($isUseDelay) ? 30 : 5;
        $direct->work_only_in_night = $workOnlyInMight ? 1 : 0;
        $direct->save();

        return redirect('account/' . $accountId);
    }

    public function changeStatus(Request $req)
    {
        $taskId = (int) $req->post('task_id', 0);
        $isActive = (int) $req->post('is_active', -1);
        $taskType = $req->post('task_type', '');

        if ($isActive == -1) {
            return response()->json(['success' => false, 'error' => 'not set status']);
        }

        if ($taskType == 'direct') {
            $direct = DirectTask::where('id', $taskId)->first();

            $activeDirect = DirectTask::where([
                'account_id' => $direct->account_id,
                'is_active' => 1
            ])->first();

            if (!is_null($activeDirect) and $isActive > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'У аккаунта уже есть одно активное задание. Сначала деактивируйте его.'
                ]);
            }

            $direct->is_active = $isActive;
            $direct->save();

            return response()->json([
                'success' => true,
                'accountId' => $direct->account_id
            ]);
        }

        return response()->json(['success' => false, 'error' => 'not set task type']);
    }
}
