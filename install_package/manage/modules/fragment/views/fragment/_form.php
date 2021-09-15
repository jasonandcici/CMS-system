<?php
use manage\assets\FormAsset;
use yii\web\View;
use common\widgets\ActiveForm;
use yii\helpers\Html;

/**
 * @var $model
 * @var $categoryInfo
 */
FormAsset::register($this);
$this->registerJs("formApp.init();commonApp.formYiiAjax($('#j_form'));", View::POS_READY);
?>
<?php $this->beginBlock('topButton'); ?>
<?= Html::a(Yii::t('common','Back List'), ['index','category_id'=>$categoryInfo->id], ['class' => 'btn btn-default j_goback']) ?>
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
        <?=Html::activeHiddenInput($model,'category_id')?>
        <?= $form->field($model, 'title')->textInput() ?>
        <?= $form->field($model, 'name')->textInput() ?>
        <?= $form->field($model, 'value')->textInput()->label('默认值') ?>
        <?= $form->field($model, 'style')->dropDownList([
            1=>'文本输入框',
            2=>'密码输入框',
            3=>'文本域',
            4=>'下拉列表',
            5=>'单选单行',
            6=>'单选多行',
            7=>'复选框单行',
            8=>'复选框多行',
            9=>'单图片上传',
            10=>'多图片上传',
            11=>'单附件上传',
            12=>'多附件上传',
            13=>'Tag文本输入框',
            14=>'富文本编辑器',
        ]) ?>
        <?= $form->field($model, 'setting')->textarea(['rows'=>8])
            ->hint('以json格式写入，可选的值有：<br /><b>rows</b> 文本域的的高度<br /><b>list</b> 下拉列表、单选和复选的选项<br /><b>hint</b> 注释
            <br />示例：<br /><code>{"list":{"0":"选项名一","1":"选项名二"},"hint":"字段注释"}</code>') ?>
        <!-- 表单控件结束 -->
        <div class="form-data-footer">
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-14">
                    <?= Html::submitButton(Yii::t('common','Submit'), ['class' => 'btn btn-primary']) ?>
                    <?= Html::a(Yii::t('common','Back List').' <span class="st">&gt;</span>', ['index','category_id'=>$categoryInfo->id], ['class' => 'btn btn-link j_goback']) ?>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<!-- 表单结束 -->

