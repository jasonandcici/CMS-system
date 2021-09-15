<?php
/**
 * @var $dataDetail
 * @var $prevLink
 * @var $nextLink
 */

?>

<div class="article-thumb" style="background-image: url(<?=\common\helpers\HtmlHelper::getFileItem($dataDetail->thumb)?>);"></div>
<article class="article-main js-reset-style">
    <div class="page-header">
        <h1><?=$dataDetail->title?></h1>
        <p><?=date('Y-m-d',$dataDetail->create_time)?></p>
    </div>
	<?=$dataDetail->content?>
</article>