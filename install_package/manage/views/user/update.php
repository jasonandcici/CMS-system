<?php
/**
 * @var $model
 * @var $roleList
 * @var $userRoles
 */

use manage\assets\FormAsset;
use yii\web\View;

$this->title = '更新管理员信息';
FormAsset::register($this);
$this->registerJs("formApp.init();commonApp.formYiiAjax($('#j_form'));", View::POS_READY);
?>
<?= $this->render('_form', ['model' => $model,'roleList'=>$roleList,'userRoles'=>$userRoles]) ?>