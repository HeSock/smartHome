<?php

namespace App\Http\Controllers;
date_default_timezone_set('Asia/Shanghai');
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HomeController extends Controller
{
    const STATITEM_QUEUE = 'channel.queue';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('channel.index');
//        return view('auth/login');

    }
    public function homeLogins()
    {

        return view('auth/login');
    }

    function getUser() {    // 用户ID 服务ID 渠道ID  第一次注册时间 登录时间
//        $detailData =   DB::table('user')->select('user_id', 'user_pid', 'user_sid', 'user_init_time', 'user_login_date')->where('user_init_time', 1531703433)->get();
//        $time =   DB::table('user')->select('user_init_time')->where('user_id', 10001029251)->get();

//        // 新增用户
//        $params['context']['channelid'] = 1;
//        $params['context']['deviceid'] = 7000;
//
//        $statItem = [
//            'p' => $params,
//            'action' => '/receive/rest/install'
//        ];
//        var_dump($statItem);
//        $this->pushItemToQueue($statItem);

        // 注册


//        $params['who'] = 70001;
//        $params['context']['channelid'] = 2;
//        $params['context']['serverid'] = 1;
//        $params['context']['deviceid'] = 7000;
//
//        $statItem = [
//            'p' => $params,
//            'action' => '/receive/rest/register'
//        ];
//        var_dump($statItem);
//        $this->pushItemToQueue($statItem);

//      //  登录
        /*$statLogin['who'] = 71001;
        $statLogin['context']['channelid'] = 1;
        $statLogin['context']['serverid'] = 1;
        $statLogin['context']['deviceid'] = 7100;


        $statItem = [
            'p' => $params,
            'action' => '/receive/rest/register'
        ];
        var_dump($statItem);
        $this->pushItemToQueue($statItem);*/

//      //  登录
//        $statLogin['who'] = 73011;
//        $statLogin['context']['channelid'] = 1;
//        $statLogin['context']['serverid'] = 3;
//        $statLogin['context']['deviceid'] = 7312;
//
//        $statItem = [
//            'p' => $statLogin,
//            'action' => '/receive/rest/loggedin'
//        ];
//        var_dump($statItem);
//        $this->pushItemToQueue($statItem);

////        // 充值详情
        $pay['who'] = 73131;
        $pay['context'] = [
            'deviceid'          => 7312,
            'channelid'         => 1,
            'serverid'          => 2,
            'transactionid'     => 5515,             // 充值账号
            'name'              => 'hahh',                                   // 充值账号
            'currencyamount'    => 10,          // 充值金额
            'virtualcoinamount' => 100,             // 购买的元宝
            'paymenttype'       => 'wx',

        ];

        $statItem = [
            'p' => $pay,
            'action' => '/receive/rest/payment'
        ];
        var_dump($statItem);
        $this->pushItemToQueue($statItem);

        // 选服界面
//        $params['who'] = 74012;
//        $params['what'] = 'inSl';
//        $params['context'] = [
//            'deviceid' => 7401,
//            'serverid' => 2,
//            'channelid'=> 1,
//        ];
//
//        $statItem = [
//            'p' => $params,
//            'action' => '/receive/rest/event'
//        ];
//        var_dump($statItem);
//        $this->pushItemToQueue($statItem);


//        // 有效用户
//        $params['who'] = 75012;
//        $params['what'] = 'pic_share_1';
//        $params['context'] = [
//            'deviceid' => 7503,
//            'serverid' => 2,
//            'channelid'=> 1,
//        ];
//
//        $statItem = [
//            'p' => $params,
//            'action' => '/receive/rest/event'
//        ];
//        var_dump($statItem);
//        $this->pushItemToQueue($statItem);

        /*$params['who'] = 75013;
        $params['what'] = 'bjpoint_kill1';
        $params['context'] = [
            'deviceid' => 7503,
            'serverid' => 2,
            'channelid'=> 1,
        ];

        $statItem = [
            'p' => $params,
            'action' => '/receive/rest/event'
        ];
        var_dump($statItem);
        $this->pushItemToQueue($statItem);*/


//        $params['who'] = 75012;
//        $params['what'] = 'pic_click_1';
//        $params['context'] = [
//            'deviceid' => 7503,
//            'serverid' => 2,
//            'channelid'=> 1,
//        ];
//
//        $statItem = [
//            'p' => $params,
//            'action' => '/receive/rest/event'
//        ];
//        var_dump($statItem);
//        $this->pushItemToQueue($statItem);
//        }

//        //推荐
//        $params['who'] = 75013;
//        $params['what'] = 'pic_register_1';
//        $params['context'] = [
//            'deviceid' => 7504,
//            'serverid' => 2,
//            'channelid'=> 0,
//        ];
//
//        $statItem = [
//            'p' => $params,
//            'action' => '/receive/rest/event'
//        ];
//        var_dump($statItem);
//        $this->pushItemToQueue($statItem);

    }
    function pushItemToQueue($statItem){
        $statItem = json_encode($statItem);

        Redis::rPush(self::STATITEM_QUEUE, $statItem);
    }
    function newHset($cacheKey, $field, $value) {
        $value = json_encode($value);
        Redis::hset($cacheKey, $field, $value);
    }
    // 获取某个用户所有账号
    function newHget($cacheKey, $field) {
        $cache = Redis::hget($cacheKey, $field);
        if(!$cache) {
            $cache = [];
            $value = json_encode($cache);
            Redis::hset($cacheKey, $field, $value);
        }
        return $cache;
    }
}
