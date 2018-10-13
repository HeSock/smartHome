<?php date_default_timezone_set('Asia/Shanghai'); ?> <!--设置时区 -->
@extends('channel.templateStyle')

@section('content')
    <!--内容-数据汇总-->

    <div class="content1 show" id="content1">
        <div class="fix-table" id="fix-table" style="width: 100% !important;height: 100%;overflow-y: auto;">
        <div class="content_sub">
            <div class="table_name">渠道付费</div>
            <form action="{{url()->full()}}">
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
                        <th>不区分日期</th>
                        <td>
                            <input type="checkbox" value="1" name="notdate"/>
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
            <div class="table_name">渠道充值统计</div>
                <table class="table table-striped table-bordered" width="100%" border="0" cellpadding="3">
                    <thead style="background-color: #ddd !important">
                        <tr class="table-tr">
                            <th style="width: 100px">日期</th>
                            <th>服务器 </th>
                            <th>渠道标识 </th>
                            <th>渠道名称</th>
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

                        @foreach( $data as $item)
                        <tr>
                            <td>{{ date('Y-m-d', $item->c_date) ? date('Y-m-d', $item->c_date) : $item->c_date }}</td>
                            <td>{{ $item->server_id }}</td>
                            <td>全渠道</td>
                            <td>全渠道</td>
                            <?php $newUser =  isset($item->new_add_user) ? $item->new_add_user : '/' ?>
                            <td><?php echo $newUser ?></td>
                            <?php $insl =  isset($item->total_insl_sum) ? $item->total_insl_sum : '/' ?>
                            <td><?php echo $insl ?></td>
                            <td>{{ $item->first_register }}</td>
                            <td>{{ $item->new_add }}</td>
                            <td>{{ $item->effective }}</td>
                            <?php $active = $item->active + $item->new_add ?>
                            <td><?php echo $active ?></td>
                            <td>{{ $item->recharge_sum }}</td>
                            <td>{{ $item->recharge_num }}</td>
                            <td>{{ $item->recharge_money }}</td>
                            <td>{{ $item->first_pay }}</td>
                            <td>{{ $item->first_pay_sum }}</td>
                            <td>{{ $item->first_pay_total }}</td>
                            <?php $rate = $item->first_register ? round(($item->recharge_num / $item->first_register) * 100, 2) : 0; ?>
                            <td><?php if ($rate) { echo $rate.'%'; }else { echo '0.00';} ?></td>
                            <?php $arppu = $item->recharge_num ? round(($item->recharge_money / $item->recharge_num), 2) : 0 ; ?>
                            <td><?php if ($arppu) { echo $arppu; }else { echo '0.00';} ?></td>
                            <?php $arpu = $item->first_register ? round(($item->recharge_money / $item->first_register), 2) : 0 ; ?>
                            <td><?php if ($arpu) { echo $arpu; }else { echo '0.00';} ?></td>
                        </tr>
                        @endforeach
                </table>
        </div>
        <div>
            <div class="pull-right">
                {{--{{ $job->render() }}--}}
            </div>
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
    // window.onload = function(){
    //     var tableCont = document.querySelector('#content1')
    //
    //     function scrollHandle (e){
    //         console.log(this)
    //         var scrollTop = this.scrollTop;
    //         this.querySelector('thead').style.transform = 'translateY(' + scrollTop + 'px)';
    //     }
    //
    //     tableCont.addEventListener('scroll',scrollHandle)
    // }
</script>
@stop
