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
            {"key":"起始地","value":"<?php echo $model['begincity'];?>"}
            ,{"key":"目的地","value":"<?php echo $model['endcity'];?>"}
            ,{"key":"订单编号","value":"<?php echo $model['ordernumber'];?>"}
            ,{"key":"物品描述","value":"<?php echo $model['goodsname'];?>"}
            ,{"key":"件数","value":"<?php echo $model['number'];?>"}
            ,{"key":"重量","value":"<?php echo $model['weight'];?>"}
            ,{"key":"体积","value":"<?php echo $model['volume'];?>"}
            ,{"key":"温度","value":"<?php echo $model['temperture'];?>"}
            ,{"key":"干线价格","value":"<?php echo $model['lineprice'];?>"}
            ,{"key":"提货费","value":"<?php echo $model['pickprice'];?>"}
            ,{"key":"配送费","value":"<?php echo $model['sendprice'];?>"}
            ,{"key":"提货服务","value":"<?php if($model['picktype']==1){ echo '上门提货';}else{ echo '自送到点';}?>"}
            ,{"key":"配送服务","value":"<?php if($model['sendtype'] == 1){ echo '配送到点';}else{echo '到点自提';};?>"}
            ,{"key":"总价","value":"<?php echo $model['total_price'];?>"}
            ,{"key":"客户公司","value":"<?php echo $model['all_name'];?>"}
            ,{"key":"归属公司","value":"<?php echo $model['company_name'];?>"}
            //,{"key":"初始上牌日期","value":"<?php //echo $model['number'];?>//"}
            //,{"key":"验车日期","value":"<?php //echo $model['weight'];?>//"}
            ,{"key":"支付状态","value":"<?php if($model['paystate'] == 1){ echo '未支付';}else{ echo '已支付';};?>"}
            ,{"key":"添加时间","value":"<?php echo $model['create_time'];?>"}
            ,{"key":"备注","value":"<?php echo $model['remark'];?>"}
        ];
        var node = viewData(data);
        $('.layui-table tbody').empty().append(node);

    });
</script>