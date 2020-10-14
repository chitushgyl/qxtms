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
    <script type="text/html" id="table_id">
        <input type="checkbox" name="car_index_id" value="{{d.id}}" class="interest" >
    </script>

    <script type="text/html" id="table_line_city">
        <div class="table-index-content">
            <span class="t-startcity">{{d.startcity}}</span>
            ->
            {{# if(d.centercity){ }}
            <span class="t-centercity">{{d.endcity}}</span>
            ->
            {{# } }}
            <span class="t-endcity">{{d.endcity}}</span>
        </div>

    </script>

    <script type="text/html" id="table_line_picktype">
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
    </script>

    <script type="text/html" id="table_line_weight_price">
        {{# if(d.set_price) { }}
        {{# for(var i in d.set_price) { }}
        <div style="float:left;width:50px;">
            <div style="text-align:center;color:red;">￥{{d.set_price[i].price}}</div>
            <div style="height:8px;width:100%;border-bottom:3px solid #1090F5;border-left:3px solid #1090F5;"></div>
            <div style="width:100%;" class="set_price_max_parent">
                {{# if (i == 0){ }}
                <span>{{d.set_price[i].min}}</span>
                {{# } }}

                {{# if ((d.set_price.length - 1) == i){ }}
                <span class="set_price_max"></span>
                {{# }else{ }}
                <span class="set_price_max">{{d.set_price[i].max}}</span>
                {{# } }}

            </div>
        </div>
        {{# } }}
        {{# } }}
    </script>

    <script type="text/html" id="table_line_line_state">
        {{# if(d.line_state == 1) { }}
        <button class="layui-btn layui-btn-xs layui-btn-warm">未上线</button>
        {{# } else if(d.line_state == 2){ }}
        <button class="layui-btn layui-btn-xs layui-btn-danger">已上线</button>
        {{# } }}

        {{# if(d.state == 1) { }}
        <button class="layui-btn layui-btn-xs layui-btn-danger">未发车</button>
        {{# } else if(d.state == 2){ }}
        <button class="layui-btn layui-btn-xs layui-btn-normal">已发车</button>
        {{# } else if(d.state == 3){ }}
        <button class="layui-btn layui-btn-xs">已完成</button>
        {{# } else if(d.state == 4){ }}
        <button class="layui-btn layui-btn-xs layui-btn-disabled">已取消</button>
        {{# } else if(d.state == 5){ }}
        <button class="layui-btn layui-btn-xs layui-btn-disabled">已过期</button>
        {{# } }}
    </script>

    <script type="text/html" id="options_right_vehical">
        <div class="layui-btn-group">
            <a class="layui-btn layui-btn-sm" lay-event="view" data="/line/view" >详情</a>
        </div>
    </script>
<!--    <script type="text/html" id="table_line_line_count">-->
<!--        <div>-->
<!--            <button class="layui-btn layui-btn-xs layui-btn-danger">{{d.count}}个</button>-->
<!--        </div>-->
<!--    </script>-->
    <script>
        layui.use(['layer','table','form'],function () {
            var layer = layui.layer;
            var form = layui.form;
            var table = layui.table;

            //用户表格初始化
            var dataTable = table.render({
                elem: '#dataTable'
                ,height: 800
                ,url: "<?php echo route('admin.line.index'); ?>" //数据接口
                // ,response:{
                //     dataName:'list',
                //     countName:'counts'
                // }
                ,page: true //开启分页
                ,cols: [[ //表头
                    {field: 'startcity', align:'left',title: '线路信息', toolbar: '#table_line_city',width:180}
                    ,{field: 'shiftnumber', align:'left',title: '班次号',width:140}
                    ,{field: 'company_name', align:'left',title: '所属公司',width:160}
                    ,{field: 'name', align:'left',title: '承运公司',width:160}
                    ,{field: 'start_time', align:'left',title: '发车时间',width:160}
                    ,{field: 'trunking', align:'left',title: '时效(天)',width:100}
                    ,{field: 'line_price', align:'left',title: '最低干线费',width:100}
                    ,{field: 'line_state', align:'left',title: '状态', toolbar: '#table_line_line_state',width:200}
                    ,{field: 'update_time',align:'left', toolbar: '#options_right_vehical',title: '操作',fixed:'right'}
                ]]
            });

            //监听工具条
            table.on('tool(dataTable)', function(obj){ //注：tool是工具条事件名，dataTable是table原始容器的属性 lay-filter="对应的值"
                var data = obj.data //获得当前行数据
                    ,layEvent = obj.event; //获得 lay-event 对应的值
                if(layEvent === 'view'){
                    location.href = '/admin/line/view?id='+data.id;
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

