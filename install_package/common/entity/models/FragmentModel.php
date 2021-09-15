<?php

namespace common\entity\models;

use common\entity\domains\FragmentDomain;
use Yii;

/**
 * This is the model class for table "{{%fragment}}".
 *
 */
class FragmentModel extends FragmentDomain
{
    /**
     * 查询系统配置数据
     * @param $siteId
     * @param bool $isFilter
     * @return array|mixed
     */
    static public function findFragment($siteId,$isFilter = true)
    {
        $fragment = Yii::$app->cache->get('fragment');
        if(!$fragment){
            $fragment = self::find()->asArray()->all();
            Yii::$app->cache->set('fragment',$fragment);
        }

        if(!$isFilter) return $fragment;

        $newData = [];
        foreach ($fragment as $item) {
            if($item['site_id'] != $siteId) continue;
            $newData[$item['name']] = $item['value'];
        }

        return $newData;

    }
}
