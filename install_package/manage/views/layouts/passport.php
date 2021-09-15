<?php
// +----------------------------------------------------------------------
// | SimplePig
// +----------------------------------------------------------------------
// | Copyright (c) 2016-+ http://www.zhuyanjun.cn.
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/3/12 16:31.
// +----------------------------------------------------------------------

/**
 * 通行证基础布局
 */

use yii\helpers\Html;
use yii\web\View;

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
    </body>
    </html>
<?php $this->endPage() ?>