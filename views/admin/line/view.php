<?php
/**
 * Created by pysh.
 * Date: 2020/2/2
 * Time: 09:46
 */
echo \Yii::$app->view->renderFile('@app/views/admin/base.php');
?>

<div class="view-top">
    <button class="layui-btn" onclick="window.history.go(-1);">返回</button>
</div>
<table class="layui-table" lay-skin="line" lay-size="lg">
    <colgroup>
        <col width="200">
        <col>
    </colgroup>
    <thead>
    </thead>
    <tbody>

    </tbody>
</table>

<script type="text/javascript">
    layui.use(['jquery'],function () {
        var $ = layui.jquery;
        var data = [
            {"key":"起始地","value":"<?php echo $model['startcity'];?>"}
            ,{"key":"目的地","value":"<?php echo $model['endcity'];?>"}
            ,{"key":"班次号","value":"<?php echo $model['shiftnumber'];?>"}
            ,{"key":"归属公司","value":"<?php echo $model['company_name'];?>"}
            ,{"key":"承运公司","value":"<?php echo $model['name'];?>"}
            //,{"key":"验车日期","value":"<?php //echo $model['check_time'];?>//"}
            //,{"key":"归属公司","value":"<?php //echo $model['company_name'];?>//"}
            //,{"key":"申请状态","value":"<?php //if($model['status'] == 1){ echo '认证中';}elseif($model['status'] ==2){ echo '成功';}else{ echo '失败';};?>//"}
            ,{"key":"添加时间","value":"<?php echo $model['create_time'];?>"}
        ];
        var node = viewData(data);
        $('.layui-table tbody').empty().append(node);

    });
</script>