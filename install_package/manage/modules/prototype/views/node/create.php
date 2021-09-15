<?php
/**
 * @var $model
 * @var $roleList
 * @var $categoryInfo
 * @var $tags
 */

use common\helpers\ArrayHelper;
use manage\assets\FormAsset;
use yii\helpers\Html;
use yii\web\View;
use common\widgets\ActiveForm;

$pList = ArrayHelper::getParents($this->context->categoryList,$categoryInfo->id);

$relation = Yii::$app->getRequest()->get('relation');
if(!empty($relation)){
    $arrRelation = explode('_',$relation);
    $this->title = $this->context->findDataList($arrRelation[0],true)->where(['id'=>$arrRelation[1]])->one()->title;
    $this->params['subTitle'] = '(添加内容)';
    $this->registerCss('
        .page-header{margin-top:0;}
        footer{display:none;}
    ');
    unset($arrRelation);
}else{
    $this->title = '添加内容';
    $this->params['subTitle'] = '('.implode(' / ',ArrayHelper::getColumn($pList,'title')).')';
}

FormAsset::register($this);
$this->registerJs("formApp.init();commonApp.formYiiAjax($('#j_form'));formApp.editorPlugin();", View::POS_READY);

$templatePath = Yii::getAlias('@home').'/themes/'.$this->context->siteInfo->theme;
$templateList = [];
foreach (\common\helpers\FileHelper::findFileList($templatePath) as $i=>$item){
    $item = str_replace($templatePath.'/','',$item);
    $item = explode('/',$item);
    $templateList[$item[0]][] = str_replace('.php','',$item[1]);
}
unset($templatePath);
?>

<?php $this->beginBlock('topButton'); ?>
<?= Html::a(Yii::t('common','Back List'), 'javascript:history.go(-1);', ['class' => 'btn btn-default j_goback']) ?>
<?php $this->endBlock(); ?>

<!-- tab开始 -->
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#tab-content-base" aria-controls="tab-content-base" role="tab" data-toggle="tab">基本选项</a></li>
    <li role="presentation"><a href="#tab-content-set" aria-controls="tab-content-set" role="tab" data-toggle="tab">其他设置</a></li>
    <li role="presentation"><a href="#tab-content-seo" aria-controls="tab-content-seo" role="tab" data-toggle="tab">SEO设置</a></li>
</ul>
<div class="panel panel-default form-data">
    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'id'=>'j_form',
            'options'=>['class' => 'form-horizontal tab-content'],
            'fieldConfig'=>['template'=>'{label}<div class="col-sm-17">{input}{error}{hint}</div>', 'labelOptions'=>['class'=>'col-sm-4 control-label']]
        ]); ?>
        <!-- 表单控件开始 -->
        <div class="tab-pane active" id="tab-content-base" role="tabpanel">
            <?= Html::activeInput('hidden', $model, 'category_id') ?>
            <?= Html::activeInput('hidden', $model, 'model_id') ?>

            <?php if(!empty($relation)) { ?>
                <?= Html::hiddenInput('relation',$relation) ?>
            <?php }?>

            <?= $form->field($model, 'title')->textInput() ?>

            <?= $this->render('_form_'.$categoryInfo->model->name, ['model' => $model,'form'=>$form,'categoryInfo'=>$categoryInfo,'parentCategoryList'=>$pList,'tags'=>$tags]) ?>

            <?php if($categoryInfo->enable_tag){ if(!isset($this->params['customTags']) || !$this->params['customTags']):
                $this->registerJs("$('#j_tag_input').tagsinput({trimValue: true});", View::POS_READY);?>
            <div class="form-group">
                <label class="col-sm-4 control-label">Tags</label>
                <div class="col-sm-17">
                    <?=Html::textInput('tags',null,['class'=>'form-control','id'=>'j_tag_input'])?>
                </div>
            </div>
            <?php endif;}?>

            <?php if($categoryInfo->enable_push) echo $form->field($model, 'is_push')->radioList([1=>'推荐',0=>'不推荐'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]])->label('是否推荐'); ?>
            <?= $form->field($model, 'status')->radioList([1=>'启用',0=>'禁用',2=>'草稿'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]])->label('状态'); ?>
        </div>
        <div class="tab-pane" id="tab-content-set" role="tabpanel">
            <!--<?= $form->field($model, 'is_comment')->radioList([1=>'开启',0=>'关闭'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]])->label('是否开启评论');?>-->
            <?= $form->field($model, 'jump_link')->textInput()->hint('设置跳转链接后，访问此页面将直接跳转到该链接。') ?>
            <?= $form->field($model, 'create_time',['template'=>'{label}<div class="col-sm-17"><label class="input-group">{input}<span class="input-group-addon iconfont">&#xe62c;</span></label></div>'])->hiddenInput(['class'=>'j_date_piker'])->label('创建日期') ?>
            <?php
            $model->is_login = $categoryInfo->is_login_content;
            echo $form->field($model, 'is_login')->radioList([1=>'是',0=>'否'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]);?>

	        <?php
	        if($this->context->config['site']['enableComment'] && $categoryInfo->is_comment){
		        if($model->is_comment === null) $model->is_comment = ($categoryInfo->is_comment?1:0);
		        echo $form->field($model, 'is_comment')->radioList([1=>'启用',0=>'禁用'],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]);
	        }
	        ?>

            <?php
            $model->layouts = $categoryInfo->layouts_content;
            echo $form->field($model, 'layouts')->dropDownList(ArrayHelper::unifyKeyValue(ArrayHelper::getValue($templateList,'layouts',[])),['prompt'=>'禁用布局']);?>
            <?=$form->field($model, 'template_content')->dropDownList(ArrayHelper::unifyKeyValue(ArrayHelper::getValue($templateList,$categoryInfo->model->name,[])),['prompt'=>'请选择'])->hint('默认使用栏目中设置的模板');?>

        </div>
        <div class="tab-pane" id="tab-content-seo" role="tabpanel">
            <?= $form->field($model, 'seo_title')->textInput()->label('SEO标题')?>
            <?= $form->field($model, 'seo_keywords')->textInput()->label('SEO关键词')?>
            <?= $form->field($model, 'seo_description')->textarea(['rows'=>8,'class'=>'form-control resize-none'])->label('SEO描述')?>
        </div>
        <!-- 表单控件结束 -->
        <div class="form-data-footer">
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-14">
                    <?= Html::submitButton(Yii::t('common','Submit'), ['class' => 'btn btn-primary']) ?>
                    <?= Html::a(Yii::t('common','Back List').' <span class="st">&gt;</span>','javascript:history.go(-1);', ['class' => 'btn btn-link j_goback']) ?>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>