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
 * @var $roleList
 * @var $userRoles
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
        <?php if(empty($model->username)){?>
            <?= $form->field($model, 'username')->textInput() ?>
            <?= $form->field($model, 'password')->textInput(['type'=>'password']) ?>
            <?= $form->field($model, 'password_repeat')->textInput(['type'=>'password']) ?>
        <?php }else{ ?>
            <?= $form->field($model, 'username')->textInput(['disabled'=>true]) ?>
        <?php } ?>

        <div class="form-group">
            <label class="col-sm-4 control-label">所属角色</label>
            <div class="col-sm-17">
                <?= Html::dropDownList('userRoles[]', ArrayHelper::getColumn($userRoles, 'name'), ArrayHelper::map($roleList, 'name', 'description'),['class'=>'form-control','multiple'=>true,'prety'=>true,'data-placeholder'=>'请选择所属角色（多选）']) ?>
                <div class="help-block"></div>
            </div>
        </div>

        <?= $form->field($model, 'mobile')->textInput() ?>
        <?= $form->field($model, 'email')->textInput() ?>
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

