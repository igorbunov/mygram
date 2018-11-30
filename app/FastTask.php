<?php

namespace App;

use App\Http\Controllers\InstagramTasksRunner\AccountFirstLoginRunner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class FastTask extends Model
{
    const STATUS_SAVED = 'saved';
    const STATUS_IN_PROCESS = 'in_process';
    const STATUS_EXECUTED = 'executed';

    const TYPE_TRY_LOGIN = 'try_login';
    const TYPE_REFRESH_ACCOUNT = 'refresh_account';

    public static function addTask(int $accountId, string $taskType)
    {
        $task = new FastTask();
        $task->task_type = $taskType;
        $task->account_id = $accountId;
        $task->save();

        return $task->id;
    }

    public static function setStatus(int $taskId, string $status)
    {
        $res = self::find($taskId);

        $res->status = $status;

        $res->save();
    }

    public static function getTask()
    {
        $res = self::where([
            'status' => self::STATUS_SAVED
        ])->orderBy('id', 'ASC')
            ->limit(1)
            ->get();

        return $res;
    }

    public static function runTask()
    {
        $i = 0;

        while($i < 10) {
//            Log::debug('fast task run while: ' . $i);

            $tasks = FastTask::getTask();

            if (!is_null($tasks) and count($tasks) > 0) {
                foreach ($tasks as $task) {
                    switch ($task->task_type) {
                        case FastTask::TYPE_TRY_LOGIN:
                            FastTask::setStatus($task->id, FastTask::STATUS_IN_PROCESS);

                            try {
                                AccountFirstLoginRunner::tryLogin($task->account_id, $task->id);
                            } catch (\Exception $err) {
                                Log::error('Error running task AccountFirstLoginRunner::tryLogin: ' . $err->getMessage());
                            }

                            break;
                        case FastTask::TYPE_REFRESH_ACCOUNT:

                            break;
                    }
                }
            }

            $i++;

            sleep(5);
        }
    }
}
