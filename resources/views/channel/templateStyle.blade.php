<!DOCTYPE html>
<html>
<?php date_default_timezone_set('Asia/Shanghai'); ?> <!--设置时区 -->
<head>
    <meta charset="utf-8" />
    <title>渠道运营后台</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://s6.static.bearjoy.com/common/channelstat/bootstrap.min.css">
    <script src="https://s6.static.bearjoy.com/common/channelstat/jquery.min.js"></script>
    <script src="https://s6.static.bearjoy.com/common/channelstat/popper.min.js"></script>
    <script src="https://s6.static.bearjoy.com/common/channelstat/bootstrap.min.js"></script>
    <script src="https://s6.static.bearjoy.com/common/channelstat/jquery.pjax.min.js"></script>
    <script type="text/javascript" src="{{ asset('js/echarts.min.js') }}" ></script>
    <script src="{{asset('/laydate/dist/laydate.js')}}"></script>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/channel.css') }}" />
</head>
<body>
<!--头部-->
<div class="nav_body">
    <!-- Right Side Of Navbar -->
    <ul class="nav navbar-nav navbar-right" style="float: right">
        <!-- Authentication Links -->
        @if (Auth::guest())
            <li ><a style="padding-right: 20px !important;" href="{{ route('login') }}">登录</a></li>
            {{--<li><a href="{{ route('register') }}">Register</a></li>--}}
        @else
            <li class="dropdown">当前用户【
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" style="color:black;">
                     {{ Auth::user()->name }}<span class="caret"></span>
                </a>
                】
                <ul class="dropdown-menu" role="menu">
                    <li>
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                            Logout
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    </li>
                </ul>
            </li>
        @endif
    </ul>
</div>
<!--主体内容-->
<div class="subject">

    <!--侧栏-->
    <div class="sidebar">
        <div id="accordion">
            <div class="card">
                <div class="card-header">
                    <a class="card-link" data-toggle="collapse" href="#collapseOne">
					        		<span class="glyphicon glyphicon-list">
					        		</span>
                        渠道数据
                    </a>
                </div>
                <div id="collapseOne" class="collapse show" data-parent="#accordion">
                    <div class="card-body">
                        <ul>
                            <li class="data_collection @if($label == 'channelDetail') leftcolor @endif">
                                <a class="@if($label != 'channelDetail') fontcolor @endif" href="{{ url('index') }}">
                                    <span></span>
                                    数据汇总
                                </a>
                            </li>
                            @if($payShow == 1 && (!in_array('cpa', $power)))
                            <li class="all_pay @if($label == 'channelPay') leftcolor @endif">
                                <a class="@if($label != 'channelPay') fontcolor @endif" href="{{ url('channelPay') }}">
                                    各渠道充值
                                </a>
                            </li>
                            @endif
                            <?php //if (empty($power) || !isset($power)) { $power =[]; } ?>
                            @if(in_array('addChannel', $power))
                                <li class="pay_details @if($label == 'channelServer') leftcolor @endif">
                                    <a class="@if($label != 'channelServer') fontcolor @endif" href="{{ url('channelServer') }}">
                                        各服数据汇总
                                    </a>

                                </li>
                                <li class="pay_details @if($label == 'rechargeDetail') leftcolor @endif">
                                    <a class="@if($label != 'rechargeDetail') fontcolor @endif" href="{{ url('rechargedetail') }}">
                                        充值详情
                                    </a>

                                </li>
                                <li class="all_pay @if($label == 'totalPay') leftcolor @endif">
                                    <a class="@if($label != 'totalPay') fontcolor @endif" href="{{ url('totalPay') }}">
                                        各区充值
                                    </a>
                                </li>
                                <li class="all_pay @if($label == 'addChannel') leftcolor @endif">
                                    <a class="@if($label != 'addChannel') fontcolor @endif" href="{{ url('addChannel') }}">
                                        添加渠道
                                    </a>
                                </li>
                                <li class="add_channel @if($label == 'channelList') leftcolor @endif">
                                    <a class="@if($label != 'channelList') fontcolor @endif" href="{{ url('channelList') }}">
                                        渠道列表
                                    </a>
                                </li>
                                <li class="add_channel @if($label == 'moreGames') leftcolor @endif">
                                    <a class="@if($label != 'moreGames') fontcolor @endif" href="{{ url('moreGame') }}">
                                        更多游戏点击
                                    </a>
                                </li>
                                <li class="all_pay @if($label == 'addUser') leftcolor @endif">
                                    <a class="@if($label != 'addUser') fontcolor @endif" href="{{ url('addUser') }}">
                                        添加用户
                                    </a>
                                </li>
                                <li class="all_pay @if($label == 'userList') leftcolor @endif">
                                    <a class="@if($label != 'userList') fontcolor @endif" href="{{ url('userList') }}">
                                        用户列表
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @section('content')

    @show
</div>
</body>
@section('script')
<script type="text/javascript">
//     // 初始化图表标签
//     var myChart = echarts.init(document.getElementById('chart'));
//     var options={
//         //定义一个标题
//         title:{
//             text:'统计结果'
//         },
// //      legend:{
// //          data:['销量']
// //      },
//         //X轴设置
//         xAxis:{
//             data:['60分','70分','80分','90分','100分']
//         },
//         yAxis:{
//         },
//         //name=legend.data的时候才能显示图例
//         series:[{
//             name:'销量',
//             type:'bar',
//             data:['12','32','45','21','1']
//         }]
//
//     };
//     myChart.setOption(options);
</script>
<script>
    // var today = new Date();
    // var submitTime=today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();
    // // $("#startDate1").attr('value',submitTime);
    // // $("#endDate1").attr('value',submitTime);
    //
    // $("#startDate2").attr('value',submitTime);
    // $("#endDate2").attr('value',submitTime);
    //
    // $("#startDate3").attr('value',submitTime);
    // $("#endDate3").attr('value',submitTime);
</script>
<script>
    $(function(){
        // 图标
        // $(".nav_pos1").click(function() {
        //     $(this).addClass("active")
        //     $(".nav_pos2").removeClass("active")
        //     $(".chart_data").show()
        //     $(".table_data").hide()
        // })
        // // 表格
        // $(".nav_pos2").click(function() {
        //     $(this).addClass("active")
        //     $(".nav_pos1").removeClass("active")
        //     $(".chart_data").hide()
        //     $(".table_data").show().removeClass("nushow")
        // })
        // // 数据汇总
        // $(".data_collection").click(function() {
        //     $(this).addClass("leftcolor")
        //     $(this).children().removeClass("fontcolor")
        //     $(".pay_details").removeClass("leftcolor")
        //     $(".pay_details a").addClass("fontcolor")
        //     $(".all_pay").removeClass("leftcolor")
        //     $(".all_pay a").addClass("fontcolor")
        //     // $("#content1").removeClass("nushow").addClass("show")
        //     // $("#content2").removeClass("show").addClass("nushow")
        //     // $("#content3").removeClass("show").addClass("nushow")
        // })
        // // 充值详情
        // $(".pay_details").click(function() {
        //     $(this).addClass("leftcolor")
        //     $(this).children().removeClass("fontcolor")
        //     $(".data_collection").removeClass("leftcolor").removeClass("fontcolor")
        //     $(".data_collection a").addClass("fontcolor")
        //     $(".all_pay").removeClass("leftcolor")
        //     $(".all_pay a").addClass("fontcolor")
        //     // $("#content1").removeClass("show").addClass("nushow")
        //     // $("#content2").removeClass("nushow").addClass("show")
        //     // $("#content3").removeClass("show").addClass("nushow")
        // })
        // // 各区充值
        // $(".all_pay").click(function() {
        //     $(this).addClass("leftcolor")
        //     $(this).children().removeClass("fontcolor")
        //     $(".pay_details").removeClass("leftcolor")
        //     $(".pay_details a").addClass("fontcolor")
        //     $(".data_collection").removeClass("leftcolor")
        //     $(".data_collection a").addClass("fontcolor")
        //     // $("#content1").removeClass("show").addClass("nushow")
        //     // $("#content2").removeClass("show").addClass("nushow")
        //     // $("#content3").removeClass("nushow").addClass("show")
        // })
    })
</script>
{{--<script type="text/javascript">--}}
    {{--laydate.render({--}}
        {{--elem: '#startDate1', //指定元素--}}
        {{--istime: true,--}}
    {{--});--}}
    {{--laydate.render({--}}
        {{--elem: '#endDate1', //指定元素--}}
        {{--istime: true, //是否开启时间选择--}}
    {{--});--}}
    {{--laydate.render({--}}
        {{--elem: '#startDate2', //指定元素--}}
        {{--istime: true,--}}
    {{--});--}}
    {{--laydate.render({--}}
        {{--elem: '#endDate2', //指定元素--}}
        {{--istime: true, //是否开启时间选择--}}
    {{--});--}}
    {{--laydate.render({--}}
        {{--elem: '#startDate3', //指定元素--}}
        {{--istime: true,--}}
    {{--});--}}
    {{--laydate.render({--}}
        {{--elem: '#endDate3', //指定元素--}}
        {{--istime: true, //是否开启时间选择--}}
    {{--});--}}

{{--</script>--}}
@show
</html>