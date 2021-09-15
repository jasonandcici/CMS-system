<?php
/**
 * @var $model
 * @var $roleList
 * @var $userRoles
 */

use common\widgets\ActiveForm;
use manage\assets\FormAsset;
use yii\helpers\Html;
use yii\web\View;

$this->title = '修改用户';
FormAsset::register($this);
$this->registerJs("formApp.init();commonApp.formYiiAjax($('#j_form'));", View::POS_READY);
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
        <?= $form->field($model, 'username')->textInput(['disabled'=>true]) ?>
        <?= $form->field($model, 'password')->textInput(['type'=>'password']) ?>
        <?= $form->field($model, 'password_repeat')->textInput(['type'=>'password']) ?>

        <?= $form->field($model, 'email')->textInput() ?>
        <?= $form->field($model, 'cellphone',[
            // 手机国际区号
            'template' => '{label}<div class="col-sm-17"><div class="row"><div class="col-sm-6">'.Html::activeDropDownList($model,'cellphone_code',$this->context->config['sms']['cellphoneCode']?:['0086'=>'中国'],['class'=>'form-control']).'</div><div class="col-sm-18">{input}</div></div></div>{error}',
        ])->textInput(); ?>

        <?= $form->field($model, 'is_enable')->radioList([1=>'启用',0=>'禁用'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]); ?>
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