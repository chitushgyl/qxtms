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
<script>

</script>
<script type="text/javascript">
    layui.use(['jquery'],function () {
        var $ = layui.jquery;
        var data = [
            {"key":"起始地","value":"<?php echo $model['startcity'];?>"}
            ,{"key":"目的地","value":"<?php echo $model['endcity'];?>"}
            ,{"key":"订单编号","value":"<?php echo $model['ordernumber'];?>"}
            ,{"key":"物品描述","value":"<?php echo $model['name'];?>"}
            ,{"key":"温度","value":"<?php echo $model['temperture'];?>"}
            ,{"key":"发车日期","value":"<?php echo $model['time_start'];?>"}
            ,{"key":"重量(kg)","value":"<?php echo $model['weight'];?>"}
            ,{"key":"体积(m³)","value":"<?php echo $model['volume'];?>"}
            ,{"key":"件数","value":"<?php echo $model['number'];?>"}
            ,{"key":"归属公司","value":"<?php echo $model['company_name'];?>"}
            ,{"key":"车型","value":"<?php echo $model['carparame'];?>"}
            ,{"key":"是否装货","value":"<?php  if($model['picktype'] == 1){ echo '客户装货';}else{ echo '司机装货';};?>"}
            ,{"key":"是否卸货","value":"<?php if($model['sendtype'] == 1){ echo '客户卸货';}else{ echo '司机卸货';};?>"}
            //,{"key":"装货地","value":"<?php //echo $model['startstr'];?>//"}
            //,{"key":"卸货地","value":"<?php //echo $model['endstr'];?>//"}
            ,{"key":"备注","value":"<?php echo $model['remark'];?>"}
            ,{"key":"添加时间","value":"<?php echo $model['create_time'];?>"}
        ];
        var node = viewData(data);
        $('.layui-table tbody').empty().append(node);

    });
</script>