<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChannelDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_details', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('c_date');                  // 日期
            $table->integer('server_id');               // 服务器
            $table->string('channel_id', 64);              // 渠道ID
            $table->string('channel_name', 64);         // 渠道名称
            $table->integer('new_add');                 // 新增账号
            $table->integer('active');                  // 活跃账号
            $table->integer('recharge_num');            // 充值人数
            $table->integer('recharge_money');          // 充值金额
            $table->integer('first_register');          // 首创角色
            $table->integer('effective');               // 有效角色
            $table->integer('recharge_sum');            // 充值总次数
            $table->integer('first_pay');               // 首付人数
            $table->integer('first_pay_sum');           // 首付金额
            $table->integer('first_pay_total');         // 首付总额
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('channel_details');
    }
}
