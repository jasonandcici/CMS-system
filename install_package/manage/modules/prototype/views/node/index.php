<?php
/**
 * @block topButton 顶部按钮
 * @var $searchModel
 * @var $dataProvider
 * @var $showCreateButton
 * @var $categoryInfo
 * @var $userAccessButton
 */

use common\helpers\ArrayHelper;
use manage\assets\ListAsset;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\web\View;
use common\widgets\ActiveForm;
use yii\widgets\LinkPager;

$pList = ArrayHelper::getParents($this->context->categoryList,$categoryInfo->id);

$relation = Yii::$app->getRequest()->get('relation');
if(!empty($relation)){
    $arrRelation = explode('_',$relation);
    $this->title = $this->context->findDataList($arrRelation[0],true)->where(['id'=>$arrRelation[1]])->one()->title;
    $this->registerCss('
        .page-header{margin-top:0;}
        footer{display:none;}
    ');
    unset($arrRelation);
}else{
    $this->title = '内容管理';
    $this->params['subTitle'] = '('.implode(' / ',ArrayHelper::getColumn($pList,'title')).')';
}

ListAsset::register($this);
\manage\assets\ZtreeAsset::register($this);
$this->registerJs("listApp.init();", View::POS_READY);

$dataList = $dataProvider->models;

$categoryList = ArrayHelper::index($this->context->categoryList,'id');
?>
<?php $this->beginBlock('topButton');
if($showCreateButton && $userAccessButton['create']){
    if(empty($relation)){
        echo Html::a('添加内容', ['create','category_id'=>$categoryInfo->id], ['class' => 'btn btn-primary']);
    }else{
        echo Html::a('添加内容', ['create','category_id'=>$categoryInfo->id,'relation'=>$relation], ['class' => 'btn btn-primary']);
    }
}
$this->endBlock();?>

<!-- 搜索框开始 -->
<?php $form = ActiveForm::begin([
    'action' => [Yii::$app->controller->action->id],
    'method' => 'get',
    'options'=>['class' => 'form-inline search-data'],
]);

if(file_exists(Yii::$app->getModule('prototype')->getViewPath().'/node/_list_'.$categoryInfo->model->name.'.php')){
    echo $this->render('_list_'.$categoryInfo->model->name,['dataList'=>$dataList,'categoryInfo'=>$categoryInfo,'searchModel'=>$searchModel,'searchForm'=>$form]);
}
if(file_exists(Yii::$app->getModule('prototype')->getViewPath().'/node/_custom_list_'.$categoryInfo->model->name.'.php')){
    echo $this->render('_custom_list_'.$categoryInfo->model->name,['dataList'=>$dataList,'categoryInfo'=>$categoryInfo,'searchModel'=>$searchModel,'searchForm'=>$form]);
}
?>
    <!-- 表单控件开始 -->
    <?= Html::input('hidden', 'category_id', $categoryInfo->id) ?>
    <?php if(!empty($relation)){
        echo Html::hiddenInput('relation',$relation);
    } ?>
    <?= $form->field($searchModel, 'title')->label('标题') ?>
    <?php if (isset($this->blocks['search'])): ?>
        <?= $this->blocks['search'] ?>
    <?php endif; ?>
    <?php
    $searchStatus = [1=>'启用',0=>'禁用'];
    if($this->context->config['member']['examine']){
        $searchStatus[4] = '待审核';
        $searchStatus[5] = '已驳回';
    }
    $searchStatus[2] = '草稿箱';
    $searchStatus[3] = '回收站';
    echo $form->field($searchModel, 'status')->dropDownList($searchStatus,['prompt'=>'—不限—','class'=>'form-control'])->label('状态') ?>
    <?php if($categoryInfo->enable_push){ echo $form->field($searchModel, 'is_push')->dropDownList([1=>'推荐',0=>'不推荐'], ['prompt'=>'—不限—','class'=>'form-control'])->label('是否推荐'); } ?>
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
	                <?php if(isset($this->blocks['thead'])):?>
		                <?= $this->blocks['thead'] ?>
	                <?php endif;if (isset($this->blocks['custom_thead'])): ?>
		                <?= $this->blocks['custom_thead'] ?>
	                <?php endif; ?>
                    <td align="center" style="min-width: 80px;">创建时间</td>
                    <?php if($userAccessButton['status']){?>
                    <td align="center" style="min-width: 60px;">状态</td>
                    <?php } if($userAccessButton['sort'] && $searchModel->status != 3){?>
                    <td align="center" width="80">排序</td>
                    <?php } ?>
                    <td align="center" style="min-width: 150px;"><?=Yii::t('common','Operation')?></td>
                </tr>
                </thead>
                <tbody>
                <?php foreach($dataList as $i=>$item){ ?>
                    <tr>
                        <td><?= Html::checkbox('choose',false,['value'=>$item->id])?></td>
                        <td><?=$item->id?></td>
                        <td>
                            <?php if($userAccessButton['update'] && $searchModel->status < 3){?>
                            <a href="<?=empty($relation)?Url::to(['update','category_id'=>$item->category_id,'id'=>$item->id]):Url::to(['update','category_id'=>$item->category_id,'id'=>$item->id,'relation'=>$relation]);?>" class="text-primary"><?=StringHelper::truncate(strip_tags($item->title),58)?></a>
                            <?php }else{ echo Html::tag('span',StringHelper::truncate(strip_tags($item->title),58)); } ?>
                            <?php if(isset($item->is_push) && $item->is_push == 1){?><span class="status-data"><em>推荐</em></span><?php }?>
                        </td>
	                    <?php if(isset($this->blocks['tbody'.$item->id])):?>
		                    <?= $this->blocks['tbody'.$item->id] ?>
	                    <?php endif;if (isset($this->blocks['custom_tbody'.$item->id])): ?>
		                    <?= $this->blocks['custom_tbody'.$item->id] ?>
	                    <?php endif; ?>
                        <td align="center"><?=date('Y-m-d',$item->create_time)?></td>
                        <?php if($userAccessButton['status']){?>
                        <td align="center">
	                        <?php if($item->status === 1){?>
		                        <?= Html::a('<span class="iconfont">&#xe62a;</span>', ['status','category_id'=>$item->category_id, 'id' => $item->id], ['class' => 'j_batch status-'.$item->id,'data-action'=>'status','data-value'=>0,'title'=>Yii::t('common','Disable')]) ?>
	                        <?php }elseif($item->status === 0){?>
		                        <?= Html::a('<span class="iconfont">&#xe625;</span>', ['status','category_id'=>$item->category_id, 'id' => $item->id], ['class' => 'j_batch status-'.$item->id,'data-action'=>'status','data-value'=>1,'title'=>Yii::t('common','Enable')]) ?>
	                        <?php }elseif($item->status === 2){?>
                                <span class="label label-info">草稿</span>
	                        <?php }elseif($item->status === 3){?>
                                <span class="label label-info">已回收</span>
	                        <?php }else{?>
                                <span class="label label-info">其他</span>
	                        <?php }?>
                        </td>
                        <?php } if($userAccessButton['sort'] && $searchModel->status != 3){?>
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
                        <?php } ?>
                        <td class="opt" align="center">
                            <?php if (isset($this->blocks['custom_operate'.$item->id])): ?>
                                <?= $this->blocks['custom_operate'.$item->id] ?>
                            <?php endif; ?>
                            <?php
                            // 预览链接
                            $currCategory = $categoryList[$item->category_id];
                            $currCategory['expand'] = json_decode($currCategory['expand']);
                            if($currCategory['expand']->enable_detail){
                                $url = empty($currCategory['slug'])?'/category_'.$currCategory['id']:'/'.$currCategory['slug'];
                                if(!$this->context->siteInfo->is_default) $url = '/'.$this->context->siteInfo->slug.$url;
                                $preview = '';
                                if($item->status != 1 || ($item->status == 1 && $item->is_login)){
                                    $preview = '?preview-token='.md5(\common\helpers\SecurityHelper::encrypt($item->id,date('dYm')));
                                }
                                echo Html::a(Yii::t('common','Preview'), $url.'/'.$item->id.$this->context->config['site']['urlSuffix'].$preview,['target'=>'_blank','class'=>'opt-preview']);
                            }else{
                                echo '<span class="text-muted opt-preview">'.Yii::t('common','Preview').'</span>';
                            }
                            ?>

                            <?php if($userAccessButton['update'] || $userAccessButton['delete']){?>
                            <span class="text-muted opt-line">|</span>
                            <?php } ?>
                            <?php if($userAccessButton['update'] && $searchModel->status < 3){?>
                            <?= Html::a(Yii::t('common','Modify'), (empty($relation)?['update','category_id'=>$item->category_id, 'id' => $item->id]:['update','category_id'=>$item->category_id, 'id' => $item->id,'relation'=>$relation]), ['class' => 'opt-update text-primary']) ?>
                            <?php } if($userAccessButton['delete']){?>
                            <?= Html::a(Yii::t('common','Delete'), ['delete','category_id'=>$item->category_id,'id' => $item->id],['class'=>'opt-delete j_batch','data-action'=>'del','data-title'=>($item->status < 2)?' （删除后可在回收站中找回）':'']) ?>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?php echo empty($dataList)?'<p class="list-data-default">'.Yii::t('common','No Data Found !').'</p>':'';?>
        </div>
    </div>
</div><!-- 数据列表结束 -->

<!-- 数据分页开始 -->
<nav class="nav-operation clearfix">
    <div class="tools">
        <a href="javascript:;" id="j_choose_all"><?=Yii::t('common','Select All')?></a>
        <a href="javascript:;" id="j_choose_reverse"><?=Yii::t('common','Select Invert')?></a>
        <a href="javascript:;" id="j_choose_empty"><?=Yii::t('common','Clears all')?></a>
	    <?php if($userAccessButton['sort'] || $userAccessButton['delete'] || $userAccessButton['move']  || $userAccessButton['status']){?>
            <span>|</span>
	    <?php } ?>
	    <?php if($userAccessButton['delete']){?>
		    <?= Html::a(Yii::t('common','Batch delete'), ['delete','category_id'=>$categoryInfo->id],['class'=>'j_batch','data-action'=>'batchDel']) ?>
	    <?php } if($searchModel->status < 2){ if($userAccessButton['sort']){?>
		    <?= Html::a(Yii::t('common','Batch sort'), ['sort','category_id'=>$categoryInfo->id],['id'=>'j_sort_batch','data-depth'=>1,'data-pid'=>0,'data-empty'=>empty($dataList)?1:0]) ?>
	    <?php } if($userAccessButton['move']){ ?>

		    <?= Html::a(Yii::t('common','Batch move'), ['move','category_id'=>$categoryInfo->id],['id'=>'j_move_batch']) ?>

	    <?php } } if($userAccessButton['status']){?>
		    <?php if ($searchModel->status == 2){ ?>
			    <?= Html::a('批量发布', ['status','category_id'=>$categoryInfo->id,'value'=>1],['class'=>'btn btn-xs btn-primary j_batch','data-action'=>'batchStatus','data-value'=>1]) ?>
		    <?php }elseif ($searchModel->status == 3){?>
			    <?= Html::a('批量恢复', ['status','category_id'=>$categoryInfo->id,'value'=>1],['class'=>'btn btn-xs btn-primary j_batch','data-action'=>'batchStatus','data-value'=>1]) ?>
		    <?php }else{ ?>
			    <?= Html::a('&#xe62a;', ['status','category_id'=>$categoryInfo->id,'value'=>1],['class'=>'iconfont j_batch','data-action'=>'batchStatus','data-value'=>1,'title'=>Yii::t('common','Batch enable')]) ?>
			    <?= Html::a('&#xe625;', ['status','category_id'=>$categoryInfo->id,'value'=>0],['class'=>'iconfont j_batch','data-action'=>'batchStatus','data-value'=>0,'title'=>Yii::t('common','Batch disable')]) ?>
		    <?php } ?>
	    <?php } ?>
    </div>
    <?= Html::beginForm('', 'get', ['class' => 'form-inline pagination-go','id'=>'j_pagination_go']) ?>
    <div class="form-group">
        <label><?=Yii::t('common','Jump to')?></label>
        <?= Html::input('text', 'page',Yii::$app->getRequest()->get('page',1), ['class' => 'form-control']) ?>
        <label><?=Yii::t('common','Page')?></label>
    </div>
    <div class="form-group">
        <label><?=Yii::t('common',',Per page')?></label>
        <?php
        $defaultPageSize = array_key_exists('page_size',Yii::$app->params)?Yii::$app->params['page_size']:15;
        echo Html::dropDownList('per-page',Yii::$app->getRequest()->get('per-page',$defaultPageSize),[$defaultPageSize=>$defaultPageSize,50=>50,100=>100,200=>200],['id'=>'j_pageSize','class'=>'form-control','style'=>'width:auto;'])?>
        <label><?=Yii::t('common','item')?></label>
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
                <div class="dd-handle"><?=$item->id.'：'.StringHelper::truncate($item->title,150)?></div>
            </li>
        <?php }unset($dataList);?>
        </ol>
        <form action="javascript:;" method="post">
            <?=Html::hiddenInput(Yii::$app->request->csrfParam,Yii::$app->request->csrfToken)?>
            <?=Html::hiddenInput('data',null,['class'=>'input-data'])?>
        </form>
    </div>
</script>
<input type="hidden" name="expand_nav" id="j_expand_nav" data-mid="<?=$categoryInfo->model_id?>" data-cid="<?=$categoryInfo->id?>" value="<?=Url::to(['/prototype/category/expand_nav','render'=>false])?>">