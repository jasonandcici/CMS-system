<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = $name;
?>
<!-- id="system-error"属性不要删除，用于排除历史记录 -->
<div class="alert alert-danger" id="system-error">
    <?= nl2br(Html::encode($message)) ?>
</div>
