<?php
/**
 * @block topButton 顶部按钮
 * @var $searchModel
 * @var $dataProvider
 */

use manage\assets\ListAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use common\widgets\ActiveForm;
use yii\widgets\LinkPager;

$this->title = '模型管理';

ListAsset::register($this);
$this->registerJs("listApp.init();", View::POS_READY);
?>
<?php $this->beginBlock('topButton'); ?>
<?= Html::a('新增模型', ['create'], ['class' => 'btn btn-primary']) ?>
<?php $this->endBlock(); ?>

<!-- 搜索框开始 -->
<?php $form = ActiveForm::begin([
    'action' => [Yii::$app->controller->action->id],
    'method' => 'get',
    'options'=>['class' => 'form-inline search-data'],
]); ?>
    <!-- 表单控件开始 -->
    <?= $form->field($searchModel, 'name') ?>
    <?= $form->field($searchModel, 'type')->dropDownList($this->context->modelTypeList, ['class'=>'form-control','prompt'=>'—不限—'])?>
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
                    <td>标题</td>
                    <td>名称</td>
                    <td>类型</td>
                    <td align="center"><?=Yii::t('common','Operation')?></td>
                </tr>
                </thead>
                <tbody>
                <?php foreach($dataList = $dataProvider->models as $item){ ?>
                    <tr>
                        <td><?=$item->id?></td>
                        <td><a href="<?=Url::to(['update','id'=>$item->id]);?>" class="text-primary"><?=Html::encode($item->title)?></a></td>
                        <td><?=Html::encode($item->name)?></td>
                        <td><span class="label label-info"><?=$this->context->modelTypeList[$item->type]?></span></td>
                        <td class="opt" align="center">

                            <?php if($this->context->isSuperAdmin && YII_DEBUG):?>
                            <?= Html::a($item->is_generate?'重新生成':'生成模型', ['generate', 'id' => $item->id], ['class' => 'text-primary js-generate']) ?>
                            <span class="text-muted">|</span>
                            <?= Html::a('字段管理', ['field/index', 'model_id' => $item->id], ['class' => 'text-primary']) ?>
                            <span class="text-muted">|</span>
                            <?php endif;?>
                            <?= Html::a(Yii::t('common','Modify'), ['update', 'id' => $item->id], ['class' => 'text-primary']) ?>
                            <?php if($this->context->isSuperAdmin && YII_DEBUG):?>
                            <?= Html::a(Yii::t('common','Delete'), ['delete','id' => $item->id],['class'=>'j_batch','data-action'=>'del']) ?>
                            <?php endif;?>
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
    <?= Html::beginForm('', 'get', ['class' => 'form-inline pagination-go','id'=>'j_pagination_go']) ?>
    <div class="form-group">
        <label><?=Yii::t('common','Jump to')?></label>
        <?= Html::input('text', 'page', 1, ['class' => 'form-control']) ?>
        <label><?=Yii::t('common','Page')?></label>
    </div>
    <?= Html::endForm() ?>
    <?=LinkPager::widget(['pagination' => $dataProvider->pagination,'hideOnSinglePage'=>false,'firstPageLabel'=>'<span class="iconfont">&#xe624;</span>','prevPageLabel'=>'<span class="iconfont">&#xe61d;</span>','lastPageLabel'=>'<span class="iconfont">&#xe623;</span>','nextPageLabel'=>'<span class="iconfont">&#xe622;</span>']); ?>
</nav><!-- 数据分页结束 -->

<?php $this->beginBlock('endBlock');?>
<script>
    $(function () {
        $('.js-generate').click(function () {
            var $this = $(this);
            commonApp.dialog.warning(($this.text()==='生成模型'?'':'执行此项可能会导致数据丢失，')+'您确定要执行此操作吗？',{
                confirm:function () {
                    $.ajax({
                        type: 'get',
                        url: $this.attr('href'),
                        dataType: 'json',
                        beforeSend: function (XMLHttpRequest) {
                            commonApp.loading('系统操作中，请稍后…');
                        },
                        complete: function () {
                            commonApp.loading(false);
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            var _html = '';
                            if(XMLHttpRequest.responseText !==null && XMLHttpRequest.responseText !== '') _html = '<p>错误原因是：<br>' + XMLHttpRequest.responseText + '</p>';
                            this.errorCallback('操作失败：'+ errorThrown + '. textStatus:' + textStatus + '. status:'+ XMLHttpRequest.status + '.'+ _html);
                        },
                        success: function (result) {
                            var _html = '';
                            if(result.status === 1){
                                commonApp.notify.success('操作成功！');
                                $this.text('重新生成');
                            }else{
                                commonApp.notify.error('操作失败！');
                            }
                        }
                    })
                }
            });

            return false;
        });
    });
</script>
<?php $this->endBlock();?>
