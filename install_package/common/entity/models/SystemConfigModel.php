<?php

namespace common\entity\models;

use common\entity\domains\SystemConfigDomain;
use common\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "{{%system_config}}".
 *
 */
class SystemConfigModel extends SystemConfigDomain
{

    /**
     * 查询系统配置数据
     * @return array|mixed
     */
    static public function findConfig()
    {
        $config = Yii::$app->cache->get('config');
        if(!$config){
            $config = [];
            foreach (self::find()->asArray()->all() as $key => $value) {
                if($value['scope'] == 'sms'){
                    if($value['name']== 'cellphoneCode'){
                        $options = str_replace(array("\r\n", "\r", "\n"),'$_break_tag_$',$value['value']);
                        $newValue = [];
                        foreach (explode('$_break_tag_$',$options) as $item){
                            $tmp = explode('=',$item);
                            $newValue[$tmp[0]] = array_key_exists(1,$tmp)?$tmp[1]:'';
                        }
                        $value['value'] = array_flip($newValue);
                    }elseif ($value['name']== 'enable'){
                        $value['value'] = intval($value['value']);
                    }
                }elseif ($value['scope'] == 'member'){
                    if($value['name'] == 'actionList' || $value['name'] == 'registerMode'){
                        $value['value'] = empty($value['value'])?[]:explode(',',$value['value']);
                    }elseif ($value['name'] == 'relationContent' || $value['name'] == 'publishContent'){
                        $value['value'] = empty($value['value'])?[]:ArrayHelper::index(json_decode($value['value'],true),'slug');
                    }
                }elseif ($value['scope'] == 'third'){
                    if ($value['name'] == 'setting'){
                        $value['value'] = empty($value['value'])?[]:ArrayHelper::index(json_decode($value['value'],true),'client');
                    }
                }
                $config[$value['scope']][$value['name']] = $value['value'];
            }
            Yii::$app->cache->set('config',$config);
        }

        return $config;
    }

}
