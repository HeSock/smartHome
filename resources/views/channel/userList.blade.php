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
        <li class="all_pay leftcolor">
            <a class="" href="{{ url('userList') }}">
                用户列表
            </a>
        </li>
    </ul>
@stop
@section('content')
    <!--内容-充值详情-->
    <div class="contetn2 show" id="content2">
        <div class="content_body">
            <div class="table_name">用户列表：</div>
            <div class="fix-table" id="fix-table" style="width: 100% !important;height: 512px;overflow-y: auto;">
                <table class="table table-striped table-bordered" width="100%" border="0" cellpadding="3">
                    <thead style="background-color: #ddd !important">
                        <tr>
                            <th scope="col">id</th>
                            <th scope="col">用户名</th>
                            <th scope="col">账号</th>
                            <th scope="col">权限</th>
                            <th scope="col">操作</th>
                        </tr>
                    </thead>
                    @foreach($users as $item)
                        <tr>
                            <td>{{ $item['id'] }}</td>
                            <td>{{ $item['name'] }}</td>
                            <td>{{ $item['email'] }}</td>
                            <td>{{ $item['power'] }}</td>
                            <td>
                                <a href="{{ url('changeUser',['id'=>$item['id']]) }}" class="btn btn-primary btn-sm" role="button">修改</a>
                            </td>
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