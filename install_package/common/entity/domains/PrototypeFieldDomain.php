<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%prototype_field}}".
 *
 * @property string $id
 * @property integer $model_id
 * @property string $title
 * @property string $name
 * @property string $field_type
 * @property string $field_length
 * @property string $field_decimal_place
 * @property string $type
 * @property string $options
 * @property string $default_value
 * @property integer $is_required
 * @property integer $is_show_list
 * @property integer $is_search
 * @property string $hint
 * @property string $placeholder
 * @property string $custom_verification_rules
 * @property string $setting
 * @property string $sort
 * @property integer $is_updated
 * @property string $updated_target
 * @property string $history
 * @property string $is_generate
 *
 */
class PrototypeFieldDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%prototype_field}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'name'],'trim'],
            [['model_id', 'title', 'name', 'type'], 'required'],
            [['model_id', 'field_length', 'field_decimal_place', 'is_required', 'is_show_list', 'is_search', 'sort', 'is_updated','is_generate'], 'integer'],
            [['field_type', 'type', 'options', 'custom_verification_rules', 'setting','history'], 'string'],
            [['title', 'name','updated_target'], 'string', 'max' => 80],
            [['name'], 'match', 'pattern' => '/^[a-z][(\w|_)*]*[A-Za-z0-9]$/'],
            [['default_value', 'hint', 'placeholder'], 'string', 'max' => 255],
            ['name', 'unique', 'targetAttribute' => ['model_id', 'name']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'model_id' => 'Model ID',
            'title' => '字段标题',
            'name' => '字段名称',
            'field_type' => '字段类型',
            'field_length' => '字段长度',
            'field_decimal_place' => '小数点',
            'type' => '字段类型',
            'options' => '选项',
            'default_value' => '默认值',
            'is_required' => '是否必填',
            'is_show_list' => '是否显示在列表',
            'is_search' => '是否设为搜索',
            'hint' => '输入提示',
            'placeholder' => '占位符',
            'custom_verification_rules' => '自定义验证规则',
            'setting' => '其他设置',
            'sort' => '排序',
            'is_updated' => '是否已更新',
            'updated_target' => '更新对象',
            'is_generate' => '是否已经生成',
            'history' => '历史记录',
        ];
    }
}
