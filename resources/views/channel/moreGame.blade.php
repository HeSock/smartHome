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
        <li class="all_pay ">
            <a class="fontcolor" href="{{ url('rechargedetail') }}">
                充值详情
            </a>

        </li>
        <li class="all_pay">
            <a class="fontcolor" href="{{ url('totalPay') }}">
                各区充值
            </a>
        </li>
        <li class="channel_pay">
            <a class="fontcolor" href="{{ url('channelPay') }}">
                各渠道充值
            </a>
        </li>
        <li class="add_channel">
            <a class="fontcolor" href="{{ url('addChannel') }}">
                添加渠道
            </a>
        </li>
        <li class="add_channel leftcolor">
            <a class="fontcolor" href="{{ url('channelList') }}">
                渠道列表
            </a>
        </li>
        <li class="add_channel leftcolor">
            <a class="" href="{{ url('moreGame') }}">
                更多游戏点击
            </a>
        </li>
    </ul>
@stop
@section('content')
    <!--内容-充值详情-->
    <div class="contetn2 show" id="content2">
        <div class="content_body">
            <div class="table_name">点击数据：</div>
            <div class="fix-table" id="fix-table" style="width: 100% !important;height: 512px;overflow-y: auto;">
                <table class="table table-striped table-bordered" width="100%" border="0" cellpadding="3">
                    <thead style="background-color: #ddd !important">
                        <tr>
                            <th scope="col">游戏标识</th>
                            <th scope="col">游戏名称</th>
                            <th scope="col">点击次数</th>
                            <th scope="col">点击人数</th>
                        </tr>
                    </thead>
                    @foreach($moregames as $item)
                        <tr>
                            <td>{{ $item->more_id }}</td>
                            <td>{{ $games[$item->more_id] }}</td>
                            <td>{{ $item->more_num }}</td>
                            <td>{{ $item->more_people_num }}</td>
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