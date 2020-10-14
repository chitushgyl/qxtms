<?php
/**
 * Created by pysh.
 * Date: 2020/2/2
 * Time: 17:46
 */
echo \Yii::$app->view->renderFile('@app/views/admin/base.php');
?>

    <style type="text/css">
        .layui-table-cell {
            height: auto;
            line-height: 40px;
        }
    </style>
    <div class="layui-card">
        <div class="layui-card-header layuiadmin-card-header-auto" >
            <span style="margin-right: 10px;"> 搜索 </span>
            <div class="layui-inline">
                <input class="layui-input" name="keyword" id="keyword" autocomplete="off" placeholder="请输入订单号/归属公司" style="width: 320px">
            </div>

            <button class=" layui-btn layui-btn-normal" data-type="reload" id="searchBtn" style="margin-left: 40px">搜索</button>
        </div>

        <div class="layui-card-body">
            <table id="dataTable" lay-filter="dataTable"></table>
        </div>
    </div>
    <script type="text/html" id="table_startcity">
        <div class="table-index-content">
            <span class="t-startcity">{{d.begincity}}</span>
            ->
            <span class="t-endcity">{{d.endcity}}</span>
        </div>
    </script>

    <script type="text/html" id="table_total_price">
        <div class="table-index-content">
            {{d.total_price}}
        </div>
    </script>
    <script type="text/html" id="table_goodsname">
        <div class="table-index-content">
            {{d.goodsname}}
        </div>
    </script>
    <script type="text/html" id="table_group_name">
        <div class="table-index-content">
            {{d.group_name}}
        </div>
    </script>

    <script type="text/html" id="table_line_type">
        <div class="table-index-content">
            {{# if(d.line_type == 2) { }}
            <button class="layui-btn layui-btn-xs layui-btn-danger">外部</button>
            {{# }else if(d.line_type == 1){ }}
            <button class="layui-btn layui-btn-xs layui-btn-warm">内部</button>
            {{# } }}
            {{# if(d.line_type == 3){ }}
            <button class="layui-btn layui-btn-xs">客户下单</button>
            {{# } }}
            {{# if(d.paystate == 2) { }}
            <button class="layui-btn layui-btn-xs">已支付</button>
            {{# } else if(d.paystate == 1){ }}
            <button class="layui-btn layui-btn-xs layui-btn-danger">待支付</button>
            {{# } }}
            {{# if(d.orderstate == 2 || d.orderstate == 1) { }}
            {{# if(d.line_type == 3){ }}
            {{# if(d.orderstate == 1){ }}
            <button class="layui-btn layui-btn-xs layui-btn-danger">待确认</button>
            {{# }else{ }}
            <button class="layui-btn layui-btn-xs" data="1">待运输</button>
            {{# } }}
            {{# }else { }}
            <button class="layui-btn layui-btn-xs" data="2">待运输</button>
            {{# } }}
            {{# } else if(d.orderstate == 3){ }}
            <button class="layui-btn layui-btn-xs">运输中</button>
            {{# } else if(d.orderstate == 4){ }}
            <button class="layui-btn layui-btn-xs">已送达</button>
            {{# } else if(d.orderstate == 5){ }}
            <button class="layui-btn layui-btn-xs">已完成</button>
            {{# } else if(d.orderstate == 6){ }}
            <button class="layui-btn layui-btn-xs layui-btn-disabled">已取消</button>
            {{# } else if(d.orderstate == 8){ }}
            <button class="layui-btn layui-btn-xs layui-btn-disabled">已超时</button>
            {{# } }}
        </div>
    </script>

    <script type="text/html" id="table_picktype">
        <div class="table-index-content">
            {{# if(d.picktype == 1){ }}
            <span style="color:red;">有</span>
            {{# } else { }}
            <span style="">无</span>
            {{# } }}
        </div>
    </script>

    <script type="text/html" id="table_sendtype">
        <div class="table-index-content">
            {{# if(d.sendtype == 1){ }}
            <span style="color:red;">有</span>
            {{# } else { }}
            <span style="">无</span>
            {{# } }}
        </div>
        </div>
    </script>

    <script type="text/html" id="table_bulk_take">
        <div class="table-index-content">
            <span class="t-startcity"></span>
            ->
            <span class="t-endcity"></span>
        </div>
        <div class="table-index-content">
            发车时间：<span class="t-start_time"></span>
        </div>
        <div class="table-index-content">
            时效：<span class="t-trunking"></span>天
        </div>
        <div class="table-index-content">
            下单时间：<span class="t-create_time">{{d.create_time}}</span>
        </div>
        <div class="table-index-content">
            订单：
            {{# if(d.line_type == 2) { }}
            <button class="layui-btn layui-btn-xs layui-btn-danger">外部</button>
            {{# }else if(d.line_type == 1){ }}
            <button class="layui-btn layui-btn-xs layui-btn-warm">内部</button>
            {{# } }}

            {{# if(d.line_type == 3){ }}
            <button class="layui-btn layui-btn-xs">客户下单</button>
            {{# } }}

            {{# if(d.paystate == 2) { }}
            <button class="layui-btn layui-btn-xs">已支付</button>
            {{# } else if(d.paystate == 1){ }}
            <button class="layui-btn layui-btn-xs layui-btn-danger">待支付</button>
            {{# } }}

            {{# if(d.orderstate == 2 || d.orderstate == 1) { }}
            {{# if(d.line_type == 3){ }}
            {{# if(d.orderstate == 1){ }}
            <button class="layui-btn layui-btn-xs layui-btn-danger">待确认</button>
            {{# }else{ }}
            <button class="layui-btn layui-btn-xs" data="1">待运输</button>
            {{# } }}
            {{# }else { }}
            <button class="layui-btn layui-btn-xs" data="2">待运输</button>
            {{# } }}
            {{# } else if(d.orderstate == 3){ }}
            <button class="layui-btn layui-btn-xs">运输中</button>
            {{# } else if(d.orderstate == 4){ }}
            <button class="layui-btn layui-btn-xs">已送达</button>
            {{# } else if(d.orderstate == 5){ }}
            <button class="layui-btn layui-btn-xs">已完成</button>
            {{# } else if(d.orderstate == 6){ }}
            <button class="layui-btn layui-btn-xs layui-btn-disabled">已取消</button>
            {{# } else if(d.orderstate == 8){ }}
            <button class="layui-btn layui-btn-xs layui-btn-disabled">已超时</button>
            {{# } }}
        </div>
    </script>

    <script type="text/html" id="table_bulk_take_picktype">
        <div class="table-index-content">
            提货服务：
            {{# if(d.picktype == 1){ }}
            <span style="color:red;">有</span>
            {{# } else { }}
            <span style="">无</span>
            {{# } }}
        </div>
        <div>
            {{# if(d.picktype == 1){ }}
            提货费：
            <span style="color:red;">{{d.pickprice}}元</span>
            {{# } }}
        </div>
        <div>
            {{# if(d.picktype == 1){ }}
            {{# for(var i in d.begin_info) { }}
            <div>{{d.begin_info[i].pro}}{{d.begin_info[i].city}}{{d.begin_info[i].area}}</div>
            <div>{{d.begin_info[i].info}}</div>
            <div>{{d.begin_info[i].contant}} &nbsp;&nbsp;{{d.begin_info[i].tel}}</div>
            {{# } }}
            {{# } }}
        </div>
        <hr>
        <div class="table-index-content">
            送货服务：
            {{# if(d.sendtype == 1){ }}
            <span style="color:red;">有</span>
            {{# } else { }}
            <span style="">无</span>
            {{# } }}
        </div>
        <div>
            {{# if(d.sendtype == 1){ }}
            送货费：
            <span style="color:red;">{{d.sendprice}}元</span>
            {{# } }}
        </div>
        <div>
            {{# if(d.sendtype == 1){ }}
            {{# for(var i in d.end_info) { }}
            <div>{{d.end_info[i].pro}}{{d.end_info[i].city}}{{d.end_info[i].area}}</div>
            <div>{{d.end_info[i].info}}</div>
            <div>{{d.end_info[i].contant}} &nbsp;&nbsp;{{d.end_info[i].tel}}</div>
            {{# } }}
            {{# } }}
        </div>
    </script>

    <script type="text/html" id="options_right_bulk_take">
        <div class="layui-btn-group">
            <a class="layui-btn layui-btn-sm" lay-event="view" data="/bulk/view" >详情</a>
        </div>
    </script>
    <script>
        layui.use(['layer','table','form'],function () {
            var layer = layui.layer;
            var form = layui.form;
            var table = layui.table;

            //用户表格初始化
            var dataTable = table.render({
                elem: '#dataTable'
                ,height: 800
                ,url: "<?php echo route('admin.bulk.index'); ?>" //数据接口
                // ,response:{
                //     dataName:'list',
                //     countName:'counts'
                // }
                ,page: true //开启分页
                ,cols: [[ //表头
                    {field: 'endcity', align:'left',title: '线路信息', toolbar: '#table_startcity',width:160}
                    ,{field: 'goodsname', align:'left',title: '货物名称', toolbar: '#table_goodsname',width:150}
                    ,{field: 'total_price', align:'left',title: '费用', toolbar: '#table_total_price',width:100}
                    ,{field: 'group_name', align:'left',title: '归属公司', toolbar: '#table_group_name',width:150}
                    ,{field: 'picktype', align:'left',title: '提货服务', toolbar: '#table_picktype',width:100}
                    ,{field: 'sendtype', align:'left',title: '配送服务', toolbar: '#table_sendtype',width:100}
                    ,{field: 'line_type', align:'left',title: '状态', toolbar: '#table_line_type',width:200}
                    ,{field: 'update_time',align:'center', toolbar: '#options_right_bulk_take',title: '操作'}
                ]]
            });

            //监听工具条
            table.on('tool(dataTable)', function(obj){ //注：tool是工具条事件名，dataTable是table原始容器的属性 lay-filter="对应的值"
                var data = obj.data //获得当前行数据
                    ,layEvent = obj.event; //获得 lay-event 对应的值
                if(layEvent === 'view'){
                    location.href = '/admin/bulk/view?id='+data.id;
                }
            });
            function reload(){
                var keyword = $("#keyword").val()
                dataTable.reload({
                    where:{keyword:keyword},
                    page:{curr:1}
                });
            }
            //搜索
            $("#searchBtn").click(function () {
                var keyword = $("#keyword").val()
                dataTable.reload({
                    where:{keyword:keyword},
                    page:{curr:1}
                });

            })
        })
    </script>
<?php

