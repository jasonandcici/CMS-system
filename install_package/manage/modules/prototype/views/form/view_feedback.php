<?php
/**
 * @var $model
 * @var $formModel
 */

use common\helpers\HtmlHelper;

$this->title = '意见反馈';
/**
 * 生成html
 * @param $model
 * @param $fieldName
 * @param $value
 * @return string
 */
function generateHtml($model,$fieldName,$value = null){
    return
        '<div class="form-group" style="margin-bottom: 0;">
        <label class="control-label col-sm-4">'.$model->getAttributeLabel($fieldName).'</label>
        <div class="col-sm-17">
            <div class="form-control-static">'.(($value===null?$model->$fieldName:$value)?:'--').'</div>
        </div>
    </div>';
};
?>

<?php $this->beginBlock('topButton'); ?>
<a href="javascript:history.go(-1);" class="btn btn-default"><?=Yii::t('common','Back List')?></a>
<?php $this->endBlock(); ?>

<div class="panel panel-default">
    <div class="panel-body form-horizontal">
        
        <?=generateHtml($model,'content')?>
        <?=generateHtml($model,'status',$model->status?"<span class='label label-success'>已处理</span>":"<span class='label label-danger'>未处理</span>")?>
        <?=generateHtml($model,'create_time',date('Y-m-d H:i',$model->create_time))?>
    </div>
</div>