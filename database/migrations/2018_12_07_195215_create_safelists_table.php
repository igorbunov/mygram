<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSafelistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('safelists', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id');
            $table->integer('total_subscriptions')->default(0);
            $table->integer('selected_accounts')->default(0);
            $table->string('status', 25)->default('empty'); // empty, updating, synchronized, in_process


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
        Schema::dropIfExists('safelists');
    }
}
