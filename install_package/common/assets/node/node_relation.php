<?php
/**
 * This is the template for generating the model class of a specified table.
 */
use common\helpers\ArrayHelper;

/* @var $model  */
/* @var $field  */

echo "<?php\n";
?>

namespace common\entity\nodes;

use Yii;

class <?=ucwords($model->name).ucwords($field->setting['modelName'])?>RelationModel extends \common\components\BaseArModel
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%node_<?=$model->name.'_'.$field->setting['modelName']?>_relation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'relation_id'], 'required'],
            [['parent_id', 'relation_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'parent_id' => 'ID',
            'relation_id' => '相关内容id',
        ];
    }

}