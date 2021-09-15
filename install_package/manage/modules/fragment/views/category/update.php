<?php
/**
 * @var $model
 */

use manage\assets\FormAsset;
use yii\web\View;

$this->title = '修改类别';
FormAsset::register($this);
$this->registerJs("formApp.init();commonApp.formYiiAjax($('#j_form'),{successCallback:function(){commonApp.inFrame(function(){parent.indexApp.clearNavAside('tree-nav-30');},null,'mainFrame')}});", View::POS_READY);
?>
<?= $this->render('_form', ['model' => $model]) ?>