<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTaskListsUnsubscribe extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('task_lists')->insert([
            'title' => 'Массовая отписка',
            'is_active' => 1,
            'tariff_list_id' => 1,
            'type' => 'unsubscribe'
        ]);

        DB::table('task_lists')->insert([
            'title' => 'Массовая отписка',
            'is_active' => 1,
            'tariff_list_id' => 2,
            'type' => 'unsubscribe'
        ]);

        DB::update("UPDATE tariff_lists SET `description` = 'Директ подписавшимся, Массовая отписка' WHERE id = 1");

        DB::update("UPDATE tariff_lists SET `description` = 'Директ подписавшимся, Массовая отписка' WHERE id = 2");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
