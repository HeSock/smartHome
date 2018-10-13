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
use App\Model\share;
use Illuminate\Support\Facades\Redis;


class statData extends Command
{

    const CHANNEL_QUEUE                 = 'channel.queue';
    const EVENT_INSL_TYPE               = 'inSl';
    const EVENT_BJPOINT_TYPE               = 'bjp';
    const EVENT_EFFECTICE_TYPE          = 'effective';

    const CONNECTOR  = '_';

    private $dateTime;
    private $serverId;
    private $channelId;

    public $overdueTime = 604800; // 7天

    /**
     * 控制台命令名称
     *
     * @var string
     */
    protected $signature = 'statData {param1}';

    /**
     * 控制台命令描述
     *
     * @var string
     */
    protected $description = 'statData';

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
            $this->getStatData();
        }else {
            $this->error('请输入： php artisan statData start');
        }
    }

   function getStatData(){
       $statNum = 0;
       $channelArr = array();

       $this->dateTime = date('Y-m-d');
       while ($statItem = Redis::lPop(self::CHANNEL_QUEUE)) {
           if ($statNum > 10000) { // 一次性取1000条来执行
               return $statNum;
           }
           $statItem = json_decode( $statItem, true);

           $item = explode('/', $statItem['action']);
           $type = end($item);

           if ($type != 'install') {
               $this->serverId   = isset($statItem['p']['context']['serverid']) ? $statItem['p']['context']['serverid'] : 1;    // 服务器ID
//               $this->serverId   = $statItem['p']['context']['serverid'];    // 服务器ID
           }

           $this->channelId  = isset($statItem['p']['context']['channelid']) ? $statItem['p']['context']['channelid'] : "0";   // 渠道ID
//           $this->channelId  = $statItem['p']['context']['channelid'];   // 渠道ID

           if (!isset($channelArr[$this->channelId])) {
               $channelArr[$this->channelId] = [];
           }
           if (!in_array($this->serverId, $channelArr[$this->channelId])) {
               $channelArr[$this->channelId][] = $this->serverId;               // 存放所有渠道
           }


           // 缓存各种数据
           $this->setTotalChannel($statItem, $type);

           $statNum++;
       }
       // 存放今天又新增、活跃、充值的所有服
       $this->saveData($channelArr);

   }

    function saveData($channelArr) {
        if ($channelArr) {
            foreach ($channelArr as $channelId => $item){
                $channelKey = $this->dateTime.self::CONNECTOR.$channelId;
                foreach ($item as $v => $value){
                    // 存放今天又新增、活跃、充值的所有服
                    $info['channel_id'] = $channelId;
                    $info['server_id']  = intval($value);
                    $this->setSAdd($channelKey, $info);
                }
            }
        }
    }


    function setTotalChannel($statItem,$type) {
        $who = isset($statItem['p']['who'])? $statItem['p']['who']: 0;
        $deviceid = $statItem['p']['context']['deviceid'];
        switch ($type) {
            case 'install':
                $checkDeviceKey     = $deviceid;
                $deviceKey           = $this->dateTime.self::CONNECTOR.$this->channelId."_device";  // 新增用户
                $totalDeviceKey     = 'total_device';

                if ($this->newGetCache($checkDeviceKey) == 0) {
                    $this->incr($deviceKey);             // 根据渠道统计每天每服的新增设备
                    $this->newIncr($totalDeviceKey);         // 统计所有新增设备
                }
                break;

            // 注册的时候 - 新增人数
            case 'register':
                $checkDeviceKey             = $deviceid.'register'; //
                $firstCreatRoleKey          = $this->dateTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId.'_fisrt_register';
                $totalFirstCreatRoleKey     = 'total_first_register';
                $serverFirstCreatRoleKey    = $this->serverId.'server_first_register';
                if ($this->newGetCache($checkDeviceKey) == 0) {
                    $this->incr($firstCreatRoleKey);                   // 根据渠道统计每天每服的首创角色
                    $this->newIncr($totalFirstCreatRoleKey);           // 统计所有首创角色
                    $this->newIncr($serverFirstCreatRoleKey);          // 统计没服首创角色
                }

                // 存取某个用的所有服的账号
                $hsetKey    = $this->channelId.self::CONNECTOR;
                $field      = $deviceid;
                $reCache    = $this->newHget($hsetKey, $field);
                $value      = empty($reCache) ? [] : json_decode($reCache,true);
                $value      = is_array($value) ? $value : [];
                $value[$this->serverId] = $who;
                $this->newHset($hsetKey, $field, $value);

                $newAddKey = $this->dateTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId."_newAdd";
                $this->incr($newAddKey); // 根据渠道统计每服当天的新增账号 (已经算上首创的人数了)

                $userKey = $this->dateTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId.self::CONNECTOR.$who."_user1";
                $this->getCache($userKey); // 当天的登录的

                break;
            // 登录的时候 - 在线人数
            case 'loggedin':
                $activeKey   = $this->dateTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId."_active";
                $userKey  = $this->dateTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId.self::CONNECTOR.$who."_user1";

                if ($this->getCache($userKey) == 0) {
                    $this->incr($activeKey); // 活跃用户（已扣除当天注册的）
                }
                break;
            // 交易的时候
            case 'payment':
                $moneyValue = $statItem['p']['context']['currencyamount']; // 充值的金额
                $payType = $statItem['p']['context']['paymenttype']; // 充值的金额
                $selfTypes = ['xcx', 'wx', 'code'];
                if (in_array($payType, $selfTypes)) { // 当是官网充值的时候，只记录下金额，return
                    // 充值总额
                    $moneyNumOtherKey = $this->dateTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId."_moneyNumOther";
                    $this->incrBy($moneyNumOtherKey, $moneyValue);

                    $orderInfo = [];
                    $orderInfo['order_Id']              =  $statItem['p']['context']['transactionid'];
                    $orderInfo['channel_Id']            =  $this->channelId;
                    $orderInfo['channel_name']          =  $this->channelId;
                    $orderInfo['payment']               =  $who;
                    $orderInfo['payment_money']         =  $statItem['p']['context']['currencyamount'];
                    $orderInfo['prop_num']              =  $statItem['p']['context']['virtualcoinamount'];
                    $orderInfo['recharge_time']         =  time();
                    $orderInfo['server_id']             =  $this->serverId;
                    $orderInfo['payment_type']          =  $payType;

                    $orderKey = $this->dateTime.self::CONNECTOR.$this->channelId."_order";
                    $this->lPush($orderKey, $orderInfo);
                    return;
                }

                $checkDeviceKey         = $deviceid.'payment';
                $fistPayUserKey         = $this->dateTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId."_pay";
                $firstServerPayUserKey  = $this->serverId.self::CONNECTOR.$this->channelId."_server_pay";
                $fisrtTotalPayUserKey   = 'total_pay';

                $firstPaySumKey         = $this->dateTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId."_pay_sum"; // 首创总和
                $firstServerPaySumKey   = $this->serverId.self::CONNECTOR.$this->channelId."_server_pay_sum";
                $fisrtTotalPaySumKey    = 'total_pay_sum';

                $firstPayTotalUserKey   = $this->dateTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId.self::CONNECTOR.$who."_first_pay";

                if ($this->newGetCache($checkDeviceKey) == 0) {
                    $this->incr($fistPayUserKey);                          // 根据渠道统计每天每服的首冲用户
                    $this->incrBy($firstPaySumKey, $moneyValue);           // 根据渠道统计每天每服的充值金额
                    $this->newIncr($fisrtTotalPayUserKey);                 // 统计所有首冲用户
                    $this->newIncrBy($firstServerPaySumKey, $moneyValue);  // 统计所有充值金额
                    $this->newIncr($firstServerPayUserKey);                // 统计没服首冲用户
                    $this->newIncrBy($fisrtTotalPaySumKey, $moneyValue);   // 统计没服充值总额

                    $this->isSetCache($firstPayTotalUserKey); // 保存首次充值用户 （有效时长 当天）

                }

                // 第一次付费总额
                $firstPaytotalSumKey = $this->dateTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId."_first_pay_total";
                if($this->isGetCache($firstPayTotalUserKey)){
                    $this->incrBy($firstPaytotalSumKey, $moneyValue);           // 根据渠道统计每天每服首付当日的总额
                }

                // 充值总额
                $moneyNumKey = $this->dateTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId."_moneyNum";
                $this->incrBy($moneyNumKey, $moneyValue);                    // 自增金额 （已包含首充金额）

                // 充值人数
                $userKey = $this->dateTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId.self::CONNECTOR.$who."_user2";
                $peopleNumKey = $this->dateTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId."_peopleNum";
                if ($this->getCache($userKey) == 0) {
                    $this->incr($peopleNumKey);
                }

                // 充值总次数
                $peopleSumKey = $this->dateTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId."_peopleSum";
                $this->incr($peopleSumKey);

                $orderInfo = [];
                $orderInfo['order_Id']              =  $statItem['p']['context']['transactionid'];
                $orderInfo['channel_Id']            =  $this->channelId;
                $orderInfo['channel_name']          =  $this->channelId;
                $orderInfo['payment']               =  $who;
                $orderInfo['payment_money']         =  $statItem['p']['context']['currencyamount'];
                $orderInfo['prop_num']              =  $statItem['p']['context']['virtualcoinamount'];
                $orderInfo['recharge_time']         =  time();
                $orderInfo['server_id']             =  $this->serverId;
                $orderInfo['payment_type']          =  $payType;

                $orderKey = $this->dateTime.self::CONNECTOR.$this->channelId."_order";
                $this->lPush($orderKey, $orderInfo);
                break;
            // 点击、分享、注册
            case 'event':
                $what = $statItem['p']['what'];
                switch ($what) {
                    case self::EVENT_INSL_TYPE: // 统计每个渠道到达选服界面的人数
                        $checkDeviceKey = $deviceid.self::EVENT_INSL_TYPE;
                        $clothesCacheKey = $this->dateTime.self::CONNECTOR.$this->channelId.'_total_insl_sum';       // 统计每个渠道到达选服界面的人数
                        if ($this->newGetCache($checkDeviceKey) == 0) {
                            $this->incr($clothesCacheKey);
                        }
                        break;
                    case self::EVENT_EFFECTICE_TYPE: // 统计有效用户
                        $checkDeviceKey = $this->dateTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId.self::CONNECTOR.$who.self::CONNECTOR.self::EVENT_EFFECTICE_TYPE;
                        $serverUserKey =  $this->dateTime.$this->serverId.self::EVENT_EFFECTICE_TYPE;
                        $channelServerUserKey = $this->dateTime.self::CONNECTOR.$this->serverId.self::CONNECTOR.$this->channelId.self::CONNECTOR.self::EVENT_EFFECTICE_TYPE;
                        if ($this->newGetCache($checkDeviceKey) == 0) {
                            $this->incr($serverUserKey);            // 每服每天的有效用户
                            $this->incr($channelServerUserKey);     // 根据渠道统计每服每天的有效用户
                        }
                        break;
                    default:
                        $whatArr = explode('_', $what);

                        if ($whatArr[0] == 'pic') {
                            $this->countShare($whatArr, $who, $deviceid);
                        }elseif($whatArr[0] == 'bjpoint'){
                            $this->countBjpoint($whatArr, $who);
                        }elseif($whatArr[0] == 'moregame'){
                            $this->countMoregame($whatArr, $who, $deviceid);
                        }
                        break;
                }
                break;
            // 默认情况下
            default:
                break;
        }
    }

    // 统计分享的数据
    function countShare($whatArr, $who, $deviceid) {
        switch ($whatArr[1]) {
            case 'share': // 总分享次数
                $shareKey = 'share'.$whatArr[2];
                $this->newIncr($shareKey);

                // 去重统计
                $removalShareKey = 'share'.$deviceid;
                $shareSum   = 'share_total';
                if ($this->newGetCache($removalShareKey) == 0) {
                    $this->newIncr($shareSum);
                }

                // 统计分享人数
                $shareTotalKey = 'share'.$who.$whatArr[2];
                $shareTotal = 'sharePeopleNum'.$whatArr[2];

                $shareIdKey = 'shareId';
                if ($this->newGetCache($shareTotalKey) == 0 ) {
                    $this->newIncr($shareTotal);
                    $num = $this->getData($shareTotal);
                    if ($num < 10) {
                        $this->newSetSAdd($shareIdKey, $whatArr[2]);
                    }
                }
                break;
            case 'click':   // 点击次数
                $clickKey = 'click'.$whatArr[2];
                $this->newIncr($clickKey);

                // 去重统计
                $removalClickKey = 'click'.$deviceid;
                $clickSum   = 'click_total';
                if ($this->newGetCache($removalClickKey) == 0) {
                    $this->newIncr($clickSum);
                }

                // 统计点击人数
                $clickTotalKey = 'click'.$who.$whatArr[2];
                $clickTotal = 'clickPeopleNum'.$whatArr[2];
                if ($this->newGetCache($clickTotalKey) == 0 ) {
                    $this->newIncr($clickTotal);
                }

                break;
            case 'register':    // 注册人数
                $registerKey = 'register'.$whatArr[2];
                $this->newIncr($registerKey);

                $registerChannelKey = 'registerChann'.self::CONNECTOR.$this->channelId;
                $this->newIncr($registerChannelKey);
                break;
            default:
                break;
        }
    }

    function countBjpoint($whatArr, $who){
        $checkDeviceKey = self::EVENT_BJPOINT_TYPE.$who.self::CONNECTOR.$whatArr[1];
        $clothesCacheKey = self::EVENT_BJPOINT_TYPE.self::CONNECTOR.$this->channelId.self::CONNECTOR.$whatArr[1];       // 统计通关人数
        if ($this->newGetCache($checkDeviceKey) == 0) {
            $this->incr($clothesCacheKey);
        }
    }
    function countMoregame($whatArr, $who, $deviceid){
        $clickKey = 'moregame'.$whatArr[1];
        $this->newIncr($clickKey);

        // 去重统计
        $removalClickKey = 'moregame'.$deviceid;
        $clickSum   = 'moregame_total';
        if ($this->newGetCache($removalClickKey) == 0) {
            $this->newIncr($clickSum);
        }

        // 统计点击人数
        $clickTotalKey = 'moregame'.$who.$whatArr[1];
        $clickTotal = 'moregamePeopleNum'.$whatArr[1];
        $moreIdKey = 'moregameId';
        if ($this->newGetCache($clickTotalKey) == 0 ) {
            $this->newIncr($clickTotal);
            $num = $this->getData($clickTotal);
            if ($num < 10) {
                $this->newSetSAdd($moreIdKey, $whatArr[1]);
            }
        }
    }

    /************************** 永久保存记录 ***************************/
    // 自增 + 1
    function newIncr ($cacheKey){
        $cache = Redis::incr($cacheKey);
    }

    // 如果能拿到数据返回1，表示已经存储过了，没拿到数据返回 0： 表示还没存储
    function newGetCache($cacheKey) {
        $cache = Redis::getSet($cacheKey, 1);
        if (!$cache) {
            return 0;
        }
        return 1;
    }

    // 存某个用户的在所有服的账号
    function newHset($cacheKey, $field, $value ) {
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
            return $value;
        }
        return $cache;
    }
    // 存储分享ID
    function newSetSAdd ($cacheKey, $id) {
        return Redis::sAdd($cacheKey, $id);
    }

    /***************** 特殊用法 ******************************/


    // 获取redis的值
    public function getData($key)
    {
        return Redis::get($key);
    }

    public function isGetCache($cacheKey) {
        $cache = Redis::get($cacheKey);
        if (!$cache) {
            return 0;
        }
        return 1;
    }

    public function isSetCache($cacheKey) {
        $time = strtotime(date('Y-m-d 23:59:59')) - time();
        $value = 100;
        Redis::expire($cacheKey, $time);
        Redis::set($cacheKey, $value);
    }

    /************************** 7天保存记录 ***************************/
    // 自增+1
    function incr($cacheKey) {
        $this->setExpire($cacheKey);
        $cache = Redis::incr($cacheKey);
    }

    // 有序集合，防止重复存储
    function setSAdd($cacheKey, $value = '1'){
        $this->setExpire($cacheKey, 0);
        $value = json_encode($value);
        return  Redis::sAdd($cacheKey, $value);
    }

    // 指定量自增
    function newIncrBy($cacheKey, $value) {
        $value = json_encode($value);
        Redis::incrBy($cacheKey, $value);
    }


    // 如果能拿到数据返回1，表示已经存储过了，没拿到数据返回 0： 表示还没存储
    function getCache($cacheKey){
        $cache = Redis::get($cacheKey);
        if (!$cache) {
            $this->setExpire($cacheKey,1);
            Redis::set($cacheKey, 1);
            return 0;
        }
        return 1;
    }

    // 指定量自增
    function incrBy($cacheKey, $value) {
        $this->setExpire($cacheKey, 1);
        $value = json_encode($value);
        Redis::incrBy($cacheKey, $value);
    }

    // 指定量自增
    function lPush($cacheKey, $value) {
        $this->setExpire($cacheKey, 1);
        $value = json_encode($value);
        Redis::lPush($cacheKey, $value);
    }

    // 设置25个小时过期
    function setExpire ($key, $time = 0) {
        if ($time) {
            $time = 604800; // 7天
        }else {
            $time = strtotime(date('Ymd 23:59:59')) - strtotime("now");
        }
        Redis::expire($key, $time);
    }

}