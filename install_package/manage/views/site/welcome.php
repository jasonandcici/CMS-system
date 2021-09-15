<?php
/**
 * @var $server
 * @var $userInfo
 */

use yii\helpers\Html;

$this->title = '信息概览';
$userInfo = Yii::$app->getUser()->getIdentity();
?>

<div class="jumbotron">
    <h1>你好，<?=$userInfo->username?>！</h1>
    <p>欢迎登录<?=Html::encode($this->context->config['site']['site_name'])?>。</p>
    <dl class="info-overview clearfix">
        <dt>系统信息</dt>
        <dd><span class="t">服务器操作系统</span><?=$server['serverOs']?></dd>
        <dd><span class="t">服务器运行软件</span><?=$server['serverSoft']?></dd>
        <dd><span class="t">PHP版本</span><?=$server['phpVersion']?></dd>
        <dd><span class="t">上传限制</span><?=$server['fileUpload']?></dd>
        <dd><span class="t">MYSQL版本</span><?=$server['mysqlVersion']?></dd>
        <dd><span class="t">MYSQL库大小</span><?=$server['dbSize']?></dd>
    </dl>
</div>