<?php

namespace App\Http\Controllers;

use App\FastTask;
use Illuminate\Http\Request;

class FastTaskController extends Controller
{
    public function checkTaskStatus(Request $req)
    {
        $fastTaskId = (int) $req->post('fast_task_id', 0);

        if ($fastTaskId == 0) {
            return response()->json(['success' => false, 'message' => 'Не верный айди задачи']);
        }

        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return response()->json(['success' => false, 'message' => 'Сессия потеряна. Перелогиньтесь']);
        }

        $status = FastTask::checkStatus($fastTaskId);

        if ($status === false) {
            return response()->json(['success' => false, 'message' => 'Ошибка получения статуса']);
        }

        if ($status == FastTask::STATUS_EXECUTED) {
            return response()->json(['success' => true, 'task_done' => true]);
        }

        return response()->json(['success' => true, 'task_done' => false]);
    }
}
