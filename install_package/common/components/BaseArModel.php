<?php
// +----------------------------------------------------------------------
// | nantong
// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/4/7.
// +----------------------------------------------------------------------

/**
 * 模型基类
 */

namespace common\components;


use common\entity\models\PrototypeModelModel;
use common\entity\models\SystemConfigModel;
use common\entity\models\UserRelationModel;
use common\helpers\ArrayHelper;
use common\helpers\StringHelper;
use Exception;
use Yii;

class BaseArModel  extends \yii\db\ActiveRecord
{
    /**
     * @var int node模型类型
     */
    protected $nodeType = null;

    /**
     * 筛选时对字段进行排除
     * @param $model
     * @param array $filedNames
     * @return array
     */
    static public function querySelectExclude($model,$filedNames = []){
        $newData = [];
        foreach ($model->getAttributes() as $name=>$item){
            if(!in_array($name,$filedNames)) $newData[] = $name;
        }
        return $newData;
    }


    /**
     * 根据对象返回一个类名（不包含命名空间）
     * @param $object
     * @return mixed
     */
    public function getClassName($object){
        $tem = explode('\\',get_class($object));
        return $tem[count($tem)-1];
    }

    /**
     * 表单错误处理
     * @param $error
     * @return string
     */
    public function getErrorString($error = null){
        if(!$error) $error = $this->getErrors();
        $message = '';
        if(is_string($error)){
            $message = $error;
        }else{
            foreach ($error as $item){
                if(is_array($item)){
                    foreach ($item as $v){
                        $message .= $v;
                    }
                }else{
                    $message .= $item;
                }
            }
        }
        return $message;
    }

    /**
     * node模型数据关联
     * @return \yii\db\ActiveQuery
     */
    public function getUserRelation(){
        return $this->hasOne(UserRelationModel::className(),['user_model_id'=>'model_id','user_data_id'=>'id']);
    }
}