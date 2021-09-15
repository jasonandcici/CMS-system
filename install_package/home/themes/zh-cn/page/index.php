<?php
/**
 * @var $dataDetail
 */

use common\helpers\HtmlHelper;
use common\widgets\ActiveForm;
use yii\captcha\Captcha;
?>
<article class="bs-docs-section">
    <h2 class="page-header"><?=$dataDetail->title?></h2>
    <?=$dataDetail->content?>
</article>
<h2 class="page-header">留言反馈</h2>
<?php
$model = $this->findModel(8);
$form = ActiveForm::begin([
    'id' => 'js-form',
    'validateOnBlur'=>false,
    'validateOnSubmit'=>true,
    'action'=>$this->generateFormUrl(8),
]); ?>
<?= $form->field($model, 'content')->textarea()?>
<?=$form->field($model, 'captcha')
    ->widget(Captcha::className(), [
        'captchaAction' => '/site/captcha',
        'template' => '<div class="input-group">{input}<span class="input-group-addon captcha">{image}</span></div>',
        ]);
?>

<?php if(Yii::$app->getUser()->getIsGuest()){
    echo HtmlHelper::button('提交',['type'=>'button','class'=>'btn btn-primary disabled']);
    echo HtmlHelper::a('登录',$this->generateUserUrl('login',['params'=>['jumpLink'=>$this->generateCurrentUrl()]]),['class'=>'btn btn-link']);
}else{echo HtmlHelper::submitButton('提交',['class'=>'btn btn-primary','data-loading-text'=>'提交中...']);} ?>
<?php ActiveForm::end(); ?>

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
                    $form[0].reset();
                    $('#feedbackmodel-captcha-image').trigger('click');
                    alert('留言成功。');
                }else{
                    alert(response.message);
                }
            });
        }).on('submit', function (e) {
            e.preventDefault();
        });
    });
</script>
<?php $this->endBlock();?>
