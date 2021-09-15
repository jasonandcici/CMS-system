<?php
/**
 * @var $model
 * @var $categoryInfo
 */

use manage\assets\FormAsset;
use yii\web\View;

$this->title = '新增碎片';
$this->params['subTitle'] = '（'.$categoryInfo->title.'）';
?>
<?= $this->render('_form', ['model' => $model,'categoryInfo'=>$categoryInfo]) ?>