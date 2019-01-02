<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatHeadersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_headers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id');
            $table->integer('chatbot_id');
            $table->string('thread_id', 100);
            $table->string('thread_title', 100);
            $table->string('my_pk', 50);
            $table->string('companion_pk', 50);
            $table->string('last_message_id', 100)->default('');
            $table->string('status', 25)->default('not_started');

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
        Schema::dropIfExists('chat_headers');
    }
}
