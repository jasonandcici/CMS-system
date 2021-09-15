<?php
/**
 * This is the template for generating the model class of a specified table.
 */
use common\helpers\ArrayHelper;
use yii\helpers\Inflector;

/* @var $model  */
/* @var $fields  */

echo "<?php\n";
?>

namespace common\entity\nodes;

use Yii;
use common\helpers\ArrayHelper;

class <?=ucwords($model->name)?>Model extends \common\components\BaseNodeModel
{
    /**
     * @var int node模型类型
     */
    protected $nodeType = 0;

    <?php foreach ($fields as $item) {
        if($item->type == 'relation_data' && $item->setting['relationType']) continue;
        $compare = ArrayHelper::getValue($item->custom_verification_rules,'compare');
        if($compare && !array_key_exists('rules',$compare)){
            echo 'public $'.$item->name.'_repeat;\n            ';
        }

        if($item->type == 'captcha'){
            echo 'public $'.$item->name.';\n            ';
        }
    }?>

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%node_<?=$model->name?>}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
<?php
$trim = $required = $integer = $number = $string = [];
$unique = $email = $ip = $url = [];
$date = [];
$multiple = [];
$allFieldName = [];
foreach ($fields as $item){
    if($item->type == 'relation_data' && $item->setting['relationType']) continue;
	$allFieldName[] = $item->name;
    if($item->field_type == 'varchar') $trim[] = "'".$item->name."'";
    if($item->is_required) $required[] = "'".$item->name."'";
    if($item->field_type == 'int' && !ArrayHelper::getValue($item->custom_verification_rules,'length')) $integer[] = "'".$item->name."'";
    if($item->field_type == 'decimal' && !ArrayHelper::getValue($item->custom_verification_rules,'length')) $number[] = "'".$item->name."'";
    if(($item->field_type == 'text' || $item->field_type == 'longtext') && !in_array($item->type,['checkbox','checkbox_inline','select_multiple'])) $string[] = "'".$item->name."'";
    if(ArrayHelper::getValue($item->custom_verification_rules,'unique')) $unique[] = "'".$item->name."'";
    if(ArrayHelper::getValue($item->custom_verification_rules,'email')) $email[] = "'".$item->name."'";
    if(ArrayHelper::getValue($item->custom_verification_rules,'ip')) $ip[] = "'".$item->name."'";
    if(ArrayHelper::getValue($item->custom_verification_rules,'url')) $url[] = "'".$item->name."'";

    if($item->field_type == 'date' || $item->field_type == 'datetime') $date[] = "'".$item->name."'";

    if(in_array($item->type,['checkbox','checkbox_inline','select_multiple'])) $multiple[] = "'".$item->name."'";
}
?>
            [['title',<?=implode(',',$trim)?>], 'trim'],
            [['site_id','model_id', 'category_id', 'title',<?=implode(',',$required)?>], 'required'],
            [['site_id', 'status', 'create_time','update_time','model_id', 'category_id', 'sort', 'is_push', 'is_comment','is_login', 'views',<?=implode(',',$integer)?>], 'integer'],
            <?php
                if(!empty($string)){
                    echo "[[".implode(',',$string)."], 'string'],\n            ";
                }
                if(!empty($number)){
                    echo "[[".implode(',',$number)."], 'number'],\n            ";
                }
                if(!empty($unique)){
                    echo "[[".implode(',',$unique)."], 'unique'],\n            ";
                }
                if(!empty($email)){
                    echo "[[".implode(',',$email)."], 'email'],\n            ";
                }
                if(!empty($ip)){
                    echo "[[".implode(',',$ip)."], 'ip'],\n            ";
                }
                if(!empty($url)){
                    echo "[[".implode(',',$url)."], 'url'],\n            ";
                }
                if(!empty($multiple)){
                    echo "[[".implode(',',$multiple)."],'filter','filter'=>function(\$value){return is_array(\$value)?implode(',',\$value):\$value;}],\n            ";
                }
                if(!empty($date)){
                    echo "[[".implode(',',$date)."], 'safe'],\n            ";
                }

                foreach ($fields as $item){
                    if($item->type == 'relation_data' && $item->setting['relationType']) continue;
                    if($item->field_type == 'enum' && !empty($item->options['list'])){
                        $options = [];
                        foreach ($item->options['list'] as $v){
                            $options[] = "'".$v['value']."'";
                        }
                        echo "['".$item->name."', 'in','range'=>[".implode(',',$options)."]],\n            ";
                    }elseif ($item->field_type == 'varchar' && $item->type !='captcha'){
                        $length = ArrayHelper::getValue($item->custom_verification_rules,'length');
                        if(!$length){
                            echo "['".$item->name."', 'string','max'=>".($item->field_length?:255)."],\n            ";
                        }else{
                            $length = explode(',',$length);
                            if(count($length) == 2){
                                echo "['".$item->name."', 'string','min'=>".$length[1].",'max'=>".$length[0]."],\n            ";
                            }else{
                                echo "['".$item->name."', 'string','max'=>".$length[0]."],\n            ";
                            }
                        }
                    }elseif ($item->field_type == 'int' || $item->field_type == 'decimal'){
                        $length = ArrayHelper::getValue($item->custom_verification_rules,'length');
                        if($length){
                            $length = explode(',',$length);
                            if(count($length) == 2){
                                echo "['".$item->name."', '".($item->field_type!=='int'?:'integer')."','min'=>".$length[1].",'max'=>".$length[0]."],\n            ";
                            }else{
                                echo "['".$item->name."', '".($item->field_type!=='int'?:'integer')."','max'=>".$length[0]."],\n            ";
                            }
                        }
                    }
                }

                foreach ($fields as $item) {
                    if($item->type == 'relation_data' && $item->setting['relationType']) continue;
                    $compare = ArrayHelper::getValue($item->custom_verification_rules,'compare');
                    if($compare){
                        if(!array_key_exists('rules',$compare)){
                            echo "['".$item->name."', 'compare'],\n            ";
                        }else{
                            foreach ($compare['rules'] as $v){
                                if($item->field_type == 'int'){
                                    $cv = intval($v['compareValue']);
                                }elseif ($item->field_type == 'decimal'){
                                    $cv = floatval($v['compareValue']);
                                }else{
                                    $cv = "'".$v['compareValue']."'";
                                }
                                echo "['".$item->name."', 'compare', 'compareValue' => ".$cv.", 'operator' => '".$v['operator']."'],\n            ";
                            }
                        }
                    }
                }

                foreach ($fields as $item) {
                    if($item->type == 'relation_data' && $item->setting['relationType']) continue;
                    $match = ArrayHelper::getValue($item->custom_verification_rules, 'match');
                    if(!empty($match)){
                        $match = str_replace(array("\r\n", "\r", "\n"),'$_break_tag_$',$match);
                        foreach (explode('$_break_tag_$',$match) as $v){
                            echo "['".$item->name."', 'match','pattern'=>'".$v."'],\n            ";
                        }
                    }
                }

                foreach ($fields as $item) {
                    if($item->type == 'relation_data' && $item->setting['relationType']) continue;
                    if($item->type == 'captcha'){
                        echo "['".$item->name."', 'captcha'],\n            ";
                    }
                }
            ?>

            [['title','jump_link', 'seo_title', 'seo_keywords','seo_description'], 'string', 'max' => 255],
            [['layouts','template_content'], 'string', 'max' => 50],
            [['count_user_relations',], 'string'],
<?php
$filter_sensitive_words_fields = [];
if(!empty($model->filter_sensitive_words_fields)){
    $filterSensitive = explode(',',$model->filter_sensitive_words_fields);
    foreach ($filterSensitive as $f){
        if(!in_array($f,$allFieldName)) continue;
        $filter_sensitive_words_fields[] = "'".$f."'";
    }
    unset($filterSensitive);
}
if(!empty($filter_sensitive_words_fields)){
?>
            /**
             * 敏感词检测
             */
            [[<?=implode(',',$filter_sensitive_words_fields)?>],function($attribute, $params){
                if (!$this->hasErrors()) {
                    $res = \common\helpers\SecurityHelper::checkSensitiveWords($this->$attribute);
                    if($res !== false){
                        $this->addError($attribute,$this->getAttributeLabel($attribute)."存在敏感词“".implode('、',$res)."”。");
                    }
                }
            }],
<?php } ?>
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'model_id' => '所属模型',
            'category_id' => '所属栏目',
            'site_id'=>'所属站点',
            'title' => '标题',
<?php foreach ($fields as $item){
    if($item->type == 'relation_data' && $item->setting['relationType']) continue;
    ?>
            '<?=$item->name?>' => '<?=$item->title?>',
<?php }?>
            'sort' => '排序',
            'status' => '状态',
            'template_content' => '页面模板',
            'is_push' => '是否推荐',
            'is_login' => '访问是否需要登录',
            'is_comment' => '是否开启评论',
            'layouts' => '页面布局',
            'views' => '浏览量',
            'jump_link' => '跳转链接',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
            'seo_title' => 'Seo Title',
            'seo_keywords' => 'Seo Keywords',
            'seo_description' => 'Seo Description',
        ];
    }

<?php

    $extraFields = [];
    // 数据关联
    foreach ($fields as $item){

    if($item->type == 'relation_data'){
        $modelName = ($item->setting['modelName']==='category'?'Prototype':'').ucwords($item->setting['modelName']);
        $classPrefix = '';
        $classPrefix = $item->setting['isNodeModel']?'':'\common\entity\models\\';
        $relationModelName = ucwords($model->name).$modelName;
        if($item->setting['relationType'] === 1){
	        $extraFields[] = '"'.lcfirst(Inflector::pluralize($item->setting['modelName'])).'"';
	        $extraFields[] = '"'.lcfirst(Inflector::pluralize($item->setting['modelName'])).'List"';
?>
    /**
     * <?=$item->title?>
     * @return \yii\db\ActiveQuery
     */
    public function get<?=ucwords(Inflector::pluralize($item->setting['modelName']))?>(){
        return $this->hasMany(<?=$relationModelName.'RelationModel'?>::className(),['parent_id'=>'id']);
    }

    /**
     * <?=$item->title?>详情
     * @return \yii\db\ActiveQuery
     */
    public function get<?=ucwords(Inflector::pluralize($item->setting['modelName']))?>List(){
        return $this->hasMany(<?=$classPrefix.$modelName.'Model'?>::className(),['id'=>'relation_id'])
            ->viaTable(<?=$relationModelName.'RelationModel'?>::tableName(),['parent_id'=>'id']);
    }
<?php
        }else{
	        $extraFields[] = '"'.lcfirst($item->setting['modelName']).'Info"';
?>
    /**
     * <?=$item->title?>详情
     * @return \yii\db\ActiveQuery
     */
    public function get<?=ucwords($item->setting['modelName'])?>Info(){
        return $this->hasOne(<?=$classPrefix.$modelName.'Model'?>::className(),['id'=>'<?=$item->name?>']);
    }
<?php }}} if(!empty($extraFields)){?>

    /**
     * api扩展字段
     * @return array
     */
    public function extraFields() {
        return ArrayHelper::merge(parent::extraFields(),[<?=implode(',',$extraFields)?>]);
    }

    <?php }
        echo $model->extend_code;
    ?>

}
