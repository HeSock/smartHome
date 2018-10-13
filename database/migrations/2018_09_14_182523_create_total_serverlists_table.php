<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTotalServerlistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('total_serverlists', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('c_date');                  // 日期
            $table->string('channel_id', 64);              // 渠道号
            $table->integer('total_insl_sum');          // 选服页面访问人数
            $table->integer('new_add_user');            // 新增用户(新增设备)

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('total_serverlists');
    }
}
