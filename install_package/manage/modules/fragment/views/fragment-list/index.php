<?php
/**
 * @block topButton 顶部按钮
 * @var $searchModel
 * @var $dataProvider
 * @var $categoryInfo
 * @var $userAccessButton
 */

use common\helpers\ArrayHelper;
use common\helpers\UploadDataHelper;
use common\helpers\UrlHelper;
use manage\assets\ListAsset;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\web\View;
use common\widgets\ActiveForm;
use yii\widgets\LinkPager;

$this->title = '碎片管理';
$this->params['subTitle'] = '('.$categoryInfo->title.')';

ListAsset::register($this);
$this->registerJs("listApp.init();", View::POS_READY);
?>
<?php if((!$categoryInfo->is_disabled_opt && $userAccessButton['create']) || $this->context->isSuperAdmin){$this->beginBlock('topButton'); ?>
<?= Html::a('新增内容', ['create','category_id'=>$categoryInfo->id], ['class' => 'btn btn-primary']) ?>
<?php $this->endBlock();} ?>

<!-- 搜索框开始 -->
<?php $form = ActiveForm::begin([
    'action' => [Yii::$app->controller->action->id],
    'method' => 'get',
    'options'=>['class' => 'form-inline search-data'],
]); ?>
    <!-- 表单控件开始 -->
    <?=Html::input('hidden','category_id',$categoryInfo->id)?>
    <?= $form->field($searchModel, 'title') ?>
    <!-- 表单控件结束 -->
    <?= Html::submitButton(Yii::t('common','Filter'), ['class' => 'btn btn-info']) ?>
<?php ActiveForm::end(); ?><!-- 搜索框结束 -->

<!-- 数据列表开始 -->
<div class="panel panel-default list-data">
    <div class="panel-body">
        <div class="table-responsive scroll-bar">
            <table class="table table-hover" id="list_data">
                <thead>
                <tr>
                    <td width="60"><?=Yii::t('common','Select')?></td>
                    <td><?=Yii::t('common','Id')?></td>
                    <?php if($categoryInfo->enable_thumb){?>
                    <td>图片</td>
                    <?php }?>
                    <td>标题</td>
                    <?php if($userAccessButton['status']){?>
                    <td align="center" width="100">状态</td>
                    <?php } if($userAccessButton['sort']){?>
                    <td align="center">排序</td>
                    <?php } if($userAccessButton['update'] || $userAccessButton['delete']){?>
                    <td align="center"><?=Yii::t('common','Operation')?></td>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach($dataList = $dataProvider->models as $i=>$item){ ?>
                    <tr>
                        <td><?= Html::checkbox('choose',false,['value'=>$item->id])?></td>
                        <td><?=$item->id?></td>
                        <?php if($categoryInfo->enable_thumb){?>
                        <td>
                            <?php if(!empty($item->thumb)){ echo \common\helpers\HtmlHelper::getImgHtml($item->thumb,['height'=>50]); }else{ echo '———';}?>
                        </td>
                        <?php }?>
                        <td><?=strip_tags($item->title)?></td>
                        <?php if($userAccessButton['status']){?>
                        <td align="center">
                            <?php if($item->status == 1){?>
                                <?= Html::a('<span class="iconfont">&#xe62a;</span>', ['status','category_id'=>$categoryInfo->id, 'id' => $item->id], ['class' => 'j_batch status-'.$item->id,'data-action'=>'status','data-value'=>0,'title'=>Yii::t('common','Disable')]) ?>
                            <?php }else{?>
                                <?= Html::a('<span class="iconfont">&#xe625;</span>', ['status','category_id'=>$categoryInfo->id, 'id' => $item->id], ['class' => 'j_batch status-'.$item->id,'data-action'=>'status','data-value'=>1,'title'=>Yii::t('common','Enable')]) ?>
                            <?php }?>
                        </td>
                        <?php } if($userAccessButton['sort']){?>
                        <td align="center">
                            <span class="sort j_sort">
                                <?php
                                $_tag = $i == 0 && ArrayHelper::getValue(Yii::$app->getRequest()->get(),'page',1) == 1?' disabled':'';
                                echo Html::tag('a',Html::tag('span','&#xe62e;',['class'=>'iconfont']),['class'=>'sort-up'.$_tag,'href'=>Url::to(['sort','category_id'=>$categoryInfo->id,'id'=>$item->id,'mode'=>1]),'title'=>'上移']);
                                $_tag = $i+1 == count($dataList) && ArrayHelper::getValue(Yii::$app->getRequest()->get(),'page',1) == $dataProvider->pagination->getPageCount()?' disabled':'';
                                echo Html::tag('a',Html::tag('span','&#xe62d;',['class'=>'iconfont']),['class'=>'sort-down'.$_tag,'href'=>Url::to(['sort','category_id'=>$categoryInfo->id,'id'=>$item->id,'mode'=>0]),'title'=>'下移']);
                                unset($_tag);
                                ?>
                            </span>
                        </td>
                        <?php } if($userAccessButton['update'] || $userAccessButton['delete']){?>
                        <td class="opt" align="center">
                            <?php if($userAccessButton['update']){?>
                            <?= Html::a(Yii::t('common','Modify'), ['update','category_id'=>$categoryInfo->id, 'id' => $item->id], ['class' => 'text-primary']) ?>
                            <?php } if($userAccessButton['delete'] || $this->context->isSuperAdmin){?>
                            <?= $categoryInfo->is_disabled_opt && !$this->context->isSuperAdmin?'':Html::a(Yii::t('common','Delete'), ['delete','category_id'=>$categoryInfo->id,'id' => $item->id],['class'=>'j_batch','data-action'=>'del']) ?>
                            <?php }?>
                        </td>
                        <?php } ?>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?=empty($dataList)?'<p class="list-data-default">'.Yii::t('common','No Data Found !').'</p>':''?>
        </div>
    </div>
</div><!-- 数据列表结束 -->

<!-- 数据分页开始 -->
<nav class="nav-operation clearfix">
    <div class="tools">
        <a href="javascript:;" id="j_choose_all"><?=Yii::t('common','Select All')?></a>
        <a href="javascript:;" id="j_choose_reverse"><?=Yii::t('common','Select Invert')?></a>
        <a href="javascript:;" id="j_choose_empty"><?=Yii::t('common','Clears all')?></a>
        <?php if($userAccessButton['sort'] ||$userAccessButton['delete'] || $userAccessButton['status']){?>
        <span>|</span>
        <?php } if($userAccessButton['delete']){?>
        <?= Html::a(Yii::t('common','Batch delete'), ['delete','category_id'=>$categoryInfo->id],['class'=>'j_batch','data-action'=>'batchDel']) ?>
        <?php } if($userAccessButton['sort']){?>
        <?= Html::a(Yii::t('common','Batch sort'), ['sort','category_id'=>$categoryInfo->id],['id'=>'j_sort_batch','data-depth'=>1,'data-pid'=>0,'data-empty'=>empty($dataList)?1:0]) ?>
        <?php } if($userAccessButton['status']){?>
        <?= Html::a('&#xe62a;', ['status','category_id'=>$categoryInfo->id,'value'=>1],['class'=>'iconfont j_batch','data-action'=>'batchStatus','data-value'=>1,'title'=>Yii::t('common','Batch enable')]) ?>
        <?= Html::a('&#xe625;', ['status','category_id'=>$categoryInfo->id,'value'=>0],['class'=>'iconfont j_batch','data-action'=>'batchStatus','data-value'=>0,'title'=>Yii::t('common','Batch disable')]) ?>
        <?php }?>
    </div>
    <?= Html::beginForm('', 'get', ['class' => 'form-inline pagination-go','id'=>'j_pagination_go']) ?>
    <div class="form-group">
        <label><?=Yii::t('common','Jump to')?></label>
        <?= Html::input('text', 'page', 1, ['class' => 'form-control']) ?>
        <label><?=Yii::t('common','Page')?></label>
    </div>
    <?= Html::endForm() ?>
    <?=LinkPager::widget(['pagination' => $dataProvider->pagination,'hideOnSinglePage'=>false,'firstPageLabel'=>'<span class="iconfont">&#xe624;</span>','prevPageLabel'=>'<span class="iconfont">&#xe61d;</span>','lastPageLabel'=>'<span class="iconfont">&#xe623;</span>','nextPageLabel'=>'<span class="iconfont">&#xe622;</span>']); ?>
</nav><!-- 数据分页结束 -->

<!-- 排序 -->
<script type="text/html" id="tpl_sort_batch">
    <div class="dd">
        <?=Html::hiddenInput('sort',implode(',',ArrayHelper::getColumn($dataList,'sort')),['class'=>'input-sort'])?>
        <ol class="dd-list">
            <?php foreach ($dataList as $item){?>
                <li class="dd-item" data-id="<?=$item->id?>">
                    <div class="dd-handle"><?=$item->id.'：'.StringHelper::truncate($item->title,24)?></div>
                </li>
            <?php }unset($dataList);?>
        </ol>
        <form action="javascript:;" method="post">
            <?=Html::hiddenInput(Yii::$app->request->csrfParam,Yii::$app->request->csrfToken)?>
            <?=Html::hiddenInput('data',null,['class'=>'input-data'])?>
        </form>
    </div>
</script>