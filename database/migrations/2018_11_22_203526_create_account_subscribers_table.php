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
            $table->string('pk');
            $table->text('json');
            $table->timestamps();

            $table->unique(['owner_account_id', 'pk']);

//full_name: "aman kk"
//has_anonymous_profile_picture: false
//is_private: false
//is_verified: false
//pk: "3971646038"
//profile_pic_id: "1862890045070104052_3971646038"
//profile_pic_url: "https://instagram.fiev7-2.fna.fbcdn.net/vp/0fcef5789c5f3228cdeff405832ba328/5C790830/t51.2885-19/s150x150/41295730_263151084527628_383114673395859456_n.jpg"
//reel_auto_archive: "on"
//username: "aman69985"
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
