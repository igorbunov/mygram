<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteTaskListAndChatbotThreads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('task_lists');
        Schema::dropIfExists('chat_threads');

        \Illuminate\Support\Facades\DB::update("UPDATE tariff_lists SET description = 'unsubscribe,direct' WHERE id = 1");
        \Illuminate\Support\Facades\DB::update("UPDATE tariff_lists SET description = 'unsubscribe,direct,chatbot' WHERE id = 2");

        Schema::table('unsubscribe_tasks', function (Blueprint $table) {
            $table->dropColumn('task_list_id');
        });
        Schema::table('direct_tasks', function (Blueprint $table) {
            $table->dropColumn('task_list_id');
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
