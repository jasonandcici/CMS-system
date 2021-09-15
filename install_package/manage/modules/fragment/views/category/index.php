<?php
/**
 * @block topButton 顶部按钮
 * @var $searchModel
 * @var $dataProvider
 */

use common\helpers\ArrayHelper;
use manage\assets\ListAsset;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\web\View;
use common\widgets\ActiveForm;
use yii\widgets\LinkPager;

$this->title = '碎片设计';

ListAsset::register($this);
$this->registerJs("listApp.init();", View::POS_READY);
?>
<?php $this->beginBlock('topButton'); ?>
<?= Html::a('新增类别', ['create'], ['class' => 'btn btn-primary']) ?>
<?php $this->endBlock(); ?>

<!-- 搜索框开始 -->
<?php $form = ActiveForm::begin([
    'action' => [Yii::$app->controller->action->id],
    'method' => 'get',
    'options'=>['class' => 'form-inline search-data'],
]); ?>
    <!-- 表单控件开始 -->
    <?= $form->field($searchModel, 'title') ?>
<?= $form->field($searchModel, 'type')->dropDownList([0=>'字段类型',1=>'列表类型'], ['prompt'=>'—不限—','class'=>'form-control']) ?>
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
                    <td>标题</td>
                    <td>标识</td>
                    <td>类型</td>
                    <td align="center">全局使用</td>
                    <td align="center">排序</td>
                    <td align="center"><?=Yii::t('common','Operation')?></td>
                </tr>
                </thead>
                <tbody>
                <?php foreach($dataList = $dataProvider->models as $i=>$item){ ?>
                    <tr>
                        <td><?= Html::checkbox('choose',false,['value'=>$item->id])?></td>
                        <td><?=$item->id?></td>
                        <td><a href="<?=Url::to(['update','id'=>$item->id]);?>" class="text-primary"><?=Html::encode($item->title)?></a></td>
                        <td><?=$item->slug?></td>
                        <td><label class="label label-<?=$item->type?'default':'primary'?>"><?=$item->type?'字段类型':'列表类型'?></label></td>
                        <td align="center"><?=$item->is_global?'是':'否'?></td>
                        <td align="center">
                            <span class="sort j_sort">
                                <?php
                                $_tag = $i == 0 && ArrayHelper::getValue(Yii::$app->getRequest()->get(),'page',1) == 1?' disabled':'';
                                echo Html::tag('a',Html::tag('span','&#xe62e;',['class'=>'iconfont']),['class'=>'sort-up'.$_tag,'href'=>Url::to(['sort','id'=>$item->id,'mode'=>1]),'title'=>'上移']);
                                $_tag = $i+1 == count($dataList) && ArrayHelper::getValue(Yii::$app->getRequest()->get(),'page',1) == $dataProvider->pagination->getPageCount()?' disabled':'';
                                echo Html::tag('a',Html::tag('span','&#xe62d;',['class'=>'iconfont']),['class'=>'sort-down'.$_tag,'href'=>Url::to(['sort','id'=>$item->id,'mode'=>0]),'title'=>'下移']);
                                unset($_tag);
                                ?>
                            </span>
                        </td>
                        <td class="opt" align="center">
                            <?php if($item->type){?>
                                <?= Html::a('碎片管理', ['fragment/index', 'category_id' => $item->id], ['class' => 'text-primary']) ?>
                                <span>|</span>
                            <?php }else{?>
                                <span style="opacity: .5;">碎片管理</span>
                                <span style="opacity: .5;">|</span>
                            <?php }?>
                            <?= Html::a(Yii::t('common','Modify'), ['update', 'id' => $item->id], ['class' => 'text-primary']) ?>
                            <?= Html::a(Yii::t('common','Delete'), ['delete','id' => $item->id],['class'=>'j_batch','data-action'=>'del']) ?>
                        </td>
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
        <span>|</span>
        <?= Html::a(Yii::t('common','Batch sort'), ['sort'],['id'=>'j_sort_batch','data-depth'=>1,'data-pid'=>0,'data-empty'=>empty($dataList)?1:0]) ?>
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