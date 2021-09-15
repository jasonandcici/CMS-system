<?php
use yii\helpers\Html;

/**
 * @var $status
 * @var $message
 * @var $waitTime
 * @var $jumpLink
 */

$this->title = Html::encode($title);
$this->params['titleClass'] = $status?'text-success':'text-danger';
?>
<div class="alert alert-<?= $status?'success':'danger'?>">
    <?php if($message){
        echo '<pre>';
        var_dump($message);
        echo '</pre>';
    }?>
    <p>页面将在<b id="wait"><?=$waitTime?></b>后，自动进行 <a id="href" href="<?=$jumpLink?>">跳转</a>。</p>
</div>
<script type="text/javascript">
    (function(){
        var wait = document.getElementById('wait'),href = document.getElementById('href').href;
        var interval = setInterval(function(){
            var time = --wait.innerHTML;
            if(time <= 0) {
                location.href = href;
                clearInterval(interval);
            }
        }, 1000);
    })();
</script>