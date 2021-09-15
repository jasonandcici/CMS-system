<?php
/**
 * @var $content
 */
use yii\helpers\Html;
use manage\assets\CommonAsset;
use yii\web\View;

$this->registerLinkTag(['rel' => 'shortcut icon','href'=>'/favicon.ico']);
$this->registerLinkTag(['rel' => 'bookmark','href'=>'/favicon.ico']);

CommonAsset::register($this);
$this->registerJs("commonApp.init();", View::POS_READY);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="renderer" content="webkit">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<?= $content ?>
<?php $this->endBody() ?>
<?php if (isset($this->blocks['endBlock'])): ?>
    <?= $this->blocks['endBlock'] ?>
<?php endif; ?>
</body>
</html>
<?php $this->endPage() ?>