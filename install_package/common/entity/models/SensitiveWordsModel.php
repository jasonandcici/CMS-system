<?php

namespace common\entity\models;

use common\entity\domains\SensitiveWordsDomain;
use common\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "{{%sensitive_words}}".
 */
class SensitiveWordsModel extends SensitiveWordsDomain
{

	/**
	 * 查找所有敏感词
	 * @return array|mixed
	 */
	static public function findSensitiveWords(){
		$sensitiveWords = Yii::$app->getCache()->get('sensitivewords');
		if(!$sensitiveWords){
			$sensitiveWords = ArrayHelper::getColumn(SensitiveWordsModel::find()->asArray()->all(),'name');
			Yii::$app->getCache()->set('sensitivewords',$sensitiveWords);
		}
		return $sensitiveWords;
	}

}
