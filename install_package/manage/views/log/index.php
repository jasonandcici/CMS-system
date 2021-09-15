<?php
/**
 * @block topButton 顶部按钮
 * @var $searchModel
 * @var $dataProvider
 * @var $userAccessButton
 */

use manage\assets\ListAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use common\widgets\ActiveForm;
use yii\widgets\LinkPager;

$this->title = '日志管理';

ListAsset::register($this);
$this->registerJsFile('@web/js/pages/role.js',['depends' => [ListAsset::className()]]);
$this->registerJs("listApp.init();roleApp.init();", View::POS_READY);

$typeTitle = [
    'delete'=>'删除',
    'update'=>'更新',
    'create'=>'新增',
    'login'=>'登陆'
];
?>

<!-- 搜索框开始 -->
<?php $form = ActiveForm::begin([
    'action' => [Yii::$app->controller->action->id],
    'method' => 'get',
    'options'=>['class' => 'form-inline search-data'],
]); ?>
    <!-- 表单控件开始 -->
    <?= $form->field($searchModel, 'crate_user')->label('用户名') ?>
    <?= $form->field($searchModel, 'site_name')->label('操作站点') ?>
    <?= $form->field($searchModel, 'operation_type')->dropDownList($typeTitle,['prompt'=>'不限'])->label('操作类型') ?>
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
                    <td><?=Yii::t('common','Id')?></td>
                    <td>用户名</td>
                    <td>操作站点</td>
                    <td align="center">操作类型</td>
                    <td>操作内容</td>
                    <td align="center">时间</td>
                    <?php if($userAccessButton['delete']){?>
                    <td align="center">操作</td>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach($dataList = $dataProvider->models as $item){ ?>
                    <tr>
                        <td><?=$item->id?></td>
                        <td><?=$item->crate_user?></td>
                        <td><?=$item->site_name?:'--'?></td>
                        <td align="center"><label class="label label-info"><?=$typeTitle[$item->operation_type]?></label></td>
                        <td><?=$item->content?></td>
                        <td align="center"><?=date('Y-m-d H:i',$item->create_time)?></td>
                        <?php if($userAccessButton['delete']){?>
                        <td align="center"><?= Html::a(Yii::t('common','Delete'), ['delete','id' => $item->id],['class'=>'j_batch','data-action'=>'del']) ?></td>
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
    <?= Html::beginForm('', 'get', ['class' => 'form-inline pagination-go','id'=>'j_pagination_go']) ?>
    <div class="form-group">
        <label><?=Yii::t('common','Jump to')?></label>
        <?= Html::input('text', 'page', 1, ['class' => 'form-control']) ?>
        <label><?=Yii::t('common','Page')?></label>
    </div>
    <?= Html::endForm() ?>
    <?=LinkPager::widget(['pagination' => $dataProvider->pagination,'hideOnSinglePage'=>false,'firstPageLabel'=>'<span class="iconfont">&#xe624;</span>','prevPageLabel'=>'<span class="iconfont">&#xe61d;</span>','lastPageLabel'=>'<span class="iconfont">&#xe623;</span>','nextPageLabel'=>'<span class="iconfont">&#xe622;</span>']); ?>
</nav><!-- 数据分页结束 -->