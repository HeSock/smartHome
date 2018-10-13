<?php date_default_timezone_set('Asia/Shanghai'); ?> <!--设置时区 -->
@extends('channel.templateStyle')
@section('content')
    <!--内容-充值详情-->
    <div class="contetn2 show" id="content2">
        <div class="content_body">
            <div class="table_name">渠道列表：</div>
            <div class="fix-table" id="fix-table" style="width: 100% !important;height: 512px;overflow-y: auto;">
                <table class="table table-striped table-bordered" width="100%" border="0" cellpadding="3">
                    <thead style="background-color: #ddd !important">
                        <tr>
                            <th scope="col">id</th>
                            <th scope="col">渠道标识</th>
                            <th scope="col">渠道名称</th>
                            <th scope="col">新增用户</th>
                            <th scope="col">首创角色</th>
                            <th scope="col">新增账号</th>
                            <th scope="col">有效角色</th>
                            <th scope="col">今日活跃用户</th>
                            <th scope="col">分享新增</th>
                            <th scope="col">充值总金额</th>
                            <th scope="col">充值总人数</th>
                            <th scope="col">充值总次数</th>
                            <th scope="col">操作</th>
                        </tr>
                    </thead>
                    @foreach($channels as $item)
                        <tr>
                            <td>{{ $item['id'] }}</td>
                            <td>{{ $item['channel_id'] }}</td>
                            <td>{{ $item['channel_name'] }}</td>
                            <td>{{ $item['new_add_user'] }}</td>
                            <td>{{ $item['first_register_sum'] }}</td>
                            <td>{{ $item['new_add_sum'] }}</td>
                            <td>{{ $item['effective_sum'] }}</td>
                            <td>{{ $item['active_sum_today'] }}</td>
                            <td>{{ $item['channel_regnum'] }}</td>
                            <td>{{ $item['totalPay'] }}</td>
                            <td>{{ $item['peopleNum'] }}</td>
                            <td>{{ $item['totalNum'] }}</td>
                            <td>
                                <a href="{{ url('changeChannel',['id'=>$item['id']]) }}" class="btn btn-primary btn-sm" role="button">修改</a>
                                <a href="{{ url('channelData',['id'=>$item['id']]) }}" class="btn btn-primary btn-sm" role="button">查看</a>
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