<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/5/19
 */

namespace api\controllers;

use common\components\ApiControllerBase;
use common\entity\models\PrototypeCategoryModel;
use common\entity\models\PrototypeModelModel;
use common\entity\models\SystemConfigModel;
use common\entity\models\TagModel;
use common\helpers\ArrayHelper;
use EasyWeChat\Factory;
use Yii;


/**
 * 站点列表
 *
 * @author 
 * @since 1.0
 */
class SiteController extends ApiControllerBase
{
    public $modelClass = 'common\\entity\\searches\\SiteSearch';

	/**
	 * 获取网站栏目信息
	 * @param null $sid
	 *
	 * @return array|mixed|\yii\db\ActiveRecord[]
	 */
    public function actionCategories($sid = null){
    	return PrototypeCategoryModel::findCategory($sid);
    }

	/**
	 * 重置modelClass
	 * @param $action
	 *
	 * @return string
	 */
    public function setModelClassOnBeforeRun( $action ) {
    	if($action->id == 'tag-search'){
		    $modelInfo = PrototypeModelModel::findModel(Yii::$app->getRequest()->get('mid'));
		    $modelName = ucwords($modelInfo->name);
		    return 'common\\entity\\nodes\\'.$modelName.'Search';
	    }
    }

	/**
	 * 根据tag筛选内容
	 *
	 * @param $mid
	 * @param $tag
	 *
	 * @return \yii\data\ActiveDataProvider
	 */
	public function actionTagSearch($mid,$tag){
		$modelInfo = PrototypeModelModel::findModel($mid);
		$modelName = ucwords($modelInfo->name);
		$this->modelClass = 'common\\entity\\nodes\\'.$modelName.'Search';
		$dataProvider = $this->prepareDataProvider();


		// 匹配标签
		$tagsId = ArrayHelper::getColumn(TagModel::find()->where(['title'=>$tag])->asArray()->all(),'id');

		if(!empty($tagsId)){
			$dataProvider->query->where(['status'=>1])->joinWith('tagRelation')->andWhere(['tag_id'=>$tagsId]);
		}else{
			$dataProvider->query->andWhere(['site_id'=>0,'status'=>1]);
		}

		return $dataProvider;
	}

	/**
	 * 搜索
	 *
	 * @param null $sid
	 * @param $mid
	 *
	 * @return \yii\data\ActiveDataProvider
	 */
	public function actionSearch($mid){
		$modelInfo = PrototypeModelModel::findModel($mid);
		$modelName = ucwords($modelInfo->name);
		$this->modelClass = 'common\\entity\\nodes\\'.$modelName.'Search';
		$dataProvider = $this->prepareDataProvider();

		$dataProvider->query->andWhere(['status'=>1]);

		return $dataProvider;
	}

	/**
	 * 获取网站配置
	 *
	 * @param $slug
	 * @param $url string 页面url,slug值为“wxConfig”时有效
	 *
	 * @return mixed
	 * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function actionConfig($slug = null,$url = null){
		if($slug == "wxConfig"){
			$app = Factory::officialAccount(Yii::$app->params['WECHAT']);
			$app->jssdk->setUrl($url);
			return $app->jssdk->buildConfig(Yii::$app->params['WECHAT_SHARE_API'], false);
		}else{
			$config = SystemConfigModel::findConfig();
			return ArrayHelper::getValue($config,$slug,$config);
		}
	}
}