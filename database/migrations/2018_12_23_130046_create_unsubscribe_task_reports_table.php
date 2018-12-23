<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnsubscribeTaskReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unsubscribe_task_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('unsubscribe_task_id');
            $table->text('response');
            $table->tinyInteger('success')->default(0);
            $table->string('error_message')->default('');
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
        Schema::dropIfExists('unsubscribe_task_reports');
    }
}
