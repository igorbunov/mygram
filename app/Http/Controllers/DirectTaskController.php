<?php

namespace App\Http\Controllers;

use App\account;
use App\AccountSubscribers;
use App\DirectTask;
use Illuminate\Http\Request;

class DirectTaskController extends Controller
{
    public function clearDirectQueue(Request $req)
    {
        $taskId = (int) $req->post('task_id', 0);
        $accountId = (int) $req->post('account_id', 0);
        $taskType = $req->post('task_type', '');

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
            $direct = DirectTask::getDirectTaskById($taskId, $accountId, false);

            if (is_null($direct)) {
                return response()->json(['success' => false, 'error' => 'Задание не найдено']);
            }

            AccountSubscribers::clearQueue($accountId);

            return response()->json([
                'success' => true,
                'accountId' => $direct->account_id
            ]);
        }

        return response()->json(['success' => false, 'error' => 'not set task type']);
    }
}
