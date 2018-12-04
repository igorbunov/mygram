<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFollowersIsSended extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_subscribers', function(Blueprint $table) {
            $table->integer('is_sended')->default(0);
        });

        \Illuminate\Support\Facades\DB::update("UPDATE account_subscribers SET is_sended=1");
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
