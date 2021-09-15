<?php
/**
 * @var $dataList
 */
use yii\helpers\Url;
?>
<?php foreach ($dataList as $item){?>
<li id="tree-form-<?=$item['id']?>" class="tree-nch"><a href="<?=Url::to(['index','model_id'=>$item['id']])?>" target="mainFrame"><span class="tree-icon"></span><?=$item['title']?></a></li>
<?php }?>
