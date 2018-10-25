<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->string('password')->default('');
            $table->text('picture');
            $table->tinyInteger('is_confirmed')->default(0);
            $table->string('confirm_code')->default('');
            $table->tinyInteger('is_forgot_password')->default(0);
            $table->string('forgot_code')->default('');
            $table->string('proxy_ip')->default('');
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
        Schema::dropIfExists('users');
    }
}
