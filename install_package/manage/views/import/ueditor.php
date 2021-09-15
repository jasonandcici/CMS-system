<?php

use yii\helpers\Url;

$this->title = '数据导入-富文本编辑器';
\manage\assets\UeditorAsset::register($this);
?>

<?php $this->beginBlock('endBlock');?>
<script>
    $(function () {
        coralUeditor.init({
            ueditorServerUrl:'<?=Url::to(['/files/index'])?>',
            localSourceUrl:'<?=Url::to(['/editor/index'])?>',
            localCategoryUrl:'<?=Url::to(['/editor/category'])?>',
            contentWidth:<?=(empty($this->context->siteInfo->devices_width)?'[]':json_encode(explode(',',$this->context->siteInfo->devices_width)))?>,
            localSourceBatch:{
                url:'<?=Url::to(['/editor/batch-operation'])?>',
                data:{'<?=Yii::$app->getRequest()->csrfParam?>':'<?=Yii::$app->getRequest()->getCsrfToken()?>'},
                beforeOperationCallback:function (data,type) {
                    if(type === 'create'){
                        $.each(data,function (i,n) {
                            if(i==='data[thumb]' && n !==''){
                                data[i] = '[{"file":"'+n+'","alt":""}]';
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
            var tools = window.UEDITOR_CONFIG.toolbars[0];
            tools.push('coralueditor');
            UE.getEditor('js-ueditor',{
                fullscreen:true,
                serverUrl:config.ueditorServerUrl,
                toolbars:[tools]
            });
        });
    });
</script>
<?php $this->endBlock();?>

<script type="text/html" style="height: 80%;width: 100%;" id="js-ueditor">
    <p>内容编辑完成后，点击“工具栏”中第一个工具“源代码”，切换到代码视图并复制代码到Excel数据模板中。</p>
    <p><?=\yii\helpers\Html::img('@web/images/import-ueditor.png')?></p>
</script>
