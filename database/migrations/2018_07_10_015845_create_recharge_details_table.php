<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRechargeDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recharge_details', function (Blueprint $table) {
            $table->increments('id');
            $table->string('order_id',64)->unique();      // 订单号
            $table->string('channel_id', 64);              // 渠道ID
            $table->string('channel_name', 64);             // 渠道名称
            $table->string('payment');                  // 充值账号
            $table->integer('payment_money');           // 充值金额
            $table->integer('prop_num');                // 道具数量
            $table->integer('recharge_time');           // 充值时间
            $table->integer('server_id');               // 服务器
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
        Schema::dropIfExists('recharge_details');
    }
}
