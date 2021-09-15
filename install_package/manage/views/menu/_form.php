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
use yii\helpers\ArrayHelper;
use common\widgets\ActiveForm;
use yii\helpers\Html;

/**
 * @var $model
 * @var $menuList
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
        <?= $form->field($model, 'pid')->dropDownList(ArrayHelper::merge([0=>'—顶级菜单—'],ArrayHelper::map($menuList,'id','title')), ['class'=>'form-control','prety'=>true])?>
        <?= $form->field($model, 'type')->dropDownList($this->context->menuTypeList, ['class'=>'form-control'])?>
        <?= $form->field($model, 'link')->textInput()->hint('格式：" site/index "，外部链接直接填写链接。') ?>
        <?= $form->field($model, 'param')->textInput()->hint('格式：" id=1"，多个用" & "号隔开') ?>
        <?php
        if($model->status === null) $model->status = 1;
        echo $form->field($model, 'status')->radioList([1=>'启用',0=>'禁用'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]);
        ?>
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

