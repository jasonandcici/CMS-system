<?php

?>
<?php $this->beginBlock('endBlock'); ?>
<script>
    $(function () {
        var $enable = $('.config-sms-enable').find(':radio');
        $enable.change(function () {
            enableFun($(this).val());
        });
        enableFun(parseInt($enable.filter(':checked').val()));
    });

    function enableFun(val) {
        if(parseInt(val) === 1){
            $('.config-sms-signName,.config-sms-signNameAbroad').show();
            $('.config-sms-tplCode,.config-sms-tplCodeAbroad').find('.hint-block').html('短信模板仅支持1个参数，例：您的验证码是${code}，3分钟内有效。');
        }else{
            $('.config-sms-signName,.config-sms-signNameAbroad').hide();
            $('.config-sms-tplCode,.config-sms-tplCodeAbroad').find('.hint-block').html('短信模板仅支持2个参数，例：您的验证码是{1}，{2}分钟内有效。');
        }

    }
    <?php if($this->context->isSuperAdmin){?>
    $('#j_form').prepend('<p class="hint-block col-md-offset-4 text-danger" style="margin-bottom: 20px;">注意：启用短信发送时，需要在控制台监听和处理消息队列。开启方法：<a href="https://github.com/yiisoft/yii2-queue/blob/master/docs/guide-zh-CN/driver-file.md" target="_blank">点击查看</a></p>');
    <?php } ?>
</script>
<?php $this->endBlock(); ?>