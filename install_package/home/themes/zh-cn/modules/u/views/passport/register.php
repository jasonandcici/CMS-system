<?php
/**
 * @var $model
 * @var $mode
 * @var $smsModel
 */

use common\helpers\HtmlHelper;
use common\widgets\ActiveForm;
use yii\captcha\Captcha;
?>
<ul class="nav nav-tabs">
    <?php foreach(['cellphone'=>'手机号注册','email'=>'邮箱','username'=>'用户名注册','fast'=>'短信快速注册'] as $k=>$v){?>
    <li<?=$mode == $k?' class="active"':''?>><a href="<?=$this->generateCurrentUrl(['mode'=>$k])?>"><?=$v?></a></li>
    <?php }?>
</ul>
<br>

<!-- 注册表单 -->
<?php $form = ActiveForm::begin(['id' => 'js-form','validateOnBlur'=>false, 'validateOnSubmit'=>true]); ?>
    <?php
    if($mode == 'cellphone'){
        echo $form->field($model, 'account',[
            // 手机国际区号
            'template' => '{label}<div class="row"><div class="col-xs-3">'.HtmlHelper::activeDropDownList($model,'cellphone_code',$this->context->config->sms->cellphoneCode,['class'=>'form-control']).'</div><div class="col-xs-9">{input}</div></div>{error}',
        ])->textInput();
    }else{
        echo $form->field($model, 'account')->textInput();
    }?>

    <?php if($mode != 'fast'){?>
        <?= $form->field($model, 'password')->passwordInput()?>
        <?= $form->field($model, 'password_repeat')->passwordInput()?>
    <?php } ?>

    <?php
    if ($mode != 'username') {
        echo $form->field($model, 'captcha',[
            'template' => '{label}<div class="input-group">{input}<span class="input-group-btn"><button class="btn btn-default" id="js-send" type="button" data-loading-text="发送中...">获取验证码</button></span></div>',
        ])->textInput();
    } else {
        echo $form->field($model, 'captcha')
            ->widget(Captcha::className(), [
                'captchaAction' => '/site/captcha',
                'template' => '<div class="input-group">{input}<span class="input-group-addon captcha">{image}</span></div>',
            ]);
    }?>
    <?= HtmlHelper::submitButton('注册',['class'=>'btn btn-primary','data-loading-text'=>'提交中...']) ?>
<?php ActiveForm::end(); ?>

<?php
// 验证码表单
if ($mode != 'username') {
    $form = ActiveForm::begin(['id' => 'js-sms-form', 'action'=>$this->generateFormUrl('sms',['params'=>['mode'=>$mode]])]);
        echo HtmlHelper::activeHiddenInput($smsModel,'type');
        if($mode == 'cellphone') echo HtmlHelper::activeHiddenInput($smsModel,'cellphone_code');
        echo HtmlHelper::activeHiddenInput($smsModel,'account');
    ActiveForm::end();
}?>

<?php $this->beginBlock('endBody');?>
<script>
    $(function () {
        // 表单提交
        $('#js-form').on('beforeSubmit', function (e) {
            var $form = $(this);
            var $submit = $form.find('[type="submit"]');

            $submit.button('loading');
            $.post($form.attr('action'),$form.serialize(),function (response) {
                if(typeof response === 'string') response = JSON.parse(response);
                $submit.button('reset');
                if(response.status){
                    alert('注册成功！');
                    location.href = response.jumpLink;
                }else{
                    alert(response.message);
                    $('#registerform-captcha-image').trigger('click');
                }
            });
        }).on('submit', function (e) {
            e.preventDefault();
        });

        <?php if ($mode != 'username') {?>
        // 验证码表单填值
        $('#registerform-account').change(function () {
            $('#smsverificationcodeform-account').val($(this).val());
        });

        <?php if($mode == 'cellphone'){?>
        $('#registerform-cellphone_code').change(function () {
            $('#smsverificationcodeform-cellphone_code').val($(this).val());
        });
        <?php }?>

        // 发送验证码
        var $smsForm = $('#js-sms-form');
        $('#js-send').click(function () {
            var $btn = $(this);
            if($btn.hasClass('disabled')) return false;

            $btn.button('loading');

            $.post($smsForm.attr('action'),$smsForm.serialize(),function (response) {
                if(typeof response === 'string') response = JSON.parse(response);
                if(response.status){
                    $btn.addClass('disabled');
                    var second = 60,
                        timer = setInterval(function () {
                            if(second === 0){
                                clearInterval(timer);
                                $btn.removeClass('disabled');
                                $btn.button('reset');
                                return;
                            }
                            second--;
                            $btn.text(second+'s');
                        },1000);
                }else{
                    alert(response.message);
                    $btn.button('reset');
                }
            });
        });
        <?php } ?>
    });
</script>
<?php $this->endBlock();?>
