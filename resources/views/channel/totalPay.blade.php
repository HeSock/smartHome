<?php date_default_timezone_set('Asia/Shanghai'); ?> <!--设置时区 -->
@extends('channel.templateStyle')

@section('content')
    <!--内容-各区充值-->
    <div class="contetn3 show" id="content3">
        <div class="content_sub">
            <div class="table_name">统计列表</div>
            <form method="POST" action="{{url('totalPay')}}">
                {{ csrf_field() }}
                <table class="table table-striped table-bordered">
                    <tr>
                        <th>日期</th>
                        <td>
                            <input type="text" size="23" id="startDate" name="startDate" value="{{ date('Y-m-d', $total->startDate) }}"/>
                            至
                            <input type="text" size="23" id="endDate" name="endDate" value="{{ date('Y-m-d', $total->endDate)}}"/>
                            <a href="{{ url('totalPayDate', ['date' => '0']) }}">今天</a>
                            <a href="{{ url('totalPayDate', ['date' => '1']) }}">昨天</a>
                            <a href="{{ url('totalPayDate', ['date' => '7']) }}">最近七天</a>
                            <a href="{{ url('totalPayDate', ['date' => '30']) }}">最近三十天</a>
                            <a href="{{ url('totalPayDate', ['date' => '-1']) }}">上个月</a>
                            <a href="{{ url('totalPayDate', ['date' => '100']) }}">当月</a>
                        </td>
                    </tr>
                    <tr>
                        <th>排序</th>
                        <td>
                            <input type="radio"  checked="checked" value="serverid" name="sort" onclick="sortRMB()" onload="sortRMB()" @if($total->sort == 'serverid') checked @endif/>按服数排序
                            <input type="radio" size="23" value="rmb" name="sort" onclick="sortMoney()"  @if($total->sort == 'rmb') checked @endif/>按金额顺序

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
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav_pos1 nav-link active" onclick="rendering()">图表</a>
                </li>
                <li class="nav-item">
                    <a class="nav_pos2 nav-link ">表格</a>
                </li>
            </ul>
            <div class="chart_data">
                <?php foreach ($total->data as $item){ $xData[] = $item->server_id;$yData[] = $item->recharge_money;} ?>
                <div class="chart" id="chart"></div>
            </div>
            <div class="table_data nushow">
                <div class="fix-table" id="fix-table" style="width: 100%;height: 300px;overflow-y: auto;">
                    <table class="table table-striped table-bordered">
                        <thead style="background-color: #ddd !important">
                            <tr>
                                <th>服务器</th>
                                <th>充值金额</th>
                                <th>充值次数</th>
                                <th>充值人数</th>
                                <th>ARPPU值</th>
                            </tr>
                        </thead>
                        @foreach($total->data as $item)
                        <tr>
                            <td>{{ $item->server_id }}</td>
                            <td>{{ $item->recharge_money }}</td>
                            <td>{{ $item->recharge_sum }}</td>
                            <td>{{ $item->recharge_num }}</td>
                            <?php $arppu = $item->recharge_num ? round(($item->recharge_money / $item->recharge_num), 2) : 0 ; ?>
                            <td><?php if ($arppu) { echo $arppu; }else { echo '0.00';} ?></td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
@section('script')
    {{--柱形图--}}
    <script type="text/javascript">
        var totalData = <?php echo json_encode($total->data) ?>;
        var xData = [];
        var yData = [];
        var totalPay = 0;
        var totalPayNum = 0;
        var totalPepleNum = 0;
        var ARPU = 0.00;
        console.log(totalData)
        totalData.sort(compare2);
        for ( var i = 0; i < totalData.length; i++) {
            yData[i] = totalData[i].recharge_money;
            xData[i] = totalData[i].server_id+ " 服";
            totalPay += parseInt(totalData[i].recharge_money);
            totalPayNum += parseInt(totalData[i].recharge_sum);
            totalPepleNum += parseInt(totalData[i].recharge_num);
        }

        getARPU();
        function getARPU(){
            if (totalPepleNum != 0) {
                 var num = totalPay/totalPepleNum;
                ARPU = num.toFixed(2);
            }
        }

        // 初始化图表标签
        var myChart = echarts.init(document.getElementById('chart'));
        var options={
            //定义一个标题
            title:{
                text:"总充值: ￥" + totalPay + "， 总充值次数:  " + totalPayNum + "次， 总充值人数:  " + totalPepleNum +"人， ARPPU值: ￥" + ARPU,
                x:'center',
                top:10
            },
            tooltip : {
                trigger: 'axis',
                axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                    type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                }
            },
             // legend:{
             //     data:['销量', '人数']
             // },
            //X轴设置
            xAxis:{
                data:  xData,
            },
            yAxis:{

            },
            //name=legend.data的时候才能显示图例
            series:[{
                name:'充值金额',
                type:'bar',
                // barWidth: '60%',
                data: yData,
                //配置样式
                itemStyle: {
                    //通常情况下：
                    normal:{
                        label:{ // 显示每根柱子的数据
                            show: true,
                            position: 'top',
                            textStyle: {
                                color: 'black'
                            }
                        },
                        //每个柱子的颜色即为colorList数组里的每一项，如果柱子数目多于colorList的长度，则柱子颜色循环使用该数组
                        color: function (params){
                            var colorList = ['rgb(164,205,258)','rgb(42,170,227)','rgb(25,46,94)','rgb(195,229,235)'];
                            return colorList[params.dataIndex];
                        },

                    },
                    //鼠标悬停时：
                    emphasis: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                },
            }],
            label: {
                normal: {
                    show: true,
                    position: 'top',
                    textStyle: {
                        color: 'black'
                    }
                }
            },
        };
        myChart.setOption(options);

        function sortMoney(){
            totalData.sort(compare1);
            for ( var i = 0; i < totalData.length; i++) {
                yData[i] = totalData[i].recharge_money;
                xData[i] = totalData[i].server_id;
            }
            myChart.setOption(options);
        }
        function sortRMB() {
            totalData.sort(compare2);
            for ( var i = 0; i < totalData.length; i++) {
                yData[i] = totalData[i].recharge_money;
                xData[i] = totalData[i].server_id;
            }
            myChart.setOption(options);
        }
        var compare1 = function (obj1, obj2) {
            var val1 = obj1.recharge_money;
            var val2 = obj2.recharge_money;
            if (val1 < val2) {
                return -1;
            } else if (val1 > val2) {
                return 1;
            } else {
                return 0;
            }
        }
        var compare2 = function (obj1, obj2) {
            var val1 = obj1.server_id;
            var val2 = obj2.server_id;
            if (val1 < val2) {
                return -1;
            } else if (val1 > val2) {
                return 1;
            } else {
                return 0;
            }
        }
    </script>

    <script type="text/javascript">
        // 时间选择器
        laydate.render({
            elem: '#startDate', //指定元素
            istime: true,
        });
        laydate.render({
            elem: '#endDate', //指定元素
            istime: true, //是否开启时间选择
        });
        {{--时间提示--}}
        // var today = new Date();
        // var submitTime=today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();
        // $("#startDate").attr('value',submitTime);
        // $("#endDate").attr('value',submitTime);

        // table 表格滚轮效果
        window.onload = function(){
            var tableCont = document.querySelector('#fix-table')

            function scrollHandle (e){
                console.log(this)
                var scrollTop = this.scrollTop;
                this.querySelector('thead').style.transform = 'translateY(' + scrollTop + 'px)';
            }

            tableCont.addEventListener('scroll',scrollHandle)
        }
        // 图表 表格切换
        $(function(){
            // 图标
            $(".nav_pos1").click(function() {
                $(this).addClass("active")
                $(".nav_pos2").removeClass("active")
                $(".chart_data").show()
                $(".table_data").hide()
            })
            // 表格
            $(".nav_pos2").click(function() {
                $(this).addClass("active")
                $(".nav_pos1").removeClass("active")
                $(".chart_data").hide()
                $(".table_data").show().removeClass("nushow")
            })
        })
    </script>

@stop