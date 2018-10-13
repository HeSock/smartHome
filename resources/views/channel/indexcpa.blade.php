<?php date_default_timezone_set('Asia/Shanghai'); ?> <!--设置时区 -->
@extends('channel.templateStyle')

@section('content')
    <!--内容-数据汇总-->
    <div class="content1 show" id="content1">
        <div class="fix-table" id="fix-table" style="width: 100% !important;height: 100%;overflow-y: auto;">
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
                        <th>是否区分渠道</th>
                        <td>
                            <input type="checkbox" value="1" name="isChannel"/>
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
                            <th>新增账号</th>
                            <th>有效角色</th>
                        </tr>
                    </thead>

                        @foreach( $data as $item)
                        <tr>
                            <td>{{ date('Y-m-d', $item->c_date) }}</td>
                            <td>{{ $item->server_id }}</td>
                            <td>{{ $item->channel_id }}</td>
                            <td>{{ $item->channel_name }}</td>
                            <td>{{ $item->new_add }}</td>
                            <td>{{ $item->effective }}</td>
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
@stop
