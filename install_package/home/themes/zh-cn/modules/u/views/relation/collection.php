<?php
/**
 * @var $searchModel
 * @var $dataProvider
 * @var $slug
 */

use common\helpers\HtmlHelper;
use common\helpers\StringHelper;

$this->params['active'] = 'relation-collection';
?>
<div class="row">
    <?php
    // 每页显示1条
    $dataProvider->pagination = [
        'pageSize'=>9
    ];

    // 使用 $dataProvider->getModels() 查询获取数据列表
    $dataList = $dataProvider->getModels();
    if (!empty($dataList)) {
        foreach ($dataList as $item) {
            ?>
            <div class="col-sm-6 col-md-4">
                <div class="thumbnail">
                    <a href="<?= $url = $this->generateDetailUrl($item) ?>" target="_blank">
                        <?=HtmlHelper::getImgHtml($item->thumb,['w/250/h/150','class'=>'img-responsive'])?>
                    </a>
                    <div class="caption">
                        <h4 class="text-ellipsis"><a href="<?= $url?>" target="_blank"><?=$item->title?></a></h4>
                        <p><?=StringHelper::truncate($item->description,30)?></p>
                        <p>
                            <a href="<?=$this->generateUserRelationUrl('collection',$item->id)?>" class="btn btn-default js-relation" data-relation-event="userCenterCollectionCallback" data-relation-event-before="userCenterCollectionBeforeCallback">取消收藏</a>
                        </p>
                    </div>
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
</div>
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

<?php $this->beginBlock('endBody');?>
<script>
    /**
     * 删除收藏回调
     */
    function userCenterCollectionBeforeCallback($btn) {
        if(!confirm('您确定要执行此操作吗?')){
            return false;
        }
    }

    function userCenterCollectionCallback($btn,action) {
        alert('操作成功！');
        history.go(0);
    }
</script>
<?php $this->endBlock();?>
