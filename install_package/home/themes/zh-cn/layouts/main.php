<?php
/**
 * @var $content
 */
use common\helpers\ArrayHelper;
use common\helpers\HtmlHelper;

$this->beginContent(Yii::$app->layoutPath.'/base.php');

$siteInfo = $this->findFragment('siteInfo');
?>
<?=$content?>
<?php $this->endContent(); ?>