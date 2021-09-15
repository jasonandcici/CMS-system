<?php
/**
 * This is the template for generating the model class of a specified table.
 */
use common\entity\models\PrototypeModelModel;
use common\helpers\ArrayHelper;
use yii\helpers\Inflector;

/* @var $model  */
/* @var $fields  */

echo "<?php\n";
?>
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

<?php
$hasUpload = false;
$hasEditor = false;
$hasRelation = false;
foreach ($fields as $item){
    if(in_array($item->type,['image','image_multiple','attachment','attachment_multiple'])){
        $hasUpload = true;
    }elseif($item->type == 'editor'){
        $hasEditor = true;
    }elseif(in_array($item->type,['checkbox','checkbox_inline','select_multiple'])){
        echo "if(!empty(\$model->id)){\$model->".$item->name." = explode(',',\$model->".$item->name.");}\n";
    }elseif($item->type == 'relation_data' && $item->setting['relationType']){
        $hasRelation = true;
    }
}
if($hasUpload || $hasEditor){
    echo "\manage\assets\UeditorAsset::register(\$this);\n";
}
if($hasUpload){
    echo "\$this->registerJsFile('@web/js/plugins/uploadUeditor.js',['depends' => [FormAsset::className()]]);\n";
    echo "\$this->registerJs('uploadUeditor.init({serverUrl:\"'.Url::to(['/files/index']).'\"});');\n";
}
if($hasEditor){
    echo '$this->registerJs("
coralUeditor.init({
    ueditorServerUrl:\'".Url::to([\'/files/index\'])."\',
    localSourceUrl:\'".Url::to([\'/editor/index\'])."\',
    localCategoryUrl:\'".Url::to([\'/editor/category\'])."\',
    contentWidth:".(empty($this->context->siteInfo->devices_width)?\'[]\':json_encode(explode(\',\',$this->context->siteInfo->devices_width))).",
    localSourceBatch:{
        url:\'".Url::to([\'/editor/batch-operation\'])."\',
        data:{\'".Yii::$app->getRequest()->csrfParam."\':\'".Yii::$app->getRequest()->getCsrfToken()."\'},
        beforeOperationCallback:function (data,type) {
            if(type === \'create\'){
                $.each(data,function (i,n) {
                    if(i===\'data[thumb]\' && n !==\'\'){
                        data[i] = \'[{\"file\":\"\'+n+\'\",\"alt\":\"\"}]\';
                    }
                });
            }
            return data;
        },
        afterOperationCallback:function (res,type, dialog) {
            if(res.status){
                commonApp.notify.success(\'操作成功。\');
            }else{
                commonApp.notify.error(res.message);
            }
            dialog.close(true);
        }
    }
},function(config){
    var \$ueditor = $(\'.j_editor\');
    if(\$ueditor.length > 0){
        var tools = window.UEDITOR_CONFIG.toolbars[0];
        tools.push(\'coralueditor\');
        \$ueditor.each(function(i,n){
            $(this).attr({\'id\':\'editor_cnt_\'+i});
            UE.getEditor(\'editor_cnt_\'+i,{
                serverUrl:config.ueditorServerUrl,
                toolbars:[tools]
            });
        });
    }
});
", View::POS_READY);';
}
echo "\$this->registerJsFile('@web/js/plugins/jquery.dataSelector.js',['depends' => [FormAsset::className()]]);";

echo "if(file_exists(Yii::\$app->getModule('prototype')->getViewPath().'/node/_custom_form_'.\$categoryInfo->model->name.'.php')){
    echo \$this->render('_custom_form_'.\$categoryInfo->model->name,['model'=>\$model,'form'=>\$form,'categoryInfo'=>\$categoryInfo,'parentCategoryList'=>\$parentCategoryList]);
}\n";
echo "?>\n";

if($hasRelation){
	echo "<?=Html::hiddenInput('expand[]','RELATION');?>\n";
}

foreach ($fields as $item){
    if($item->type=='select' || $item->type=='select_multiple'){
        $placeholder = empty($item->placeholder)?'':",'data-placeholder'=>'".$item->placeholder."','prompt'=>'".$item->placeholder."',";
    }else{
        $placeholder = empty($item->placeholder)?'':"'placeholder'=>'".$item->placeholder."',";
    }

    $hint = empty($item->hint)?'':"->hint('".$item->hint."')";

    echo "<?php if (isset(\$this->blocks['customField_".$item->name."'])){ echo \$this->blocks['customField_".$item->name."'];}else{ ?>\n";

    switch ($item->type){
        case 'passport':
            echo "<?= \$form->field(\$model, '".$item->name."')->passwordInput([".$placeholder."])".$hint." ?>\n";
            break;
        case 'date':
            echo "<?= \$form->field(\$model, '".$item->name."')->textInput([".$placeholder." 'class'=>'form-control js-date'])".$hint." ?>\n";
            break;
        case 'datetime':
            echo "<?= \$form->field(\$model, '".$item->name."')->textInput([".$placeholder." 'class'=>'form-control js-date-time'])".$hint." ?>\n";
            break;
        //case 'number':
        case 'int':
            echo "<?= \$form->field(\$model, '".$item->name."')->textInput([".$placeholder." 'type'=>'number'])".$hint." ?>\n";
            break;
        case 'captcha':
            break;
        case 'textarea':
            echo "<?= \$form->field(\$model, '".$item->name."')->textarea([".$placeholder." 'rows'=>4])".$hint." ?>\n";
            break;
        case 'radio':
            echo "<?= \$form->field(\$model, '".$item->name."')->radioList([".PrototypeModelModel::optionsMap($item->options)."],['item'=>function(\$index, \$label, \$name, \$checked, \$value){return '<div class=\"radio\"><label>'.'<input type=\"radio\" name=\"'.\$name.'\" value=\"'.\$value.'\" '.(\$checked?\"checked\":\"\").'>'.ucwords(\$label).'</label></div>';}]); ?>\n";
            break;
        case 'radio_inline':
            echo "<?= \$form->field(\$model, '".$item->name."')->radioList([".PrototypeModelModel::optionsMap($item->options)."],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]); ?>\n";
            break;
        case 'checkbox':
            echo "<?= \$form->field(\$model, '".$item->name."')->checkboxList([".PrototypeModelModel::optionsMap($item->options)."],['item'=>function(\$index, \$label, \$name, \$checked, \$value){return '<div class=\"radio\"><label>'.'<input type=\"checkbox\" name=\"'.\$name.'\" value=\"'.\$value.'\" '.(\$checked?\"checked\":\"\").'>'.ucwords(\$label).'</label></div>';}]); ?>\n";
            break;
        case 'checkbox_inline':
            echo "<?= \$form->field(\$model, '".$item->name."')->checkboxList([".PrototypeModelModel::optionsMap($item->options)."],['itemOptions'=>['labelOptions'=>['class'=>'checkbox-inline']]]); ?>\n";
            break;
        case 'select':
            echo "<?= \$form->field(\$model, '".$item->name."')->dropDownList([".PrototypeModelModel::optionsMap($item->options)."],[".$placeholder." 'prety'=>true])".$hint." ?>\n";
            break;
        case 'select_multiple':
            echo "<?= \$form->field(\$model, '".$item->name."')->dropDownList([".PrototypeModelModel::optionsMap($item->options)."],[".$placeholder." 'multiple'=>true])".$hint." ?>\n";
            break;
        case 'tag':
            echo "<?php \$this->registerJs(\"$('#js-tag-".$item->id."').tagsinput({trimValue: true});\", View::POS_READY); echo \$form->field(\$model, '".$item->name."')->textInput([".$placeholder." 'id'=>'js-tag-".$item->id."'])".$hint."; ?>\n";
            break;
        case 'editor':
            echo "<?= \$form->field(\$model, '".$item->name."')->textarea([".$placeholder." 'class'=>'j_editor', 'style'=>'width:100%;height:350px;'])".$hint." ?>\n";
            break;
        case 'image':
            echo "<?= \$form->field(\$model, '".$item->name."',[
    'template'=> '{label}<div class=\"col-sm-17\"><div class=\"list-img list-img-multiple clearfix j_upload_single_img\">{input}<ul class=\"upload_list\"></ul><a class=\"upload upload_btn\" href=\"javascript:;\"><span class=\"iconfont\">&#xe607;</span></a></div>{error}{hint}</div>'])->hiddenInput(['class'=>'upload_input'])".$hint." ?>\n";
            break;
        case 'image_multiple':
            echo "<?= \$form->field(\$model, '".$item->name."',[
    'template'=> '{label}<div class=\"col-sm-17\"><div class=\"list-img list-img-multiple clearfix j_upload_multiple_img\">{input}<ul class=\"upload_list\"></ul><a class=\"upload upload_btn\" href=\"javascript:;\"><span class=\"iconfont\">&#xe607;</span></a></div>{error}{hint}</div>'])->hiddenInput(['class'=>'upload_input'])".$hint." ?>\n";
            break;
        case 'attachment':
            echo "<?= \$form->field(\$model, '".$item->name."',[
    'template'=> '{label}<div class=\"col-sm-17\"><div class=\"list-file clearfix j_upload_single_file\">{input}<ul class=\"upload_list\"></ul><a class=\"upload btn btn-default upload_btn\" href=\"javascript:;\">文件上传</a></div>{error}{hint}</div>'])->hiddenInput(['class'=>'upload_input'])".$hint." ?>\n";
            break;
        case 'attachment_multiple':
            echo "<?= \$form->field(\$model, '".$item->name."',[
    'template'=> '{label}<div class=\"col-sm-17\"><div class=\"list-file clearfix j_upload_multiple_file\">{input}<ul class=\"upload_list\"></ul><a class=\"upload btn btn-default upload_btn\" href=\"javascript:;\">文件上传</a></div>{error}{hint}</div>'])->hiddenInput(['class'=>'upload_input'])".$hint." ?>\n";
            break;
        case 'relation_data':

            if(!$item->setting['relationType']){
                $inputHtml = "<?=Html::activeHiddenInput(\$model,'".$item->name."',['class'=>'related_input'])?>";
            }else{
                $inputHtml = "<?=Html::input('hidden','relation[".$item->setting['modelName']."]',implode(',',ArrayHelper::getColumn(\$model->".Inflector::pluralize($item->setting['modelName']).",'relation_id')),['class'=>'related_input'])?>";
            }

            switch ($item->setting['modelName']){
                case 'user':
                    $urlString = "'/assets/user','cm'=>'".$model->name."'";
                    break;
                case 'category':
                    $urlString = "'/assets/category','cm'=>'".$model->name."'";
                    break;
                default:
                    $urlString = "'/assets/node','m'=>'".$item->setting['modelName']."','cm'=>'".$model->name."'";
                    break;
            }

            echo "<div class=\"form-group\"><label class=\"control-label col-sm-4\">".$item->title."</label><div class=\"col-sm-17\"><div class=\"form-control-static help-block related j_related_selector\">".$inputHtml."已经添加 <b class=\"related_count\">0</b> 条数据<a href=\"<?=UrlHelper::to([".$urlString.",'multiple'=>".($item->setting['relationType']?"true":"false")."])?>\" class=\"text-primary related_btn\">点击添加</a><ul class=\"related_list\"></ul></div></div></div>";
            break;
        case 'city':
            break;
        case 'city_multiple':
            break;
        default:
            echo "<?= \$form->field(\$model, '".$item->name."')->textInput([".$placeholder."])".$hint." ?>\n";
            break;
    }

    echo "<?php } ?>";
}

echo "<?php if (isset(\$this->blocks['customOther'])){ echo \$this->blocks['customOther'];} ?>\n";

?>
