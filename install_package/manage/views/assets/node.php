<?php
/**
 * @block topButton 顶部按钮
 * @var $searchModel
 * @var $dataProvider
 * @var $roleList
 * @var $multiple
 * @var $categoryDropDownList
 * @var $categoryDropDownDisable
 */

use manage\assets\ListAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use common\widgets\ActiveForm;
use yii\widgets\LinkPager;

$this->title = '列表';

$this->registerJsFile('@web/js/plugins/related_data_frame.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJs("
    $('select[prety]').each(function(){
      var \$this = $(this),
        _val = \$this.data('val'),
        _placeholder = \$this.data('placeholder')||'';
      \$this.select2({
        placeholder: _placeholder
      });
      if(_val) \$this.select2('val',_val);
    });
    
    if(".Yii::$app->getRequest()->get('hide_category_search',"false")."){
        $('.field-newssearch-category_id').hide();
    }
",View::POS_READY)
?>
<!-- 搜索框开始 -->
<?php $form = ActiveForm::begin([
    'action' => [Yii::$app->controller->action->id],
    'method' => 'get',
    'options'=>['class' => 'form-inline search-data'],
]); ?>
<!-- 表单控件开始 -->
<?=Html::input('hidden','m',Yii::$app->getRequest()->get('m'))?>
<?=Html::input('hidden','cm',Yii::$app->getRequest()->get('cm'))?>
<?=Html::input('hidden','id',Yii::$app->getRequest()->get('id'))?>
<?=Html::input('hidden','multiple',Yii::$app->getRequest()->get('multiple'))?>

<?php if(Yii::$app->getRequest()->get('hide_category_search')){
    echo Html::hiddenInput('hide_category_search',1);
}?>

<?= $form->field($searchModel, 'title')->label('标题') ?>
<?= $form->field($searchModel, 'category_id')->dropDownList($categoryDropDownList,['options'=>$categoryDropDownDisable,'prety'=>true,'data-placeholder'=>'不限','prompt'=>'不限'])->label('栏目') ?>
<?= $form->field($searchModel, 'status')->dropDownList([1=>'启用',0=>'禁用'], ['prompt'=>'—不限—','class'=>'form-control'])->label('状态') ?>
<div class="form-group">
    <label class="control-label">是否已选</label>
    <?=Html::dropDownList('filter',Yii::$app->getRequest()->get('filter'),[1=>'已选',0=>'未选'],['prompt'=>'—不限—','class'=>'form-control'])?>
</div>
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
                    <td>标题</td>
                    <td width="100"><?=Yii::t('common','Select')?></td>
                </tr>
                </thead>
                <tbody>
                <?php foreach($dataList = $dataProvider->models as $item){ ?>
                    <tr>
                        <td><label for="choose_<?=$item->id?>" style="display: block;margin-bottom: 0;font-weight: normal;"><?=Html::encode($item->title)?></label></td>
                        <td><?= $multiple?Html::checkbox('choose',false,['id'=>'choose_'.$item->id,'value'=>$item->id]):Html::radio('choose',false,['id'=>'choose_'.$item->id,'value'=>$item->id])?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?=empty($dataList)?'<p class="list-data-default">'.Yii::t('common','No Data Found !').'</p>':''?>
        </div>
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