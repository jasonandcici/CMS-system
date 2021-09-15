<?php

namespace common\entity\models;

use common\entity\domains\PrototypeFieldDomain;
use Yii;

class PrototypeFieldModel extends PrototypeFieldDomain
{
    /**
     * @var array 字段类型列表
     */
    public $filedTypeText = [
        'text'=>'文本框',
        'int'=>'整数框',
        'textarea'=>'文本域',
        'editor'=>'富文本编辑器',
        'image'=>'单图片上传',
        'image_multiple'=>'多图片上传',
        'attachment'=>'单附件上传',
        'attachment_multiple'=>'多附件上传',
        'radio_inline'=>'单选框',
        'radio'=>'多行单选框',
        'checkbox_inline'=>'复选框',
        'checkbox'=>'多行复选框',
        'select'=>'下拉选择列表',
        'select_multiple'=>'下拉多选择列表',
        'tag'=>'TAG标签框',
        'passport'=>'密码框',
        'date'=>'日期',
        'datetime'=>'日期&时间',
        'number'=>'数字框',
        'captcha'=>'验证码',
        'relation_data'=>'数据关联',
        //'relation_category'=>'栏目关联',
        //'city'=>'城市选择',
        //'city_multiple'=>'多城市选择',
    ];


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModel()
    {
        return $this->hasOne(PrototypeModelModel::className(), ['id' => 'model_id']);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        switch ($this->type){
            case 'int':
                $this->field_type = 'int';
                break;
            case 'number':
                $this->field_type = 'decimal';
                break;
            case 'image':
            case 'image_multiple':
            case 'attachment':
            case 'attachment_multiple':
                $this->field_type = 'text';
                break;
            case 'editor':
                $this->field_type = 'longtext';
                break;
            case 'radio':
            case 'radio_inline':
            case 'select':
                $this->field_type = 'enum';
                break;
            case 'date':
                $this->field_type = 'date';
                break;
            case 'datetime':
                $this->field_type = 'datetime';
                break;
            case 'relation_data':
                $this->field_type = 'int';
                break;
            default:
                $this->field_type = 'varchar';
                break;
        }

        return parent::beforeSave($insert);
    }
}
