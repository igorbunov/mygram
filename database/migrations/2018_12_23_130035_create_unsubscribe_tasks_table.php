<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnsubscribeTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unsubscribe_tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id');
            $table->string('status', 50)->default('active');
            $table->integer('task_list_id');
            $table->integer('total')->default(0);
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
        Schema::dropIfExists('unsubscribe_tasks');
    }
}
