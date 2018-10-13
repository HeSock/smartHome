<?php

namespace App\Http\Controllers;
date_default_timezone_set('Asia/Shanghai');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;

use App\User;

class ChannelController extends Controller
{

    private $channelId = [];
    public $userr;
    public $showAllDate = false;
    protected $id;
    const STATITEM_QUEUE = 'channel.queue';

    public function login(){
        return view('channel/login');
    }

    function getChannelId($user){
        $isAdmin = $this->validatPowerRes($user, 'admin');

        if ($isAdmin){
            $channelArr = DB::table('qudaos')->pluck('channel_id')->toArray();
        }else{
            $channelArr = DB::table('channels')->where('user_id', $user->id)->pluck('channel_id')->toArray();
        }

        // 拿出所有的渠道ID
        $this->channelId = $channelArr;
    }

    // 数据汇总
    public function channelDetail(Request $request)
    {
        $id = $request->user()->id;
        $this->getChannelId($request->user());

        if ($this->validatPowerRes($request->user(), 'addChannel')){
            $view = 'channel/index';
        }elseif ($this->validatPowerRes($request->user(), 'cps')){
            $view = 'channel/indexcps';
        }else{
            $userTime = strtotime($request->user()->created_at);
            $switchTime = strtotime('2018-10-03');
            if ($userTime > $switchTime) {
                $view = 'channel/indexcpaNew';
            } else {
                $view = 'channel/indexcpa';
            }
        }

        // 如果查不到渠道则返回一个空对象
        if (empty($this->channelId)) {
            return view($view,[
                'data' => (object)array(),
                'power' => $this->getPowerArr($request->user()),
                'payShow' => config('base.payShow'),
                'label' => 'channelDetail',
            ]);
        }

        // 根据服务器查询
        $sId = $request->input('serverId');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $isChannel = $request->input('isChannel');

        if (!isset($isChannel)) {
            $isChannel = 0;
        }

        if (isset($startDate) || isset($endDate)){
            $this->showAllDate = true;
        }

        // 开始时间
        if (!isset($startDate)) { // 如果没有设置，则默认查询最近20天
            $startDate = strtotime(date('Ymd', strtotime("-20 day")));
        }else{
            $startDate = $this->startTime($startDate);
        }

        // 结束时间
        if (!isset($endDate)) {
            $endDate = strtotime(date('Y-m-d 23:59:59', time()));
        }else{
            $endDate = $this->endTime($endDate);
        }

        // 没有任何条件下的查询
        if(!isset($sId)){

            $countDay = round(($endDate - $startDate + 1) / 86400 - 1);
            // 根据日期查询
            $dataDetail =$this->statOldDateChannel($countDay, $isChannel, $startDate, $endDate);

        }else {
            $query = DB::table('channel_details');

            // 查找自己的渠道
            $query = $this->getMyselfChannel($query);
            $query->whereBetween('c_date', [$startDate, $endDate]);


            if(strpos( $sId,',')) {  // 1,2,3,4  查询方式
                $serverArr =  explode(',', $sId);
                $query->whereBetween('server_id', [$serverArr]);
            } elseif(strpos($sId,'-')){ // 1-20 查询方式
                $serverArr = explode('-', $sId);
                $query->whereBetween('server_id', [$serverArr]);
            }elseif(isset($sId)) { // 单服查询方式
                $query->where('server_id', $sId);
            }

            $dataDetail = $query->orderBy('c_date', 'desc')->orderBy('server_id', 'desc')->get();


        }

        return view($view,[
            'data' => (object)$dataDetail,
            'power' => $this->getPowerArr($request->user()),
            'payShow' => config('base.payShow'),
            'label' => 'channelDetail',
        ]);
    }

    // 数据汇总-- 渠道统计
    function statOldDateChannel($countDay, $isChannel, $startData = '', $endDate= '') {
        if ($startData) {
            $timeArr = $this->getQueryTime($startData, $countDay);
        }else{
            $timeArr = $this->getTwentyTime($countDay);
        }

        $info = [];
        $dataDetail = [];
        $totalDB = DB::table('total_serverlists')
            ->select('channel_id','c_date',
                DB::raw('SUM(new_add_user) as new_add_user'),
                DB::raw('SUM(total_insl_sum) as total_insl_sum')
            )
            ->where('c_date', '>=', $startData)
            ->where('c_date', '<', $endDate);

        $channelDB = DB::table('channel_details')
            ->select('channel_id','server_id','c_date',
                DB::raw('SUM(first_register) as first_register'),
                DB::raw('SUM(effective) as effective'),
                DB::raw('SUM(recharge_sum) as recharge_sum'),
                DB::raw('SUM(first_pay) as first_pay'),
                DB::raw('SUM(first_pay_sum) as first_pay_sum'),
                DB::raw('SUM(first_pay_total) as first_pay_total'),
                DB::raw('SUM(new_add) as new_add'),
                DB::raw('SUM(active) as active'),
                DB::raw('SUM(recharge_money) as recharge_money'),
                DB::raw('SUM(recharge_num) as recharge_num')
            )
            ->where('c_date', '>=', $startData)
            ->where('c_date', '<', $endDate)
            ->where('server_id', '<>', 0);

        if ($isChannel ==1) {

            $totalInfo = $totalDB->groupBy('c_date','channel_id')->get()->toArray();

            $channelInfo = $channelDB->groupBy('c_date','channel_id')->get()->toArray();

            $channels = DB::table('qudaos')->pluck('channel_name','channel_id')->toArray();
            $dataDetail = [];

            foreach ($this->channelId as $value) {
                $l = [];
                foreach ($totalInfo as $totalIdTo){
                    $totalIdTo = (array)$totalIdTo;
                    $totalIdTo['channel_id'] == $value && $l[] = $totalIdTo;
                }
                $totalDateTo = array_column($l, NULL, 'c_date');
                $list = [];

                foreach ($channelInfo as $channelIdTo){
                    $channelIdTo = (array)$channelIdTo;
                    $channelIdTo['channel_id'] == $value && $list[] = $channelIdTo;
                }
                $channelDateTo = array_column($list, NULL, 'c_date');

                $channelData = [
                    'channel_id' => $value,
                    'channel_name' => $channels[$value]
                ];

                $this->reArray($dataDetail, $timeArr, $totalDateTo, $channelDateTo,$channelData);
            }
        }else {

            $totalInfo = $totalDB->whereIn('channel_id', $this->channelId)->groupBy('c_date')->get()->toArray();
            $totalDateTo = array_column($totalInfo, NULL, 'c_date');
            $channelInfo = $channelDB->whereIn('channel_id', $this->channelId)->groupBy('c_date')->get()->toArray();
            $channelDateTo = array_column($channelInfo, NULL, 'c_date');

            $channelData = [
                'channel_id' => '全渠道',
                'channel_name' => '全渠道'
            ];
            $dataDetail = [];
            $this->reArray($dataDetail,$timeArr, $totalDateTo, $channelDateTo,$channelData);
        }
        // 根据日期排序
        usort($dataDetail, array($this, "cmp"));
        return $dataDetail;
    }

    function channelServer(Request $request){
        $this->getChannelId($request->user());
        $power = $this->validatPowerRes($request->user(), 'admin');
        if (!$power){
            return redirect('index');
        }

        $sId = $request->input('serverId');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $notdate = $request->input('notdate');

        // 开始时间
        if (!isset($startDate)) { // 如果没有设置，则默认查询最近20天
            $startDate = strtotime(date('Ymd', strtotime("-20 day")));
        }else{
            $startDate = $this->startTime($startDate);
        }

        // 结束时间
        if (!isset($endDate)) {
            $endDate = strtotime(date('Y-m-d 23:59:59', time()));
        }else{
            $endDate = $this->endTime($endDate);
        }

        $query = DB::table('channel_details');
        $query->select('channel_id','server_id','c_date',
            DB::raw('SUM(first_register) as first_register'),
            DB::raw('SUM(effective) as effective'),
            DB::raw('SUM(recharge_sum) as recharge_sum'),
            DB::raw('SUM(first_pay) as first_pay'),
            DB::raw('SUM(first_pay_sum) as first_pay_sum'),
            DB::raw('SUM(first_pay_total) as first_pay_total'),
            DB::raw('SUM(new_add) as new_add'),
            DB::raw('SUM(active) as active'),
            DB::raw('SUM(recharge_money) as recharge_money'),
            DB::raw('SUM(recharge_num) as recharge_num')
        )
            ->whereBetween('c_date', [$startDate, $endDate]);

        if(strpos( $sId,',')) {  // 1,2,3,4  查询方式
            $serverArr =  explode(',', $sId);
            $query->whereBetween('server_id', [$serverArr]);
        } elseif(strpos($sId,'-')){ // 1-20 查询方式
            $serverArr = explode('-', $sId);
            $query->whereBetween('server_id', [$serverArr]);
        }elseif(isset($sId)) { // 单服查询方式
            $query->where('server_id', $sId);
        }

        if ($notdate){
            $query->groupBy('server_id');
        }else{
            $query->groupBy('c_date','server_id');
            $query->orderBy('c_date', 'desc');
        }
        $dataDetail = $query->orderBy('server_id', 'asc')->get();

        if ($notdate){
            foreach ($dataDetail as $item){
                $item->c_date = '全部';
            }
        }

        return view('channel/channelServer',[
            'data' => (object)$dataDetail,
            'power' => $this->getPowerArr($request->user()),
            'payShow' => config('base.payShow'),
            'label' => 'channelServer',
        ]);

    }

    function reArray(&$dataDetail, $timeArr, $totalDateTo, $channelDateTo, $channelInfo){

        foreach ($timeArr as $item){

            if (!$this->showAllDate && !isset($totalDateTo[$item]) && !isset($channelDateTo[$item])){
                continue;
            }

            $info = [];
            $info['c_date']         = $item;
            $info['new_add_user']   = 0;
            $info['total_insl_sum'] = 0;
            $info['first_register'] = 0;
            $info['effective']      = 0;
            $info['recharge_sum']   = 0;
            $info['first_pay']      = 0;
            $info['first_pay_sum']  = 0;
            $info['first_pay_total']= 0;
            $info['new_add']        = 0;
            $info['active']         = 0;
            $info['recharge_money'] = 0;
            $info['recharge_num']   = 0;
            $info['server_id']      = '全服';
            $info['channel_id']     = $channelInfo['channel_id'];
            $info['channel_name']   = $channelInfo['channel_name'];

            if (isset($totalDateTo[$item])){
                $totalDateToArr = is_object($totalDateTo[$item]) ? (array)$totalDateTo[$item] : $totalDateTo[$item];
                $info['new_add_user']   = (int)$totalDateToArr['new_add_user'];
                $info['total_insl_sum'] = (int)$totalDateToArr['total_insl_sum'];
            }
            if (isset($channelDateTo[$item])){
                $channelDateToArr = is_object($channelDateTo[$item]) ? (array)$channelDateTo[$item] : $channelDateTo[$item];
                $serverId = max((int)$channelDateToArr['server_id'],1);
                $info['first_register'] = (int)$channelDateToArr['first_register'];
                $info['effective']      = (int)$channelDateToArr['effective'];
                $info['recharge_sum']   = (int)$channelDateToArr['recharge_sum'];
                $info['first_pay']      = (int)$channelDateToArr['first_pay'];
                $info['first_pay_sum']  = (int)$channelDateToArr['first_pay_sum'];
                $info['first_pay_total']= (int)$channelDateToArr['first_pay_total'];
                $info['new_add']        = (int)$channelDateToArr['new_add'];
                $info['active']         = (int)$channelDateToArr['active'];
                $info['recharge_money'] = (int)$channelDateToArr['recharge_money'];
                $info['recharge_num']   = (int)$channelDateToArr['recharge_num'];
            }
            $dataDetail[] = (object)$info;
        }
        return true;
    }


    // 充值详情
    public function rechargeDetail(Request $request)
    {
        $id = $request->user()->id;
        $this->getChannelId($request->user());
        $power = $this->validatPowerRes($request->user(), 'admin');
        if (!$power){
            return redirect('index');
        }

        // 获取请求的值
        $payData = $request->input('pay');

        $sIds = DB::table('recharge_details')->groupBy('server_id')->pluck('server_id')->toArray();

        // 连接数据库
        $query = DB::table('recharge_details');

        // 按充值时间查找
        if (empty($payData['startDate']) && empty($payData['endDate'])) {
            $startTime  = strtotime(date('Y-m-d 0:0:0', time()));
            $endTime    = strtotime(date('Y-m-d 23:59:59', time()));
        }else {
            $startTime  = $this->startTime($payData['startDate']);
            $endTime    = $this->endTime($payData['endDate']);
        }
        $query->whereBetween('recharge_time', [$startTime, $endTime]);

        // 查找自己的渠道
        $query = $this->getMyselfChannel($query);

        // 按充值金额查找
        if (isset($payData['startNum']) && isset($payData['endNum'])) {
            $query->whereBetween('payment_money', [$payData['startNum'], $payData['endNum']]);
        }

        // 按服务器查找
        if (isset($payData['num']) && $payData['num'] != 'N') {
            $query->where('server_id', $payData['num']);
        }

        // 按账号查找
        if (isset($payData['account'])) {
            $query->where('payment', $payData['account']);
        }

        // 按订单号查找
        if (isset($payData['order'])) {
            $query->where('order_id', $payData['order']);
        }

        // 链式调用
        $payDetail['data']      = $query->get();
        $payDetail['start']     = $startTime;
        $payDetail['end']       = $endTime;
        $payDetail['startNum']  = isset($payData['startNum']) ? $payData['startNum'] : null;
        $payDetail['endNum']    = isset($payData['endNum']) ? $payData['endNum'] : null;
        $payDetail['num']       = isset($payData['num']) ? $payData['num'] : null;
        $payDetail['account']   = isset($payData['account']) ? $payData['account'] : null;
        $payDetail['order']     = isset($payData['order']) ? $payData['order'] : null;
        $payDetail['sIds']      = $sIds;
        $payDetail['checksId']  = $payData['num'];

        return view('channel/rechargeDetail',
            [
                'pay' => (object)$payDetail,
                'power' => $this->getPowerArr($request->user()),
                'payShow' => config('base.payShow'),
                'label' => 'rechargeDetail',
            ]);

    }

    // 充值详情-按天查询
    public function rechargeDetailDate (Request $request, $date)
    {
        $id = $request->user()->id;
        $this->getChannelId($request->user());
        $power = $this->validatPowerRes($request->user(), 'admin');
        if (!$power){
            return redirect('index');
        }

        $sIds = DB::table('recharge_details')->groupBy('server_id')->pluck('server_id');

        // 连接数据库
        $query = DB::table('recharge_details');

        // 获取开始时间和结束时间
        $butweenTime = $this->startToEndTime($date);

        // 按充值时间查找
        $query->whereBetween('recharge_time', $butweenTime);

        // 查找自己的渠道
        $query = $this->getMyselfChannel($query);

        // 链式调用
        $payDetail['data']      = $query->get();
        $payDetail['start']     = $butweenTime[0];
        $payDetail['end']       = $butweenTime[1];
        $payDetail['startNum']  =  null;
        $payDetail['endNum']    =  null;
        $payDetail['num']       =  null;
        $payDetail['account']   =  null;
        $payDetail['order']     =  null;

        return view('channel/rechargeDetail',
            [
                'pay' => (object)$payDetail,
                'power' => $this->getPowerArr($request->user()),
                'payShow' => config('base.payShow'),
                'label' => 'rechargeDetail',
            ]);
    }

    // 各区充值
    public function totalPay(Request $request)
    {
        $id = $request->user()->id;
        $this->getChannelId($request->user());
        $power = $this->validatPowerRes($request->user(), 'admin');
        if (!$power){
            return redirect('index');
        }


        $requestStartDate = $request->input('startDate');
        $requestEndDate = $request->input('endDate');
        $order = $request->input('sort');   //serverid rmb
        $orderField = $order == 'rmb' ? 'recharge_money' : 'server_id';

        if(!isset($requestStartDate)) {
            $startDate = strtotime(date('Y-m-d', time()));
        }else {
            $startDate = $this->startTime($requestStartDate);
        }

        if (!isset($requestEndDate)) {
            $endDate = strtotime(date('Y-m-d 23:59:59', time()));
        }else {
            $endDate = $this->endTime($requestEndDate);
        }

        $betweenTime = [$startDate, $endDate];
        $total['data'] = $this->getTotalData($betweenTime,$orderField);
        $total['startDate'] = $startDate;
        $total['endDate'] = $endDate;
        $total['sort'] = $order;

        return view('channel/totalPay',
            [
                'total' => (object)$total,
                'power' => $this->getPowerArr($request->user()),
                'payShow' => config('base.payShow'),
                'label' => 'totalPay',
            ]);
    }

    // 各区充值情况 - 按天查询
    public function totalPayDate (Request $request, $date) {

        $id = $request->user()->id;
        $this->getChannelId($request->user());
        $power = $this->validatPowerRes($request->user(), 'admin');
        if (!$power){
            return redirect('index');
        }

        $butweenTime = $this->startToEndTime($date);
        $order = $request->input('sort');   //serverid rmb
        $orderField = $order == 'rmb' ? 'recharge_money' : 'server_id';

        $total['data'] = $this->getTotalData($butweenTime, $orderField);
        $total['startDate'] = $butweenTime[0];
        $total['endDate'] = $butweenTime[1];
        $total['sort'] = $order;

        return view('channel/totalPay',
            [
                'total' => (object)$total,
                'power' => $this->getPowerArr($request->user()),
                'payShow' => config('base.payShow'),
                'label' => 'totalPay',
            ]);
    }

    // 获取各区的统计数值
    function getTotalData ($butweenTime, $orderField) {

        $data = [];
        $channelPays = DB::table('channel_details')
            ->select('server_id','c_date',
                DB::raw('SUM(recharge_money) as recharge_money'),
                DB::raw('SUM(recharge_num) as recharge_num'),
                DB::raw('SUM(recharge_sum) as recharge_sum'))
            ->where('server_id', '!=', 0)
            ->whereBetween('c_date', $butweenTime)
            ->groupBy('server_id')
            ->orderBy($orderField, 'asc')
            ->get()
            ->toArray();
        if (!$channelPays){
            return (object)array();
        }

        foreach ($channelPays as $channelPay){
            $channelPay = (array)$channelPay;
            $info = $channelPay;
            $info['arpu'] = $channelPay['recharge_num'] ? ($channelPay['recharge_money'] / $channelPay['recharge_num']) : 0;
            $data[] = (object)$info;
        }

        return $data;
    }

    // 获得属于自己的渠道值
    function getMyselfChannel($query) {
        // 查找自己的渠道
        $query->whereIn('channel_id', $this->channelId);
        return $query;
    }


    // 获取Redis的值
    public function getData($key)
    {
        return Redis::get($key);
    }

    // 获取每天的是时间戳
    function getEveryDayTime($startDate, $endDate)
    {

        $time = strtotime(date('Y-m-d'));
        for ($i = $startDate; $i <= $endDate; $i++){
            $info[] = strtotime(date('Y-m-d',$time-$i*24*60*60));
        }

        return $info;
    }

    // 获取20天内每天的时间戳
    function getTwentyTime($dayNum) {
        $time = strtotime(date('Y-m-d'));
        for ($i = 0; $i <= $dayNum; $i++){
            $info[] = strtotime(date('Y-m-d',$time-$i*24*60*60));
        }
        return $info;
    }

    // 获取查询时间的所有日期
    function getQueryTime($startData, $dayNum) {
        for ($i = 0; $i <= $dayNum; $i++){
            $info[] = strtotime(date('Y-m-d',$startData + $i*24*60*60));
        }
        return $info;
    }

    // 开始时间
    function startTime($start)
   {
       return strtotime($start);
   }

    // 结束时间
    function endTime($end)
   {
       return strtotime($end) + 86399;
   }

    // 开始--结束时间
    function startToEndTime($date) {
        if ($date == 0 ) {       // 今天
            $startTime  = strtotime(date('Y-m-d 0:0:0', time()));
            $endTime    = strtotime(date('Y-m-d 23:59:59', time()));
        }elseif ($date == 1) {  // 昨天
            $startTime  = strtotime(date('Y-m-d 0:0:0', strtotime('-1 day')));
            $endTime    = strtotime(date('Y-m-d 23:59:59', strtotime('-1 day')));
        }elseif ($date == 7) {  // 最近七天
            $startTime  = strtotime(date('Ymd', strtotime("-7 day")));
            $endTime    = strtotime(date('Y-m-d 23:59:59', time()));
        }elseif ($date == 30) { // 最近三十天
            $startTime  = strtotime(date('Ymd', strtotime("-30 day")));
            $endTime    = strtotime(date('Y-m-d 23:59:59', time()));
        }elseif ($date == -1) { // 上个月
            $startTime  = strtotime(date('Y-m-01 00:00:00',strtotime('-1 month')));
            $endTime    = strtotime(date("Y-m-d 23:59:59", strtotime(-date('d').'day')));
        }elseif ($date == '100') {// 当月
            $startTime  = strtotime(date('Y-m-01 00:00:00' ,time()));
            $endTime    = strtotime(date('Y-m-d 23:59:59', time()));
        }
        return [$startTime, $endTime];
    }

    function cmp($a, $b) {
        return strcmp($b->c_date, $a->c_date);
    }

    function pushItemToQueue($statItem){
        $statItem = json_encode($statItem);

        Redis::rPush(self::STATITEM_QUEUE, $statItem);
    }

    function addChannel(Request $request){

        $power = $this->validatPowerRes($request->user(), 'addChannel');
        if (!$power){
            return redirect('index');
        }

        $users = DB::table('users')->select('id','name')->orderBy('id', 'desc')->get();
        if (!$request->isMethod('post')){
            return view('channel/addChannel',[
                'users'=>$users,
                'power' => $this->getPowerArr($request->user()),
                'payShow' => config('base.payShow'),
                'label' => 'addChannel',
            ]);
        }

        $inputData = $request->input();
        $channle = DB::table('qudaos')->where('channel_id', $inputData['channelId'])->first();
        $validArr = [
            'channelId' => 'required',
            'channelName' => 'required',
        ];
        ($channle || $inputData['userId']) && $validArr['userId'] = 'required|userExist';
        $validate = Validator::make($request->all(), $validArr,[
            'userId.required' => '添加失败，请填写关联用户ID！',
            'userId.user_exist' => '添加失败，用户不存在！',
            'channelId.required' => '添加失败，请填写关联用户ID！',
            'channelName.required' => '添加失败，请填写渠道名称',
        ]);
        if ($validate->fails()) {
            return redirect('addChannel')
                ->withErrors($validate)
                ->withInput();
        }

        $data = [
            'channel_id' => $inputData['channelId'],
            'channel_name' => $channle ? $channle->channel_name : $inputData['channelName'],
            'user_id' => $inputData['userId'],
        ];
        if ($inputData['userId']){

            DB::table('channels')->insert($data);
        }

        if (!$channle){
            $channleData = $data;
            unset($channleData['user_id']);
            DB::table('qudaos')->insert($channleData);
        }

        return redirect('addChannel');
    }

    function channelPay(Request $request){
        $user = $request->user();
        $power = $this->validatPowerRes($request->user(), 'cpa');
        if ($power){
            return redirect('index');
        }
        $power = $this->validatPowerRes($user, 'addChannel');
        $where = "channel_id<>''";
        if (!$power){
            $str = '';
            $cIds = DB::table('channels')->where('user_id', $user->id)->pluck('channel_id')->toArray();
            foreach ($cIds as &$cId){
                $cId = "'" . $cId . "'";
            }
            $str = implode(',', $cIds);
            $where = "channel_id IN ($str)";
            if (count($cIds) == 0){
                return view('channel/channelPay',
                    [
                        'total' => (object)[],
                        'power' => $this->getPowerArr($request->user()),
                        'payShow' => config('base.payShow'),
                        'label' => 'channelPay',
                    ]);
            }
        }

        $channels = DB::table('qudaos')->whereRaw($where)->pluck('channel_name','channel_id')->toArray();
        $channelPays = DB::table('channel_details')
            ->select('*',DB::raw('SUM(recharge_money) as total_pay'),DB::raw('SUM(recharge_sum) as total_num'),DB::raw('SUM(recharge_num) as people_num'))
            ->groupBy('channel_id')
            ->orderBy('total_pay', 'desc')
            ->havingRaw($where)
            ->get();

        $l=[];
        foreach ($channelPays as $channelPay){
            $l[] = [
                'channelId' => $channelPay->channel_id,
                'channelName' => $channels[$channelPay->channel_id],
                'totalPay' => $channelPay->total_pay,
                'totalNum' => $channelPay->total_num,
                'peopleNum' => $channelPay->people_num,
                //'arpu' => $channelPay->people_num ? ($channelPay->total_pay / $channelPay->people_num) : 0,
            ];
        }

        return view('channel/channelPay',
            [
                'total' => (object)$l,
                'power' => $this->getPowerArr($request->user()),
                'payShow' => config('base.payShow'),
                'label' => 'channelPay',
            ]);
    }

    /*function validatPower($user, $power){
        $uId = $user->id;
        $powerArr = explode(',',$user->no_power);
        if (in_array($power, $powerArr)){
            return false;
        }
        return redirect('index');
    }*/
    function validatPowerRes($user, $power){
        $uId = $user->id;
        //$powerArr = explode(',',$user->no_power);
        $powerArr = $this->getPowerArr($user);
        if (in_array($power, $powerArr)){
            return true;
        }
        return false;
    }
    function getPowerArr($user){
        $powerLabel = explode(',',$user->no_power);
        if (in_array('admin',$powerLabel) || in_array('addChannel',$powerLabel)){
            return $power = config('base.role.admin');
        }else if (in_array('cps',$powerLabel)){
            return $power = config('base.role.cps');
        }else if (in_array('cpa',$powerLabel)){
            return $power = config('base.role.cpa');
        }
        return [];
    }

    function channelList(Request $request){
        $power = $this->validatPowerRes($request->user(), 'addChannel');
        if (!$power){
            return redirect('index');
        }

        $channels = DB::table('qudaos')->get();

        $channelSum = DB::table('channel_details')
            ->select('channel_id', DB::raw('SUM(first_register) as first_register_sum'), DB::raw('SUM(new_add) as new_add_sum'), DB::raw('SUM(effective) as effective_sum'))
            ->groupBy('channel_id')
            ->get()
            ->toArray();
        $channelSumArr = array_column($channelSum, NULL, 'channel_id');

        $todayTime = strtotime(date('Y-m-d'));
        $activeSum = DB::table('channel_details')
            ->select('channel_id', 'c_date',DB::raw('SUM(active) as active_sum'),DB::raw('SUM(new_add) as new_add_sum'))
            ->where('c_date', $todayTime)
            ->groupBy('channel_id')
            ->get()
            ->toArray();
        $activeSumArr = array_column($activeSum, NULL, 'channel_id');

        $total = DB::table('total_serverlists')
            ->select('channel_id', DB::raw('SUM(new_add_user) as new_add_user'))
            ->groupBy('channel_id')
            ->get()
            ->toArray();
        $totalArr = array_column($total, NULL, 'channel_id');

        $channelPays = DB::table('channel_details')
            ->select('*',DB::raw('SUM(recharge_money) as total_pay'),DB::raw('SUM(recharge_sum) as total_num'),DB::raw('SUM(recharge_num) as people_num'))
            ->groupBy('channel_id')
            ->get()
            ->toArray();
        $channelPayArr = array_column($channelPays, NULL, 'channel_id');

        $channelList = [];
//        foreach ($channels as $channel){
//            $new_add_user = empty($totalArr[$channel->channel_id]) ? 0 : (int)((array)$totalArr[$channel->channel_id])['new_add_user'];
//            $first_register_sum = empty($channelSumArr[$channel->channel_id]) ? 0 : (int)((array)$channelSumArr[$channel->channel_id])['first_register_sum'];
//            $new_add_sum = empty($channelSumArr[$channel->channel_id]) ? 0 : (int)((array)$channelSumArr[$channel->channel_id])['new_add_sum'];
//            $effective_sum = empty($channelSumArr[$channel->channel_id]) ? 0 : (int)((array)$channelSumArr[$channel->channel_id])['effective_sum'];
//            $active_sum = empty($activeSumArr[$channel->channel_id]) ? 0 : (int)((array)$activeSumArr[$channel->channel_id])['active_sum'];
//            $new_add_sum_today = empty($activeSumArr[$channel->channel_id]) ? 0 : (int)((array)$activeSumArr[$channel->channel_id])['new_add_sum'];
//            $total_pay = empty($channelPayArr[$channel->channel_id]) ? 0 : (int)((array)$channelPayArr[$channel->channel_id])['total_pay'];
//            $total_num = empty($channelPayArr[$channel->channel_id]) ? 0 : (int)((array)$channelPayArr[$channel->channel_id])['total_num'];
//            $people_num = empty($channelPayArr[$channel->channel_id]) ? 0 : (int)((array)$channelPayArr[$channel->channel_id])['people_num'];
//            $channelList[] = [
//                'id' => $channel->id,
//                'channel_id' => $channel->channel_id,
//                'channel_name' => $channel->channel_name,
//                'new_add_user' => $new_add_user,
//                'first_register_sum' => $first_register_sum,
//                'new_add_sum' => $new_add_sum,
//                'effective_sum' => $effective_sum,
//                'active_sum_today' => $active_sum + $new_add_sum_today,
//                'channel_regnum' => $channel->channel_regnum,
//                'totalPay' => $total_pay,
//                'totalNum' => $total_num,
//                'peopleNum' => $people_num,
//            ];
//        }

        return view('channel/channelList',
            [
                'channels' => (object)$channelList,
                'power' => $this->getPowerArr($request->user()),
                'payShow' => config('base.payShow'),
                'label' => 'channelList',
            ]);
    }

    function changeChannel(Request $request){

        $power = $this->validatPowerRes($request->user(), 'changeChannel');
        if (!$power){
            return redirect('index');
        }
        $id = $request->route( 'id' );
        $channel = DB::table('qudaos')->where('id', $id)->first();
        if (!$channel){
            return redirect('channelList');
        }

        if (!$request->isMethod('post')){
            return view('channel/changeChannel',[
                'channel' => $channel,
                'power' => $this->getPowerArr($request->user()),
                'payShow' => config('base.payShow'),
                'label' => 'channelList',
            ]);
        }

        $this->validate($request, [
            'channelName' => 'required',
        ],[
            'channelName.required' => '添加失败，请填写渠道名！',
        ]);

        $inputData = $request->input();
        $data = [
            'channel_name' => $inputData['channelName']
        ];
        DB::table('qudaos')->where('id',$id)->update($data);

        return redirect('channelList');
    }

    function channelData(Request $request){
        $power = $this->validatPowerRes($request->user(), 'changeChannel');
        if (!$power){
            return redirect('index');
        }
        $id = $request->route( 'id' );
        $channel = DB::table('qudaos')->where('id', $id)->first();
        if (!$channel){
            return redirect('channelList');
        }
        // 根据服务器查询
        $sId = $request->input('serverId');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        // 开始时间
        if (!isset($startDate)) { // 如果没有设置，则默认查询最近20天
            $startDate = strtotime(date('Ymd', strtotime("-20 day")));
        }else{
            $startDate = $this->startTime($startDate);
        }

        // 结束时间
        if (!isset($endDate)) {
            $endDate = strtotime(date('Y-m-d 23:59:59', time()));
        }else{
            $endDate = $this->endTime($endDate);
        }

        $countDay = round(($endDate - $startDate + 1) / 86400 - 1);

        // 根据日期查询
        $dataDetail = $this->getOneChannel($channel->channel_id, $startDate, $endDate, $sId);

        return view('channel/channelData',[
            'data' => $dataDetail,
            'channelId' => $channel->channel_id,
            'channelName' => $channel->channel_name,
            'power' => $this->getPowerArr($request->user()),
            'payShow' => config('base.payShow'),
            'label' => 'channelList',
        ]);
    }

    function getOneChannel($cId, $startDate, $endDate, $sId){

        $channelSum = DB::table('channel_details')
            ->select('channel_id','c_date',
                DB::raw('SUM(first_register) as first_register'),
                DB::raw('SUM(new_add) as new_add'),
                DB::raw('SUM(effective) as effective'),
                DB::raw('SUM(recharge_sum) as recharge_sum'),
                DB::raw('SUM(first_pay) as first_pay'),
                DB::raw('SUM(first_pay_sum) as first_pay_sum'),
                DB::raw('SUM(first_pay_total) as first_pay_total'),
                DB::raw('SUM(active) as active'),
                DB::raw('SUM(recharge_money) as recharge_money'),
                DB::raw('SUM(recharge_num) as recharge_num')
            )
            ->where('channel_id',$cId)
            ->groupBy('c_date')
            ->having('c_date', '>=', $startDate)
            ->having('c_date', '<=', $endDate)
            ->get()
            ->toArray();
        $channelSumArr = array_column($channelSum, NULL, 'c_date');

        $total = DB::table('total_serverlists')
            ->select('channel_id','c_date', DB::raw('SUM(total_insl_sum) as total_insl_sum'), DB::raw('SUM(new_add_user) as new_add_user'))
            ->where('channel_id',$cId)
            ->groupBy('c_date')
            ->having('c_date', '>=', $startDate)
            ->having('c_date', '<=', $endDate)
            ->get()
            ->toArray();
        $totalArr = array_column($total, NULL, 'c_date');

        $list = [];
        $newEndDate = $endDate + 1 - 3600*24;
        for($i=$newEndDate; $i>=$startDate; $i-=3600*24){

            $data = [];
            if (isset($channelSumArr[$i])){
                $data = (array)$channelSumArr[$i];
            }
            if (isset($totalArr[$i])){
                $data['total_insl_sum'] = isset($totalArr[$i]->total_insl_sum) ? (int)$totalArr[$i]->total_insl_sum : 0;
                $data['new_add_user'] = isset($totalArr[$i]->new_add_user) ? (int)$totalArr[$i]->new_add_user : 0;
            }
            $data['c_date'] = $i;
            (!isset($data['server_id']) && !isset($data['new_add_user'])) && $data['new_add_user'] = isset($data['new_add_user']) ? (int)$data['new_add_user'] : 0;
            (!isset($data['server_id']) && !isset($data['total_insl_sum'])) && $data['total_insl_sum'] = isset($data['total_insl_sum']) ? (int)$data['total_insl_sum'] : 0;
            $list[] = $data;
        }

        return $list;
    }

    function addUser(Request $request){
        $power = $this->validatPowerRes($request->user(), 'addChannel');
        if (!$power){
            return redirect('index');
        }

        if (!$request->isMethod('post')){
            return view('channel/addUser',[
                'power' => $this->getPowerArr($request->user()),
                'payShow' => config('base.payShow'),
                'label' => 'addUser',
            ]);
        }

        Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'power' => 'required',
        ])->validate();
        $data = $request->all();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'no_power' => $data['power'],
        ]);
        event(new Registered($user));

        return redirect('addUser');
    }

    function userList(Request $request){
        $power = $this->validatPowerRes($request->user(), 'addChannel');
        if (!$power){
            return redirect('index');
        }

        $users = DB::table('users')->get();

        $userList = [];
        foreach ($users as $user){
            $power = 'cpa';
            $powerLabel = 'cpa';
            if ($this->validatPowerRes($user, 'addChannel')){
                $power = '管理员';
                $powerLabel = 'admin';
            }elseif ($this->validatPowerRes($user, 'cps')){
                $power = 'cps';
                $powerLabel = 'cps';
            }
            $userList[] = [
                'id' => $user->id,
                'name' => $user->name,
                'power' => $power,
                'powerLabel' => $powerLabel,
                'email' => $user->email,
            ];
        }

        return view('channel/userList',
            [
                'users' => $userList,
                'power' => $this->getPowerArr($request->user()),
                'payShow' => config('base.payShow'),
                'label' => 'userList',
            ]);
    }

    function changeUser(Request $request){
        $power = $this->validatPowerRes($request->user(), 'addChannel');
        if (!$power){
            return redirect('index');
        }

        $id = $request->route( 'id' );
        $user = DB::table('users')->where('id', $id)->first();
        if (!$user){
            return redirect('userList');
        }
        $powerLabel = 'cpa';
        if ($this->validatPowerRes($user, 'addChannel')){
            $powerLabel = 'admin';
        }elseif ($this->validatPowerRes($user, 'cps')){
            $powerLabel = 'cps';
        }
        $user->power_label = $powerLabel;
        if (!$request->isMethod('post')){
            return view('channel/changeUser',[
                'user' => $user,
                'power' => $this->getPowerArr($request->user()),
                'payShow' => config('base.payShow'),
                'label' => 'userList',
            ]);
        }

        $this->validate($request, [
            'power' => 'required',
        ],[
            'channelName.power' => '修改失败，请选择权限！',
        ]);
        $inputData = $request->input();
        $powerLabel = $inputData['power'];


        $data = [
            'no_power' => $powerLabel
        ];
        DB::table('users')->where('id',$id)->update($data);

        return redirect('userList');
    }

    function moreGames(Request $request){

        $power = $this->validatPowerRes($request->user(), 'addChannel');
        if (!$power){
            return redirect('index');
        }

        $moregames = DB::table('moregames')->get();

        return view('channel/moreGame',[
            'moregames' => $moregames,
            'games' => config('moregame.games'),
            'power' => $this->getPowerArr($request->user()),
            'payShow' => config('base.payShow'),
            'label' => 'moreGames',
        ]);
    }
}