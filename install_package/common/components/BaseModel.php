<?php
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/6/14.
// +----------------------------------------------------------------------

/**
 * 基础模型
 */

namespace common\components;

use yii\base\Model;

class BaseModel extends Model
{
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
     * 获取非空属性
     * @return array
     */
    public function getNonnullAttributes(){
        $attributes = [];
        foreach($this->getAttributes() as $name=>$value){
            if($value !== null) $attributes[$name] = $value;
        }
        return $attributes;
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
}