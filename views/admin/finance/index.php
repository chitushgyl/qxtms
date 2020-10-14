<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>layuiAdmin 控制台</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/static/admin/layuiadmin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="/static/admin/layuiadmin/style/admin.css" media="all">
    <style>
        table{
            text-align: center;
        }
    </style>
</head>
<body>
<div class="layui-card">

<div class="layui-fluid">

    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-row layui-col-space15">
                <div class="layui-col-md6">
                     <div class="layui-card">
                         <div class="layui-card-header">数据概览</div>
                         <div class="layui-card-body">
                             <div style="width: 500px;height: 330px;" carousel-item id="dataview">
                             </div>
                         </div>
                     </div>
                </div>
                <div class="layui-col-md6">
                    <div class="layui-card">
                        <div class="layui-card-header">数据概览</div>
                        <div class="layui-card-body">
                            <div style="width: 500px;height: 330px;" carousel-item id="normcol">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="layui-col-md12">
            <div class="layui-row layui-col-space15">
                <div class="layui-col-md6">
                    <div class="layui-card">
                        <div class="layui-card-header">数据概览</div>
                        <div class="layui-card-body">
                            <div carousel-item style="width: 500px;height: 330px;" id="normbar">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="layui-col-md6">
                    <div class="layui-card">
                        <div class="layui-card-header">数据概览</div>
                        <div class="layui-card-body">
                            <div carousel-item style="width: 500px;height: 330px;" id="heapline">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/static/admin/layuiadmin/layui/layui.js?t=1"></script>
<script type="text/javascript" src="/js/echarts.js"></script>
<script src="/js/jquery.min.js"></script>

<script>
    layui.config({
        base: '/static/admin/layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'console']);


// console.log(day,week,month,year);
    $("#tabpic").click(function () {



    })

    var chartZhu = echarts.init(document.getElementById('heapline'));
    var chart = echarts.init(document.getElementById('normbar'));
    var chartZhe = echarts.init(document.getElementById('normcol'));
    var chartZh = echarts.init(document.getElementById('dataview'));
    //指定图表配置项和数据
    function count_price(week_time,week_receive,week_true_receive,week_payment,week_true_payment,month_time,month_receive,month_true_receive,month_payment,month_true_payment,day_time
        ,day_receive,day_true_receive,day_payment,day_true_payment,year_time,year_receive,year_true_receive,year_payment,year_true_payment){
        var optionchart = {
            title: {
                text: '年应收应付'
            },
            tooltip: {},
            legend: {
                data: ['应收','实收','应付','实付']
            },
            xAxis: {
                data: year_time
            },
            yAxis: {
                type: 'value'
            },
            series: [{
                name: '应收',
                type: 'line', //柱状
                data: year_receive,
                itemStyle: {
                    normal: { //柱子颜色
                        color: '#F94145'
                    }
                },
            },{
                name:'实收',
                type:'line',
                data:year_true_receive,
                itemStyle:{
                    normal:{
                        color:'#F8961F'
                    }
                }
            },{
                name:'应付',
                type:'line',
                data:year_payment,
                itemStyle:{
                    normal:{
                        color:'#90BE6D'
                    }
                }
            },{
                name:'实付',
                type:'line',
                data:year_true_payment,
                itemStyle:{
                    normal:{
                        color:'#1882C4'
                    }
                }
            }]
        };

        var optionchartZhe = {
            title: {
                text: '周应收应付'
            },
            tooltip: {},
            legend: { //顶部显示 与series中的数据类型的name一致
                data: ['应收', '实收', '应付', '实付']
            },
            xAxis: {
                // type: 'category',
                // boundaryGap: false, //从起点开始
                data: week_time
            },
            yAxis: {
                type: 'value'
            },
            series: [{
                name: '应收',
                type: 'line', //线性
                data: week_receive,
                itemStyle: {
                    normal: { //柱子颜色
                        color: '#F94145'
                    }
                },
            }, {
                name: '实收',
                type: 'line', //线性
                data: week_true_receive,
                itemStyle:{
                    normal:{
                        color:'#F8961F'
                    }
                }
            }, {
                smooth: true, //曲线 默认折线
                name: '应付',
                type: 'line', //线性
                data: week_payment,
                itemStyle:{
                    normal:{
                        color:'#90BE6D'
                    }
                }
            }, {
                smooth: true, //曲线
                name: '实付',
                type: 'line', //线性
                data: week_true_payment,
                itemStyle:{
                    normal:{
                        color:'#1882C4'
                    }
                }
            }]
        };

        var optionchartBing = {
            title: {
                text: '月应收应付',
                // subtext: '纯属虚构', //副标题
            },
            tooltip: {
                // trigger: 'item' //悬浮显示对比
            },
            legend: {
                // orient: 'vertical', //类型垂直,默认水平
                // left: 'left', //类型区分在左 默认居中
                data: ['应收', '实收', '应付', '实付']
            },
            xAxis: {
                data: month_time
            },
            yAxis: {
                type: 'value'
            },
            series: [{
                name: '应收',
                type:'line',
                data: month_receive,
                itemStyle: {
                    normal: { //柱子颜色
                        color: '#F94145'
                    }
                }
            }, {
                name: '实收',
                type:'line',
                data: month_true_receive,
                itemStyle: {
                    normal: { //柱子颜色
                        color: '#F8961F'
                    }
                }
            }, {
                name: '应付',
                type:'line',
                data: month_payment,
                itemStyle: {
                    normal: { //柱子颜色
                        color: '#90BE6D'
                    }
                }
            }, {
                name: '实付',
                type:'line',
                data: month_true_payment,
                itemStyle: {
                    normal: { //柱子颜色
                        color: '#1882C4'
                    }
                }
            }]

        };
        var optioncharts = {
            title: {
                text: '日应收应付'
            },
            tooltip: {},
            legend: {
                data: ['应收','实收','应付','实付']
            },
            xAxis: {
                data: day_time
            },
            yAxis: {
                type: 'value'
            },
            series: [{
                name: '应收',
                type: 'line', //柱状
                data: day_receive,
                itemStyle: {
                    normal: { //柱子颜色
                        color: '#F94145'
                    }
                },
            },{
                name:'实收',
                type:'line',
                data:day_true_receive,
                itemStyle:{
                    normal:{
                        color:'#F8961F'
                    }
                }
            },{
                name:'应付',
                type:'line',
                data:day_payment,
                itemStyle:{
                    normal:{
                        color:'#90BE6D'
                    }
                }
            },{
                name:'实付',
                type:'line',
                data:day_true_payment,
                itemStyle:{
                    normal:{
                        color:'#1882C4'
                    }
                }
            }]
        };
        chartZhu.setOption(optionchart, true);
        chartZhe.setOption(optionchartZhe, true);
        chart.setOption(optionchartBing, true);
        chartZh.setOption(optioncharts, true);
    }

    $(document).ready(function (){
        $.ajax({
            url:'/admin/finance/get',
            type:'POST',
            data:'123',
            dataType:'json',
            success:function (w){
                console.log(w.week[0].time);
                var week_time = [];
                var week_receive = [];
                var week_true_receive = [];
                var week_payment = [];
                var week_true_payment = [];

                for (var i = 0;i<w.week.length;i++){
                    week_time.unshift(w.week[i].time);
                    week_receive.unshift(w.week[i].receiveprice);
                    week_true_receive.unshift(w.week[i].trueprice);
                    week_payment.unshift(w.week[i].pay_price);
                    week_true_payment.unshift(w.week[i].truepay);
                }
                var month_time = [];
                var month_receive = [];
                var month_true_receive = [];
                var month_payment = [];
                var month_true_payment = [];
                for (var i = 0;i<w.month.length;i++){
                    month_time.unshift(w.month[i].time);
                    month_receive.unshift(w.month[i].receiveprice);
                    month_true_receive.unshift(w.month[i].trueprice);
                    month_payment.unshift(w.month[i].pay_price);
                    month_true_payment.unshift(w.month[i].truepay);
                }
                var day_time = [];
                var day_receive = [];
                var day_true_receive = [];
                var day_payment = [];
                var day_true_payment = [];
                for (var i = 0;i<w.day.length;i++){
                    day_time.push(w.day[i].time);
                    day_receive.push(w.day[i].receiveprice);
                    day_true_receive.push(w.day[i].trueprice);
                    day_payment.push(w.day[i].pay_price);
                    day_true_payment.push(w.day[i].truepay);
                }

                var year_time = [];
                var year_receive = [];
                var year_true_receive = [];
                var year_payment = [];
                var year_true_payment = [];
                for (var i = 0;i<w.year.length;i++){
                    year_time.push(w.year[i].time);
                    year_receive.push(w.year[i].receiveprice);
                    year_true_receive.push(w.year[i].trueprice);
                    year_payment.push(w.year[i].pay_price);
                    year_true_payment.push(w.year[i].truepay);
                }

                count_price(week_time,week_receive,week_true_receive,week_payment,week_true_payment,month_time,month_receive,month_true_receive,month_payment,month_true_payment,day_time
                ,day_receive,day_true_receive,day_payment,day_true_payment,year_time,year_receive,year_true_receive,year_payment,year_true_payment);
            }

        })
    })


</script>

</body>
</html>