<?php

namespace App\Http\Controllers;
date_default_timezone_set('Asia/Shanghai');
use App\Model\Channel_details;
use App\Model\Recharge_detail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class RedisController extends Controller
{
//
//    private $dateTime;
//    private $serverId;
//    private $channelId;
//    private $channelName;
//    private $date;
//
//    public function __construct()
//    {
//        $this->dateTime = strtotime(date('Y-m-d'));
//
//        $channelArr = DB::table('users')
//            ->join('channels', function ($join)
//            {
//               $join->on('users.id', '=', 'channels.user_id');
//            })->select('channel_id')->get();
//        // 拿出所有的渠道ID
//        foreach ($channelArr as $item){
//            $key = $this->dateTime.$item->channel_id;
//            $redisValues = Redis::sMembers($key);
//            $this->getRedisData($redisValues);
//        }
//    }
//
//    // 获取服+渠道+时间等数据
//    function getRedisData($arr = []){
//        foreach ($arr as $item){
//            $item = json_decode($item);
//
//            $this->serverId     = $item->server_id;
//            $this->channelId    = $item->channel_id;
//            $this->date         = $item->time;
//            $this->channelName  = DB::table('channels')->where('channel_id', $item->channel_id)->value('channel_name');
//            $this->rechargeDetail();
//            $this->channelDetail();
//        }
//    }
//
//    function test() {
//        var_dump($this->channelId);
//    }
//
//    /*
//     * 充值详情
//     */
//    public function rechargeDetail()
//    {
//        $orderKey   = $this->dateTime.$this->channelId."order";
//        while ($orderData  = Redis::lPop($orderKey)){
//            $orderData = json_decode( $orderData, true);
//            $rId = DB::table('recharge_details')
//                ->where('order_id', $orderData['order_Id'])
//                ->value('id');
//
//            // 如果订单重复则不保存
//            if (empty($rId)) {
//                $rechargeData = new Recharge_detail();
//                $rechargeData['order_id']       = $orderData['order_Id'];
//                $rechargeData['channel_id']     = $orderData['channel_Id'];
//                $rechargeData['channel_name']   = $orderData['channel_name'];
//                $rechargeData['payment']        = $orderData['payment'];
//                $rechargeData['payment_money']  = $orderData['payment_money'];
//                $rechargeData['prop_num']       = $orderData['prop_num'];
//                $rechargeData['recharge_time']  = $orderData['recharge_time'];
//                $rechargeData['server_id']      = $orderData['server_id'];
//
//                $rechargeData->save();
//            }
//        }
//    }
//
//    /*
//     * 渠道数据 保存 / 更新数据
//     */
//    public function channelDetail()
//    {
//        // 根据 时间 + 服ID + 渠道ID 获取数据库的自增ID
//        $cId = DB::table('channel_details')
//            ->where('c_date', $this->dateTime)
//            ->Where('server_id', $this->serverId)
//            ->Where('channel_id', $this->channelId)
//            ->value('id');
//        var_dump($cId);
//        // 获取所有的Key
//        $newAddKey  = $this->dateTime.$this->serverId.$this->channelId."newAdd";
//        $activeKey  = $this->dateTime.$this->serverId.$this->channelId."active";
//        $moneyKey   = $this->dateTime.$this->serverId.$this->channelId."moneyNum";
//        $peopleKey  = $this->dateTime.$this->serverId.$this->channelId."peopleNum";
//
//        // 获取key 所对应的值
//        $newAddNUm  = $this->getData($newAddKey);       // 新增人数
//        $active     = $this->getData($activeKey);       // 活跃人数
//        $moneyNum   = $this->getData($moneyKey);        // 充值金额
//        $peopleNum  = $this->getData($peopleKey);       // 充值人数
//        var_dump($newAddNUm." + ".$active." + ".$moneyNum." + ".$peopleNum);
//
//
//        // 如果数据库不存在则存储
//        if (empty($cId)){
//            $channelData = new Channel_details();
//            $channelData->c_date            = $this->dateTime;
//            $channelData->server_id         = $this->serverId;
//            $channelData->channel_id        = $this->channelId;
//            $channelData->channel_name      = $this->channelName;
//            $channelData->new_add           = empty($newAddNUm) ? 0 :$newAddNUm;
//            $channelData->active            = empty($active) ? 0 : $active;
//            $channelData->recharge_money    = empty($moneyNum) ? 0 : $moneyNum;
//            $channelData->recharge_num      = empty($peopleNum) ? 0 : $peopleNum;
//            $channelData->rate              = $active ==0 ? 0 :($peopleNum / $active);
//            $channelData->arpu              = $peopleNum == 0 ? 0 : ($moneyNum / $peopleNum);
//
//            $channelData->save();
//        }elseif (! (empty($newAddNUm) && empty($active) && empty($moneyNum) && empty($peopleNum))){ // 存在则更新,如果没有数据则不存在更新
//            $data = channel_details::find($cId);
//
//            $data->new_add           = empty($newAddNUm) ? 0 :$newAddNUm;
//            $data->active            = empty($active) ? 0 : $active;
//            $data->recharge_money    = empty($moneyNum) ? 0 : $moneyNum;
//            $data->recharge_num      = empty($peopleNum) ? 0 : $peopleNum;
//            $data->rate              = $active ==0 ? 0 :($peopleNum / $active);
//            $data->arpu              = $peopleNum == 0 ? 0 : ($moneyNum / $peopleNum);
//
//            $data->update();;
//        }
//
//    }
//
//    // 获取redis的值
//    public function getData($key)
//    {
//        return Redis::get($key);
//    }

}
