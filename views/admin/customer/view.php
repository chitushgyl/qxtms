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
            {"key":"客户公司名称","value":"<?php echo $model['all_name'];?>"}
            ,{"key":"归属公司","value":"<?php echo $model['company_name'];?>"}
            ,{"key":"联系人","value":"<?php echo $model['contact_name'];?>"}
            ,{"key":"联系电话","value":"<?php echo $model['contact_tel'];?>"}
            ,{"key":"结算方式","value":"<?php if($model['paystate'] == 1){echo '现结';}else{ echo '月结';};?>"}
            ,{"key":"发票抬头","value":"<?php echo $model['title'];?>"}
            ,{"key":"开户银行","value":"<?php echo $model['bank'];?>"}
            ,{"key":"银行账号","value":"<?php echo $model['bank_number'];?>"}
            ,{"key":"税号","value":"<?php echo $model['tax_number'];?>"}
            ,{"key":"企业地址","value":"<?php echo $model['com_address'];?>"}
            ,{"key":"企业电话","value":"<?php echo $model['com_tel'];?>"}
            ,{"key":"状态","value":"<?php if($model['use_flag'] =='Y'){ echo '启用';}else{ echo '禁用';};?>"}
            ,{"key":"备注","value":"<?php echo $model['remark'];?>"}
            ,{"key":"添加时间","value":"<?php echo $model['create_time'];?>"}
        ];
        var node = viewData(data);
        $('.layui-table tbody').empty().append(node);

    });
</script>
