<?php

namespace common\entity\models;

use common\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "{{%files_category}}".
 */
class FilesCategoryModel extends \common\entity\domains\FilesCategoryDomain
{
    /**
     * 获取文件分类
     * @param null $type
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    public static function getFileCategory($type = null){
        $category = Yii::$app->cache->get('fileCategory');
        if(!$category){
            $category = self::find()->indexBy('id')->orderBy(['sort'=>SORT_ASC])->asArray()->all();
            Yii::$app->cache->set('fileCategory',$category);
        }

        if($type){
            $newData = [];
            foreach ($category as $i=>$item){
                if($item['type'] == $type) $newData[$i] = $item;
            }
            return $newData;
        }

        return $category;
    }
}
