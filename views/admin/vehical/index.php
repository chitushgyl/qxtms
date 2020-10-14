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
                <input class="layui-input" name="keyword" id="keyword" autocomplete="off" placeholder="请输入订单号/支付宝账户/姓名/归属公司" style="width: 320px">
            </div>

            <button class=" layui-btn layui-btn-normal" data-type="reload" id="searchBtn" style="margin-left: 40px">搜索</button>
        </div>

        <div class="layui-card-body">
            <table id="dataTable" lay-filter="dataTable"></table>
        </div>
    </div>
    <script type="text/html" id="table_startcity_order">
        <div class="table-index-content">
            <span class="t-startcity">{{d.startcity}}</span>
            ->
            <span class="t-endcity">{{d.endcity}}</span>
        </div>
    </script>
    <script type="text/html" id="table_line_price">
        <div class="table-index-content">
            {{# if(d.line_status == 2){ }}
            {{# if(d.line_price){ }}
            <span class="t-line_price">{{d.line_price}}</span>
            {{# }else{ }}
            <span class="t-line_price">电议</span>
            {{# } }}
            {{# }else if(d.line_status == 1){ }}
            {{# if(d.total_price){ }}
            <span class="t-line_price">{{d.total_price}}</span>
            {{# }else{ }}
            <span class="t-line_price">电议</span>
            {{# } }}
            {{# } }}
        </div>
    </script>

    <script type="text/html" id="table_ordernumber_order">
        <div class="table-index-content"><span class="t-ordernumber">{{d.ordernumber}}</span></div>
    </script>

    <script type="text/html" id="table_order_status_vehical">
        <div class="table-index-content">
            {{# if(d.line_status == 1){ }}
            <button class="layui-btn layui-btn-xs layui-btn-warm">内部</button>
            {{# }else{ }}
            <button class="layui-btn layui-btn-xs">上线</button>
            {{# } }}

            {{# if(d.order_type ==1 || d.order_type ==3 || d.order_type ==5 || d.order_type ==8 || d.order_type ==11){ }}
            <button class="layui-btn layui-btn-xs">整车</button>
            {{# } else{ }}
            <button class="layui-btn layui-btn-xs layui-btn-normal">零担</button>
            {{# } }}

            {{# if(d.order_status ==1){ }}
            {{# if(d.order_type ==3){ }}
            <button class="layui-btn layui-btn-xs layui-btn-danger">待确认</button>
            {{# }else{ }}
            <button class="layui-btn layui-btn-xs layui-btn-danger">待调度</button>
            {{# } }}
            {{# } else if(d.order_status ==5){ }}
            <button class="layui-btn layui-btn-xs">已送达</button>
            {{# } else if(d.order_status ==4){ }}
            <button class="layui-btn layui-btn-xs layui-btn-normal">运输中</button>
            {{# } else if(d.order_status ==3){ }}
            <button class="layui-btn layui-btn-xs layui-btn-warm">已调度</button>
            {{# } else if(d.order_status ==2){ }}
            <button class="layui-btn layui-btn-xs layui-btn-warm">已接单</button>
            {{# } else if(d.order_status ==6){ }}
            <button class="layui-btn layui-btn-xs">已完成</button>
            {{# } else if(d.order_status ==7){ }}
            <button class="layui-btn layui-btn-xs layui-btn-disabled">已超时</button>
            {{# } else if(d.order_status ==8){ }}
            <button class="layui-btn layui-btn-xs layui-btn-disabled">已取消</button>
            {{# } }}
            {{# if(d.carriage_id){ }}
            {{# if(d.carriage_status ==1){ }}
            <button class="layui-btn layui-btn-xs layui-btn-danger">承运待确认</button>
            {{# }else{ }}
            <button class="layui-btn layui-btn-xs">承运已确认</button>
            {{# } }}
            {{# } }}
        </div>
    </script>

    <script type="text/html" id="table_name_order">
        <div class="table-index-content">
            <span class="t-name">{{d.name}}</span>
        </div>
    </script>

    <script type="text/html" id="table_temperture_order">
        <div class="table-index-content">
            <span class="t-temperture">{{d.temperture}}</span>
        </div>
    </script>

    <script type="text/html" id="table_cartype_vehical">
        <div class="table-index-content">
            <span class="t-carparame">{{d.carparame}}</span>
        </div>

    </script>

    <script type="text/html" id="table_time_start_vehical">
        <div class="table-index-content">
            <span class="t-time_start">{{d.time_start}}</span>
        </div>
    </script>

    <script type="text/html" id="options_right_vehical">
        <div class="layui-btn-group">
            <a class="layui-btn layui-btn-sm layui-btn-normal" lay-event="view" data="/vehical/view" data-a="/vehical/view">详情</a>

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
                ,url: "<?php echo route('admin.vehical.index'); ?>" //数据接口
                // ,response:{
                //     dataName:'list',
                //     countName:'counts'
                // }
                ,page: true //开启分页
                ,cols: [[ //表头
                    {field: 'startcity', align:'left',title: '起始地',toolbar: '#table_startcity_order',width:180}
                    ,{field: 'ordernumber', align:'left',title: '订单号',width:160}
                    ,{field: 'group_name', align:'left',title: '归属公司',width:200}
                    ,{field: 'order_status', align:'left',title: '订单状态',toolbar: '#table_order_status_vehical',width:160}
                    ,{field: 'cartype', align:'left',title: '预约车辆',toolbar: '#table_cartype_vehical',width:80}
                    ,{field: 'temperture', align:'left',title: '温度',toolbar: '#table_temperture_order',width:110}
                    ,{field: 'name', align:'left',title: '货物名称',toolbar: '#table_name_order',width:100}
                    ,{field: 'time_start', align:'left',title: '装车时间',toolbar: '#table_time_start_vehical',width:150}
                    ,{field: 'line_price', align:'left',title: '费用(元)',toolbar: '#table_line_price',width:100}
                    ,{field: 'update_time',align:'left', title: '操作',toolbar: '#options_right_vehical',fixed:'right'}
                ]]
            });

            //监听工具条
            table.on('tool(dataTable)', function(obj){ //注：tool是工具条事件名，dataTable是table原始容器的属性 lay-filter="对应的值"
                var data = obj.data //获得当前行数据
                    ,layEvent = obj.event; //获得 lay-event 对应的值
                if(layEvent === 'view'){
                    location.href = '/admin/vehical/view?id='+data.id;
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
