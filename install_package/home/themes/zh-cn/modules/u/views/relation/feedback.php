<?php
/**
 * @var $searchModel
 * @var $dataProvider
 * @var $slug
 */

use common\helpers\HtmlHelper;
use common\helpers\StringHelper;

$this->params['active'] = 'relation-feedback';
?>

    <?php
    $dataList = $dataProvider->getModels();
    if (!empty($dataList)) {
        foreach ($dataList as $item) {
            ?>
            <div class="media media-feedback">
                <div class="media-body">
                    <p><?=$item->content?></p>
                    <p class="date">反馈状态：<?=$item->status?'已处理':'未处理'?> &nbsp; 反馈时间：<?=date('Y-m-d',$item->userRelation->relation_create_time)?></p>
                </div>
            </div>
            <?php
        }
    } else { ?>
        <div class="text-center">
            <h3>暂无数据</h3>
            <p>没找到数据，去其他页面看看吧！</p>
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
