<?php
/**
 * Created by PhpStorm.
 * User: hwp
 * Date: 18/7/20
 * Time: 上午10:59
 */
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Recharge_detail extends Model
{
    //
    protected $table = 'recharge_details';

    public $timestamps = false;

    protected $filelable = [
        'order_id', 'channle_id', 'channel_name', 'payment',
        'payment_money', 'prop_num', 'recharge_time', 'server_id','payment_type',
    ];

    protected  function getDateFormat(){
        return time();
    }

    protected function asDateTime($value){
        return $value;
    }
}