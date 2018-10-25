<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use \Illuminate\Support\Facades\DB;

class CreateTaskListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->tinyInteger('is_active')->default(1);
            $table->integer('tariff_list_id');
            $table->string('type'); // direct, unsubscribe
            $table->timestamps();
        });

        DB::table('task_lists')->insert([
            'title' => 'Директ подписавшимся',
            'tariff_list_id' => 1,
            'type' => 'direct'
        ]);

        DB::table('task_lists')->insert([
            'title' => 'Директ подписавшимся',
            'tariff_list_id' => 2,
            'type' => 'direct'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_lists');
    }
}
