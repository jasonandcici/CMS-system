<?php
/**
 * @block topButton 顶部按钮
 * @var $searchModel
 * @var $dataProvider
 * @var $dataList
 * @var $showCreateButton
 * @var $categoryInfo
 * @var $formModel
 * @var $userAccessButton
 * @var $commentObject
 * @var $categoryList
 */

use manage\assets\ListAsset;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

$this->title = '评论管理';

ListAsset::register($this);
$this->registerJs("listApp.init();", View::POS_READY);
?>

<!-- 搜索框开始 -->
<?php $form = ActiveForm::begin([
	'method' => 'get',
	'options'=>['class' => 'form-inline search-data'],
]);
 ?>
<?= $form->field($searchModel, 'is_enable')->dropDownList([1=>'已审核',0=>'未审核'], ['prompt'=>'—不限—','class'=>'form-control'])->label('状态') ?>
<div class="form-group search-time">
    <label class="control-label">评论日期</label>
    <div class="input-group" style="width: 135px;">
		<?=Html::activeHiddenInput($searchModel,'searchStartTime',['class'=>'j_date_piker'])?>
        <span class="input-group-addon iconfont">&#xe62c;</span>
    </div>
    ~
    <div class="input-group" style="width: 135px;">
		<?=Html::activeHiddenInput($searchModel,'searchEndTime',['class'=>'j_date_piker'])?>
        <span class="input-group-addon iconfont">&#xe62c;</span>
    </div>
</div>
<?= Html::submitButton(Yii::t('common','Filter'), ['class' => 'btn btn-info']) ?>
<?php ActiveForm::end(); ?>

<div class="panel panel-default list-data">
	<div class="panel-body">
		<div class="table-responsive scroll-bar">
			<table class="table table-hover" id="list_data">
				<thead>
				<tr>
					<td width="60"><?=Yii::t('common','Select')?></td>
					<td><?=Yii::t('common','Id')?></td>
                    <td>用户</td>
                    <td style="min-width: 120px;">评论内容</td>
                    <td style="min-width: 120px;">评论对象</td>
					<td align="center" width="150">评论时间</td>
					<td align="center" width="100">状态</td>
					<?php if($userAccessButton['view'] || $userAccessButton['delete']){?>
						<td align="center" width="120"><?=Yii::t('common','Operation')?></td>
					<?php } ?>
				</tr>
				</thead>
				<tbody>
				<?php foreach($dataList as $item){ ?>
					<tr>
						<td><?= Html::checkbox('choose',false,['value'=>$item->id])?></td>
						<td><?=$item->id?></td>
                        <td>
							<?=$item->userProfile->nickname?>
                        </td>
						<td><?=StringHelper::truncate($item->content,48)?></td>
                        <td>
                            <?php
                            if(empty($commentObject[$item->id])){echo '--';}else{
                                $currCategory = $categoryList[$item->category_id];

	                            echo '【'.$currCategory['title'].'】 ';

                                $currCategory['expand'] = json_decode($currCategory['expand']);
                                if($currCategory['expand']->enable_detail){
                                    $url = empty($currCategory['slug'])?'/category_'.$currCategory['id']:'/'.$currCategory['slug'];
                                    if(!$this->context->siteInfo->is_default) $url = '/'.$this->context->siteInfo->slug.$url;
                                    $preview = '';
                                    if($commentObject[$item->id]->status != 1 || ($commentObject[$item->id]->status == 1 && $commentObject[$item->id]->is_login)){
                                        $preview = '?preview-token='.md5(\common\helpers\SecurityHelper::encrypt($commentObject[$item->id]->id,date('dYm')));
                                    }
                                    echo Html::a(StringHelper::truncate($commentObject[$item->id]->title,20), $url.'/'.$commentObject[$item->id]->id.$this->context->config['site']['urlSuffix'].$preview,['target'=>'_blank']);
                                }else{
                                    echo '<span class="text-muted">--</span>';
                                }
                            }
                            ?>
                        </td>
						<td align="center"><?=date('Y-m-d',$item->create_time)?></td>
						<td align="center"><label class="label label-<?=$item->is_enable?'info':'warning'?>"><?=$item->is_enable?'已通过':'待审核'?></label></td>
						<?php if($userAccessButton['view'] || $userAccessButton['delete']){?>
							<td class="opt" align="center">
								<?php if($userAccessButton['view']){?>
									<?= Html::a('查看详情', ['view','id' => $item->id,'layout'=>'base'], ['class' => 'text-primary j_dialog_open','data-size'=>'large','data-cancel-class'=>'hide','data-height'=>400]) ?>
								<?php } if($userAccessButton['delete']){?>
									<?= Html::a(Yii::t('common','Delete'), ['delete','id' => $item->id],['class'=>'j_batch','data-action'=>'del']) ?>
								<?php } ?>
							</td>
						<?php }?>
					</tr>
				<?php } ?>
				</tbody>
			</table>
			<?=empty($dataList)?'<p class="list-data-default">'.Yii::t('common','No Data Found !').'</p>':''?>
		</div>
	</div>
</div>
<nav class="nav-operation clearfix">
	<div class="tools">
		<a href="javascript:;" id="j_choose_all"><?=Yii::t('common','Select All')?></a>
		<a href="javascript:;" id="j_choose_reverse"><?=Yii::t('common','Select Invert')?></a>
		<a href="javascript:;" id="j_choose_empty"><?=Yii::t('common','Clears all')?></a>
		<?php if($userAccessButton['delete'] ||$userAccessButton['status']){?>
			<span>|</span>
		<?php } ?>
		<?php if($userAccessButton['delete']){?>
			<?= Html::a(Yii::t('common','Batch delete'), ['delete'],['class'=>'j_batch','data-action'=>'batchDel']) ?>
		<?php } if($userAccessButton['status']){?>
			<?= Html::a('取消通过', ['status','value'=>0],['class'=>'btn btn-xs btn-info j_batch','data-action'=>'batchStatus','data-value'=>0,'title'=>Yii::t('common','Batch disable')]) ?>
			<?= Html::a('审核通过', ['status','value'=>1],['class'=>'btn btn-xs btn-success j_batch','data-action'=>'batchStatus','data-value'=>1,'title'=>Yii::t('common','Batch enable')]) ?>
		<?php } ?>
	</div>
	<?= Html::beginForm('', 'get', ['class' => 'form-inline pagination-go','id'=>'j_pagination_go']) ?>
	<div class="form-group">
		<label><?=Yii::t('common','Jump to')?></label>
		<?= Html::input('text', 'page', 1, ['class' => 'form-control']) ?>
		<label><?=Yii::t('common','Page')?></label>
	</div>
	<?= Html::endForm() ?>
	<?=LinkPager::widget(['pagination' => $dataProvider->pagination,'hideOnSinglePage'=>false,'firstPageLabel'=>'<span class="iconfont">&#xe624;</span>','prevPageLabel'=>'<span class="iconfont">&#xe61d;</span>','lastPageLabel'=>'<span class="iconfont">&#xe623;</span>','nextPageLabel'=>'<span class="iconfont">&#xe622;</span>']); ?>
</nav>