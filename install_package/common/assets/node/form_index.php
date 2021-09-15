<?php
/**
 * This is the template for generating the model class of a specified table.
 */
use common\helpers\ArrayHelper;

/* @var $model  */
/* @var $fields  */

$newFields = [];
foreach ($fields as $item){
    if(in_array($item->type,['editor','image','image_multiple','attachment','attachment_multiple','passport','date','datetime','captcha','relation_data','relation_category','city','city_multiple'])) continue;
    $newFields[] = $item;
}

echo "<?php\n";
echo "/**
 * @block topButton 顶部按钮
 * @var \$searchModel
 * @var \$dataProvider
 * @var \$showCreateButton
 * @var \$categoryInfo
 * @var \$formModel
 * @var \$userAccessButton
 */

use manage\assets\ListAsset;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;
use common\helpers\HtmlHelper;

\$dataList = \$dataProvider->getModels();

// 数据导出
if(Yii::\$app->getRequest()->get('export',false)){
    \$columns = [];
    foreach (\$searchModel->getAttributes() as \$i=>\$item){
        if(\$i == 'id') continue;
        \$columns[\$i] = ['attribute'=>\$i];
        if(strpos(\$i,'_time')) \$columns[\$i]['format'] = 'date';
    }

    \moonland\phpexcel\Excel::export([
        'models' => \$dataList,
        'format'=>'Excel5',
        'fileName'=>date('Ymd',time()).'_'.\$this->context->modelInfo->title.'('.\$this->context->siteInfo->title.')'.'_v'.substr(time(),5),
        'columns' => [
";


// 导出字段
foreach ($fields as $item){
    if(in_array($item->type,['captcha','relation_data','relation_category','city','city_multiple'])) continue;
    switch ($item->type){
        case 'image':
        case 'image_multiple':
        case 'attachment':
        case 'attachment_multiple':
            echo "            '".$item->name."'=>['attribute'=>'".$item->name."','value'=>function(\$model){
                \$files = [];
                foreach(HtmlHelper::fileDataHandle(\$model->".$item->name.") as \$v){
                    \$files[] = HtmlHelper::getFileItem(\$v);
                }
                return implode(',',\$files);
            }],\n";
            break;
        default:
            echo "            '".$item->name."',\n";
            break;
    }
}


echo "            'create_time'=>['attribute'=>'create_time','value'=>function(\$model){ return date('Y/n/j',\$model->create_time); }],
        ],
    ]);
    exit;
}

\$this->title = '".$model->title."';

ListAsset::register(\$this);
\$this->registerJs(\"listApp.init();\", View::POS_READY);
?>

<!-- 搜索框开始 -->
<?php \$form = ActiveForm::begin([
    'method' => 'get',
    'options'=>['class' => 'form-inline search-data'],
]);
echo Html::input('hidden', 'model_id', \$this->context->modelInfo->id);
echo Html::input('hidden', 'export', false,['id'=>'exportInput']); ?>
";

// 搜索
foreach ($newFields as $item){
    if(!$item->is_search) continue;

    if(in_array($item->type,['radio','radio_inline','checkbox','checkbox_inline','select','select_multiple'])){
        echo "<?= \$form->field(\$searchModel, '".$item->name."')->dropDownList([".\common\entity\models\PrototypeModelModel::optionsMap($item->options)."],['prompt'=>'请选择','data-placeholder'=>'请选择','prety'=>true]) ?>\n";
    }else{
        echo "<?= \$form->field(\$searchModel, '".$item->name."')->textInput() ?>\n";
    }
}

echo "
<?= \$form->field(\$searchModel, 'status')->dropDownList([1=>'已处理',0=>'待处理'], ['prompt'=>'—不限—','class'=>'form-control'])->label('状态') ?>
<div class=\"form-group search-time\">
    <label class=\"control-label\">提交日期</label>
    <div class=\"input-group\" style=\"width: 135px;\">
        <?=Html::activeHiddenInput(\$searchModel,'searchStartTime',['class'=>'j_date_piker'])?>
        <span class=\"input-group-addon iconfont\">&#xe62c;</span>
    </div>
    ~
    <div class=\"input-group\" style=\"width: 135px;\">
        <?=Html::activeHiddenInput(\$searchModel,'searchEndTime',['class'=>'j_date_piker'])?>
        <span class=\"input-group-addon iconfont\">&#xe62c;</span>
    </div>
</div>
<?= Html::submitButton(Yii::t('common','Filter'), ['class' => 'btn btn-info']) ?>
<?= Html::button('导出', ['id'=>'j_export','class' => 'btn btn-primary','style'=>'margin-left:10px;']) ?>
<?php ActiveForm::end(); ?>

<div class=\"panel panel-default list-data\">
    <div class=\"panel-body\">
        <div class=\"table-responsive scroll-bar\">
            <table class=\"table table-hover\" id=\"list_data\">
                <thead>
                <tr>
                    <td width=\"60\"><?=Yii::t('common','Select')?></td>
                    <td><?=Yii::t('common','Id')?></td>
";

// 列表显示标题
foreach ($newFields as $item){
    if(!$item->is_show_list) continue;
    echo "                    <td>".$item->title."</td>\n";
}

echo "
                    <td align=\"center\" width=\"150\">创建时间</td>
                    <td align=\"center\" width=\"100\">状态</td>
                    <?php if(\$userAccessButton['view'] || \$userAccessButton['delete']){?>
                    <td align=\"center\" width=\"200\"><?=Yii::t('common','Operation')?></td>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach(\$dataList as \$item){ ?>
                    <tr>
                        <td><?= Html::checkbox('choose',false,['value'=>\$item->id])?></td>
                        <td><?=\$item->id?></td>
";

//列表显示内容

foreach ($newFields as $item){
    if(!$item->is_show_list) continue;
    echo "                        <td><?=StringHelper::truncate(\$item->".$item->name.",48)?></td>\n";
}

echo "
                        <td align=\"center\"><?=date('Y-m-d',\$item->create_time)?></td>
                        <td align=\"center\"><label class=\"label label-<?=\$item->status?'info':'warning'?>\"><?=\$item->status?'已处理':'待处理'?></label></td>
                        <?php if(\$userAccessButton['view'] || \$userAccessButton['delete']){?>
                        <td class=\"opt\" align=\"center\">
                            <?php if(\$userAccessButton['view']){?>
                            <?= Html::a('查看详情', ['view','model_id'=>\$this->context->modelInfo->id,'id' => \$item->id,'layout'=>'base'], ['class' => 'text-primary j_dialog_open','data-size'=>'large','data-cancel-class'=>'hide','data-height'=>400]) ?>
                            <?php } if(\$userAccessButton['delete']){?>
                            <?= Html::a(Yii::t('common','Delete'), ['delete','model_id'=>\$this->context->modelInfo->id,'id' => \$item->id],['class'=>'j_batch','data-action'=>'del']) ?>
                            <?php } ?>
                        </td>
                        <?php }?>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?=empty(\$dataList)?'<p class=\"list-data-default\">'.Yii::t('common','No Data Found !').'</p>':''?>
        </div>
    </div>
</div>
<nav class=\"nav-operation clearfix\">
    <div class=\"tools\">
        <a href=\"javascript:;\" id=\"j_choose_all\"><?=Yii::t('common','Select All')?></a>
        <a href=\"javascript:;\" id=\"j_choose_reverse\"><?=Yii::t('common','Select Invert')?></a>
        <a href=\"javascript:;\" id=\"j_choose_empty\"><?=Yii::t('common','Clears all')?></a>
        <?php if(\$userAccessButton['delete'] ||\$userAccessButton['status']){?>
        <span>|</span>
        <?php } ?>
        <?php if(\$userAccessButton['delete']){?>
        <?= Html::a(Yii::t('common','Batch delete'), ['delete','model_id'=>\$this->context->modelInfo->id],['class'=>'j_batch','data-action'=>'batchDel']) ?>
        <?php } if(\$userAccessButton['status']){?>
        <?= Html::a('取消处理', ['status','model_id'=>\$this->context->modelInfo->id,'value'=>0],['class'=>'btn btn-xs btn-info j_batch','data-action'=>'batchStatus','data-value'=>0,'title'=>Yii::t('common','Batch disable')]) ?>
        <?= Html::a('设置已处理', ['status','model_id'=>\$this->context->modelInfo->id,'value'=>1],['class'=>'btn btn-xs btn-success j_batch','data-action'=>'batchStatus','data-value'=>1,'title'=>Yii::t('common','Batch enable')]) ?>
        <?php } ?>
    </div>
    <?= Html::beginForm('', 'get', ['class' => 'form-inline pagination-go','id'=>'j_pagination_go']) ?>
    <div class=\"form-group\">
        <label><?=Yii::t('common','Jump to')?></label>
        <?= Html::input('text', 'page', 1, ['class' => 'form-control']) ?>
        <label><?=Yii::t('common','Page')?></label>
    </div>
    <?= Html::endForm() ?>
    <?=LinkPager::widget(['pagination' => \$dataProvider->pagination,'hideOnSinglePage'=>false,'firstPageLabel'=>'<span class=\"iconfont\">&#xe624;</span>','prevPageLabel'=>'<span class=\"iconfont\">&#xe61d;</span>','lastPageLabel'=>'<span class=\"iconfont\">&#xe623;</span>','nextPageLabel'=>'<span class=\"iconfont\">&#xe622;</span>']); ?>
</nav>";
?>
