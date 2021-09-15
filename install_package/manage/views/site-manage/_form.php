<?php
// +----------------------------------------------------------------------
// | nantong
// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/3/31.
// +----------------------------------------------------------------------
use common\helpers\ArrayHelper;
use common\helpers\FileHelper;
use manage\assets\FormAsset;
use yii\helpers\Url;
use yii\web\View;
use common\widgets\ActiveForm;
use yii\helpers\Html;

/**
 * @var $model
 * @var $form
 */
FormAsset::register($this);
\manage\assets\UeditorAsset::register($this);
$this->registerJsFile('@web/js/plugins/uploadUeditor.js',['depends' => [FormAsset::className()]]);
$this->registerJs('formApp.init();commonApp.formYiiAjax($(\'#j_form\'));uploadUeditor.init({serverUrl:"'.Url::to(['/files/index']).'"});uploadUeditor.singleImage($("#j_upload_single_img"));$("#js-tags").tagsinput({trimValue: true});', View::POS_READY);
?>
<?php $this->beginBlock('topButton'); ?>
<?= Html::a(Yii::t('common','Back List'), ['index'], ['class' => 'btn btn-default j_goback']) ?>
<?php $this->endBlock(); ?>

<!-- 表单开始 -->
<div class="panel panel-default form-data">
    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'id'=>'j_form',
            'options'=>['class' => 'form-horizontal'],
            'fieldConfig'=>['template'=>'{label}<div class="col-sm-17">{input}{error}{hint}</div>', 'labelOptions'=>['class'=>'col-sm-4 control-label']]
        ]); ?>
        <!-- 表单控件开始 -->
        <?= $form->field($model, 'title')->textInput() ?>
        <?= $form->field($model, 'slug')->textInput() ?>
        <?php if($this->context->isSuperAdmin):?>
            <?=$form->field($model, 'theme')->dropDownList(ArrayHelper::unifyKeyValue(FileHelper::findChildFolder(Yii::getAlias('@home').'/themes')),['prompt'=>'请选择']) ?>
            <?= $form->field($model, 'language')->dropDownList([
                "zh-CN"=>"简体中文","zh-TW"=>"繁体中文","en"=>"英文","ar"=>"阿拉伯语","az"=>"阿塞拜疆语","bg"=>"保加利亚语","bs"=>"波斯尼亚语","ca"=>"加泰罗尼亚语","cs"=>"捷克语","da"=>"丹麦语","de"=>"	
德语","el"=>"希腊语","es"=>"西班牙语","et"=>"爱沙尼亚语","fa"=>"波斯语","fi"=>"芬兰语",
                "fr"=>"法语","he"=>"希伯来语","hr"=>"克罗地亚语","hu"=>"匈牙利语","id"=>"印度尼西亚语","it"=>"意大利语","ja"=>"日语","ka"=>"格鲁吉亚语","kk"=>"哈萨克语","ko"=>"韩语","lt"=>"立陶宛语","lv"=>"拉脱维亚语","ms"=>"马来西亚语",
                "nb-NO"=>"挪威语","nl"=>"荷兰语","pl"=>"波兰语","pt"=>"葡萄牙语","pt-BR"=>"葡萄牙语（巴西）","ro"=>"罗马尼亚语","ru"=>"俄语","sk"=>"斯洛伐克语","sl"=>"斯洛文尼亚语","sr"=>"塞尔维亚语","sr-Latn"=>"塞尔维亚语（拉丁）","sv"=>"瑞典语","tg"=>"塔吉克斯坦语",
                "th"=>"泰国语","tr"=>"土耳其语","uk"=>"乌克兰语","uz"=>"乌兹别克语","vi"=>"越南语"
            ],['prety'=>true]) ?>
        <?php endif;?>
        <?=$form->field($model, "logo",[
            'template'=> '{label}<div class="col-sm-17"><div class="list-img list-img-multiple clearfix" id="j_upload_single_img">{input}<ul class="upload_list"></ul><a class="upload upload_btn" href="javascript:;"><span class="iconfont">&#xe607;</span></a></div>{error}{hint}</div>'
        ])->hiddenInput(['class'=>'upload_input']);?>

        <?php if($this->context->isSuperAdmin):?>
            <?= $form->field($model, 'enable_mobile')->radioList([1=>'是',0=>'否'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]) ?>
            <?= $form->field($model, 'devices_width')->textInput(['id'=>'js-tags'])->hint('例如：768px,992px,1200px……，默认为100%') ?>
        <?php endif;?>
        <!-- 表单控件结束 -->
        <div class="form-data-footer">
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-14">
                    <?= Html::submitButton(Yii::t('common','Submit'), ['class' => 'btn btn-primary']) ?>
                    <?= Html::a(Yii::t('common','Back List').' <span class="st">&gt;</span>', ['index'], ['class' => 'btn btn-link j_goback']) ?>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<!-- 表单结束 -->

