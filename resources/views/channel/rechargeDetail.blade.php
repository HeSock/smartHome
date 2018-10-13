<?php date_default_timezone_set('Asia/Shanghai'); ?> <!--设置时区 -->
@extends('channel.templateStyle')

@section('content')
    <!--内容-充值详情-->
    <div class="contetn2 show" id="content2">
        <div class="fix-table" id="fix-table" style="width: 100% !important;height: 100%;overflow-y: auto;">
        <div class="content_sub">
            <div class="table_name">统计列表</div>
            <form method="POST" id="form1" action="{{ url('rechargedetail') }}" >
                {{ csrf_field() }}
                <table class="table table-striped table-bordered">
                    <tr>
                        <th>日期</th>
                        <td>
                            <input type="text" size="23" id="startDate" name="pay[startDate]" value="{{ date('Y-m-d',$pay->start) }}"/>
                            至
                            <input type="text" size="23" id="endDate" name="pay[endDate]" value="{{ date('Y-m-d',$pay->end) }}"/>
                            <a href="{{ url('rechargeDetailDate', ['date' => '0']) }}">今天</a>
                            <a href="{{ url('rechargeDetailDate', ['date' => '1']) }}">昨天</a>
                            <a href="{{ url('rechargeDetailDate', ['date' => '7']) }}">最近七天</a>
                            <a href="{{ url('rechargeDetailDate', ['date' => '30']) }}">最近三十天</a>
                            <a href="{{ url('rechargeDetailDate', ['date' => '-1']) }}">上个月</a>
                            <a href="{{ url('rechargeDetailDate', ['date' => '100']) }}">当月</a>
                        </td>
                    </tr>
                    <tr>
                        <th>充值金额范围</th>
                        <td>
                            <input type="text" size="23"  name="pay[startNum]"  value="{{ $pay->startNum }}"/>
                            至
                            <input type="text" size="23" name="pay[endNum]" value="{{ $pay->endNum }}"/>
                            <div><?php echo old('pay')['endNum'] ?></div>
                        </td>
                    </tr>
                    <tr>
                        <th>服务器</th>
                        <td>

                            <select name="pay[num]" value="{{ $pay->num }}" >
                                <option value="N">请选择</option>
                                @foreach($pay->sIds as $sId)
                                <option value="{{$sId}}" @if($pay->checksId == $sId) selected @endif>{{$sId}}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>用户账号/订单号</th>
                        <td>
                            <input type="text" size="23" name="pay[account]"  value="{{ $pay->account }}">
                            /
                            <input type="text" size="23" name="pay[order]" value="{{ $pay->order }}"/>
                        </td>
                    </tr>
                    <tr>
                        <th></th>
                        <td>
                            {{--<input type="submit" class="btn btn-primary" value="搜索" id="btn" onclick="return btuSubmit()"/>--}}
                            {{--<button type="button" id="btn" class="btn btn-primary" onclick="btuSubmit(event)">搜索</button>--}}
                            <button type="button" class="btn btn-primary" onclick="btuSubmit()">搜索</button>
                        </td>
                    </tr>
                </table>
            </form>

        </div>
        <div class="content_body">
            <?php $total = 0;foreach ($pay->data as $item) { $total += $item->payment_money;} ?>
            <div class="table_name">充值总和：<span><?php echo $total ?></span></div>
                <table class="table table-striped table-bordered" width="100%" border="0" cellpadding="3">
                    <thead style="background-color: #ddd !important">
                        <tr>
                            <th scope="col">订单号</th>
                            <th scope="col">渠道标识</th>
                            <th scope="col">渠道名称</th>
                            <th scope="col">充值账号</th>
                            <th scope="col">充值金额</th>
                            <th scope="col">元宝数</th>
                            <th scope="col">充值时间</th>
                            <th scope="col">服务器ID</th>
                        </tr>
                    </thead>
                    @foreach($pay->data as $item)
                        <tr>
                            <td>{{ $item->order_id }}</td>
                            <td>{{ $item->channel_id }}</td>
                            <td>{{ $item->channel_name }}</td>
                            <td>{{ $item->payment }}</td>
                            <td>{{ $item->payment_money }}</td>
                            <td>{{ $item->prop_num }}</td>
                            <td>{{ date('Y-m-d H:i:s',$item->recharge_time) }}</td>
                            <td>{{ $item->server_id }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
                <div class="pull-right">
                    {{--{{ $job->render() }}--}}
                </div>
            </div>
        </div>
        <div>
    </div>
@stop
@section('script')
    <script>
        {{--时间提示--}}
        // var today = new Date();
        // var submitTime=today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();;
        // $("#startDate").attr('value',submitTime);
        // $("#endDate").attr('value',submitTime);

        function btuSubmit(event){
            document.getElementById("form1").submit();
            document.getElementById("startDate").innerText = 11;
            return false;
        }

    </script>
    <script>  {{-- 固定表格的表头 --}}
        // window.onload = function(){
        //     var tableCont = document.querySelector('#fix-table')
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
@stop