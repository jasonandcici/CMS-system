<?php
/**
 * @var $model
 * @var $smsModel
 * @model
 */

use common\helpers\HtmlHelper;
use common\widgets\ActiveForm;
use yii\captcha\Captcha;

$this->params['active'] = 'reset-password';

$user = Yii::$app->getUser()->getIdentity();

echo $this->render('_nav');
?>
<div class="alert alert-warning"><strong>警告：</strong>用户名只能修改一次，请谨慎修改。</div>

<!-- 修改密码表单 -->
<?php $form = ActiveForm::begin(['id' => 'js-form','validateOnBlur'=>false, 'validateOnSubmit'=>true]); ?>

    <?= $form->field($model, 'username')->textInput()?>
    <?=$form->field($model, 'captcha')
        ->widget(Captcha::className(), [
            'captchaAction' => '/site/captcha',
            'template' => '<div class="input-group">{input}<span class="input-group-addon captcha">{image}</span></div>',
        ]);?>

    <?= HtmlHelper::submitButton('确定',['class'=>'btn btn-primary','data-loading-text'=>'提交中...']) ?>
<?php ActiveForm::end(); ?>

<?php $this->beginBlock('endBody');?>
<script>
    $(function () {
        // 表单提交
        $('#js-form').on('beforeSubmit', function (e) {
            var $form = $(this);
            var $submit = $form.find('[type="submit"]');

            if(confirm('用户名只能修改一次，您确定要执行此操作吗？')){
                $submit.button('loading');
                $.post($form.attr('action'),$form.serialize(),function (response) {
                    if(typeof response === 'string') response = JSON.parse(response);
                    $submit.button('reset');
                    if(response.status){
                        $form.find('input').attr('disabled',true);
                        $submit.hide();
                        alert('操作成功。');
                    }else{
                        alert(response.message);
                    }
                });
            }

        }).on('submit', function (e) {
            e.preventDefault();
        });
    });
</script>
<?php $this->endBlock();?>
