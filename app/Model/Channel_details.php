<?php
/**
 * Created by PhpStorm.
 * User: hwp
 * Date: 18/7/20
 * Time: 上午10:52
 */
namespace App\Model;
use Illuminate\Database\Eloquent\Model;
class Channel_details extends Model
{
    //
    protected $table = 'channel_details';  // 表名

    public $timestamps = false; // 自动维护的时间戳

    protected $filelable = [ // 添加白名单，（允许提交数据库）
        'c_date', 'server_id', 'channle_id', 'channel_name', 'first_register', 'new_add', 'effective',
        'active', 'recharge_sum', 'recharge_num', 'recharge_money',  'first_pay', 'first_pay_sum', 'first_pay_total',
    ];

    protected  function getDateFormat()
    {
        return time(); // 获取时间的格式
    }

    protected function asDateTime($value)
    {
        return $value;  // g更新时间
    }

    public static function saveChannelData( ) {

    }


}