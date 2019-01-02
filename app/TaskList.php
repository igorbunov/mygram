<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskList extends Model
{
    const TYPE_DIRECT = 'direct';
    const TYPE_UNSUBSCRIBE = 'unsubscribe';
    const TYPE_CHATBOT = 'chatbot';

    public function directTasks() {
        return $this->hasMany('App\DirectTask', 'task_list_id', 'id');
    }

    public function tariffList() {
        return $this->hasMany('App\TariffList', 'tariff_list_id', 'id');
    }

    public static function getAvaliableTasksForTariffListId(int $tariffListId, bool $asArray = false)
    {
        $res = self::where([
            'tariff_list_id' => $tariffListId,
            'is_active' => 1
        ])->get();

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
