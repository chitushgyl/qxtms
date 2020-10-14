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
            <?php if(can('admin.account-number.add')){?>
                <a class="layui-btn layui-btn-sm" lay-event="add">添加</a>
            <?php } ?>

            <?php if(can('admin.account-number.view')){?>
                <a class="layui-btn  layui-btn-sm " lay-event="view">详情</a>
            <?php } ?>
            <?php if(can('admin.account-number.del')){?>
                <a class="layui-btn layui-btn-danger layui-btn-sm " lay-event="del">删除</a>
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
                ,url: "<?php echo route('admin.account-number.index'); ?>" //数据接口
                // ,response:{
                //     dataName:'list',
                //     countName:'counts'
                // }
                ,page: true //开启分页
                ,cols: [[ //表头
                    {field: 'id', title: 'ID',width:80, align:'center',hide:true}
                    ,{field: 'login', title: '账号', align:'center',width:200}
                    ,{field: 'group_name', title: '公司名称', align:'center',width:200}
                    ,{field: 'name', title: '登陆名称', align:'center',width:250}
                    ,{field: 'tel', title: '联系电话', align:'center',width:150}
                    ,{field: 'email', title: '邮箱', align:'center',width:200}
                    ,{field: 'balance', title: '余额', align:'center',width:100}
                    ,{field: 'use_flag', title: '状态', align:'center',width:100,toolbar: '#state'}
                    ,{field: 'create_time', title: '创建时间', align:'center',width:200}
                    ,{fixed: 'right', align:'center', toolbar: '#options',title: '操作'}
                ]]
            });

            //监听工具条
            table.on('tool(dataTable)', function(obj){ //注：tool是工具条事件名，dataTable是table原始容器的属性 lay-filter="对应的值"
                var data = obj.data //获得当前行数据
                    ,layEvent = obj.event; //获得 lay-event 对应的值
                if(layEvent === 'del'){
                    var id= data.id
                    layer.confirm('确认要刪除吗？', function(index){
                            $.ajax({
                                url: '/admin/account-number/del',
                                type: 'POST',
                                dataType: 'json',
                                data: {reason: val,id:id},
                            })
                                .done(function(res) {
                                    console.log("success");
                                    if(res.code == 2000){

                                        layer.msg('操作成功',{icon:1,time:2000});
                                        reload();
                                        layer.close(index);
                                    }else{
                                        layer.msg('操作失败',{icon:2,time:2000});
                                        layer.close(index);
                                    }

                                })
                                .fail(function() {
                                    layer.close(index);
                                })
                                .always(function() {
                                    layer.close(index);
                                });
                        });

                }else if(layEvent === 'add'){
                    location.href = '/admin/account-number/add';
                }else if(layEvent == 'edit'){
                    location.href = '/admin/account-number/edit?id='+ data.id;
                }else{
                    location.href = '/admin/account-number/view?id='+ data.id;
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
