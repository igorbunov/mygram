<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->default(0);
            $table->string('nickname')->default('');
            $table->string('password')->default('');
            $table->string('instagram_id')->default('');
            $table->string('fio')->default('');
            $table->integer('publications')->default(0);
            $table->integer('subscribers')->default(0);
            $table->integer('subscriptions')->default(0);
            $table->text('picture');
            $table->tinyInteger('is_active')->default(1);
            $table->string('rank_token')->default('');
            $table->tinyInteger('is_confirmed')->default(0);
            $table->text('response');

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
        Schema::dropIfExists('accounts');
    }
}
