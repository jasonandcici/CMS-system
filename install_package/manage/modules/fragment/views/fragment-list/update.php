<?php
/**
 * @var $model
 * @var $categoryInfo
 * @var $modelList
 */

$this->title = '修改内容';
$this->params['subTitle'] = '('.$categoryInfo->title.')';
?>
<?= $this->render('_form', ['model' => $model,'categoryInfo'=>$categoryInfo,'modelList'=>$modelList]) ?>