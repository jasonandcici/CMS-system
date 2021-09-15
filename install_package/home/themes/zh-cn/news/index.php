<?php
/**
 * @var $searchModel
 * @var $dataProvider
 */

use common\helpers\HtmlHelper;
use yii\helpers\StringHelper;

?>
<!-- 面包屑 -->
<?=$this->render('_breadcrumb')?>

<!-- 内容区域 -->
<?php
// 每页显示1条
$dataProvider->pagination = [
    'pageSize'=>5
];

// 使用 $dataProvider->getModels() 查询获取数据列表
$dataList = $dataProvider->getModels();
$isCollection = $this->findDataIsRelation('collection',$dataList);

$dataList = $this->countUserRelationsFilter($dataList);

if (!empty($dataList)) {
    foreach ($dataList as $item) {
        ?>
        <div class="media mt-2">
            <div class="media-left pr-2">
                <a href="<?= $url = $this->generateDetailUrl($item) ?>">
                    <?=HtmlHelper::getImgHtml($item->thumb,['w/250/h/150','class'=>'media-object','height'=>150,'width'=>250])?>
                </a>
            </div>
            <div class="media-body">
                <h4 class="media-heading"><a href="<?=$url?>"><?=$item->title?></a></h4>
                <p><?=date('Y-m-d',$item->create_time)?> | <?=$item->views?>次浏览 | <?=$item->count_user_relations->collection?>个收藏</p>
                <p><?=StringHelper::truncate($item->description,120)?></p>
                <p>
                    <a href="<?=$this->generateUserRelationUrl('collection',$item->id)?>" class="pull-right collection<?=$isCollection[$item->id]?' active':''?> js-relation" data-relation-text="点击收藏,取消收藏"><i class="glyphicon glyphicon-heart"></i></a>
                    <a href="<?=$url?>" class="btn btn-default">查看详情</a>
                </p>
            </div>
        </div>
        <?php
    }
} else { ?>
    <div class="text-center p-3">
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
