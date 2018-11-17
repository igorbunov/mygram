<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateTariffListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tariff_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description');
            $table->decimal('price_one_uah')->default(0);
            $table->decimal('price_three_uah')->default(0);
            $table->decimal('price_five_uah')->default(0);
            $table->decimal('price_ten_uah')->default(0);
            $table->string('length')->default('month');
            $table->tinyInteger('is_active')->default(1);
            $table->tinyInteger('is_trial')->default(0);
            $table->timestamps();
        });

        DB::table('tariff_lists')->insert([
            'name' => 'Пробный',
            'description' => 'Директ подписавшимся',
            'is_trial' => 1
        ]);

        DB::table('tariff_lists')->insert([
            'name' => 'Начальный',
            'description' => 'Директ подписавшимся',
            'length' => 'month',
            'price_one_uah' => 300,
            'price_three_uah' => 750,
            'price_five_uah' => 1000,
            'price_ten_uah' => 1500
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tariff_lists');
    }
}
