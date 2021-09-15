<?php
/**
 * @var $model
 * @var $userList
 */

use manage\assets\FormAsset;
use yii\helpers\Html;
use yii\web\View;

$this->title = '编辑角色';

FormAsset::register($this);
$this->registerJs("formApp.init();commonApp.formYiiAjax($('#j_form'));", View::POS_READY);
?>
<?php $this->beginBlock('topButton'); ?>
<?= Html::a(Yii::t('common','Back List'), 'javascript:;', ['class' => 'btn btn-default j_goback']) ?>
<?php $this->endBlock(); ?>
<?=$this->render('role_form', ['model'=>$model,'userList'=>$userList]) ?>