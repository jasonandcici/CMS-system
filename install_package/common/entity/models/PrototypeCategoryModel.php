<?php

namespace common\entity\models;

use common\entity\domains\PrototypeCategoryDomain;
use common\helpers\UrlHelper;
use Yii;
use yii\web\Link;
use yii\web\Linkable;

/**
 * This is the model class for table "{{%prototype_category}}".
 */
class PrototypeCategoryModel extends PrototypeCategoryDomain implements Linkable
{
    /**
     * 返回关联数据名
     * @return array
     */
    public function extraFields(){
        return ['model'];
    }

    /**
     * 关联栏目所属的模型
     * @return \yii\db\ActiveQuery
     */
    public function getModel(){
        return $this->hasOne(PrototypeModelModel::className(), ['id' => 'model_id']);
    }

    /**
     * 关联栏目单网页
     * @return \yii\db\ActiveQuery
     */
    public function getPage(){
        return $this->hasOne(PrototypePageModel::className(), ['category_id' => 'id']);
    }

    /**
     * @return array 生成链接(用于restful接口)
     */
    public function getLinks(){

        switch($this->type){
            case 0:
                $url = UrlHelper::toRoute(['html5/node/list','category_id'=>$this->id],true);
                break;
            case 1:
                $url = UrlHelper::toRoute(['html5/node/page','category_id'=>$this->id],true);
                break;
            case 2:
                $url = '#';
                break;
            case 3:
                $url = $this->link;
                break;
            default:
                $url = '#';
                break;
        }

        return [
            Link::REL_SELF => $url,
        ];
    }

    /**
     * 查询栏目列表
     * @param null $siteId
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    static public function findCategory($siteId = null){
        $category = Yii::$app->cache->get('category');
        if(!$category){
            $category = self::find()->indexBy('id')->with(['model'=>function($query){
                $query->select(PrototypeModelModel::querySelectExclude(new PrototypeModelModel(),['extend_code']));
            }])->orderBy(['sort'=>SORT_ASC,'id'=>SORT_ASC])->asArray()->all();
            Yii::$app->cache->set('category',$category);
        }

        if($siteId){
            $newData = [];
            foreach ($category as $i=>$item){
                if($item['site_id'] == $siteId) $newData[$i] = $item;
            }
            return $newData;
        }

        return $category;
    }
}
