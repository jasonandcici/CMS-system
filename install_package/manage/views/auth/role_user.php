<?php
/**
 * @block topButton 顶部按钮
 * @var $searchModel
 * @var $dataProvider
 */

use yii\helpers\Html;
use common\widgets\ActiveForm;
use yii\widgets\LinkPager;

$this->title = '角色关联用户';


$this->registerJsFile('@web/js/plugins/related_data_frame.js',['depends' => [\yii\web\JqueryAsset::className()]]);
?>
<!-- 搜索框开始 -->
<?php $form = ActiveForm::begin([
    'action' => [Yii::$app->controller->action->id],
    'method' => 'get',
    'options'=>['class' => 'form-inline search-normal'],
]); ?>
<!-- 表单控件开始 -->
<?=Html::input('hidden','name',Yii::$app->getRequest()->get('name',0))?>
<?= $form->field($searchModel, 'username')->label('用户名') ?>
<div class="form-group">
    <label class="control-label">是否已设置</label>
    <?=Html::dropDownList('filter',Yii::$app->getRequest()->get('filter'),[1=>'已设置',0=>'未设置'],['prompt'=>'—不限—','class'=>'form-control'])?>
</div>
<!-- 表单控件结束 -->
<?= Html::submitButton(Yii::t('common','Filter'), ['class' => 'btn btn-info']) ?>
<?php ActiveForm::end(); ?><!-- 搜索框结束 -->

<!-- 数据列表开始 -->
<div class="panel panel-default list-data">
    <div class="panel-body">
        <table class="table table-hover" id="list_data">
            <thead>
            <tr>
                <td><?=Yii::t('common','Select')?></td>
                <td>用户名</td>
                <td>手机</td>
                <td>邮箱</td>
            </tr>
            </thead>
            <tbody>
            <?php foreach($dataList = $dataProvider->models as $item){?>
                <tr>
                    <td><?= Html::checkbox('choose',false,['id'=>'choose_'.$item->id,'value'=>$item->id])?></td>
                    <td><label for="choose_<?=$item->id?>" style="font-weight: normal;display: block"><?=Html::encode($item->username)?></label></td>
                    <td><?=Html::encode($item->mobile?:'--')?></td>
                    <td><?=Html::encode($item->email?:'--')?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?=empty($dataList)?'<p class="list-data-default">'.Yii::t('common','No Data Found !').'</p>':''?>
    </div>
</div><!-- 数据列表结束 -->

<!-- 数据分页开始 -->
<nav class="nav-operation pagination-iframe clearfix">
    <?= Html::beginForm([Yii::$app->controller->action->id], 'get', ['class' => 'form-inline pagination-go','id'=>'j_pagination_go']) ?>
    <div class="form-group">
        <label>跳到</label>
        <?= Html::input('text', 'page', 1, ['class' => 'form-control']) ?>
        <label><?=Yii::t('common','Page')?></label>
    </div>
    <?= Html::endForm() ?>
    <?=LinkPager::widget(['pagination' => $dataProvider->pagination,'hideOnSinglePage'=>false,'firstPageLabel'=>'<span class="iconfont">&#xe624;</span>','prevPageLabel'=>'<span class="iconfont">&#xe61d;</span>','lastPageLabel'=>'<span class="iconfont">&#xe623;</span>','nextPageLabel'=>'<span class="iconfont">&#xe622;</span>']); ?>
</nav><!-- 数据分页结束 -->