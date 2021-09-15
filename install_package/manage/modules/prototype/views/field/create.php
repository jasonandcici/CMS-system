<?php
/**
 * @var $model
 * @var $fieldModel
 */
use manage\assets\FormAsset;
use yii\web\View;

$this->title = '新增字段';
$this->params['subTitle'] = '('.$model->title.')';
FormAsset::register($this);
$this->registerJs("formApp.init();commonApp.formYiiAjax($('#j_form'));", View::POS_READY);
?>
<?= $this->render('_form', ['model' => $model,'fieldModel'=>$fieldModel,'modelList'=>$modelList]) ?>