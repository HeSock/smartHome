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
        <li class="all_pay">
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
        <li class="pay_details leftcolor">
            <a class="" href="{{ url('addChannel') }}">
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
        <li class="all_pay">
            <a class="fontcolor" href="{{ url('userList') }}">
                用户列表
            </a>
        </li>
    </ul>
@stop
@section('content')
    <!--内容-充值详情-->
    <div class="contetn2 show" id="content2">
        <div class="content_sub">
            @if(count($errors->all())>0)
                <div class="col-md-3 col-md-offset-6 alert alert-warning alert-dismissible in" role="alert">
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">x</span>
                    </button>
                    @foreach($errors->all() as $err) {{$err}} <br>@endforeach
                </div>
            @endif
            <div class="table_name">添加渠道</div>
            <form action="{{url()->full()}}" method="post" enctype="multipart/form-data"  id="addChannle">
                {{ csrf_field() }}
                <table class="table table-striped table-bordered">
                    <tr>
                        <th>关联用户ID</th>
                        <td>
                            <label>
                                <select class="form-control" form="addChannle" name="userId" id="userId">
                                    <option value="">请选择</option>
                                @foreach($users as $item)
                                    <option value="{{$item->id}}">{{$item->name}}</option>
                                @endforeach
                                </select>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th>渠道ID</th>
                        <td>
                            <label>
                                <input type="text" name="channelId" id="channelId" required>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th>渠道名称</th>
                        <td>
                            <label>
                                <input type="text" name="channelName" id="channelName" required>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th></th>
                        <td>
                            <input type="submit" class="btn btn-primary" value="保存" />
                        </td>
                    </tr>
                </table>
            </form>

        </div>
            <div>
                <div class="pull-right">
                    {{--{{ $job->render() }}--}}
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