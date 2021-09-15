<?php

namespace common\entity\models;

use common\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "{{%site}}".
 */
class SiteModel extends \common\entity\domains\SiteDomain
{
	/**
	 * 查询系统配置数据
	 * @param null $id
	 * @return array|mixed
	 */
	static public function findSite($id = null)
	{
		$site = Yii::$app->cache->get('site');
		if(!$site){
			$site = self::find()->indexBy('id')->asArray()->all();
			Yii::$app->cache->set('site',$site);
		}
		return $id !== null?ArrayHelper::getValue($site,$id):$site;
	}
}
