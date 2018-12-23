<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsUnsubscribedToSubscribtionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_subscriptions', function(Blueprint $table) {
            $table->integer('is_unsubscribed')->default(0);
        });

        //\Illuminate\Support\Facades\DB::update("UPDATE account_subscriptions SET is_unsubscribed=1");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscribtions', function (Blueprint $table) {
            //
        });
    }
}
