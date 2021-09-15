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
use common\widgets\ActiveForm;
use yii\helpers\Html;

/**
 * @var $model
 * @var $form
 */
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
        <?= $form->field($model, 'type')->dropDownList([0=>'列表类型',1=>'字段类型'],$model->isNewRecord?[]:['disabled'=>true]) ?>
        <?=$form->field($model, 'is_global')->radioList([0=>'否',1=>'是'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]);?>
        <div id="type-config" <?=$model->type?'style="display:none;"':''?>>
            <?=$form->field($model, 'enable_link')->radioList([0=>'禁用',1=>'启用'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]);?>

            <?=$form->field($model, 'enable_thumb')->radioList([0=>'禁用',1=>'启用'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]);?>
            <?=$form->field($model, 'multiple_thumb',['options'=>$model->enable_thumb?['class'=>'form-group']:['class'=>'form-group','style'=>'display:none;']])->radioList([0=>'禁用',1=>'启用'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]);?>
            <?=$form->field($model, 'enable_attachment')->radioList([0=>'禁用',1=>'启用'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]);?>
            <?=$form->field($model, 'multiple_attachment',['options'=>$model->enable_attachment?['class'=>'form-group']:['class'=>'form-group','style'=>'display:none;']])->radioList([0=>'禁用',1=>'启用'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]);?>
            <?=$form->field($model, 'enable_sub_title')->radioList([0=>'禁用',1=>'启用'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]);?>
            <?=$form->field($model, 'enable_ueditor')->radioList([0=>'禁用',1=>'启用'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]);?>
            <?=$form->field($model, 'is_disabled_opt')->radioList([0=>'否',1=>'是'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]);?>
        </div>
        <!-- 表单控件结束 -->
        <div class="form-data-footer">
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-14">
                    <?= Html::submitButton(Yii::t('common','Submit'), ['class' => 'btn btn-primary']) ?>
                    <?= Html::resetButton(Yii::t('common','Reset'), ['class' => 'btn btn-default']) ?>
                    <?= Html::a(Yii::t('common','Back List').' <span class="st">&gt;</span>', ['index'], ['class' => 'btn btn-link j_goback']) ?>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<!-- 表单结束 -->
<?php $this->beginBlock('endBlock');?>
<script>
    $(function(){
        var $typeConfig = $('#type-config');
        $('#fragmentcategorymodel-type').change(function(){
            if($(this).val() == 1){
                $typeConfig.hide();
            }else{
                $typeConfig.show();
            }
        });

        var multipleThumb = $('.field-fragmentcategorymodel-multiple_thumb');
        $('#fragmentcategorymodel-enable_thumb').find('input').change(function () {
            if($(this).val() == 1){
                multipleThumb.show();
            }else{
                multipleThumb.hide();
            }
        });

        var multipleAttachment = $('.field-fragmentcategorymodel-multiple_attachment');
        $('#fragmentcategorymodel-enable_attachment').find('input').change(function () {
            if($(this).val() == 1){
                multipleAttachment.show();
            }else{
                multipleAttachment.hide();
            }
        });
    });
</script>
<?php $this->endBlock();?>
