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
            $table->decimal('price')->default(0);
            $table->text('payment_response');
            $table->string('payment_error_message')->default('');
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
