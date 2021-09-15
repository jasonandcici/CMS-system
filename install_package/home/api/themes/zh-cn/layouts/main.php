<?php
/**
 * @var $content
 */

use yii\helpers\Html;

\home\assets\ApiAsset::register($this);
\home\assets\ResetArticleStyleAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="format-detection" content="telephone=no"/>
    <meta name="msapplication-tap-highlight" content="no"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no"/>
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title)?></title>
    <?php $this->head() ?>
</head>
<body>
<!--内容区-->
<div class="main-content">
    <?=$content?>
</div>
<?php
$this->endBody();
// 定义endBody内容块
if (isset($this->blocks['endBody'])) echo $this->blocks['endBody'];
?>
</body>
</html>
<?php $this->endPage() ?>