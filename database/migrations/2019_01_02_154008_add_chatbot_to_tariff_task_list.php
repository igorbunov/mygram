<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddChatbotToTariffTaskList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('task_lists')->insert([
            'title' => 'Чат бот',
            'is_active' => 1,
            'tariff_list_id' => 2,
            'type' => 'chatbot'
        ]);

        DB::update("UPDATE tariff_lists SET `description` = 'Директ подписавшимся, Массовая отписка, Чат бот' WHERE id = 2");


        Schema::table('chatbot_accounts', function(Blueprint $table) {
            $table->tinyInteger('is_sended')->default(0);
            $table->integer('sender_account_id')->default(0);
        });
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
