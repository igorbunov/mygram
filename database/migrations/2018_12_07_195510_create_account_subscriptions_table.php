<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_account_id');
            $table->string('username', 100);
            $table->string('pk', 50);
            $table->text('json');
            $table->tinyInteger('is_my_subscriber')->default(0);
            $table->tinyInteger('is_in_safelist')->default(0);
            $table->text('picture');
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
        Schema::dropIfExists('account_subscriptions');
    }
}
