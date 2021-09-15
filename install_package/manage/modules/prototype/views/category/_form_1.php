<?php
use common\helpers\ArrayHelper;
use common\widgets\ActiveForm;
use manage\helpers\UrlHelper;
use yii\helpers\Html;

/**
 * @var $model
 * @var $categoryList
 * @var $modelList
 * @var $roleList
 * @var $auth
 * @var $currUserRoles
 * @var $hasSetRoleAuth
 */

$templatePath = Yii::getAlias('@home').'/themes/'.$this->context->siteInfo->theme;
$templateList = [];
foreach (\common\helpers\FileHelper::findFileList($templatePath) as $i=>$item){
    $item = str_replace($templatePath.'/','',$item);
    $item = explode('/',$item);
    $templateList[$item[0]][] = str_replace('.php','',$item[1]);
}
unset($templatePath);
?>
<!-- tab开始 -->
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#tab-category-base" aria-controls="tab-category-base" role="tab" data-toggle="tab">基本选项</a></li>
    <li role="presentation"><a href="#tab-category-config" aria-controls="tab-category-config" role="tab" data-toggle="tab">栏目配置</a></li>
    <li role="presentation"><a href="#tab-category-seo" aria-controls="tab-category-seo" role="tab" data-toggle="tab">SEO设置</a></li>
    <?php if($hasSetRoleAuth){?>
    <li role="presentation"><a href="#tab-category-auth" aria-controls="tab-category-auth" role="tab" data-toggle="tab">权限设置</a></li>
    <?php } ?>
</ul>
<!-- 表单开始 -->
<div class="panel panel-default form-data">
    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'id'=>'j_form',
            'options'=>['class' => 'form-horizontal tab-content'],
            'fieldConfig'=>['template'=>'{label}<div class="col-sm-17">{input}{error}{hint}</div>', 'labelOptions'=>['class'=>'col-sm-4 control-label']]
        ]); ?>
        <!-- 表单控件开始 -->
        <div class="tab-pane active" id="tab-category-base" role="tabpanel">
            <?= Html::activeInput('hidden', $model, 'type') ?>
            <?= $form->field($model, 'title')->textInput()->label('<b class="text-danger">*</b> '.$model->getAttributeLabel('title')) ?>
            <?= $form->field($model, 'sub_title')->textInput() ?>
            <?= $form->field($model, 'pid')->dropDownList(ArrayHelper::merge(['0'=>'—顶级栏目—'],ArrayHelper::map($allCategoryList,'id','title')), ['class'=>'form-control','prety'=>true])?>
            <?= $form->field($model, 'template')->dropDownList(ArrayHelper::unifyKeyValue(ArrayHelper::getValue($templateList,'page',[])),['prompt'=>'请选择'])->label('内容模板')?>
            <?= $form->field($model, 'slug')->textInput()->hint('格式：" channel/news/list/…… "') ?>

            <div class="form-group">
	            <?php
	            $link = [];
	            if(!empty($model->link) && strpos($model->link,'{') === 0){
		            $link = json_decode($model->link,true);
	            }else{ $link = ['link'=>$model->link]; }?>
                <label class="col-sm-4 control-label">跳转链接</label>
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
                    <div class="hint-block">设置跳转链接后，生成栏目链接时将会变成此链接。</div>
                </div>
            </div>

	        <?= $form->field($model, 'target')->dropDownList(['target="_blank"'=>'新页面'],['prompt'=>'当前页'])?>
            <?= $form->field($model, 'thumb',[
                'template'=> '{label}<div class="col-sm-17"><div class="list-img list-img-multiple clearfix j_upload_single_img">{input}<ul class="upload_list"></ul><a class="upload upload_btn" href="javascript:;"><span class="iconfont">&#xe607;</span></a></div>{error}{hint}</div>'
            ])->hiddenInput(['class'=>'upload_input']);?>
            <?= $form->field($model, 'content')->textarea(['class'=>'j_editor', 'style'=>'width:100%;height:350px;'])?>
        </div>
        <div class="tab-pane" id="tab-category-config" role="tabpanel">
	        <?php if($this->context->isSuperAdmin):?>
		        <?= $form->field($model, 'system_mark')->textInput() ?>
	        <?php endif;?>
            <?php
            if($model->status === null) $model->status = 1;
            echo $form->field($model, 'status')->radioList([1=>'显示',0=>'隐藏'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]])->label('前台是否显示');
            ?>
            <div class="form-group">
                <label class="control-label col-sm-4">在内容管理显示</label>
                <div class="col-sm-17">
                    <label class="radio-inline">
                        <input type="radio" name="PrototypeCategoryModel[expand][enable_admin]" value="1"<?=$model->expand->enable_admin?' checked':''?>> 显示
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="PrototypeCategoryModel[expand][enable_admin]" value="0"<?=!$model->expand->enable_admin?' checked':''?>> 隐藏
                    </label>
                </div>
            </div>
            <?php
            if($model->is_login === null) $model->is_login = 0;
            echo $form->field($model, 'is_login')->radioList([1=>'是',0=>'否'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]);
            ?>
            <?php
            if($model->isNewRecord) $model->layouts = 'main';
            echo $form->field($model, 'layouts')->dropDownList(ArrayHelper::unifyKeyValue(ArrayHelper::getValue($templateList,'layouts',[])),['prompt'=>'禁用布局']);
            ?>

        </div>
        <div class="tab-pane" id="tab-category-seo" role="tabpanel">
            <?= $form->field($model, 'seo_title')->textInput()?>
            <?= $form->field($model, 'seo_keywords')->textInput()?>
            <?= $form->field($model, 'seo_description')->textarea(['rows'=>8,'class'=>'form-control resize-none'])?>
        </div>
        <?php if($hasSetRoleAuth){?>
        <div class="tab-pane" id="tab-category-auth" role="tabpanel">
            <div class="form-group">
                <label class="col-sm-4 control-label">权限列表</label>
                <div class="col-sm-17">
                    <table class="table table-bordered">
                        <tbody>
                        <?php foreach ($roleList as $item){?>
                            <tr>
                                <th style="padding:5px 8px;vertical-align: middle;" width="100"><?=$item->description?></th>
                                <td style="padding:5px 8px 10px;">
                                    <button type="button" class="btn btn-xs btn-link" style="padding-bottom: 0;padding-top: 6px;">( 全选 )</button>
                                    <?php foreach ($this->context->accessList as $k=>$v){?>
                                        <label class="checkbox-inline" data-auth="<?=$k?>" style="<?=($k=='prototype/node/page' && $model->type === 0) || ($k != 'prototype/node/page' && $model->type === 1) ?'display:none;':''?>">
                                            <input name="auth[]" <?=in_array($item->name.','.$k.'?category_id='.$model->id,$auth) || array_key_exists($item->name,$currUserRoles)?' checked ':''?> type="checkbox" value="<?=$item->name.','.$k?>"> <?=$v?>
                                        </label>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php }?>
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

