<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimeoutToFastTask extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fast_tasks', function(Blueprint $table) {
            $table->integer('delay')->default(-1);
        });

        Schema::table('direct_tasks', function (Blueprint $table) {
            $table->dropColumn('work_only_in_night');
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
