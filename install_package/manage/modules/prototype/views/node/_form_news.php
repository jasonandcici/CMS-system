<?php
/**
* @var $model
* @var $form
* @var $categoryInfo
* @var $parentCategoryList
*/
use common\helpers\ArrayHelper;
use common\helpers\UrlHelper;
use manage\assets\FormAsset;
use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;

\manage\assets\UeditorAsset::register($this);
$this->registerJsFile('@web/js/plugins/uploadUeditor.js',['depends' => [FormAsset::className()]]);
$this->registerJs('uploadUeditor.init({serverUrl:"'.Url::to(['/files/index']).'"});');
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
", View::POS_READY);$this->registerJsFile('@web/js/plugins/jquery.dataSelector.js',['depends' => [FormAsset::className()]]);if(file_exists(Yii::$app->getModule('prototype')->getViewPath().'/node/_custom_form_'.$categoryInfo->model->name.'.php')){
    echo $this->render('_custom_form_'.$categoryInfo->model->name,['model'=>$model,'form'=>$form,'categoryInfo'=>$categoryInfo,'parentCategoryList'=>$parentCategoryList]);
}
?>
<?php if (isset($this->blocks['customField_thumb'])){ echo $this->blocks['customField_thumb'];}else{ ?>
<?= $form->field($model, 'thumb',[
    'template'=> '{label}<div class="col-sm-17"><div class="list-img list-img-multiple clearfix j_upload_single_img">{input}<ul class="upload_list"></ul><a class="upload upload_btn" href="javascript:;"><span class="iconfont">&#xe607;</span></a></div>{error}{hint}</div>'])->hiddenInput(['class'=>'upload_input']) ?>
<?php } ?><?php if (isset($this->blocks['customField_atlas'])){ echo $this->blocks['customField_atlas'];}else{ ?>
<?= $form->field($model, 'atlas',[
    'template'=> '{label}<div class="col-sm-17"><div class="list-img list-img-multiple clearfix j_upload_multiple_img">{input}<ul class="upload_list"></ul><a class="upload upload_btn" href="javascript:;"><span class="iconfont">&#xe607;</span></a></div>{error}{hint}</div>'])->hiddenInput(['class'=>'upload_input']) ?>
<?php } ?><?php if (isset($this->blocks['customField_content'])){ echo $this->blocks['customField_content'];}else{ ?>
<?= $form->field($model, 'content')->textarea([ 'class'=>'j_editor', 'style'=>'width:100%;height:350px;']) ?>
<?php } ?><?php if (isset($this->blocks['customField_description'])){ echo $this->blocks['customField_description'];}else{ ?>
<?= $form->field($model, 'description')->textarea([ 'rows'=>4]) ?>
<?php } ?><?php if (isset($this->blocks['customField_attachment'])){ echo $this->blocks['customField_attachment'];}else{ ?>
<?= $form->field($model, 'attachment',[
    'template'=> '{label}<div class="col-sm-17"><div class="list-file clearfix j_upload_single_file">{input}<ul class="upload_list"></ul><a class="upload btn btn-default upload_btn" href="javascript:;">文件上传</a></div>{error}{hint}</div>'])->hiddenInput(['class'=>'upload_input']) ?>
<?php } ?><?php if (isset($this->blocks['customOther'])){ echo $this->blocks['customOther'];} ?>
