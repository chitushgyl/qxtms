<?php
/**
 * Created by Joker.
 * Date: 2019/7/4
 * Time: 18:32
 */
?>
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
    </style>
</head>
<body>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md8">
            <div class="layui-row layui-col-space15">
                <div class="layui-col-md6">
                    <div class="layui-card">
                        <div class="layui-card-header">快捷方式</div>
                        <div class="layui-card-body">

                            <div class="layui-carousel layadmin-carousel layadmin-shortcut">
                                <div carousel-item>
                                    <ul class="layui-row layui-col-space10">
                                        <li class="layui-col-xs3">
                                            <a lay-href="<?php echo route('admin.withdraw.index'); ?>">
                                                <i class="layui-icon layui-icon-console"></i>
                                                <cite>提现管理</cite>
                                            </a>
                                        </li>
                                        <li class="layui-col-xs3">
                                            <a lay-href="<?php echo route('admin.app-ip-apply.index'); ?>">
                                                <i class="layui-icon layui-icon-chart"></i>
                                                <cite>域名申请</cite>
                                            </a>
                                        </li>
                                        <li class="layui-col-xs3">
                                            <a lay-href="<?php echo route('admin.vehical.index'); ?>">
                                                <i class="layui-icon layui-icon-template-1"></i>
                                                <cite>整车订单</cite>
                                            </a>
                                        </li>
                                        <li class="layui-col-xs3">
                                            <a lay-href="<?php echo route('admin.bulk.index'); ?>">
                                                <i class="layui-icon layui-icon-chat"></i>
                                                <cite>零担订单</cite>
                                            </a>
                                        </li>
                                        <li class="layui-col-xs3">
                                            <a lay-href="<?php echo route('admin.city.index'); ?>">
                                                <i class="layui-icon layui-icon-find-fill"></i>
                                                <cite>市配订单</cite>
                                            </a>
                                        </li>
                                        <li class="layui-col-xs3">
                                            <a lay-href="<?php echo route('admin.withdraw.index'); ?>">
                                                <i class="layui-icon layui-icon-survey"></i>
                                                <cite>工单</cite>
                                            </a>
                                        </li>
                                        <li class="layui-col-xs3">
                                            <a lay-href="<?php echo route('admin.account-number.index'); ?>">
                                                <i class="layui-icon layui-icon-user"></i>
                                                <cite>用户</cite>
                                            </a>
                                        </li>
                                    </ul>
<!--                                    <ul class="layui-row layui-col-space10">-->
<!---->
<!--                                    </ul>-->

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="layui-col-md6">
                    <div class="layui-card">
                        <div class="layui-card-header">数据概览</div>
                        <div class="layui-card-body">
                            <div class="layui-carousel layadmin-carousel layadmin-backlog">
                                <div carousel-item>
                                    <ul class="layui-row layui-col-space10">
                                        <li class="layui-col-xs6">
                                            <a href="#" class="layadmin-backlog-body">
                                                <h3>用户量</h3>
                                                <p><cite id="account">0</cite></p>
                                            </a>
                                        </li>
                                        <li class="layui-col-xs6">
                                            <a href="#" class="layadmin-backlog-body">
                                                <h3>交易额</h3>
                                                <p><cite id="payment">0</cite></p>
                                            </a>
                                        </li>
                                        <li class="layui-col-xs6">
                                            <a href="#" class="layadmin-backlog-body">
                                                <h3>车辆总数</h3>
                                                <p><cite id="carcount">0</cite></p>
                                            </a>
                                        </li>
<!--                                        <li class="layui-col-xs6">-->
<!--                                            <a href="javascript:;" onclick="layer.tips('不跳转', this, {tips: 3});" class="layadmin-backlog-body">-->
<!--                                                <h3>总重量</h3>-->
<!--                                                <p><cite>0</cite></p>-->
<!--                                            </a>-->
<!--                                        </li>-->
                                    </ul>
<!--                                    <ul class="layui-row layui-col-space10">-->
<!--                                        <li class="layui-col-xs6">-->
<!--                                            <a href="javascript:;" class="layadmin-backlog-body">-->
<!--                                                <h3>待审友情链接</h3>-->
<!--                                                <p><cite style="color: #FF5722;">5</cite></p>-->
<!--                                            </a>-->
<!--                                        </li>-->
<!--                                    </ul>-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="layui-col-md12">
<!--                    <div class="layui-card">-->
<!--                        <div class="layui-card-header">数据概览</div>-->
<!--                        <div class="layui-card-body">-->
<!---->
<!--                            <div class="layui-carousel layadmin-carousel layadmin-dataview" data-anim="fade" lay-filter="LAY-index-dataview">-->
<!--                                <div carousel-item id="LAY-index-dataview">-->
<!--                                    <div><i class="layui-icon layui-icon-loading1 layadmin-loading"></i></div>-->
<!--                                    <div></div>-->
<!--                                    <div></div>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
                    <div class="layui-card">
                        <div class="layui-tab layui-tab-brief layadmin-latestData">
                            <ul class="layui-tab-title">
                                <li class="layui-this">新增用户</li>
                                <li>新增整车订单</li>
                            </ul>
                            <div class="layui-tab-content">
                                <div class="layui-tab-item layui-show">
                                    <table id="accounttable"></table>
                                </div>
                                <div class="layui-tab-item">
                                    <table id="vehicaltable"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="layui-col-md4">
            <div class="layui-card">
                <div class="layui-card-header">关联站点</div>
                <div class="layui-card-body layui-text">
                    <table class="layui-table">
                        <colgroup>
                            <col width="100">
                            <col>
                        </colgroup>
                        <tbody>
                        <tr>
                            <td>系统</td>
                            <td>
<!--                                <script type="text/html" template>-->
<!--                                    <a href="http://fly.layui.com/docs/3/" target="_blank" style="padding-left: 10px;">日志</a>-->
<!--                                </script>-->
                                <a href="http://yun.56cold.com" layadmin-event="update" target="_blank" style="padding-left: 20px;">yun.56cold.com</a>
                            </td>
                        </tr>
                        <tr>
                            <td>官网</td>
                            <td>
                                <a href="http://www.56cold.com" layadmin-event="update" target="_blank" style="padding-left: 20px;">www.56cold.com</a>
                            </td>
                        </tr>
                        <tr>
                            <td>货主</td>
                            <td><a href="http://u.56cold.com" layadmin-event="update" target="_blank" style="padding-left: 20px;">u.56cold.com</a></td>
                        </tr>
                        <tr>
                            <td>市配</td>
                            <td style="padding-bottom: 0;">
                                <a href="http://city.56cold.com" layadmin-event="update" target="_blank" style="padding-left: 20px;">city.56cold.com</a>
<!--                                <div class="layui-btn-container">-->
<!--                                    <a href="http://city.56cold.com" target="_blank" class="layui-btn layui-btn-danger">city.56cold.com</a>-->
<!--                                    <a href="http://fly.layui.com/download/layuiAdmin/" target="_blank" class="layui-btn">立即下载</a>-->
<!--                                </div>-->
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="layui-card">
                <div class="layui-card-header">日交易量</div>
                <div class="layui-card-body layadmin-takerates">
                    <div class="layui-progress" lay-showPercent="yes">
                        <h3>同比率（日同比 <span id="day_pant">0</span>% <span class="layui-edge layui-edge-top" lay-tips="增长" lay-offset="-15"></span>）</h3>
                        <div id="pant_attr" class="layui-progress-bar" lay-percent="0"></div>
                    </div>
                    <div class="layui-progress" lay-showPercent="yes">
                        <h3>环比率（周同比 11% <span class="layui-edge layui-edge-bottom" lay-tips="下降" lay-offset="-15"></span>）</h3>
                        <div class="layui-progress-bar" lay-percent="32%"></div>
                    </div>
                </div>
            </div>

            <div class="layui-card">
                <div class="layui-card-header">订单数</div>
                <div class="layui-card-body layadmin-takerates">
                    <div class="layui-progress" lay-showPercent="yes">
                        <h3>整车订单</h3>
                        <div id="vehical" class="layui-progress-bar" lay-percent="0"></div>
                    </div>
                    <div class="layui-progress" lay-showPercent="yes">
                        <h3>零担订单</h3>
                        <div id="bulk" class="layui-progress-bar layui-bg-red" lay-percent="0"></div>
                    </div>
                    <div class="layui-progress" lay-showPercent="yes">
                        <h3>市配订单</h3>
                        <div id="city" class="layui-progress-bar layui-bg-red" lay-percent="0"></div>
                    </div>
                </div>
            </div>

<!--            <div class="layui-card">-->
<!--                <div class="layui-card-header">产品动态</div>-->
<!--                <div class="layui-card-body">-->
<!--                    <div class="layui-carousel layadmin-carousel layadmin-news" data-autoplay="true" data-anim="fade" lay-filter="news">-->
<!--                        <div carousel-item>-->
<!--                            <div><a href="http://fly.layui.com/docs/2/" target="_blank" class="layui-bg-red">layuiAdmin 快速上手文档</a></div>-->
<!--                            <div><a href="http://fly.layui.com/vipclub/list/layuiadmin/" target="_blank" class="layui-bg-green">layuiAdmin 会员讨论专区</a></div>-->
<!--                            <div><a href="http://www.layui.com/admin/#get" target="_blank" class="layui-bg-blue">获得 layui 官方后台模板系统</a></div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->

<!--            <div class="layui-card">-->
<!--                <div class="layui-card-header">-->
<!--                    作者心语-->
<!--                    <i class="layui-icon layui-icon-tips" lay-tips="要支持的噢" lay-offset="5"></i>-->
<!--                </div>-->
<!--                <div class="layui-card-body layui-text layadmin-text">-->
<!--                    <p>一直以来，layui 秉承无偿开源的初心，虔诚致力于服务各层次前后端 Web 开发者，在商业横飞的当今时代，这一信念从未动摇。即便身单力薄，仍然重拾决心，埋头造轮，以尽可能地填补产品本身的缺口。</p>-->
<!--                    <p>在过去的一段的时间，我一直在寻求持久之道，已维持你眼前所见的一切。而 layuiAdmin 是我们尝试解决的手段之一。我相信真正有爱于 layui 生态的你，定然不会错过这一拥抱吧。</p>-->
<!--                    <p>子曰：君子不用防，小人防不住。请务必通过官网正规渠道，获得 <a href="http://www.layui.com/admin/" target="_blank">layuiAdmin</a>！</p>-->
<!--                    <p>—— 贤心（<a href="http://www.layui.com/" target="_blank">layui.com</a>）</p>-->
<!--                </div>-->
<!--            </div>-->
        </div>

    </div>
</div>

<script src="/static/admin/layuiadmin/layui/layui.js?t=1"></script>
<script src="/js/jquery.min.js"></script>
<script type="text/html" id="table_startcity_order">
    <div class="table-index-content">
        <span class="t-startcity">{{d.startcity}}</span>
        ->
        <span class="t-endcity">{{d.endcity}}</span>
    </div>
</script>
<script>
    layui.config({
        base: '/static/admin/layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'console']);
   // console.log(JSON.parse(window.parent.data).car_count);

    //赋值
    var  data = JSON.parse(window.parent.data);
    var  list = JSON.parse(window.parent.list);
    var pant = window.parent.pant + '%';
    $('#account').text(data.account_count);
    $('#carcount').text(data.car_count);
    $('#payment').text(data.receive_count);
    $('#day_pant').text(data.pant);
    // console.log(pant);
    var shopping = document.getElementById("pant_attr");
    shopping.setAttribute("lay-percent",pant);

    var vehical = document.getElementById("vehical");
    vehical.setAttribute("lay-percent",list.vehical);

    var bulk = document.getElementById("bulk");
    bulk.setAttribute("lay-percent",list.bulk);

    var city = document.getElementById("city");
    city.setAttribute("lay-percent",list.city);
    layui.use(['layer','table','form'],function () {
        var layer = layui.layer;
        var form = layui.form;
        var table = layui.table;

        //用户表格初始化
        var dataTable = table.render({
            elem: '#accounttable'
            ,height: 800
            ,url: "<?php echo route('admin.account-number.new_index'); ?>" //数据接口
            // ,response:{
            //     dataName:'list',
            //     countName:'counts'
            // }
            ,page: true //开启分页
            ,cols: [[ //表头
                {field: 'id', title: 'ID',width:80, align:'center',hide:true}
                ,{field: 'login', title: '账号', align:'center'}
                ,{field: 'group_name', title: '公司名称', align:'center'}
                ,{field: 'name', title: '登陆名称', align:'center'}
                ,{field: 'tel', title: '联系电话', align:'center'}
                ,{field: 'email', title: '邮箱', align:'center'}
                ,{field: 'create_time', title: '创建时间', align:'center'}
            ]]
        });

        var dataTable = table.render({
            elem: '#vehicaltable'
            ,height: 800
            ,url: "<?php echo route('admin.vehical.new_index'); ?>" //数据接口
            // ,response:{
            //     dataName:'list',
            //     countName:'counts'
            // }
            ,page: true //开启分页
            ,cols: [[ //表头
                {field: 'startcity', align:'left',title: '起始地',toolbar: '#table_startcity_order'}
                ,{field: 'ordernumber', align:'left',title: '订单号'}
                ,{field: 'group_name', align:'left',title: '归属公司'}
                ,{field: 'carparame', align:'left',title: '预约车辆'}
                ,{field: 'temperture', align:'left',title: '温度'}
                ,{field: 'name', align:'left',title: '货物名称'}
                ,{field: 'time_start', align:'left',title: '装车时间'}
                ,{field: 'line_price', align:'left',title: '费用(元)'}
            ]]
        });

    })
</script>

</body>
</html>

