<?php date_default_timezone_set('Asia/Shanghai'); ?> <!--设置时区 -->
@extends('channel.templateStyle')

@section('sidebar')
    <ul>
        <li class="data_collection ">
            <a class="fontcolor" href="{{ url('index') }}">
                <span></span>
                数据汇总
            </a>
        </li>
        {{--<li class="all_pay ">--}}
            {{--<a class="fontcolor" href="{{ url('rechargedetail') }}">--}}
                {{--充值详情--}}
            {{--</a>--}}

        {{--</li>--}}
        {{--<li class="all_pay">--}}
            {{--<a class="fontcolor" href="{{ url('totalPay') }}">--}}
                {{--各区充值--}}
            {{--</a>--}}
        {{--</li>--}}
        <li class="channel_pay leftcolor">
            <a class="" href="{{ url('channelPay') }}">
                各渠道充值
            </a>
        </li>
        @if(in_array('addChannel', $power))
        <li class="add_channel">
            <a class="fontcolor" href="{{ url('addChannel') }}">
                添加渠道
            </a>
        </li>
        @endif
        @if(in_array('channelList', $power))
            <li class="add_channel">
                <a class="fontcolor" href="{{ url('channelList') }}">
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
    <!--内容-充值详情-->
    <div class="contetn2 show" id="content2">
        <div class="content_body">
            <div class="table_name">渠道充值排行：</div>
            <div class="fix-table" id="fix-table" style="width: 100% !important;height: 512px;overflow-y: auto;">
                <table class="table table-striped table-bordered" width="100%" border="0" cellpadding="3">
                    <thead style="background-color: #ddd !important">
                        <tr>
                            <th scope="col">渠道标识</th>
                            <th scope="col">渠道名称</th>
                            <th scope="col">充值总金额</th>
                            <th scope="col">充值总人数</th>
                            <th scope="col">充值总次数</th>
                            {{--<th scope="col">ARPU</th>--}}
                        </tr>
                    </thead>
                    @foreach($total as $item)
                        <tr>
                            <td>{{ $item['channelId'] }}</td>
                            <td>{{ $item['channelName'] }}</td>
                            <td>{{ $item['totalPay'] }}</td>
                            <td>{{ $item['peopleNum'] }}</td>
                            <td>{{ $item['totalNum'] }}</td>
                            {{--<td>{{ $item['arpu'] }}</td>--}}
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