<?php
/**
 * @var $scope
 * @var $config
 */

use common\helpers\ArrayHelper;
use manage\assets\FormAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use common\widgets\ActiveForm;

$this->title = $title;
if(isset($maxFileSize)) $this->params['subTitle'] = '（服务器限制上传的文件大小不超过<b class="text-danger" id="max-upload-size">'.$maxFileSize.'</b>）';

if(file_exists(Yii::$app->viewPath.'/'.Yii::$app->controller->id.'/_'.$scope.'.php')) echo $this->render('_'.$scope,\common\helpers\ArrayHelper::merge(['config'=>$config,'scope'=>$scope],(isset($maxFileSize)?['maxFileSize'=>$maxFileSize]:[])));

FormAsset::register($this);
\manage\assets\UeditorAsset::register($this);
$this->registerJsFile('@web/js/plugins/uploadUeditor.js',['depends' => [FormAsset::className()]]);
$this->registerJs("
formApp.init();
commonApp.formYiiAjax($('#j_form'));
uploadUeditor.init({serverUrl:'".Url::to(['/files/index'])."'});
", View::POS_READY);
?>
<!-- 表单开始 -->
<div class="panel panel-default form-data">
    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'id'=>'j_form',
            'options'=>['class' => 'form-horizontal'],
            'fieldConfig'=>['template'=>'{label}<div class="col-sm-17">{input}{error}{hint}</div>', 'labelOptions'=>['class'=>'col-sm-4 control-label']]
        ]); ?>
        <?php
        foreach ($config as $i => $item) {
            // 用户配置部分
            if(!$this->context->isSuperAdmin && $item['scope'] == 'member' && in_array($item['name'],['registerMode','actionList','relationContent','publishContent','defaultLogin','defaultRegister','defaultFindPassword'])) continue;

            if(!$this->context->isSuperAdmin && $item['scope'] == 'site' && ($item['name'] == 'enableComment' || $item['name']=='enableApi')) continue;

            $setting = !empty($item->setting) && strpos($item->setting,'{') === 0?json_decode($item->setting,true):[];

            $control = $form->field($item, "[$i]value",['options'=>['class'=>'form-group config-'.$scope.'-'.$item->name]])->label($item->title);
            if(array_key_exists('hint',$setting)) $control->hint($setting['hint']);
            switch($item->style){
                case 1:
                    echo $control->textInput();
                    break;
                case 2:
                    echo $control->passwordInput();
                    break;
                case 3:
                    echo $control->textarea(['rows'=>array_key_exists('rows',$setting)?$setting['rows']:3,'class'=>'form-control resize-none']);
                    break;
                case 4:
                    if(ArrayHelper::getValue(ArrayHelper::getValue($setting,'other',[]),'multiple')){
                        $item->value = explode(',',$item->value);
                    }

                    echo $control->dropDownList(array_key_exists('list',$setting)?$setting['list']:[], array_merge(['class'=>'form-control'],ArrayHelper::getValue($setting,'other',[])));
                    break;
                case 5:
                    echo $control->radioList(array_key_exists('list',$setting)?$setting['list']:[],['itemOptions'=>['labelOptions'=>['class'=>'radio-inline']]]);
                    break;
                case 6:
                    echo $control->radioList(array_key_exists('list',$setting)?$setting['list']:[],['item'=>function($index, $label, $name, $checked, $value){
                        return '<div class="radio"><label><input type="radio" name="'.$name.'" value="'.$value.'"'.($checked==$value?' checked':'').'>'.$label.'</label></div>';
                    }]);
                    break;
                case 7:
                    echo $control->radioList(array_key_exists('list',$setting)?$setting['list']:[],['itemOptions'=>['labelOptions'=>['class'=>'checkbox-inline']]]);
                    break;
                case 8:
                    echo $control->radioList(array_key_exists('list',$setting)?$setting['list']:[],['item'=>function($index, $label, $name, $checked, $value){
                        return '<div class="checkbox"><label><input type="checkbox" name="'.$name.'" value="'.$value.'"'.($checked==$value?' checked':'').'>'.$label.'</label></div>';
                    }]);
                    break;
                case 9:
                    $hint = array_key_exists('hint',$setting)?$setting['hint']:'';
                    echo $form->field($item, "[$i]value",[
                        'template'=> '{label}<div class="col-sm-17"><div class="list-img list-img-multiple clearfix" id="j_upload_single_img'.$i.'">{input}<ul class="upload_list"></ul><a class="upload upload_btn" href="javascript:;"><span class="iconfont">&#xe607;</span></a></div>{error}{hint}</div>'
                    ])->hiddenInput(['class'=>'upload_input'])->label($item->title)->hint($hint);

                    \manage\assets\UeditorAsset::register($this);
                    $this->registerJs('uploadUeditor.singleImage($("#j_upload_single_img'.$i.'"));', View::POS_READY);
                    break;
                case 10:
                    $hint = array_key_exists('hint',$setting)?$setting['hint']:'';
                    echo $form->field($item, "[$i]value",[
                        'template'=> '{label}<div class="col-sm-17"><div class="list-img list-img-multiple clearfix" id="j_upload_multiple_img'.$i.'">{input}<ul class="upload_list"></ul><a class="upload upload_btn" href="javascript:;"><span class="iconfont">&#xe607;</span></a></div>{error}{hint}</div>'
                    ])->hiddenInput(['class'=>'upload_input'])->label($item->title)->hint($hint);

                    \manage\assets\UeditorAsset::register($this);
                    $this->registerJs('uploadUeditor.multipleImage($("#j_upload_multiple_img'.$i.'"));', View::POS_READY);
                    break;
                case 11:
                    $hint = array_key_exists('hint',$setting)?$setting['hint']:'';
                    echo $form->field($item, "[$i]value",[
                        'template'=> '{label}<div class="col-sm-17"><div class="list-file clearfix j_upload_single_file">{input}<ul class="upload_list"></ul><a class="upload btn btn-default upload_btn" href="javascript:;">文件上传</a></div>{error}{hint}</div>'])->hiddenInput(['class'=>'upload_input'])->label($item->title)->hint($hint);
                    break;
                case 12:
                    $hint = array_key_exists('hint',$setting)?$setting['hint']:'';
                    echo $form->field($item, "[$i]value",[
                        'template'=> '{label}<div class="col-sm-17"><div class="list-file clearfix j_upload_multiple_file">{input}<ul class="upload_list"></ul><a class="upload btn btn-default upload_btn" href="javascript:;">文件上传</a></div>{error}{hint}</div>'])->hiddenInput(['class'=>'upload_input'])->label($item->title)->hint($hint);
                    break;
                case 13:
                    echo $control->textInput(['id'=>'j_tag_input'.$i]);
                    $this->registerJs("$('#j_tag_input".$i."').tagsinput();", View::POS_READY);
                    break;

                default:
                    if (isset($this->blocks[$item->name])){
                        echo $this->blocks[$item->name];
                    }
                    break;
            }
        }
        ?>
        <!-- 表单控件结束 -->
        <div class="form-data-footer">
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-14">
                    <?= Html::submitButton(Yii::t('common','Submit'), ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<!-- 表单结束 -->