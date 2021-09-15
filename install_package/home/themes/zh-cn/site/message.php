<?php

/**
 * @var $status
 * @var $message
 * @var $waitTime
 * @var $jumpLink
 */

use common\helpers\HtmlHelper;

?>

<div style="margin-top: 60px;">
    <h1><?=HtmlHelper::encode($title)?></h1>
    <?php if($message){
        echo '<p>'.$message.'</p>';
    }?>
    <p class="lead"><a href="javascript:history.go(-1);" class="text-primary">返回上一步</a>。</p>
</div>
