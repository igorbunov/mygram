<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDirectTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('direct_tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id');
            $table->tinyInteger('is_active')->default(0);
            $table->integer('task_list_id');
            $table->text('message'); // 800
            $table->integer('delay_time_min')->default(5);
            $table->integer('total_messages')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('failure_count')->default(0);
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
        Schema::dropIfExists('direct_tasks');
    }
}
