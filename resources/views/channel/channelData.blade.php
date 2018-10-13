<?php date_default_timezone_set('Asia/Shanghai'); ?> <!--设置时区 -->
@extends('channel.templateStyle')

@section('sidebar')
    <ul>
        <li class="data_collection">
            <a class="fontcolor" href="{{ url('index') }}">
                <span></span>
                数据汇总
            </a>
        </li>
        <li class="pay_details">
            <a class="fontcolor" href="{{ url('rechargedetail') }}">
                充值详情
            </a>

        </li>
        <li class="all_pay">
            <a class="fontcolor" href="{{ url('totalPay') }}">
                各区充值
            </a>
        </li>
        <li class="all_pay">
            <a class="fontcolor" href="{{ url('channelPay') }}">
                各渠道充值
            </a>
        </li>
        <?php if (empty($power) || !isset($power)) { $power =[]; } ?>
        @if(in_array('addChannel', $power))
        <li class="all_pay">
            <a class="fontcolor" href="{{ url('addChannel') }}">
                添加渠道
            </a>
        </li>
        @endif
        @if(in_array('channelList', $power))
            <li class="add_channel leftcolor">
                <a class="" href="{{ url('channelList') }}">
                    渠道列表
                </a>
            </li>
            <li class="add_channel">
                <a class="fontcolor" href="{{ url('moreGame') }}">
                    更多游戏点击
                </a>
            </li>
            <li class="all_pay">
                <a class="fontcolor" href="{{ url('userList') }}">
                    用户列表
                </a>
            </li>
        @endif
    </ul>
@stop
@section('content')
    <!--内容-数据汇总-->
    <div class="content1 show" id="content1">
        <div class="content_sub">
            <div class="table_name">渠道付费</div>
            <form action="{{url('index')}}">
                {{ csrf_field() }}
                <table class="table table-striped table-bordered">
                    <tr>
                        <th>服务器【支持1,2,3或者1-3】</th>
                        <td>
                            <input type="text" size="50" name="serverId" />
                        </td>
                    </tr>
                    <tr>
                        <th>日期</th>
                        <td>
                            <input type="text" size="23" id="startDate" name="startDate"/>
                            至
                            <input type="text" size="23" id="endDate" name="endDate"/>
                        </td>
                    </tr>
                    <tr>
                        <th></th>
                        <td>
                            <input type="submit" class="btn btn-primary" value="搜索" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="content_body">
            <div class="table_name row">
                <div class="col-xs-2 col-md-2">单个渠道数据汇总</div>
                <div class="col-xs-2 col-md-2">渠道名称：{{$channelName}}</div>
            </div>
            <div class="fix-table" id="fix-table" style="width: 100% !important;height: 512px;overflow-y: auto;">
                <table class="table table-striped table-bordered" width="100%" border="0" cellpadding="3">
                    <thead style="background-color: #ddd !important">
                        <tr class="table-tr">
                            <th style="width: 100px">日期</th>
                            <th>服务器 </th>
                            <th>新增用户</th>
                            <th>选服用户</th>
                            <th>首创角色</th>
                            <th>新增账号</th>
                            <th>有效角色</th>
                            <th>活跃用户</th>
                            <th>充值总次数</th>
                            <th>充值人数</th>
                            <th>充值金额</th>
                            <th>首付人数</th>
                            <th>首付金额</th>
                            <th>首付当日总额</th>
                            <th>付费率</th>
                            <th>ARPPU</th>
                            <th>ARPU</th>
                        </tr>
                    </thead>
                    <?php $data = isset($data) ? $data :[];  ?>
                        @foreach( $data as $item)
                        <tr>
                            <td>{{ date('Y-m-d', $item['c_date']) }}</td>
                            <td>{{ isset($item['server_id']) ? $item['server_id'] : '全服'}}</td>
                            <?php $newUser =  isset($item['new_add_user']) ? $item['new_add_user'] : '/' ?>
                            <td><?php echo $newUser ?></td>
                            <?php $insl =  isset($item['total_insl_sum']) ? $item['total_insl_sum'] : '/' ?>
                            <td><?php echo $insl ?></td>
                            <td>{{ isset($item['first_register']) ? $item['first_register'] : 0}}</td>
                            <td>{{ isset($item['new_add']) ? $item['new_add'] : 0 }}</td>
                            <td>{{ isset($item['effective']) ? $item['effective'] : 0 }}</td>
                            <?php $activeNum =isset($item['active']) ? (int)$item['active'] : 0 ?>
                            <?php $newAddNum =isset($item['new_add']) ? (int)$item['new_add'] : 0 ?>
                            <?php $active = $activeNum + $newAddNum ?>
                            <td><?php echo $active ?></td>
                            <td>{{ isset($item['recharge_sum']) ? $item['recharge_sum'] : 0 }}</td>
                            <td>{{ isset($item['recharge_num']) ? $item['recharge_num'] : 0 }}</td>
                            <td>{{ isset($item['recharge_money']) ? $item['recharge_money'] : 0 }}</td>
                            <td>{{ isset($item['first_pay']) ? $item['first_pay'] : 0 }}</td>
                            <td>{{ isset($item['first_pay_sum']) ? $item['first_pay_sum'] : 0 }}</td>
                            <td>{{ isset($item['first_pay_total']) ? $item['first_pay_total'] : 0 }}</td>
                            <?php $rate = (isset($item['first_register']) && $item['first_register'] != 0) ? round(($item['recharge_num'] / $item['first_register']) * 100, 2) : 0; ?>
                            <td><?php if ($rate) { echo $rate.'%'; }else { echo '0.00';} ?></td>
                            <?php $arppu = (isset($item['recharge_num']) && $item['recharge_num'] != 0) ? round(($item['recharge_money'] / $item['recharge_num']), 2) : 0 ; ?>
                            <td><?php if ($arppu) { echo $arppu; }else { echo '0.00';} ?></td>
                            <?php $arpu = (isset($item['first_register']) && $item['first_register'] != 0) ? round(($item['recharge_money'] / $item['first_register']), 2) : 0 ; ?>
                            <td><?php if ($arpu) { echo $arpu; }else { echo '0.00';} ?></td>
                        </tr>
                        @endforeach
                </table>
            </div>
        </div>
        <div>
            <div class="pull-right">
                {{--{{ $job->render() }}--}}
            </div>
        </div>
    </div>
@stop
@section('script')
<script type="text/javascript">
    laydate.render({
        elem: '#startDate', //指定元素
        istime: true,
    });
    laydate.render({
        elem: '#endDate', //指定元素
        istime: true, //是否开启时间选择
    });
</script>
<script>
    window.onload = function(){
        var tableCont = document.querySelector('#fix-table')

        function scrollHandle (e){
            console.log(this)
            var scrollTop = this.scrollTop;
            this.querySelector('thead').style.transform = 'translateY(' + scrollTop + 'px)';
        }

        tableCont.addEventListener('scroll',scrollHandle)
    }
</script>
@stop
