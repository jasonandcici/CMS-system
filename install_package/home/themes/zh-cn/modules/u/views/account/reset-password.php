<?php
/**
 * @var $model
 * @var $smsModel
 * @var $mode
 * @model
 */

use common\helpers\HtmlHelper;
use common\widgets\ActiveForm;

$this->params['active'] = 'reset-password';

echo $this->render('_nav');
?>

<?php if(($mode == 'email' || $mode == 'cellphone') && empty($smsModel->account)):?>
    <div class="alert alert-danger">您还没有绑定<?=$mode=='email'?'邮箱':'手机号'?>，将无法修改密码。</div>
<?php endif;?>

<!-- 修改密码表单 -->
<?php $form = ActiveForm::begin(['id' => 'js-form','validateOnBlur'=>false, 'validateOnSubmit'=>true]); ?>

    <div class="form-group">
        <label class="control-label">验证类型</label>
        <div id="js-mode-change">
            <?=HtmlHelper::radioList(null,$mode,['password'=>'旧密码','cellphone'=>'手机号','email'=>'邮箱',],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]])?>
        </div>
    </div>


    <?php if($mode != 'password'){echo $form->field($model, 'captcha',[
        'template' => '{label}<div class="input-group">{input}<span class="input-group-btn"><button class="btn btn-default" id="js-send" type="button" data-loading-text="发送中...">获取验证码</button></span></div>',
    ])->textInput();}?>

    <?php if($mode == 'password') echo $form->field($model, 'password_old')->passwordInput();?>

    <?= $form->field($model, 'password')->passwordInput()?>
    <?= $form->field($model, 'password_repeat')->passwordInput()?>

    <?= HtmlHelper::submitButton('确定',['class'=>'btn btn-primary','data-loading-text'=>'提交中...']) ?>
<?php ActiveForm::end(); ?>

<?php
    if($mode != 'password'){
        $form = ActiveForm::begin(['id' => 'js-sms-form', 'action'=>$this->generateFormUrl('sms',['params'=>['mode'=>$mode]])]);
        echo HtmlHelper::activeHiddenInput($smsModel,'type');
        echo HtmlHelper::activeHiddenInput($smsModel,'cellphone_code');
        echo HtmlHelper::activeHiddenInput($smsModel,'account');
        ActiveForm::end();
    }
?>

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
                    alert('操作成功。');
                    $form[0].reset();
                }else{
                    alert(response.message);
                }
            });
        }).on('submit', function (e) {
            e.preventDefault();
        });

        // 发送验证码
        var $smsForm = $('#js-sms-form');
        if(!$smsForm.data('action')) $smsForm.data('action',$smsForm.attr('action'));

        $('#js-send').click(function () {
            var $btn = $(this);
            if($btn.hasClass('disabled')) return false;

            $.ajax({
                url:$smsForm.attr('action'),
                type:'post',
                data:$smsForm.serialize(),
                dataType:'json',
                beforeSend:function () {
                    $btn.button('loading');
                },
                success:function (response) {
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
                },
                error:function () {
                    alert('操作失败。');
                    $btn.button('reset');
                }
            });
        });

        // 切换验证类型
        $('#js-mode-change :radio').change(function () {
            location.href = '<?=$this->generateUserUrl('reset-password')?>?mode='+$(this).val();
        });
    });
</script>
<?php $this->endBlock();?>