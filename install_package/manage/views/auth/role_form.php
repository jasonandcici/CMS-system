<?php
use common\helpers\UrlHelper;
use common\widgets\ActiveForm;
use yii\helpers\Html;

/**
 * @var $model
 * @var $userList
 */
$this->registerJsFile('@web/js/plugins/jquery.dataSelector.js',['depends' => [\yii\web\JqueryAsset::className()]]);
if(!empty($model->data)){
    $model->data = unserialize($model->data);
}
?>
<!-- 表单开始 -->
<div class="panel panel-default form-data">
    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'id'=>'j_form',
            'options'=>['class' => 'form-horizontal tab-content'],
            'fieldConfig'=>['template'=>'{label}<div class="col-sm-17">{input}{error}{hint}</div>', 'labelOptions'=>['class'=>'col-sm-4 control-label']]
        ]); ?>
        <!-- 表单控件开始 -->
        <?= $form->field($model, 'description')->textInput()->label('角色名称')?>
        <?php
        if(empty($model->name)){
            echo $form->field($model, 'name')->textInput()->label('角色标识');
        } ?>
        <div class="form-group">
            <label class="control-label col-sm-4">选择用户</label>
            <div class="col-sm-17">
                <p class="form-control-static help-block j_related_selector">
                    <?=Html::input('hidden','user',empty($userList)?null:implode(',',$userList),['id'=>'access_input','class'=>'related_input'])?>
                    已经添加 <b class="related_count">0</b> 个用户
                    <a href="<?=UrlHelper::to(['role-user','name'=>$model->name])?>" class="text-primary related_btn">点击添加</a>
                </p>
            </div>
        </div>
        <?= $form->field($model, 'data[loginSite]')->dropDownList(\common\helpers\ArrayHelper::map(\common\entity\models\SiteModel::findSite(),'id','title'))->label('登录跳转站点')?>
        <!-- 表单控件结束 -->
        <div class="form-data-footer">
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-14">
                    <?= Html::submitButton(Yii::t('common','Submit'), ['class' => 'btn btn-primary']) ?>
                    <?= Html::resetButton(Yii::t('common','Reset'), ['class' => 'btn btn-default']) ?>
                    <?= Html::a(Yii::t('common','Back List').' <span class="st">&gt;</span>','javascript:;', ['class' => 'btn btn-link j_goback']) ?>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>