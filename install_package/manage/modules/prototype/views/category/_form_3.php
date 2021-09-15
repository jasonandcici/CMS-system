<?php

use manage\helpers\UrlHelper;
use yii\helpers\ArrayHelper;
use common\widgets\ActiveForm;
use yii\helpers\Html;

/**
 * @var $model
 * @var $categoryList
 * @var $modelList
 */

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
        <?= Html::activeInput('hidden', $model, 'type') ?>
        <?= $form->field($model, 'title')->textInput()->label('<b class="text-danger">*</b> '.$model->getAttributeLabel('title')) ?>
        <?= $form->field($model, 'sub_title')->textInput() ?>
        <?= $form->field($model, 'pid')->dropDownList(ArrayHelper::merge(['0'=>'—顶级栏目—'],ArrayHelper::map($allCategoryList,'id','title')), ['class'=>'form-control','prety'=>true])?>

        <div class="form-group">
	        <?php
	        $link = [];
	        if(!empty($model->link) && strpos($model->link,'{') === 0){
		        $link = json_decode($model->link,true);
	        }else{ $link = ['link'=>$model->link]; }?>
            <label class="col-sm-4 control-label"><b class="text-danger">*</b> 跳转链接</label>
            <div class="col-sm-17">
	            <?=Html::dropDownList('',ArrayHelper::getValue($link,'categoryId',0),ArrayHelper::merge(['0'=>'--自定义链接--'],ArrayHelper::map($allCategoryList,'id','title')),['id'=>'link-select','class'=>'form-control','prety'=>true])?>
                <div id="link-select-select" style="margin-top:10px;display: <?=array_key_exists('categoryId',$link)?'block':'none'?>;">
                    <div class="related j_related_selector" data-callback="selectCallback" data-add-text="选择数据" data-edit-text="重新选择">
                        <input type="hidden" class="related_input" value="<?=ArrayHelper::getValue($link,'dataId')?>">
                        <ul class="related_list" id="upload_list" style="margin-bottom: 7px;margin-top: 0;"></ul>
                        <span style="display: none;">已经添加 <b class="related_count">0</b></span>
                        <a id="related_btn" data-href="<?=UrlHelper::to([ '/assets/node', 'multiple' => false ])?>" href="<?= UrlHelper::to( ArrayHelper::merge([ '/assets/node', 'multiple' => false ],(array_key_exists('dataId',$link)?['m'=>$allCategoryList[$link['categoryId']]['model']['name'],'cm'=>$allCategoryList[$link['categoryId']]['model']['name'],'hide_category_search'=>true,ucwords($allCategoryList[$link['categoryId']]['model']['name']).'Search[category_id]'=>ArrayHelper::getValue($link,'categoryId')]:[])) ) ?>" class="related_btn btn btn-default" style="margin-left: 0;">点击添加</a>
                    </div>
                    <div class="hint-block">如果<span class="alert-danger">不选</span>，则表示选择所选栏目链接。</div>
                </div>
                <div id="link-select-input" style="margin-top:10px;display: <?=array_key_exists('link',$link)?'block':'none'?>;">
                    <input type="text" class="form-control" value="<?=ArrayHelper::getValue($link,'link')?>">
                </div>
                <?=Html::activeHiddenInput($model,'link')?>
            </div>
        </div>

	    <?= $form->field($model, 'target')->dropDownList(['target="_blank"'=>'新页面'],['prompt'=>'当前页'])?>
        <?= $form->field($model, 'thumb',[
            'template'=> '{label}<div class="col-sm-17"><div class="list-img list-img-multiple clearfix j_upload_single_img">{input}<ul class="upload_list"></ul><a class="upload upload_btn" href="javascript:;"><span class="iconfont">&#xe607;</span></a></div>{error}{hint}</div>'
        ])->hiddenInput(['class'=>'upload_input']);?>
        <?= $form->field($model, 'content')->textarea(['class'=>'j_editor', 'style'=>'width:100%;height:350px;'])?>
	    <?php if($this->context->isSuperAdmin):?>
		    <?= $form->field($model, 'system_mark')->textInput() ?>
	    <?php endif;?>
        <?php
        if($model->status === null) $model->status = 1;
        echo $form->field($model, 'status')->radioList([1=>'显示',0=>'隐藏'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]])->label('前台是否显示');
        ?>
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

