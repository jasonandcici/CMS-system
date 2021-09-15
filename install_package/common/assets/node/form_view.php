<?php
/**
 * This is the template for generating the model class of a specified table.
 */
use common\helpers\ArrayHelper;

/* @var $model  */
/* @var $fields  */

echo "<?php\n";

echo "/**
 * @var \$model
 * @var \$formModel
 */

use common\helpers\HtmlHelper;

\$this->title = '".$model->title."';
/**
 * 生成html
 * @param \$model
 * @param \$fieldName
 * @param \$value
 * @return string
 */
function generateHtml(\$model,\$fieldName,\$value = null){
    return
        '<div class=\"form-group\" style=\"margin-bottom: 0;\">
        <label class=\"control-label col-sm-4\">'.\$model->getAttributeLabel(\$fieldName).'</label>
        <div class=\"col-sm-17\">
            <div class=\"form-control-static\">'.((\$value===null?\$model->\$fieldName:\$value)?:'--').'</div>
        </div>
    </div>';
};
?>

<?php \$this->beginBlock('topButton'); ?>
<a href=\"javascript:history.go(-1);\" class=\"btn btn-default\"><?=Yii::t('common','Back List')?></a>
<?php \$this->endBlock(); ?>

<div class=\"panel panel-default\">
    <div class=\"panel-body form-horizontal\">
        
";

// 输出内容
foreach ($fields as $item){
    if(in_array($item->type,['captcha','relation_data','relation_category','city','city_multiple'])) continue;

    switch ($item->type){
        case 'image':
        case 'image_multiple':
            echo "        <?php
            \$html = '<div class=\"list-img  clearfix\"><ul class=\"upload_list\">';
            foreach(HtmlHelper::fileDataHandle(\$model->".$item->name.") as \$item){
                \$html .= '<li><div class=\"left\"><div class=\"pic-wraper\"><div class=\"pic\"><div class=\"inner\"><a class=\"upload_preview\" target=\"_blank\" href=\"'.HtmlHelper::getFileItem(\$item).'\">'.HtmlHelper::getImgHtml(\$item).'</a></div></div></div></div></li>';
            }
            \$html .= '</ul></div>';
            echo generateHtml(\$model,'".$item->name."',\$html); ?>\n";
            break;
        case 'attachment':
        case 'attachment_multiple':
            echo "        <?php
            \$html = '<div class=\"list-file clearfix\"><ul class=\"upload_list\">';
            foreach(HtmlHelper::fileDataHandle(\$model->".$item->name.") as \$item){
                \$html .= '<li><a class=\"t\" target=\"_blank\" href=\"'.HtmlHelper::getFileItem(\$item).'\"><span class=\"iconfont\">&#xe627;</span>'.HtmlHelper::getFileItem(\$item,'title').'</a></li>';
            }
            \$html .= '</ul></div>';
            echo generateHtml(\$model,'".$item->name."',\$html); ?>\n";
            break;
        default:
            echo "        <?=generateHtml(\$model,'".$item->name."')?>\n";
            break;
    }
}

echo "        <?=generateHtml(\$model,'status',\$model->status?\"<span class='label label-success'>已处理</span>\":\"<span class='label label-danger'>未处理</span>\")?>
        <?=generateHtml(\$model,'create_time',date('Y-m-d H:i',\$model->create_time))?>
    </div>
</div>";
?>