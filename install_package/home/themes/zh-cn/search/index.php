<?php
/**
 * @var $searches
 * @var $dataProvider
 */

use common\helpers\HtmlHelper;
use yii\helpers\StringHelper;

?>
<!-- 内容区域 -->
<?php
// 每页显示1条
$dataProvider->pagination = [
    'pageSize'=>5
];

// 使用 $dataProvider->getModels() 查询获取数据列表
$dataList = $dataProvider->getModels();
if (!empty($dataList)) {
    foreach ($dataList as $item) {
        ?>
        <div class="media mt-2">
            <div class="media-left pr-2">
                <a href="<?= $url = $this->generateDetailUrl($item) ?>">
                    <?=HtmlHelper::getImgHtml($item->thumb,['w/250/h/150','class'=>'media-object'])?>
                </a>
            </div>
            <div class="media-body">
                <h4 class="media-heading"><?=$item->title?></h4>
                <p><?=date('Y-m-d',$item->create_time)?> | <?=$item->views?>次浏览</p>
                <p><?=StringHelper::truncate($item->description,120)?></p>
                <a href="<?=$url?>" class="btn btn-default">查看详情</a>
            </div>
        </div>
        <?php
    }
} else { ?>
    <div class="text-center p-3">
        <h3>没有找到数据</h3>
        <p>没有搜索到相关数据，搜索其他关键字试试吧！</p>
    </div>
<?php } ?>
<!-- 分页 -->
<?= \yii\widgets\LinkPager::widget([
    'pagination' => $dataProvider->pagination,
    'firstPageCssClass' => 'first',
    'prevPageCssClass' => 'prev',
    'firstPageLabel' => '首页',
    'prevPageLabel' => '上一页',

    'nextPageCssClass' => 'next',
    'lastPageCssClass' => 'end',
    'nextPageLabel' => '下一页',
    'lastPageLabel' => '尾页',

    'maxButtonCount' => 8,
    'disabledPageCssClass' => true,
    'options' => ['class' => 'pagination']
]) ?>