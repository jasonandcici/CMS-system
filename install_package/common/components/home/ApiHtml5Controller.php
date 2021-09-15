<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/5/19
 */

namespace common\components\home;

use common\entity\models\UserAuthTokenModel;
use common\entity\models\UserModel;
use common\entity\models\RedisAuthTokenModel;
use common\helpers\SystemHelper;
use Yii;


/**
 * ApiHtml5Controller
 *
 * @author 
 * @since 1.0
 */
class ApiHtml5Controller extends NodeController
{

	/**
	 * @throws \yii\web\NotFoundHttpException
	 */
    public function init()
    {
        parent::init();

        // 重置视图路径
        $this->module->setViewPath('@api/themes/'.$this->siteInfo->theme);

        // 登录
	    if($slug = Yii::$app->getRequest()->get('slug')){
		    $tokenModel = SystemHelper::isEnableRedis()?new RedisAuthTokenModel():new UserAuthTokenModel();
		    $token = $tokenModel::find()->where(['token'=>base64_decode($slug),'type'=>'loginApi'])->asArray()->one();
		    if($token){
		    	$user = UserModel::findOne($token['value']);
			    if($user){
			    	Yii::$app->getUser()->login($user);
			    }
		    }
	    }
    }

	/**
	 * @param string $view
	 * @param array $params
	 *
	 * @return string
	 */
    public function render( $view, $params = [] ) {
	    if($this->route === 'node/detail'){
		    if(empty($this->categoryInfo->layouts_content)){
			    return $this->renderPartial($view, $params);
		    }
		    $this->layout = strpos($this->categoryInfo->layouts_content,'/')===0?str_replace('/','',$this->categoryInfo->layouts_content):$this->categoryInfo->layouts_content;
	    }else{
		    if(empty($this->categoryInfo->layouts)){
			    return $this->renderPartial($view, $params);
		    }
		    $this->layout = strpos($this->categoryInfo->layouts,'/')===0?str_replace('/','',$this->categoryInfo->layouts):$this->categoryInfo->layouts;
	    }

	    $content = $this->getView()->render($view, $params, $this);
	    return $this->renderContent($content);
    }

}