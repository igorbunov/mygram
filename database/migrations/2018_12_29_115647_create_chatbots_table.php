<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatbotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatbots', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('hashtags', 500)->default('');
            $table->string('status', 25)->default('empty');
            $table->integer('max_accounts')->default(100);
            $table->integer('work_with_direct_answer_task')->default(0);
            $table->integer('total_chats')->default(0);
            $table->integer('chats_in_progress')->default(0);
            $table->integer('chats_finished')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chatbots');
    }
}
