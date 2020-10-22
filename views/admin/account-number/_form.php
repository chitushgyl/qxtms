<?php
/**
 * Created by pysh.
 * Date: 2020/2/2
 * Time: 17:15
 */
// echo \Yii::$app->view->renderFile('@app/views/admin/base.php');
?>

<input name="_csrf" type="hidden" id="_csrf" value="<?php echo Yii::$app->request->csrfToken ?>">
<div class="layui-form-item">
    <label for="" class="layui-form-label"><span class="required_red">*</span>手机号</label>
    <div class="layui-input-block">
        <input type="text" name="tel" value="" lay-verify="tel" placeholder="请输入手机号" class="layui-input">
    </div>
</div>
<div class="layui-form-item">
    <label for="" class="layui-form-label"><span class="required_red">*</span>密码</label>
    <div class="layui-input-block">
        <input type="password" name="password" value=""  lay-verify="password" placeholder="请输入密码" class="layui-input" >
    </div>
</div>


<div class="layui-form-item">
    <label for="" class="layui-form-label"><span class="required_red">*</span>确认密码</label>
    <div class="layui-input-block">
        <input type="password" name="password1" value=""  placeholder="请输入确认密码" class="layui-input" lay-verify="password1">
    </div>
</div>

<div class="layui-form-item">
    <label for="" class="layui-form-label"><span class="required_red">*</span>公司名称</label>
    <div class="layui-input-block">
        <input type="text" name="group_name" value=""  placeholder="请输入公司名称" class="layui-input" lay-verify="group_name">
    </div>
</div>

<div class="layui-form-item">
    <label for="" class="layui-form-label"><span class="required_red">*</span>注册人姓名</label>
    <div class="layui-input-block">
        <input type="text" name="name" value=""  placeholder="请输入注册人姓名" class="layui-input" lay-verify="name">
    </div>
</div>


<div class="layui-form-item">
    <div class="layui-input-block">
        <button type="submit" class="layui-btn" lay-submit="" lay-filter="*">确 认</button>
        <a class="layui-btn" href="<?php echo route('admin.account-number.index');?>" >返 回</a>
    </div>
</div>
<script type="text/javascript" src="/js/address.js"></script>

<script type="text/javascript">

        layui.use(['layer','form'],function () {
            var layer = layui.layer;
            var form = layui.form;
            var id = '<?php echo $info->id;?>';
            //if (id) {
            //    var pro = '<?php //echo $info->province;?>//';
            //    var city = '<?php //echo $info->city;?>//';
            //    var area = '<?php //echo $info->area;?>//';
            //
            //    $('.pro option[value="'+pro+'"]').attr('selected',true);
            //    getData(pro,$('.city'),'city',form,city);
            //    $('.city option[value="'+city+'"]').attr('selected',true);
            //    getData(city,$('.area'),'area',form,area);
            //    $('.area option[value="'+area+'"]').attr('selected',true);
            //
            //    form.render('select');
            //}
        });

</script>