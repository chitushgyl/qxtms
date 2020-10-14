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
    <script type="text/html" id="table_id_order">
        <input type="checkbox" name="vehical_index_id" value="{{d.id}}" class="interest">
        <input type="hidden" name="" class="t-startstr" value="{{d.startstr}}">
        <input type="hidden" name="" class="t-endstr" value="{{d.endstr}}">
    </script>

    <script type="text/html" id="table_ordernumber_order">
        <div class="table-index-content"><span class="t-ordernumber">{{d.ordernumber}}</span></div>
    </script>

    <script type="text/html" id="table_line_price">
        <div class="table-index-content">
            {{# if(d.total_price){ }}
            <span class="t-total_price">{{d.total_price}}</span>
            {{# }else{ }}
            <span class="t-total_price">电议</span>
            {{# } }}
        </div>
    </script>

    <script type="text/html" id="table_startcity_order">
        <div class="table-index-content">
            <span class="t-startcity">{{d.startcity}}</span>
            ->
            <span class="t-endcity">{{d.endcity}}</span>
        </div>
    </script>

    <script type="text/html" id="table_delivery_time">
        <div class="table-index-content">
            <span class="t-delivery_time">{{d.delivery_time}}</span>
        </div>
    </script>

    <script type="text/html" id="table_receive_time">
        <div class="table-index-content">
            <span class="t-receive_time">{{d.receive_time}}</span>
        </div>
    </script>

    <script type="text/html" id="table_name_order">
        <div class="table-index-content">
            <span class="t-goodsname">{{d.goodsname}}</span>
        </div>
    </script>

    <script type="text/html" id="table_number">
        <div class="table-index-content">
            {{# if(d.weight) { }}
            <span class="t-weight">{{d.weight}}&nbsp;kg&nbsp;&nbsp;</span>
            {{# } }}

            {{# if(d.volume) { }}
            <span class="t-volume">{{d.volume}}&nbsp;m<sup>3</sup></span>
            {{# } }}
        </div>
    </script>

    <script type="text/html" id="table_temperture_order">
        <div class="table-index-content">
            <span class="t-temperture">{{d.temperture}}</span>
        </div>
    </script>
    <script type="text/html" id="table_group_name">
        <div class="table-index-content">
            {{d.group_name}}
        </div>
    </script>
    <script type="text/html" id="table_order_state">
        <div class="table-index-content">
            {{# if(d.order_state ==1){ }}
            <button class="layui-btn layui-btn-xs layui-btn-danger">待确认</button>
            {{# } else if(d.order_state ==2){ }}
            <button class="layui-btn layui-btn-xs layui-btn-normal">已确认</button>
            {{# } else if(d.order_state ==3){ }}
            <button class="layui-btn layui-btn-xs layui-btn-normal">进行中</button>
            {{# } else if(d.order_state ==4){ }}
            <button class="layui-btn layui-btn-xs">已完成</button>
            {{# } else if(d.order_state ==5){ }}
            <button class="layui-btn layui-btn-xs layui-btn-disabled">已取消</button>
            {{# } }}
        </div>
    </script>

    <script type="text/html" id="table_begin_store">
        <div class="table-index-content">
            {{d.begin_store[0].areaName}}
        </div>
    </script>

    <script type="text/html" id="options_right_vehical_tool">
        <div class="layui-btn-group">
            <a class="layui-btn layui-btn-sm layui-btn-normal" lay-event="view" data="/city/view" >详情</a>
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
                ,url: "<?php echo route('admin.city.index'); ?>" //数据接口
                // ,response:{
                //     dataName:'list',
                //     countName:'counts'
                // }
                ,page: true //开启分页
                ,cols: [[ //表头
                    {field: 'begin_store', align:'left',title: '装货地', toolbar: '#table_begin_store', width:180},
                    {field: 'ordernumber', align:'left',title: '订单号', toolbar: '#table_ordernumber_order', width:150}
                    ,{field: 'group_name', align:'left',title: '归属公司', toolbar: '#table_group_name',width:150}
                    ,{field: 'delivery_time', align:'left',title: '发货日期', toolbar: '#table_delivery_time', width:150}
                    ,{field: 'receive_time', align:'left',title: '交货日期', toolbar: '#table_receive_time', width:150}
                    ,{field: 'temperture', align:'left',title: '温度', toolbar: '#table_temperture_order',width:110}
                    ,{field: 'goodsname', align:'left',title: '货物名称', toolbar: '#table_name_order',width:150}
                    ,{field: 'number', align:'left',title: '货物信息', toolbar: '#table_number',width:170}
                    ,{field: 'total_price', align:'left',title: '总费用(元)', toolbar: '#table_line_price', width:100}
                    ,{field: 'order_state', align:'left',title: '状态', toolbar: '#table_order_state', width:100}
                    ,{field: 'update_time',align:'left', toolbar: '#options_right_vehical_tool',title: '操作',fixed:'right'}
                ]]
            });

            //监听工具条
            table.on('tool(dataTable)', function(obj){ //注：tool是工具条事件名，dataTable是table原始容器的属性 lay-filter="对应的值"
                var data = obj.data //获得当前行数据
                    ,layEvent = obj.event; //获得 lay-event 对应的值
                if(layEvent === 'view'){
                    location.href = '/admin/city/view?id='+data.id;
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
