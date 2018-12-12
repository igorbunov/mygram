<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToDirectTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('direct_tasks', function(Blueprint $table) {
            $table->string('status', '50')->default('active');
        });

        \Illuminate\Support\Facades\DB::update("UPDATE direct_tasks SET `status` = 'active' WHERE is_active = 1");
        \Illuminate\Support\Facades\DB::update("UPDATE direct_tasks SET `status` = 'deactivated' WHERE is_active = 0");

        Schema::table('direct_tasks', function (Blueprint $table) {
            $table->dropColumn('is_active');
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
