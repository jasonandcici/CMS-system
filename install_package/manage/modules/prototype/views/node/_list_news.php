<?php
/**
 * @var $searchModel
 * @var $searchForm
 * @var $dataList
 * @var $categoryInfo
 */?>
<?php $this->beginBlock('search');?>
<?php $this->endBlock();?>
<?php $this->beginBlock('thead');?>
<?php $this->endBlock();?>
<?php foreach ($dataList as $item):?>
<?php $this->beginBlock('tbody'.$item->id);?>
<?php $this->endBlock();?>
<?php endforeach;?>