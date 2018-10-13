<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVotesToChannelDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('channel_details', function (Blueprint $table) {
            //
            $table->integer('recharge_money_other');    // 充值金额 该用户在小程序，官网的充值
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('channel_details', function (Blueprint $table) {
            //
        });
    }
}
