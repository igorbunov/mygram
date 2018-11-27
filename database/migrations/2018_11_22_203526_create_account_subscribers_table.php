<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountSubscribersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_subscribers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_account_id');
            $table->string('username');
            $table->string('pk', 50);
            $table->text('json');
            $table->timestamps();
            $table->unique(['owner_account_id', 'pk']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_subscribers');
    }
}
