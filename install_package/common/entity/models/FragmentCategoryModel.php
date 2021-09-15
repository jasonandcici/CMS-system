<?php

namespace common\entity\models;

use common\entity\domains\FragmentCategoryDomain;
use common\helpers\ArrayHelper;
use common\helpers\UrlHelper;
use Yii;

/**
 * This is the model class for table "{{%fragment_category}}".
 */
class FragmentCategoryModel extends FragmentCategoryDomain
{

    /**
     * 查找碎片
     * @param $siteId
     * @return array
     */
    static public function findFragment($siteId){
        $fragment = Yii::$app->cache->get('fragment');
        if(!$fragment){
	        $siteList = SiteModel::findSite();
	        $categoryList = PrototypeCategoryModel::findCategory();
            $fragment = self::find()->where(['is_global'=>1])->orderBy(['sort'=>SORT_DESC])
                ->with(['fragments'=>function($query){
                    $query->orderBy(['sort'=>SORT_ASC]);
                },'fragmentLists'=>function($query){
                    $query->orderBy(['sort'=>SORT_DESC]);
                }])->asArray()->all();

            foreach ($fragment as $k=>$v){
                foreach ($v['fragmentLists'] as $i=>$item){
	                if($item['related_data_model'] > 0){

		                if($item['related_data_id'] > 0){
			                $fragment[$k]['fragmentLists'][$i]['link'] = UrlHelper::detailPage(['id'=>$item['related_data_id'],'site_id'=>$siteId,'category_id'=>$item['related_data_model']],$siteList,$categoryList,['static'=>true]);
		                }else{
			                $fragment[$k]['fragmentLists'][$i]['link'] = UrlHelper::categoryPage($categoryList[$item['related_data_model']],$siteList,['currentSite'=>$siteList[$siteId],'categoryList'=>$categoryList,'static'=>true]);
		                }
	                }
                }
            }

            $fragment['categoryList'] = self::find()->select(['id','site_id','slug','type'])->asArray()->all();

            Yii::$app->cache->set('fragment',$fragment);
        }

        $newData = [];
        foreach ($fragment as $i=>$item) {
            if($i === 'categoryList' || $item['site_id'] != $siteId) continue;

            if($item['type']){
                $f = [];
                foreach ($item['fragments'] as $v){
                    $f[$v['name']] = $v['value'];
                }
                $newData[$item['slug']] = $f;
            }else{
                $newData[$item['slug']] = $item['fragmentLists'];
            }
        }
        $categoryList = [];
        $categoryType = [];
        foreach ($fragment['categoryList'] as $i=>$item){
            if($item['site_id'] != $siteId) continue;
            $categoryList[$item['slug']] = $item['id'];
	        $categoryType[$item['slug']] = $item['type'];
        }
        $newData['fragmentCategoryMap'] = $categoryList;
        $newData['fragmentCategoryType'] = $categoryType;

        return $newData;
    }

}
