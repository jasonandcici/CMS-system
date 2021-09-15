<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%system_log}}".
 *
 * @property string $id
 * @property integer $site_name
 * @property string $crate_user
 * @property string $operation_type
 * @property string $content
 * @property string $create_time
 */
class SystemLogDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%system_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['site_name', 'crate_user', 'operation_type', 'content'], 'required'],
            [['create_time'], 'integer'],
            [['operation_type', 'content'], 'string'],
            [['crate_user','site_name'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'site_name' => 'Site Name',
            'crate_user' => 'Crate User',
            'operation_type' => 'Operation Type',
            'content' => 'Content',
            'create_time' => 'Create Time',
        ];
    }
}
