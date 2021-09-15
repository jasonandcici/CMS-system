<?php
/**
 * @var $model
 */

use common\widgets\ActiveForm;
use manage\assets\FormAsset;
use yii\helpers\Html;
use yii\web\View;

$this->title = '添加敏感词';
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
		<?= $form->field($model, 'name')->textInput() ?>

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