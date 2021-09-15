<?php
/**
 * @block topButton 顶部按钮
 * @var $searchModel
 * @var $dataProvider
 * @var $userAccessButton
 */

use manage\assets\ListAsset;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

$this->title = '敏感词管理';

ListAsset::register($this);
$this->registerJs("listApp.init();", View::POS_READY);

$dataList = $dataProvider->models;
?>

<?php if($userAccessButton['create']) { $this->beginBlock('topButton'); ?>
	<?= Html::a('添加敏感词', ['create'], ['class' => 'btn btn-primary']) ?>
	<?php $this->endBlock();} ?>

<!-- 搜索框开始 -->
<?php $form = ActiveForm::begin([
	'method' => 'get',
	'options'=>['class' => 'form-inline search-data'],
]);
 ?>
<?= $form->field($searchModel, 'name') ?>
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
                    <td>敏感词</td>
					<?php if($userAccessButton['delete']){?>
						<td align="center" width="120"><?=Yii::t('common','Operation')?></td>
					<?php } ?>
				</tr>
				</thead>
				<tbody>
				<?php foreach($dataList as $item){ ?>
					<tr>
						<td><?= Html::checkbox('choose',false,['value'=>$item->id])?></td>
						<td><?=$item->id?></td>
                        <td><?=$item->name?></td>
						<?php if($userAccessButton['delete']){?>
							<td class="opt" align="center">
								<?php if($userAccessButton['delete']){?>
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
		<?php if($userAccessButton['delete']){?>
			<span>|</span>
			<?= Html::a(Yii::t('common','Batch delete'), ['delete'],['class'=>'j_batch','data-action'=>'batchDel']) ?>
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