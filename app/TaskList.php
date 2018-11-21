<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskList extends Model
{
    public function directTasks() {
        return $this->hasMany('App\DirectTask', 'task_list_id', 'id');
    }

    public function tariffList() {
        return $this->hasMany('App\TariffList', 'tariff_list_id', 'id');
    }

    public static function getAvaliableTasksForTariff(int $taskListId, bool $asArray = false)
    {
        $res = TaskList::where(['tariff_list_id' => $taskListId])->get();

        if (!$asArray) {
            return $res;
        }

        $result = [];

        foreach ($res as $row) {
            $result[] = $row->toArray();
        }

        return $result;
    }
}
