<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%sensitive_words}}".
 *
 * @property string $id
 * @property string $name
 */
class SensitiveWordsDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sensitive_words}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 40],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '敏感词',
        ];
    }
}
