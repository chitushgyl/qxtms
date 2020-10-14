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
            {"key":"配送城市","value":"<?php echo $model['city'];?>"}
            //,{"key":"目的地","value":"<?php //echo $model['endcity'];?>//"}
            ,{"key":"订单编号","value":"<?php echo $model['ordernumber'];?>"}
            ,{"key":"客户公司","value":"<?php echo $model['all_name'];?>"}
            ,{"key":"结算方式","value":"<?php  if($model['paytype']== 1){ echo '现付';}else{ echo '月结';};?>"}
            ,{"key":"发货时间","value":"<?php echo $model['delivery_time'];?>"}
            ,{"key":"收货时间","value":"<?php echo $model['receive_time'];?>"}
            ,{"key":"计费标准","value":"<?php if($model['count_type'] == 1){ echo '标准价';}else{ echo '合同价';};?>"}
            ,{"key":"物品名称","value":"<?php echo $model['goodsname'];?>"}
            ,{"key":"数量","value":"<?php echo $model['number'];?>"}
            ,{"key":"重量","value":"<?php echo $model['weight'];?>"}
            ,{"key":"体积","value":"<?php echo $model['volume'];?>"}
            ,{"key":"温度","value":"<?php echo $model['temperture'];?>"}
            ,{"key":"总价","value":"<?php echo $model['total_price'];?>"}
            ,{"key":"归属公司","value":"<?php echo $model['company_name'];?>"}
            ,{"key":"备注","value":"<?php echo $model['remark'];?>"}
            ,{"key":"添加时间","value":"<?php echo $model['create_time'];?>"}
        ];
        var node = viewData(data);
        $('.layui-table tbody').empty().append(node);

    });
</script>