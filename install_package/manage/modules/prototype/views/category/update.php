<?php
/**
 * @var $model
 * @var $categoryList
 * @var $allCategoryList
 * @var $modelList
 * @var $roleList
 * @var $auth
 */

use manage\assets\FormAsset;
use manage\assets\UeditorAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = '更新栏目';
$this->params['subTitle'] = '('.$this->context->categoryTypeList[$model->type].')';

$newAllCategoryList = [];
foreach ($allCategoryList as $item){
	$newAllCategoryList[$item['id']] = [
		'id'=>$item['id'],
		'pid'=>$item['pid'],
		'title'=>$item['title'],
		'type'=>$item['type'],
		'model_id'=>$item['model_id'],
		'model'=>$item['model']
	];
}
unset($allCategoryList);

FormAsset::register($this);
UeditorAsset::register($this);
$this->registerJsFile('@web/js/plugins/uploadUeditor.js',['depends' => [FormAsset::className()]]);
$this->registerJsFile('@web/js/plugins/jquery.dataSelector.js',['depends' => [FormAsset::className()]]);
$this->registerJs("
formApp.init();
commonApp.formYiiAjax($('#j_form'));
uploadUeditor.init({serverUrl:'".Url::to(['/files/index'])."'});
uploadUeditor.singleImage($('.j_upload_single_img'));
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

$('#js-auth-select').find('button').click(function () {
    $(this).parent().find('input:checkbox').each(function () {
        $(this).prop('checked',true);
    });
});

// 跳转链接选择
var categoryList = ".json_encode($newAllCategoryList).";
var \$linkSelect = $('#link-select'),
    \$linkSelectInput = $('#link-select-input'),
    \$linkSelectSelect = $('#link-select-select'),
    \$prototypecategorymodelLink = $('#prototypecategorymodel-link');
    
\$linkSelect.change(function(){
	var _val = $(this).val();
	$('#upload_list').find('.related_list_del').trigger('click');
    \$linkSelectInput.find('input').val('');
	if (_val > 0) {
        if (categoryList[_val].type < 1) {
            \$linkSelectSelect.show();
            var \$relatedBtn = $('#related_btn');
            \$relatedBtn.attr('href',\$relatedBtn.data('href')+'&m='+ categoryList[_val].model.name +'&cm='+ categoryList[_val].model.name+'&'+categoryList[_val].model.name.replace(/^\S/,function(s){return s.toUpperCase();})+'Search[category_id]='+_val+'&hide_category_search=1');
        } else {
            \$linkSelectSelect.hide();
        }
        \$linkSelectInput.hide();
        \$prototypecategorymodelLink.val('{\"categoryId\":'+_val+'}');
    } else {
        \$linkSelectSelect.hide();
        \$linkSelectInput.show();
        \$prototypecategorymodelLink.val('');
    }
});
\$linkSelectInput.find('input').change(function(){
    \$prototypecategorymodelLink.val($(this).val());
});
", View::POS_READY);
?>
<script>
    function selectCallback(ids,opt,$e) {
        var _val = $('#link-select').val(),
            $prototypecategorymodelLink = $('#prototypecategorymodel-link');
        if(opt === 'select'){
            if(ids.length>0){
                $prototypecategorymodelLink.val('{"categoryId":'+_val+',"dataId":'+ ids.join(',') +'}');
            }
        }else{
            $prototypecategorymodelLink.val('{"categoryId":'+_val+'}');
        }
    }
</script>

<?php $this->beginBlock('topButton'); ?>
<?= Html::a(Yii::t('common','Back List'), ['index'], ['class' => 'btn btn-default j_goback']) ?>
<?php $this->endBlock(); ?>
<?= $this->render('_form_'.$model->type, ['model' => $model,'categoryList'=>$categoryList,'modelList'=>$modelList,'roleList'=>$roleList,'auth'=>$auth,'hasSetRoleAuth'=>$hasSetRoleAuth,'currUserRoles'=>$currUserRoles,'allCategoryList'=>$newAllCategoryList]) ?>