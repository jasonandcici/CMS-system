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
use manage\assets\FormAsset;
use manage\helpers\UrlHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\View;
use common\widgets\ActiveForm;
use yii\helpers\Html;

/**
 * @var $model
 * @var $categoryInfo
 * @var $modelList
 */
FormAsset::register($this);
\manage\assets\UeditorAsset::register($this);
$this->registerJsFile('@web/js/plugins/uploadUeditor.js',['depends' => [FormAsset::className()]]);

$this->registerJs('
formApp.init();
commonApp.formYiiAjax($("#j_form"));
uploadUeditor.init({serverUrl:"'.Url::to(['/files/index']).'"});
uploadUeditor.singleImage($(".j_upload_single_img"));
var categoryList = '.json_encode($modelList).';
slideFormInit('.($model->related_data_model?:0).');

$("#fragmentlistmodel-related_data_model").change(function () {
        slideFormInit($(this).val())
    });

    function slideFormInit(_val) {
        $("#upload_list").find(".related_list_del").trigger("click");
        $("#fragmentlistmodel-link").val("");
        if (_val > 0) {
            if (categoryList[_val].type < 1) {
                $(".field-fragmentlistmodel-related_data_id").show();
                var $relatedBtn = $("#related_btn");
                $relatedBtn.attr("href",$relatedBtn.data("href")+"&m="+ categoryList[_val].model.name +"&cm="+ categoryList[_val].model.name+"&"+categoryList[_val].model.name.replace(/^\S/,function(s){return s.toUpperCase();})+"Search[category_id]="+_val+"&hide_category_search=1");
            } else {
                $(".field-fragmentlistmodel-related_data_id").hide();
            }
            $(".field-fragmentlistmodel-link").hide();
        } else {
            $(".field-fragmentlistmodel-related_data_id").hide();
            $(".field-fragmentlistmodel-link").show();
        }
    }

'.($categoryInfo->enable_ueditor?"
coralUeditor.init({
    ueditorServerUrl:'".Url::to(['/files/index'])."',
    localSourceUrl:'".Url::to(['/editor/index'])."',
    localCategoryUrl:'".Url::to(['/editor/category'])."',
    contentWidth:".(empty($this->context->siteInfo->devices_width)?'[]':json_encode(explode(',',$this->context->siteInfo->devices_width))).",
    localSourceBatch:{
        url:'".Url::to(['/editor/batch-operation'])."',
        data:{'".Yii::$app->getRequest()->csrfParam."':'".Yii::$app->getRequest()->getCsrfToken()."'},
        beforeOperationCallback:function (data,type) {
            if(type === 'create'){
                $.each(data,function (i,n) {
                    if(i==='data[thumb]' && n !==''){
                        data[i] = '[{\"file\":\"'+n+'\",\"alt\":\"\"}]';
                    }
                });
            }
            return data;
        },
        afterOperationCallback:function (res,type, dialog) {
            if(res.status){
                commonApp.notify.success('操作成功。');
            }else{
                commonApp.notify.error(res.message);
            }
            dialog.close(true);
        }
    }
},function(config){
    UE.getEditor('fragmentlistmodel-description',{
        serverUrl:config.ueditorServerUrl,
        toolbars: [
        ['source','|', 'bold', 'italic','underline', 'strikethrough','forecolor','paragraph','insertorderedlist','insertunorderedlist','link','insertimage','inserttable','pasteplain','removeformat','coralueditor']
    ],
        elementPathEnabled:false,
        wordCount:false,
        autoHeightEnabled:false
    });
});
":''), View::POS_READY);
$this->registerJsFile('@web/js/plugins/jquery.dataSelector.js',['depends' => [FormAsset::className()]]);
$this->registerCss('.field-fragmentlistmodel-related_data_id,.field-fragmentlistmodel-link{display:none;}');
?>
<?php $this->beginBlock('topButton'); ?>
<?= Html::a(Yii::t('common','Back List'), ['index','category_id'=>$categoryInfo->id], ['class' => 'btn btn-default j_goback']) ?>
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
        <?=Html::activeInput('hidden',$model,'category_id')?>

        <?= $form->field($model, 'title')->textInput() ?>
        <?php if($categoryInfo->enable_sub_title) echo $form->field($model, 'title_sub')->textInput(); ?>
        <?php if($categoryInfo->enable_thumb) echo $form->field($model, 'thumb',[
            'template'=> '{label}<div class="col-sm-17"><div class="list-img list-img-multiple clearfix '.($categoryInfo->multiple_thumb?'j_upload_multiple_img':'j_upload_single_img').'">{input}<ul class="upload_list"></ul><a class="upload upload_btn" href="javascript:;"><span class="iconfont">&#xe607;</span></a></div>{error}{hint}</div>'
        ])->hiddenInput(['class'=>'upload_input']);?>

        <?php if($categoryInfo->enable_attachment) echo $form->field($model, 'attachment',[
            'template'=> '{label}<div class="col-sm-17"><div class="list-file clearfix '.($categoryInfo->multiple_attachment?'j_upload_multiple_file':'j_upload_single_file').'">{input}<ul class="upload_list"></ul><a class="upload btn btn-default upload_btn" href="javascript:;">文件上传</a></div>{error}{hint}</div>'
        ])->hiddenInput(['class'=>'upload_input'])?>

        <?php if($categoryInfo->enable_link){?>
        <?= $form->field($model, 'related_data_model')->dropDownList(ArrayHelper::merge(['0'=>'--自定义链接--'],ArrayHelper::map($modelList,'id','title')), ['class'=>'form-control','prety'=>true])->label('目标栏目')?>
        <?= $form->field($model, 'link')->textInput() ?>

        <div class="form-group field-fragmentlistmodel-related_data_id">
            <label class="control-label col-sm-4">选择数据</label>
            <div class="col-sm-17">
                <div class="related j_related_selector">
	                <?=Html::activeHiddenInput($model,'related_data_id',['class'=>'related_input'])?>
                    <ul class="related_list" id="upload_list" style="margin-bottom: 7px;margin-top: 0;"></ul>
                    <span style="display: none;">已经添加 <b class="related_count">0</b></span><a id="related_btn" data-href="<?=UrlHelper::to(['/assets/node','multiple'=>false])?>" class="related_btn btn btn-default" style="margin-left: 0;">点击添加</a>
                </div>
                <div class="hint-block">如果<span class="alert-danger">为空</span>，则表示生成所选栏目链接。</div>
            </div>
        </div>

        <?php }?>

        <?= $categoryInfo->enable_ueditor?$form->field($model, 'description')->textarea(['class'=>'','style'=>'width:100%;height:250px;']):$form->field($model, 'description')->textarea(['rows'=>'4'])?>

        <!-- 表单控件结束 -->
        <div class="form-data-footer">
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-14">
                    <?= Html::submitButton(Yii::t('common','Submit'), ['class' => 'btn btn-primary']) ?>
                    <?= Html::a(Yii::t('common','Back List').' <span class="st">&gt;</span>', ['index','category_id'=>$categoryInfo->id], ['class' => 'btn btn-link j_goback']) ?>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<!-- 表单结束 -->