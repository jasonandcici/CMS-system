<?php
/**
 * @var $model
 * @var $menuList
 */

use manage\assets\FormAsset;
use yii\web\View;

$this->title = '新增菜单';
FormAsset::register($this);
$this->registerJs("formApp.init();commonApp.formYiiAjax($('#j_form'));", View::POS_READY);
?>
<?= $this->render('_form', ['model' => $model,'menuList'=>$menuList]) ?>