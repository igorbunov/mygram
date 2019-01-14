<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProxyIpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proxy_ips', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id')->default(0);
            $table->string('proxy_string', 50);
            $table->timestamp('date_end', 0)->nullable();
            $table->unique(['proxy_string']);
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
        Schema::dropIfExists('proxy_ips');
    }
}
