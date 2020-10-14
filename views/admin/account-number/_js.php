<?php
/**
 * Created by pysh.
 * Date: 2020/2/2
 * Time: 09:46
 */
?>
<script>
    function numAndLetter(obj){
        obj.value = obj.value.replace(/[^\w\/]/ig,'');
        obj.value = obj.value.substring(0,20);
    }
    layui.use(['layer','table','form','upload'],function () {
        var layer = layui.layer;
        var form = layui.form;
        var upload = layui.upload;

        form.verify({
            tel : function(value, item){
                if (!value) {
                    return '手机号不能为空！';
                }
                var m = checkMobile(value);
                if (m != 1) {
                    return m;
                }
            },
            code : function(value, item){
                if (!value) {
                    return '验证码不能为空！';
                }
                if (value.length <2) {
                    return '验证码长度最少2位！';
                }
            },
            password : function(value, item){
                if (!value) {
                    return '密码不能为空！';
                }
                if (value.length <8) {
                    return '密码长度最少8位！';
                }
            },
            password1 : function(value, item){
                if (!value) {
                    return '确认密码不能为空！';
                }
                if (value.length <8) {
                    return '确认密码长度最少8位！';
                }
            },
            group_name : function(value, item){
                if (!value) {
                    return '公司名称不能为空！';
                }
            },            

            name : function(value, item){
                if (!value) {
                    return '注册人姓名不能为空！';
                }
            },
        });

        form.on('submit(*)', function(){
            layer.load(2,{time:10*1000});
        })
    });

</script>
