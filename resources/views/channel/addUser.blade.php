<?php date_default_timezone_set('Asia/Shanghai'); ?> <!--设置时区 -->
@extends('channel.templateStyle')
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
            <div class="table_name">添加用户</div>
            <form action="{{url()->full()}}" method="post" enctype="multipart/form-data"  id="addChannle">
                {{ csrf_field() }}
                <table class="table table-striped table-bordered">
                    <tr>
                        <th>用户名</th>
                        <td>
                            <label>
                                <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th>邮箱</th>
                        <td>
                            <label>
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th>密码</th>
                        <td>
                            <label>
                                <input id="password" type="password" class="form-control" name="password" required>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th>确认密码</th>
                        <td>
                            <label>
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th>权限</th>
                        <td>
                            <label>
                                <input type="radio" name="power" id="power1" value="admin">
                                管理员
                            </label>
                            <label>
                                <input type="radio" name="power" id="power2" value="cps" checked>
                                cps
                            </label>
                            <label>
                                <input type="radio" name="power" id="power3" value="cpa">
                                cpa
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