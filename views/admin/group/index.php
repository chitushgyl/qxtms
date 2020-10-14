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
                <input class="layui-input" name="keyword" id="keyword" autocomplete="off" placeholder="请输入公司名称" style="width: 320px">
            </div>
            <button class=" layui-btn layui-btn-normal" data-type="reload" id="searchBtn" style="margin-left: 40px">搜索</button>
            <!--            <button class=" layui-btn layui-btn-normal" data-type="add" id="add" style="margin-left: 100px;float:right">添加</button>-->
        </div>



        <div class="layui-card-body">
            <table id="dataTable" lay-filter="dataTable"></table>
        </div>
    </div>

    <script type="text/html" id="state">
        {{# if(d.use_flag == 'Y'){ }}
        <a class="layui-btn layui-btn-sm">启用</a>
        {{# }else{ }}
        <a class="layui-btn layui-btn-danger layui-btn-sm" >禁用</a>
        {{# } }}
    </script>

    <script type="text/html" id="options">
        <div class="layui-btn-group">
            <?php if(can('admin.group.view')){?>
                <a class="layui-btn  layui-btn-sm " lay-event="view">详情</a>
            <?php } ?>
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
                ,url: "<?php echo route('admin.group.index'); ?>" //数据接口
                // ,response:{
                //     dataName:'list',
                //     countName:'counts'
                // }
                ,page: true //开启分页
                ,cols: [[ //表头
                    {field: 'group_name', align:'center',title: '公司名称'}
                    ,{field: 'name', align:'center',title: '联系人'}
                    ,{field: 'tel', align:'center',title: '联系电话'}
                    ,{field: 'use_flag', align:'center',title: '状态', toolbar: '#state'}
                    ,{field: 'update_time', align:'center',title: '更新时间'}
                    ,{fixed: 'right',align:'center', toolbar: '#options',title: '操作'}
                ]]
            });

            //监听工具条
            table.on('tool(dataTable)', function(obj){ //注：tool是工具条事件名，dataTable是table原始容器的属性 lay-filter="对应的值"
                var data = obj.data //获得当前行数据
                    ,layEvent = obj.event; //获得 lay-event 对应的值
                if(layEvent === 'view'){
                    location.href = '/admin/group/view?id='+ data.id;
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
