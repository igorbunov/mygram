<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatbotAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatbot_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('chatbot_id');
            $table->string('username', 100);
            $table->string('pk', 50);
            $table->text('json');
            $table->text('picture');
            $table->integer('is_private_profile')->default(0);
            $table->unique(['chatbot_id', 'pk']);
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
        Schema::dropIfExists('chatbot_accounts');
    }
}
