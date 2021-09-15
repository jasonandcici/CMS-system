<?php

/**
 * @var $model
 * @userInfo
 */

use manage\assets\LoginAsset;
use yii\captcha\Captcha;
use yii\helpers\Html;
use yii\web\View;
use common\widgets\ActiveForm;

$this->title = '登录 - '.$this->context->config['site']['site_name'].'系统管理中心';

LoginAsset::register($this);
$this->registerJs("loginApp.init();", View::POS_READY);

$model->rememberMe = true;
?>
<!-- logo -->
<h1 class="brand">
    <a href="/" style="font-size: 20px;color: #fff;display: inline-block;line-height: 22px;">
	    <?=\common\helpers\HtmlHelper::getImgHtml($this->context->config['site']['logo'],['draggable'=>false,'style'=>'height: 22px;vertical-align: top;'])?>
	    <?=$this->context->config['site']['site_name']?>
    </a>
</h1>
<!-- 登陆框开始 -->
<div class="login-box">
    <h2>管理员登录</h2>

    <?php $form = ActiveForm::begin([
        'id'=>'j_form',
        'fieldConfig'=>['template'=>'{input}{error}{hint}']
    ]); ?>
    <?= $form->field($model, 'username')->textInput(['placeholder'=>$model->getAttributeLabel('username')]) ?>
    <?= $form->field($model, 'password')->passwordInput(['placeholder'=>$model->getAttributeLabel('password')]) ?>
    <?= $form->field($model, 'captcha', ['options' => ['class' => 'form-group verificate-code clearfix']])
        ->widget(Captcha::className(),[
            'template' => '{input}<a href="javascript:;" id="j_verify_code" data-placement="bottom" data-placeholder="'.$model->getAttributeLabel('captcha').'" title="点击切换验证码">{image}</a>',
        ]); ?>

    <div class="checkbox">
        <?=Html::activeCheckbox($model,'rememberMe')?>
    </div>
    <?= Html::submitButton('登&nbsp;&nbsp;录', ['class' => 'btn btn-primary', 'data-loading-text' => '登录中，请稍后...', 'data-jump-text' => '登录成功，页面跳转中...']) ?>

    <?php ActiveForm::end();?>

</div><!-- 登陆框结束 -->
<p class="copyright"><?=$this->context->config['site']['copyright']?></p>