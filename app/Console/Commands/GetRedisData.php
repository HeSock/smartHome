<?php
/**
 * Created by PhpStorm.
 * User: hwp
 * Date: 18/7/24
 * Time: 上午10:00
 */
namespace App\Console\Commands;
date_default_timezone_set('Asia/Shanghai');
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Model\Channel_details;
use App\Model\Recharge_detail;
use App\Model\total_serverlist;
use App\Model\share;
use App\Model\share_total;
use App\Model\Moregame;
use Illuminate\Support\Facades\Redis;

class GetRedisData extends Command
{

    private $dateTime;
    private $keyTime;
    private $serverId;
    private $channelId;
    private $channelName;
    const SHARE_ID_KEY = 'shareId';
    const MORE_ID_KEY = 'moregameId';
    const CONNECTOR  = '_';

    /**
     * 控制台命令名称
     *
     * @var string
     */
    protected $signature = 'channel {param1}';

    /**
     * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'channel';

    /**
     * 创建新的命令实例
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 执行控制台命令
     *
     * @return mixed
     */
    public function handle()
    {
        //参数调用方法
        $param1 = $this->argument('param1');
//        $param2 = $this->option('param2');
        if($param1 == 'start') {
            $this->info('-------开始工作---------');
            $this->dateTime = strtotime(date('Y-m-d')); // 存数据库的时间戳
            $this->keyTime = date('Y-m-d');             // 获取key的时间

            $channelArr = DB::table('qudaos')->select('channel_id')->get();
            // 拿出所有的渠道ID
            foreach ($channelArr as $item){
                $key = $this->keyTime.self::CONNECTOR.$item->channel_id;
                $redisValues = Redis::sMembers($key);

                $this->getRedisData($redisValues, $item->channel_id);

            }

//            $shareArr = DB::table('shares')->select('shareid')->get();
            $shareArr = Redis::sMembers(self::SHARE_ID_KEY);

            foreach ($shareArr as $k => $item) { // 应用宝 微信
                $this->getShareData($item);
            }
            $this->getTotalData();

            $moreArr = Redis::sMembers(self::MORE_ID_KEY);

            foreach ($moreArr as $k => $item) {
                $this->getMoreData($item);
            }

            $this->getChannelTotal();
        }else {
            $this->error('请输入： php artisan channel start');
        }
    }

    // 获取服+渠道+时间等数据
    function getRedisData($arr = [], $channelId)
    {
        foreach ($arr as $item) {
            $item = json_decode($item);

            $this->serverId = $item->server_id;
            $this->channelId = $channelId;
            $this->channelName = DB::table('channels')->where('channel_id', $item->channel_id)->value('channel_name');
            $this->rechargeDetail();    // 充值详情
            $this->channelDetail();     // 渠道数据
            $this->saveINSL();          // 统计访问选服页面人数
        }
    }

    // 存share的数据
    function getShareData($shareid) {
        // 获取Key
        $shareKey       = 'share'.$shareid;
        $sharePeopleKey = 'sharePeopleNum'.$shareid;
        $clickKey       = 'click'.$shareid;
        $clickPeopleKey = 'clickPeopleNum'.$shareid;
        $registerKey    = 'register'.$shareid;

        // 获取数值
        $shareNum       = $this->getData($shareKey);
        $sharePeopleNum = $this->getData($sharePeopleKey);
        $clickNum       = $this->getData($clickKey);
        $clickPeopleNum = $this->getData($clickPeopleKey);
        $registerNum    = $this->getData($registerKey);


        $this->info('分享ID '.$shareid.' 【分享次数：'.$shareNum.', 分享人数：'.$sharePeopleNum.', 点击次数：'.$clickNum.', 点击人数：'.$clickPeopleNum.', 注册人数：'.$registerNum.'】');
        $cId = DB::table('shares')
            ->where('shareid', $shareid)
            ->value('id');

        if (empty($cId)){ // 不存在则存储
            $shareData = new Share();
            $shareData['shareid']           = $shareid;
            $shareData['share_people_num']  = empty(!$sharePeopleNum) ? $sharePeopleNum : 0;
            $shareData['share_num']         = empty(!$shareNum) ? $shareNum : 0;
            $shareData['click_num']         = empty(!$clickNum) ? $clickNum : 0;
            $shareData['click_people_num']  = empty(!$clickPeopleNum) ? $clickPeopleNum : 0;
            $shareData['register_num']      = empty(!$registerNum) ? $registerNum : 0;
            $shareData->save();
        }else { // 存在则更新
            $data = share::find($cId);
            $sign = 1;
            if ($data->share_people_num > $sharePeopleNum) {
                $sign = 0;
                $this->error('分享次数错误'.'>>>>> 分享ID为 【'.$shareid.'】: 数据库的数据:《 '.$data->share_people_num.' 》, 当前统计数据:《 '.$sharePeopleNum.'》');
            }

            if ($data->share_num > $shareNum) {
                $sign = 0;
                $this->error('分享总次数错误'.'>>>>> 分享ID为 【'.$shareid.'】:  数据库的数据 《'.$data->share_num.'》, 当前统计数据 《'.$shareNum.'》');
            }
            if ($data->click_num > $clickNum) {
                $sign = 0;
                $this->error('点击次数错误'.'>>>>> 分享ID为 【'.$shareid.'】:  数据库的数据 《'.$data->click_num.'》, 当前统计数据 《'.$clickNum.' 》');
            }
            if ($data->click_people_num > $clickPeopleNum) {
                $sign = 0;
                $this->error('点击总人数错误'.'>>>>> 分享ID为 【'.$shareid.'】:  数据库的数据 《'.$data->click_people_num.'》, 当前统计数据 《'.$clickPeopleNum.' 》');
            }
            if ($data->register_num > $registerNum) {
                $sign = 0;
                $this->error('注册错误'.'>>>>> 分享ID为 【'.$shareid.'】:  数据库的数据 《'.$data->register_num.'》, 当前统计数据 《'.$registerNum.' 》');
            }
            if ($sign) {
                // 存数据
                if (!empty($sharePeopleNum)) {  // 分享人数
                    $data->share_people_num     = $sharePeopleNum;
                }
                if (!empty($shareNum)) {        // 分享总次数
                    $data->share_num            = $shareNum;
                }
                if (!empty($clickNum)) {        // 点击总次数
                    $data->click_num            = $clickNum;
                }
                if (!empty($clickPeopleNum)) {  // 点击人数
                    $data->click_people_num     = $clickPeopleNum;
                }
                if (!empty($registerNum)) {     // 注册人数
                    $data->register_num         = $registerNum;
                }

                $data->update();
            }

        }

    }
    // 存Moregame的数据
    function getMoreData($moreid) {
        // 获取Key
        $moreKey       = 'moregame'.$moreid;
        $morePeopleKey = 'moregamePeopleNum'.$moreid;

        // 获取数值
        $moreNum       = $this->getData($moreKey);
        $morePeopleNum = $this->getData($morePeopleKey);


        $this->info('推荐ID '.$moreid.' 【 点击次数：'.$moreNum.', 点击人数：'.$morePeopleNum.'】');
        $cId = DB::table('moregames')
            ->where('more_id', $moreid) //"'".$moreid."'"
            ->value('id');

        if (empty($cId)){ // 不存在则存储
            $shareData = new Moregame();
            $shareData['more_id']           = $moreid;
            $shareData['more_people_num']  = empty(!$morePeopleNum) ? $morePeopleNum : 0;
            $shareData['more_num']         = empty(!$moreNum) ? $moreNum : 0;
            $shareData->save();
        }else { // 存在则更新
            $data = Moregame::find($cId);
            $sign = 1;
            if ($data->more_num > $moreNum) {
                $sign = 0;
                $this->error('抽屉点击次数错误'.'>>>>> 游戏ID为 【'.$moreid.'】:  数据库的数据 《'.$data->more_num.'》, 当前统计数据 《'.$moreNum.' 》');
            }
            if ($data->more_people_num > $morePeopleNum) {
                $sign = 0;
                $this->error('抽屉点击总人数错误'.'>>>>> 游戏ID为 【'.$moreid.'】:  数据库的数据 《'.$data->more_people_num.'》, 当前统计数据 《'.$morePeopleNum.' 》');
            }
            if ($sign) {
                // 存数据
                if (!empty($moreNum)) {        // 点击总次数
                    $data->more_num            = $moreNum;
                }
                if (!empty($morePeopleNum)) {  // 点击人数
                    $data->more_people_num     = $morePeopleNum;
                }

                $data->update();
            }

        }

    }

    function getTotalData(){
        $shareSumKey    = 'share_total';
        $clickSumKey    = 'click_total';
        $moreSumKey    = 'moregame_total';

        $shareSum       = $this->getData($shareSumKey);
        $clickSum       = $this->getData($clickSumKey);
        $moreSum       = $this->getData($moreSumKey);

        $this->info('【 推荐点击人数：'.$moreSum.', 分享人数：'.$shareSum.', 分享点击人数：'.$clickSum.'】');

        $total = DB::table('share_totals')->first();
        if (!$total){
            $totalData = new share_total();
            $totalData['share_total'] = (int)$shareSum;
            $totalData['click_total'] = (int)$clickSum;
            $totalData['moregame_total'] = (int)$moreSum;
            $totalData->save();
        }else{
            $totalUpData = [];
            if (((int)$shareSum != 0 || (int)$clickSum != 0) && ($total->share_total < (int)$shareSum || $total->click_total < (int)$clickSum)){
                $totalUpData['share_total'] = (int)$shareSum;
                $totalUpData['click_total'] = (int)$clickSum;
            }else{
                if ($total->share_total > (int)$shareSum){
                    $this->error('总分享人数错误'.'>>>>> :  数据库的数据 《'.$total->share_total.'》, 当前统计数据 《'.$shareSum.' 》');
                }
                if ($total->click_total > (int)$clickSum){
                    $this->error('总点击次数错误'.'>>>>> :  数据库的数据 《'.$total->click_total.'》, 当前统计数据 《'.$clickSum.' 》');
                }
            }
            if ((int)$moreSum != 0 && $total->moregame_total < (int)$moreSum){
                $totalUpData['moregame_total'] = (int)$moreSum;
            }else{
                if ($total->moregame_total > (int)$moreSum){
                    $this->error('抽屉点击人数错误'.'>>>>> :  数据库的数据 《'.$total->moregame_total.'》, 当前统计数据 《'.$moreSum.' 》');
                }
            }
            count($totalUpData) > 0 && DB::table('share_totals')->where('id',$total->id)->update($totalUpData);
        }
    }

    /*
     * 充值详情
     */
    function getChannelTotal(){
        $channels = DB::table('qudaos')->get();
        $key = 'registerChann_';
        foreach ($channels as $channel){
            $num = $this->getData($key. $channel->channel_id);
            if ($num == 0 || $num <= $channel->channel_regnum){
                continue;
            }
            $data = [
                'channel_regnum'=> (int)$num,
            ];
            DB::table('qudaos')->where('id',$channel->id)->update($data);
        }
    }

    /*
     * 充值详情
     */
    public function rechargeDetail()
    {
        $orderKey   = $this->keyTime.self::CONNECTOR.$this->channelId."_order";
        while ($orderData  = Redis::lPop($orderKey)){
            $orderData = json_decode( $orderData, true);
            $rId = DB::table('recharge_details')
                ->where('order_id', $orderData['order_Id'])
                ->value('id');
            // 如果订单重复则不保存
            if (empty($rId)) {
                $rechargeData = new Recharge_detail();
                $rechargeData['order_id']       = $orderData['order_Id'];
                $rechargeData['channel_id']     = $orderData['channel_Id'];
                $rechargeData['channel_name']   = $orderData['channel_name'];
                $rechargeData['payment']        = $orderData['payment'];
                $rechargeData['payment_money']  = $orderData['payment_money'];
                $rechargeData['prop_num']       = $orderData['prop_num'];
                $rechargeData['recharge_time']  = $orderData['recharge_time'];
                $rechargeData['server_id']      = $orderData['server_id'];
                $rechargeData['payment_type']   = $orderData['payment_type'];

                $rechargeData->save();
            }
        }
    }

    /*
     * 渠道数据 保存 / 更新数据
     */
    public function channelDetail()
    {
        // 根据 时间 + 服ID + 渠道ID 获取数据库的自增ID
        $cId = DB::table('channel_details')
            ->where('c_date', $this->dateTime)
            ->Where('server_id', $this->serverId)
            ->Where('channel_id', $this->channelId)
            ->value('id');

        // 获取所有的Key
        $firstRegisterKey   = $this->keyTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId."_fisrt_register";    // 首创角色
        $newAddKey          = $this->keyTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId."_newAdd";            // 新增账号
        $effectiveKey       = $this->keyTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId."_effective";         // 有效角色
        $activeKey          = $this->keyTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId."_active";            // 活跃用户
        $peopleSumKey       = $this->keyTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId."_peopleSum";         // 充值总次数
        $peopleKey          = $this->keyTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId."_peopleNum";         // 充值人数
        $moneyKey           = $this->keyTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId."_moneyNum";          // 充值金额
        $moneyKeyOther      = $this->keyTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId."_moneyNumOther";          // 充值金额 其他充值
        $firstPayKey        = $this->keyTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId."_pay";               // 首付人数
        $firstPaySumKey     = $this->keyTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId."_pay_sum";           // 首付金额
        $TotalPaySumKey     = $this->keyTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId."_first_pay_total";   // 首付当日总额


        // 获取key 所对应的值
        $firstRegisterNum   = $this->getData($firstRegisterKey );       // 新增人数
        $newAddNUm          = $this->getData($newAddKey);               // 新增人数
        $effectiveNum       = $this->getData($effectiveKey );           // 新增人数
        $activeNum          = $this->getData($activeKey );              // 新增人数
        $peopleSumNum       = $this->getData($peopleSumKey );           // 新增人数
        $peopleNum          = $this->getData($peopleKey );              // 新增人数
        $moneyNum           = $this->getData($moneyKey );               // 新增人数
        $moneyNumOther      = $this->getData($moneyKeyOther );               // 新增人数
        $firstPayNum        = $this->getData($firstPayKey );            // 新增人数
        $firstPaySumNum     = $this->getData($firstPaySumKey );         // 新增人数
        $TotalPaySumNum     = $this->getData($TotalPaySumKey );         // 新增人数


//        var_dump("新增人数: ".$newAddNUm. "; 活跃人数: ".$active."; 充值金额：".$moneyNum. "; 充值人数：".$peopleNum);
//        $this->info("新增人数: [".$newAddNUm. "]; 活跃人数: [".$activeNum."]; 充值金额：[".$moneyNum. "]; 充值人数：[".$peopleNum."]");
        // 如果数据库不存在则存储
        if (empty($cId)){
            if (empty($this->serverId) || $this->serverId == 0){
                return false;
            }
            $channelData = new channel_details();
            $channelData->c_date            = $this->dateTime;
            $channelData->server_id         = $this->serverId;
            $channelData->channel_id        = $this->channelId;
            $channelData->channel_name      = $this->channelId;
            $channelData->first_register    = empty($firstRegisterNum) ? 0 :$firstRegisterNum;
            $channelData->new_add           = empty($newAddNUm) ? 0 :$newAddNUm;
            $channelData->effective         = empty($effectiveNum) ? 0 :$effectiveNum;
            $channelData->active            = empty($activeNum) ? 0 : $activeNum;
            $channelData->recharge_sum      = empty($peopleSumNum) ? 0 :$peopleSumNum;
            $channelData->recharge_num      = empty($peopleNum) ? 0 : $peopleNum;
            $channelData->recharge_money    = empty($moneyNum) ? 0 : $moneyNum;
            $channelData->recharge_money_other    = empty($moneyNumOther) ? 0 : $moneyNumOther;
            $channelData->first_pay         = empty($firstPayNum) ? 0 :$firstPayNum;
            $channelData->first_pay_sum     = empty($firstPaySumNum) ? 0 :$firstPaySumNum;
            $channelData->first_pay_total   = empty($TotalPaySumNum) ? 0 :$TotalPaySumNum;

            $channelData->save();
        }else{ // 存在则更新,如果没有数据则不存在更新
            $data = Channel_details::find($cId);
            $sign = 1;
            if ($data->first_register > $firstRegisterNum) {
                $sign = 0;
                $this->error('首创角色错误'.'>>>>> 日期为 【'.$this->keyTime.'服务器: '.$this->serverId.'渠道: '.$this->channelId.'】: 数据库的数据:《 '.$data->first_register.' 》, 当前统计数据:《 '.$firstRegisterNum.'》');
            }
            if ($data->new_add > $newAddNUm) {
                $sign = 0;
                $this->error('新增账号错误'.'>>>>> 日期为 【'.$this->keyTime.'服务器: '.$this->serverId.'渠道: '.$this->channelId.'】: 数据库的数据:《 '.$data->new_add.' 》, 当前统计数据:《 '.$newAddNUm.'》');
            }
            if ($data->effective > $effectiveNum) {
                $sign = 0;
                $this->error('有效角色错误'.'>>>>> 日期为 【'.$this->keyTime.'服务器: '.$this->serverId.'渠道: '.$this->channelId.'】: 数据库的数据:《 '.$data->effective.' 》, 当前统计数据:《 '.$effectiveNum.'》');
            }
            if ($data->active > $activeNum) {
                $sign = 0;
                $this->error('活跃账号错误'.'>>>>> 日期为 【'.$this->keyTime.'服务器: '.$this->serverId.'渠道: '.$this->channelId.'】: 数据库的数据:《 '.$data->active.' 》, 当前统计数据:《 '.$activeNum.'》');
            }
            if ($data->recharge_sum > $peopleSumNum) {
                $sign = 0;
                $this->error('充值总次数错误'.'>>>>> 日期为 【'.$this->keyTime.'服务器: '.$this->serverId.'渠道: '.$this->channelId.'】: 数据库的数据:《 '.$data->recharge_sum.' 》, 当前统计数据:《 '.$peopleSumNum.'》');
            }
            if ($data->recharge_num > $peopleNum) {
                $sign = 0;
                $this->error('充值人数错误'.'>>>>> 日期为 【'.$this->keyTime.'服务器: '.$this->serverId.'渠道: '.$this->channelId.'】: 数据库的数据:《 '.$data->recharge_num.' 》, 当前统计数据:《 '.$peopleNum.'》');
            }
            if ($data->recharge_money > $moneyNum) {
                $sign = 0;
                $this->error('充值金额错误'.'>>>>> 日期为 【'.$this->keyTime.'服务器: '.$this->serverId.'渠道: '.$this->channelId.'】: 数据库的数据:《 '.$data->recharge_money.' 》, 当前统计数据:《 '.$moneyNum.'》');
            }
            if ($data->recharge_money_other > $moneyNumOther) {
                $sign = 0;
                $this->error('other充值金额错误'.'>>>>> 日期为 【'.$this->keyTime.'服务器: '.$this->serverId.'渠道: '.$this->channelId.'】: 数据库的数据:《 '.$data->recharge_money_other.' 》, 当前统计数据:《 '.$moneyNumOther.'》');
            }
            if ($data->first_pay > $firstPayNum) {
                $sign = 0;
                $this->error('首充人数错误'.'>>>>> 日期为 【'.$this->keyTime.'服务器: '.$this->serverId.'渠道: '.$this->channelId.'】: 数据库的数据:《 '.$data->first_pay.' 》, 当前统计数据:《 '.$firstPayNum.'》');
            }
            if ($data->first_pay_sum > $firstPaySumNum) {
                $sign = 0;
                $this->error('首充金额错误'.'>>>>> 日期为 【'.$this->keyTime.'服务器: '.$this->serverId.'渠道: '.$this->channelId.'】: 数据库的数据:《 '.$data->first_pay_sum.' 》, 当前统计数据:《 '.$firstPaySumNum.'》');
            }
            if ($data->first_pay_total > $TotalPaySumNum) {
                $sign = 0;
                $this->error('首付当日总额错误'.'>>>>> 日期为 【'.$this->keyTime.'服务器: '.$this->serverId.'渠道: '.$this->channelId.'】: 数据库的数据:《 '.$data->first_pay_total.' 》, 当前统计数据:《 '.$TotalPaySumNum.'》');
            }


            if ($sign) {

                if (!empty($firstRegisterNum)) {
                    $data->first_register       = $firstRegisterNum;
                }
                if (!empty($newAddNUm)) {
                    $data->new_add              = $newAddNUm;
                }
                if (!empty($effectiveNum)) {
                    $data->effective            = $effectiveNum;
                }
                if (!empty($activeNum)) {
                    $data->active               = $activeNum;
                }
                if (!empty($peopleSumNum)) {
                    $data->recharge_sum         = $peopleSumNum;
                }
                if (!empty($peopleNum)) {
                    $data->recharge_num         = $peopleNum;
                }
                if (!empty($moneyNum)) {
                    $data->recharge_money       = $moneyNum;
                }
                if (!empty($moneyNumOther)) {
                    $data->recharge_money_other       = $moneyNumOther;
                }
                if (!empty($firstPayNum)) {
                    $data->first_pay            = $firstPayNum;
                }
                if (!empty($firstPaySumNum)) {
                    $data->first_pay_sum        = $firstPaySumNum;
                }

                if (!empty($TotalPaySumNum)) {
                    $data->first_pay_total      = $TotalPaySumNum;
                }


                $data->update();
            }


        }

    }

    public function saveINSL(){
        $cId = DB::table('total_serverlists')
            ->where('c_date', $this->dateTime)
            ->Where('channel_id', $this->channelId)
            ->value('id');

        $totalInslKey       = $this->keyTime.self::CONNECTOR.$this->channelId."_total_insl_sum";            // 新增点击选服页面的人数
        $newAddDeviceKey    = $this->keyTime.self::CONNECTOR.$this->channelId."_device";                    // 新增设备

        $totalInslNum       = $this->getData($totalInslKey);
        $newAddDeviceNum    = $this->getData($newAddDeviceKey );

        if (empty($cId)) {
            $inslData = new total_serverlist();
            $inslData->c_date           = $this->dateTime;
            $inslData->channel_id       = $this->channelId;
            $inslData->total_insl_sum   = empty($totalInslNum) ? 0 :$totalInslNum;
            $inslData->new_add_user     = empty($newAddDeviceNum) ? 0 :$newAddDeviceNum;

            $inslData->save();
        }else {
            $data = total_serverlist::find($cId);
            $sign = 1;
            if ($data->total_insl_sum > $totalInslNum) {
                $sign = 0;
                $this->error('首付当日总额错误'.'>>>>> 日期为 【'.$this->keyTime.'渠道: '.$this->channelId.'】: 数据库的数据:《 '.$data->total_insl_sum.' 》, 当前统计数据:《 '.$totalInslNum.'》');
            }
            if ($data->new_add_user > $newAddDeviceNum) {
                $sign = 0;
                $this->error('新增用户错误'.'>>>>> 日期为 【'.$this->keyTime.'渠道: '.$this->channelId.'】: 数据库的数据:《 '.$data->new_add_user.' 》, 当前统计数据:《 '.$newAddDeviceNum.'》');
            }
            if ($sign) {
                if (!empty($totalInslNum)) {
                    $data->total_insl_sum           = $totalInslNum;
                }
                if (!empty($newAddDeviceNum)) {
                    $data->new_add_user             = $newAddDeviceNum;
                }
                $data->update();
            }
        }
    }

    // 获取redis的值
    public function getData($key)
    {
        return Redis::get($key);
    }

}