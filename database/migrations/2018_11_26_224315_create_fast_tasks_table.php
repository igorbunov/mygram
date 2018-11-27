<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFastTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fast_tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('task_type'); // try_login, refresh_account
            $table->integer('account_id');
            $table->string('status')->default('saved'); //executed, in_process
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
        Schema::dropIfExists('fast_tasks');
    }
}
