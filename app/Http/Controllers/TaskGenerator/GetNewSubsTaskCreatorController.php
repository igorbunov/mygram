<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 15.12.18
 * Time: 12:19
 */
namespace App\Http\Controllers\TaskGenerator;

use App\DirectTask;
use App\FastTask;
use Illuminate\Support\Facades\Log;

class GetNewSubsTaskCreatorController
{
    public static function generateGetSubsTask(DirectTask $directTask): bool
    {
        if (is_null($directTask)) {
            return false;
        }

        if (!FastTask::isCanRun($directTask->account_id, FastTask::TYPE_GET_NEW_SUBSCRIBERS)) {
            return false;
        }

        $randomDelayMinutes = (FastTask::isNight()) ? rand(30, 50) : rand(10, 20);

        FastTask::addTask($directTask->account_id,
            FastTask::TYPE_GET_NEW_SUBSCRIBERS,
            $directTask->id,
            $randomDelayMinutes);

        return true;
    }
}