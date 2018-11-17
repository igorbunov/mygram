<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTariffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tariffs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tariff_list_id');
            $table->integer('user_id');
            $table->tinyInteger('is_active')->default(0);
            $table->tinyInteger('is_payed')->default(0);
            $table->string('payment_id')->default('');
            $table->string('currency')->default('');
            $table->string('ip')->default('');
            $table->decimal('amount')->default(0);
            $table->integer('accounts_count')->default(0);
            $table->text('payment_response');
            $table->string('payment_message')->default('');
            $table->timestamp('dt_start', 0)->nullable();
            $table->timestamp('dt_end', 0)->nullable();
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
        Schema::dropIfExists('tariffs');
    }
}
