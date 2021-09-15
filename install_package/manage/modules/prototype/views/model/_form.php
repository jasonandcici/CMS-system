<?php
use yii\web\View;
use common\widgets\ActiveForm;
use yii\helpers\Html;

/**
 * @var $model
 * @var $form
 */
$this->registerJsFile('@web/js/plugins/ace/ace.js',['depends' => [\manage\assets\FormAsset::className()]]);
$this->registerJs("$('#j_tag_input').tagsinput();", View::POS_READY);
$this->registerJs('
    var $codeInput = $("#prototypemodelmodel-extend_code"),
        editor = ace.edit("ace-editor");
    editor.setTheme("ace/theme/dawn");
    editor.session.setMode("ace/mode/php");
    editor.getSession().on("change", function(e) {
        $codeInput.val(editor.getValue());
    });
', View::POS_READY);
?>
<?php $this->beginBlock('topButton'); ?>
<?= Html::a(Yii::t('common','Back List'), ['index'], ['class' => 'btn btn-default j_goback']) ?>
<?php $this->endBlock(); ?>

<!-- tab开始 -->
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#tab-model-base" aria-controls="tab-model-base" role="tab" data-toggle="tab">基本选项</a></li>
    <li role="presentation"><a href="#tab-model-extend" aria-controls="tab-model-extend" role="tab" data-toggle="tab">扩展代码</a></li>
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
        <div class="tab-pane active" id="tab-model-base" role="tabpanel">
            <?= $form->field($model, 'title')->textInput() ?>
            <?= $form->field($model, 'name')->textInput(['disabled'=>!$model->isNewRecord]) ?>
            <?= $form->field($model, 'type')->dropDownList($this->context->modelTypeList, ['class'=>'form-control','disabled'=>!$model->isNewRecord])?>
            <?= $form->field($model, 'filter_sensitive_words_fields')->textInput(['id'=>'j_tag_input']) ?>
            <?=$form->field($model, 'is_login_category')->radioList([1=>'需要',0=>'不需要'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]); ?>
            <?=$form->field($model, 'is_login')->radioList([1=>'需要',0=>'不需要'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]); ?>
            <?=$form->field($model, 'is_login_download')->radioList([1=>'需要',0=>'不需要'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]); ?>
        </div>
        <div class="tab-pane" id="tab-model-extend" role="tabpanel">
            <?= Html::activeHiddenInput($model,'extend_code')?>
            <pre id="ace-editor" style="height: 500px;border: none;"><?=$model->extend_code?></pre>
        </div>
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
<?php $this->beginBlock('endBlock');?>
<script>
    var $type = $('#prototypemodelmodel-type');
    changeView($type.val());
    $type.change(function () {
        changeView($(this).val());
    });

    function changeView(_val) {
        if(_val==1){
            $('.field-prototypemodelmodel-is_login_category,.field-prototypemodelmodel-is_login_download').hide();
            $('.field-prototypemodelmodel-is_login .control-label').text('提交表单需登录');
        }else{
            $('.field-prototypemodelmodel-is_login_category,.field-prototypemodelmodel-is_login_download').show();
            $('.field-prototypemodelmodel-is_login .control-label').text('详情访问需登录');
        }
    }
</script>
<?php $this->endBlock();?>
