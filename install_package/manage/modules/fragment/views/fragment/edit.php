<?php
/**
 * @var $dataList
 * @var $categoryInfo
 */

use manage\assets\FormAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use common\widgets\ActiveForm;

$this->title = '碎片管理';
$this->params['subTitle'] = '（'.$categoryInfo->title.'）';

FormAsset::register($this);
$this->registerJs("
    formApp.init();
    commonApp.formYiiAjax($('#j_form'));
");

$hasUpload = false;
$hasEditor = false;
foreach ($dataList as $i=>$item){
    $item->style = intval($item->style);
    if(in_array($item->style,[9,10,11,12])){
        $hasUpload = true;
    }elseif ($item->style === 14){
        $hasEditor = true;
    }
}
if($hasEditor || $hasUpload){
    \manage\assets\UeditorAsset::register($this);
}
if($hasUpload){
    $this->registerJsFile('@web/js/plugins/uploadUeditor.js',['depends' => [FormAsset::className()]]);
    $this->registerJs("uploadUeditor.init({serverUrl:'".Url::to(['/files/index'])."'});", View::POS_READY);
}
if($hasEditor){
    $this->registerJs("
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
        var \$ueditor = $('.j_editor');
        if(\$ueditor.length > 0){
            var tools = window.UEDITOR_CONFIG.toolbars[0];
            tools.push('coralueditor');
            \$ueditor.each(function(i,n){
                $(this).attr({'id':'editor_cnt_'+i});
                UE.getEditor('editor_cnt_'+i,{
                    serverUrl:config.ueditorServerUrl,
                    toolbars:[tools]
                });
            });
        }
    });
    ", View::POS_READY);
}
?>

<!-- 表单开始 -->
<div class="panel panel-default form-data">
    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'id'=>'j_form',
            'options'=>['class' => 'form-horizontal'],
            'fieldConfig'=>['template'=>'{label}<div class="col-sm-17">{input}{error}{hint}</div>', 'labelOptions'=>['class'=>'col-sm-4 control-label']]
        ]); ?>
        <!-- 表单控件开始 -->
        <?php
        foreach ($dataList as $i => $item) {
            $setting = empty($item->setting)?[]:unserialize($item->setting);
            $control = $form->field($item, "[$i]value")->label($item->title);
            if(array_key_exists('hint',$setting)) $control->hint($setting['hint']);
            switch($item->style){
                case 1:
                    echo $control->textInput();
                    break;
                case 2:
                    echo $control->passwordInput();
                    break;
                case 3:
                    echo $control->textarea(['rows'=>array_key_exists('rows',$setting)?$setting['rows']:3,'class'=>'form-control resize-none']);
                    break;
                case 4:
                    echo $control->dropDownList(array_key_exists('list',$setting)?$setting['list']:[], array_merge(['class'=>'form-control'],$setting['other']));
                    break;
                case 5:
                    echo $control->radioList(array_key_exists('list',$setting)?$setting['list']:[],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]);
                    break;
                case 6:
                    echo $control->radioList(array_key_exists('list',$setting)?$setting['list']:[],['item'=>function($index, $label, $name, $checked, $value){
                        return '<div class="radio"><label><input type="radio" name="'.$name.'" value="'.$value.'"'.($checked==$value?' checked':'').'>'.$label.'</label></div>';
                    }]);
                    break;
                case 7:
                    echo $control->radioList(array_key_exists('list',$setting)?$setting['list']:[],['itemOptions'=>['labelOptions'=>['class'=>'checkbox-inline']]]);
                    break;
                case 8:
                    echo $control->radioList(array_key_exists('list',$setting)?$setting['list']:[],['item'=>function($index, $label, $name, $checked, $value){
                        return '<div class="checkbox"><label><input type="checkbox" name="'.$name.'" value="'.$value.'"'.($checked==$value?' checked':'').'>'.$label.'</label></div>';
                    }]);
                    break;
                case 9:
                    $hint = array_key_exists('hint',$setting)?$setting['hint']:'';
                    echo $form->field($item, "[$i]value",[
                        'template'=> '{label}<div class="col-sm-17"><div class="list-img list-img-multiple clearfix" id="j_upload_single_img'.$i.'">{input}<ul class="upload_list"></ul><a class="upload upload_btn" href="javascript:;"><span class="iconfont">&#xe607;</span></a></div>{error}{hint}</div>'
                    ])->hiddenInput(['class'=>'upload_input'])->label($item->title)->hint($hint);
                    $this->registerJs('uploadUeditor.singleImage($("#j_upload_single_img'.$i.'"));', View::POS_READY);

                    break;
                case 10:
                    $hint = array_key_exists('hint',$setting)?$setting['hint']:'';
                    echo $form->field($item, "[$i]value",[
                        'template'=> '{label}<div class="col-sm-17"><div class="list-img list-img-multiple clearfix" id="j_upload_multiple_img'.$i.'">{input}<ul class="upload_list"></ul><a class="upload upload_btn" href="javascript:;"><span class="iconfont">&#xe607;</span></a></div>{error}{hint}</div>'
                    ])->hiddenInput(['class'=>'upload_input'])->label($item->title)->hint($hint);
                    $this->registerJs('uploadUeditor.multipleImage($("#j_upload_multiple_img'.$i.'"));', View::POS_READY);
                    break;

                case 11:
                    $hint = array_key_exists('hint',$setting)?$setting['hint']:'';
                    echo $form->field($item, "[$i]value",[
                        'template'=> '{label}<div class="col-sm-17"><div class="list-file clearfix j_upload_single_file">{input}<ul class="upload_list"></ul><a class="upload btn btn-default upload_btn" href="javascript:;">文件上传</a></div>{error}{hint}</div>'])->hiddenInput(['class'=>'upload_input'])->label($item->title)->hint($hint);
                    break;
                case 12:
                    $hint = array_key_exists('hint',$setting)?$setting['hint']:'';
                    echo $form->field($item, "[$i]value",[
                        'template'=> '{label}<div class="col-sm-17"><div class="list-file clearfix j_upload_multiple_file">{input}<ul class="upload_list"></ul><a class="upload btn btn-default upload_btn" href="javascript:;">文件上传</a></div>{error}{hint}</div>'])->hiddenInput(['class'=>'upload_input'])->label($item->title)->hint($hint);
                    break;

                case 13:
                    echo $control->textInput(['id'=>'j_tag_input'.$i]);
                    $this->registerJs("$('#j_tag_input".$i."').tagsinput({trimValue: true});", View::POS_READY);
                    break;
                case 14:
                    $control->textarea([ 'class'=>'j_editor', 'style'=>'width:100%;height:300px;']);
                    break;
                default:
                    if (isset($this->blocks[$item->name])){
                        echo $this->blocks[$item->name];
                    }
                    break;
            }
        }
        ?>
        <!-- 表单控件结束 -->
        <div class="form-data-footer">
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-14">
                    <?= Html::submitButton(Yii::t('common','Submit'), ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<!-- 表单结束 -->