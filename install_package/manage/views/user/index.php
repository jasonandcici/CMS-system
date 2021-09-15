<?php
/**
 * @block topButton 顶部按钮
 * @var $searchModel
 * @var $dataProvider
 * @var $roleList
 * @var $userAccessButton
 */

use manage\assets\ListAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use common\widgets\ActiveForm;
use yii\widgets\LinkPager;

$this->title = '管理员管理';

ListAsset::register($this);
$this->registerJs("listApp.init();", View::POS_READY);
?>
<?php if($userAccessButton['create']){ $this->beginBlock('topButton'); ?>
<?= Html::a('新增管理员', ['create'], ['class' => 'btn btn-primary']) ?>
<?php $this->endBlock(); } ?>

<!-- 搜索框开始 -->
<?php $form = ActiveForm::begin([
    'action' => [Yii::$app->controller->action->id],
    'method' => 'get',
    'options'=>['class' => 'form-inline search-data'],
]); ?>
    <!-- 表单控件开始 -->
    <?= $form->field($searchModel, 'username')->label('用户名') ?>
    <?= $form->field($searchModel, 'status')->dropDownList([1=>'启用',0=>'禁用'], ['prompt'=>'—不限—','class'=>'form-control'])->label('状态') ?>
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
                    <td>用户名</td>
                    <td>角色</td>
                    <td>邮箱</td>
                    <?php if($userAccessButton['status']){?>
                    <td align="center">状态</td>
                    <?php } if($userAccessButton['update'] || $userAccessButton['delete']){?>
                    <td align="center"><?=Yii::t('common','Operation')?></td>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach($dataList = $dataProvider->models as $item){ ?>
                    <tr>
                        <td><?= Html::checkbox('choose',false,['value'=>$item->id])?></td>
                        <td><?=$item->id?></td>
                        <td><?=Html::encode($item->username)?></td>
                        <td>
                            <?php foreach(Yii::$app->getAuthManager()->getRolesByUser($item->id) as $role){?>
                                <span class="label label-info"><?=$role->description?></span>
                            <?php } ?>
                        </td>
                        <td><?=$item->email?Html::encode($item->email):'——'?></td>
                        <?php if($userAccessButton['status']){?>
                        <td align="center">
                            <?php if($item->status == 1){?>
                                <?= Html::a('<span class="iconfont">&#xe62a;</span>', ['status', 'id' => $item->id], ['class' => 'j_batch status-'.$item->id,'data-action'=>'status','data-value'=>0,'title'=>Yii::t('common','Disable')]) ?>
                            <?php }else{?>
                                <?= Html::a('<span class="iconfont">&#xe625;</span>', ['status', 'id' => $item->id], ['class' => 'j_batch status-'.$item->id,'data-action'=>'status','data-value'=>1,'title'=>Yii::t('common','Enable')]) ?>
                            <?php }?>
                        </td>
                        <?php } if($userAccessButton['update'] || $userAccessButton['delete']){?>
                        <td class="opt" align="center">
                            <?php if($userAccessButton['update']){?>
                                <?= Html::a(Yii::t('common','Modify'), ['update', 'id' => $item->id], ['class' => 'text-primary']) ?>
                            <?php } if($userAccessButton['delete']){ ?>
                                <?php if(Yii::$app->getUser()->getId() == $item->id):?>
                                <span class="text-muted">删除</span>
                                <?php else:?>
                                <?= Html::a(Yii::t('common','Delete'), ['delete','id' => $item->id],['class'=>'j_batch','data-action'=>'del']) ?>
                                <?php endif;?>
                            <?php } ?>
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
        <?php if($userAccessButton['status'] || $userAccessButton['delete']){?>
        <span>|</span>
        <?php } if($userAccessButton['delete']){?>
        <?= Html::a(Yii::t('common','Batch delete'), ['delete'],['class'=>'j_batch','data-action'=>'batchDel']) ?>
        <?php } if($userAccessButton['status']){?>
        <?= Html::a('&#xe62a;', ['status','value'=>1],['class'=>'iconfont j_batch','data-action'=>'batchStatus','data-value'=>1,'title'=>Yii::t('common','Batch enable')]) ?>
        <?= Html::a('&#xe625;', ['status','value'=>0],['class'=>'iconfont j_batch','data-action'=>'batchStatus','data-value'=>0,'title'=>Yii::t('common','Batch disable')]) ?>
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
</nav><!-- 数据分页结束 -->