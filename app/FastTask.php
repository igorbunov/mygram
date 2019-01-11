<?php

namespace App;

use App\Http\Controllers\AccountController;
use App\Http\Controllers\InstagramTasksRunner\AccountFirstLoginRunner;
use App\Http\Controllers\InstagramTasksRunner\AccountSubscribersRunner;
use App\Http\Controllers\InstagramTasksRunner\AccountWhiteListRunner;
use App\Http\Controllers\InstagramTasksRunner\ChatbotTaskRunner;
use App\Http\Controllers\InstagramTasksRunner\DirectToSubsTasksRunner;
use App\Http\Controllers\InstagramTasksRunner\UnsubscribeTaskRunner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FastTask extends Model
{
    const STATUS_SAVED = 'saved';
    const STATUS_IN_PROCESS = 'in_process';
    const STATUS_EXECUTED = 'executed';

    const TYPE_DIRECT_ANSWER = 'direct_answer';
    const TYPE_TRY_LOGIN = 'try_login';
    const TYPE_REFRESH_ACCOUNT = 'refresh_account';
    const TYPE_UNSUBSCRIBE = 'unsubscribe';
    const TYPE_REFRESH_WHITELIST = 'refresh_whitelist';
    const TYPE_GET_NEW_SUBSCRIBERS = 'get_new_subscribers';
    const TYPE_REFRESH_CHATBOT_LIST = 'refresh_chatbot_list';
    const TYPE_GET_DIRECT_INBOX = 'get_direct_inbox';
    const TYPE_SEND_FIRST_CHATBOT_MESSAGE = 'send_first_chatbot_message';
    const TYPE_CHATBOT_ANALIZE_AND_ANSWER = 'chatbot_analize_and_answer';

    public static function isHadRestInLastOneAndHalfHoursUnsubscribeTasks(int $accountId): bool
    {
        $runsPerHour = (int) 60 / env('UNSUBSCRIBE_DELAY_MIN_SLEEP', 3);

        $res = DB::select("SELECT
               COUNT(1) AS cnt,
               IFNULL(SUM(IF(delay > 15, 1, 0)), 0) AS is_rest
            FROM fast_tasks
            WHERE task_type = :type AND account_id = :accountId
                AND updated_at > (NOW() - INTERVAL 100 MINUTE) 
            ORDER BY id DESC
            LIMIT {$runsPerHour}", [':type' => self::TYPE_UNSUBSCRIBE, ':accountId' => $accountId]);

        if (is_null($res) or count($res) == 0) {
//            Log::debug('is had rest: zero');
            return true;
        }

        $row = $res[0];

//        Log::debug('runsPerHour: ' . $runsPerHour . ' res: ' . \json_encode($row));

        if ($row->cnt > ($runsPerHour - 2)) {
            return ($row->is_rest > 0);
        }

        return true;
    }

    public static function isReachedHourlyLimitForFirstChatMessage(int $accountId): bool
    {
        $cnt = DB::selectOne("SELECT
              COUNT(1) as cnt
            FROM fast_tasks
            WHERE account_id = :accountId AND task_type IN('".self::TYPE_SEND_FIRST_CHATBOT_MESSAGE."')
                AND updated_at > (NOW() - INTERVAL 100 MINUTE)
            ORDER BY id DESC", [':accountId' => $accountId]);

        if (is_null($cnt)) {
            return false;
        }

        return ($cnt->cnt >= env('NOT_FRIEND_DIRECT_LIMITS_BY_HOUR', '10'));
    }

    public static function isHadRestInLastOneAndHalfHoursDirectTasks(int $accountId): bool
    {
        $res = DB::select("SELECT
                IFNULL(SUM(IF(delay > 30, 1, 0)), 0) AS is_rest
                , COUNT(1) as cnt
            FROM fast_tasks
            WHERE account_id = :accountId AND task_type IN('".self::TYPE_DIRECT_ANSWER."')
                AND updated_at > (NOW() - INTERVAL 90 MINUTE)
            ORDER BY id DESC", [':accountId' => $accountId]);

        if (is_null($res) or count($res) == 0) {
//            Log::debug('is had rest: zero');
            return true;
        }

        $row = $res[0];

//        Log::debug('res' . \json_encode($row));

        if ($row->cnt >= (env('FRIEND_DIRECT_LIMITS_BY_HOUR', '10') - 1) ) {
            if ($row->cnt > env('FRIEND_DIRECT_LIMITS_BY_HOUR', '10')) {
                return false;
            }

            return ($row->is_rest > 0);
        }

        return true;
    }

    public static function isCanRun(int $accountId, string $taskType)
    {
        $lastTask = self::getLastTask($accountId, $taskType);

        if (is_null($lastTask)) {
            return true;
        }

        if ($lastTask->status == self::STATUS_SAVED or $lastTask->status == self::STATUS_IN_PROCESS) {
            return false;
        }

        if ($lastTask->delay * 1 == -1) {
            return true;
        }

        return $lastTask->need_run * 1 > 0;
    }

    public static function getLastTask(int $accountId, string $taskType)
    {
        $res = self::select([DB::raw("*, IF((updated_at + INTERVAL delay MINUTE) < NOW(), 1, 0) AS need_run")])
            ->where(['account_id' => $accountId, 'task_type' => $taskType])
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->first();

        return $res;
    }

    public static function addTask(int $accountId, string $taskType, int $taskId = 0, int $delayMinutes = -1)
    {
        $task = new FastTask();
        $task->task_type = $taskType;
        $task->account_id = $accountId;
        $task->task_id = $taskId;
        $task->delay = $delayMinutes;
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
        return DB::selectOne("SELECT * 
            FROM fast_tasks 
            WHERE `status` = ?
            ORDER BY IF(delay = -1, 0, id) ASC
            LIMIT 1", [self::STATUS_SAVED]);
    }

    public static function runTask()
    {
        $task = self::getTask();

        if (is_null($task)) {
            return;
        }

        self::setStatus($task->id, FastTask::STATUS_IN_PROCESS);
//        Log::debug('Found fast task # ' . $task->id . ' ' .$task->task_type);
//        DirectTaskReport::getNow();

        switch ($task->task_type) {
            case self::TYPE_TRY_LOGIN:
                try {
                    AccountFirstLoginRunner::tryLogin($task->account_id);
                } catch (\Exception $err) {
                    $errorMessage = $err->getMessage();

                    Log::debug('Error running task AccountFirstLoginRunner::tryLogin: ' . $errorMessage . ' trace: ' . $err->getTraceAsString());

                    self::mailToDeveloper('ошибка выполнения задачи tryLogin', $errorMessage);
                } finally {
                    FastTask::setStatus($task->id, FastTask::STATUS_EXECUTED);
                }

                break;
            case self::TYPE_DIRECT_ANSWER:
                try {
                    DirectToSubsTasksRunner::runDirectTasks($task->task_id, $task->account_id);
                } catch (\Exception $err) {
                    $errorMessage = $err->getMessage();

                    if (strpos($errorMessage, 'DirectSendItemResponse: Feedback required') !== false) {
                        if (self::updateDelay($task->id, 180)) {
                            Log::debug('Delay updated for account: ' . $task->account_id);

                            AccountController::mailToClient($task->account_id, 'Ошибка директ автоответа', 'При попытке отправить директ сообщение возникла ошибка. Задача рассылки продолжится через 3 часа.');

                            AccountController::mailToClient($task->account_id, 'Ошибка директ ответ подписщикам', 'Инст отклонил директ сообщение (спам). Задача рассылки продолжится через 3 часа.');
                        }
                    }

                    Log::debug('Error running task DirectToSubsTasksRunner::runDirectTasks: ' . $errorMessage . ' ' . $err->getTraceAsString());

                    self::mailToDeveloper('ошибка выполнения задачи runDirectTasks', $errorMessage);
                } finally {
                    FastTask::setStatus($task->id, FastTask::STATUS_EXECUTED);
                }

                break;
            case self::TYPE_REFRESH_ACCOUNT:
                try {
                    AccountFirstLoginRunner::runRefresh($task->account_id);
                } catch (\Exception $err) {
                    $errorMessage = $err->getMessage();

                    Log::debug('Error running task AccountFirstLoginRunner::runRefresh: ' . $errorMessage);

                    self::mailToDeveloper('ошибка выполнения задачи runRefresh', $errorMessage);
                } finally {
                    FastTask::setStatus($task->id, FastTask::STATUS_EXECUTED);
                }

                break;
            case self::TYPE_REFRESH_WHITELIST:
                try {
                    AccountWhiteListRunner::runRefresh($task->task_id);
                } catch (\Exception $err) {
                    $errorMessage = $err->getMessage();

                    Log::debug('Error running task AccountWhiteListRunner::refreshWhitelist: ' . $errorMessage);

                    self::mailToDeveloper('ошибка выполнения задачи refreshWhitelist', $errorMessage);
                } finally {
                    FastTask::setStatus($task->id, FastTask::STATUS_EXECUTED);
                }

                break;
            case self::TYPE_GET_NEW_SUBSCRIBERS:
                try {
                    AccountSubscribersRunner::runGetSubscribers($task->account_id);
                } catch (\Exception $err) {
                    $errorMessage = $err->getMessage();

                    Log::debug('Error running task get new subscribers: ' . $errorMessage);

                    self::mailToDeveloper('ошибка выполнения задачи subscribers', $errorMessage);
                } finally {
                    FastTask::setStatus($task->id, FastTask::STATUS_EXECUTED);
                }

                break;
            case self::TYPE_UNSUBSCRIBE:
                try {
                    UnsubscribeTaskRunner::runUnsubscribeTasks($task->task_id, $task->account_id);
                } catch (\Exception $err) {
                    $errorMessage = $err->getMessage();

                    Log::debug('Error running task unsubscribe: ' . $errorMessage . ' trace: ' . $err->getTraceAsString());

                    self::mailToDeveloper('ошибка выполнения задачи unsubscribe', $errorMessage);
                } finally {
                    FastTask::setStatus($task->id, FastTask::STATUS_EXECUTED);
                }

                break;
            case self::TYPE_REFRESH_CHATBOT_LIST:
                $chatbotId = $task->task_id;

                try {
                    ChatbotTaskRunner::runRefreshList($chatbotId, $task->account_id);
                } catch (\Exception $err) {
                    $errorMessage = $err->getMessage();

                    Log::debug('Error running task refresh chatbot list: ' . $errorMessage);

                    self::mailToDeveloper('ошибка выполнения задачи refresh chatbot list', $errorMessage);
                } finally {
                    FastTask::setStatus($task->id, FastTask::STATUS_EXECUTED);
                    Chatbot::setStatus($chatbotId, Chatbot::STATUS_SYNCHRONIZED);
                }

                break;
            case self::TYPE_GET_DIRECT_INBOX:
//                $chatbotId = $task->task_id;

                try {
                    ChatbotTaskRunner::getDirectInbox($task->account_id);
                } catch (\Exception $err) {
                    $errorMessage = $err->getMessage();

                    Log::debug('Error running task TYPE_GET_DIRECT_INBOX: ' . $errorMessage);

                    self::mailToDeveloper('ошибка выполнения задачи TYPE_GET_DIRECT_INBOX', $errorMessage);
                } finally {
                    FastTask::setStatus($task->id, FastTask::STATUS_EXECUTED);
                }

                break;
            case self::TYPE_SEND_FIRST_CHATBOT_MESSAGE:
//                $chatbotId = $task->task_id;

                try {
                    Log::debug('['.$task->account_id.'] sendFirstMessage');
                    ChatbotTaskRunner::sendFirstMessage($task->account_id);
                } catch (\Exception $err) {
                    $errorMessage = $err->getMessage();

                    Log::debug('['.$task->account_id.'] Error running task sendFirstMessage: ' . $errorMessage);

                    self::mailToDeveloper('['.$task->account_id.'] Директ не отправился, превышен лимит. sendFirstMessage', $errorMessage);

                    AccountController::mailToClient($task->account_id, 'Ошибка первого сообщения', 'Инст отклонил директ сообщение (спам). Задача рассылки продолжится через 3 часа.');

                    if (strpos($errorMessage, 'DirectSendItemResponse: Feedback required') !== false) {
                        if (self::updateDelay($task->id, 180)) {
                            Log::debug('Delay updated for account: ' . $task->account_id);
                        }
                    }
                } finally {
                    FastTask::setStatus($task->id, FastTask::STATUS_EXECUTED);
                }

                break;
            case self::TYPE_CHATBOT_ANALIZE_AND_ANSWER:
                $chatbotId = $task->task_id;

                try {
                    ChatbotTaskRunner::analizeDialogAndAnswer($chatbotId, $task->account_id);
                } catch (\Exception $err) {
                    $errorMessage = $err->getMessage();

                    Log::debug('Error running task analizeDialogAndAnswer: ' . $errorMessage . ' ' . $err->getTraceAsString());

                    self::mailToDeveloper('ошибка выполнения задачи analizeDialogAndAnswer', $errorMessage);
                } finally {
                    FastTask::setStatus($task->id, FastTask::STATUS_EXECUTED);
                }

                break;
        }
    }

    public static function checkStatus(int $taskId)
    {
        $res = self::find($taskId);

        if (is_null($res)) {
            return false;
        }

        return $res->status;
    }

    public static function isNight()
    {
        date_default_timezone_set('Europe/Kiev');

        $nightStartTime = env('NIGHT_TIME_START_HOUR', '23');
        $nightEndTime = env('NIGHT_TIME_END_HOUR', '5');

        $curHour = (int) date("H");

        $isNight = (($nightStartTime >= 21 and $curHour >= $nightStartTime) or $curHour <= $nightEndTime);

        return $isNight;
    }

    public static function mailToDeveloper($subject, $message)
    {
        if (env('ENABLE_EMAIL')) {
            $headers = "From: mygram.in.ua\nReply-To: igorbunov.ua@gmail.com\nMIME-Version: 1.\nContent-Type: text/html; charset=UTF-8";

            \mail(env('DEVELOPER_EMAIL'), $subject, $message, $headers);
        }
    }

    public static function updateDelay(int $taskId, int $delay)
    {
        $res = self::find($taskId);

        if (is_null($res)) {
            return false;
        }

        $res->delay = $delay;

        Log::debug('=== FastTask: delay updated for taskId: ' . $taskId. ' = ' . $delay);

        return $res->save();
    }
}
