<?php
/**
 * @copyright
 * @link 
 * @create Created on 2016/12/2
 */

namespace common\components\home;

use common\helpers\ArrayHelper;
use EasyWeChat\Factory;
use home\modules\u\models\ThirdAuthForm;
use Yii;
use yii\base\Action;

/**
 * 微信授权
 *
 * @property string $config 配置
 *
 * @author 
 * @since 1.0
 */
class WxAuthAction extends Action
{

	/**
	 * @var object 系统配置
	 */
	private $_config;

	public function setConfig($config)
	{
		$this->_config = $config;
	}

	public function getConfig()
	{
		return $this->_config;
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 * @throws \yii\base\Exception
	 */
    public function run()
    {

	    $app = Factory::officialAccount(Yii::$app->params['WECHAT']);
	    if(Yii::$app->getRequest()->get('code') && Yii::$app->getRequest()->get('state')){
		    $userInfo = $app->oauth->user();
		    //todo::微信和小程序授权待做
			dump($userInfo);exit;
		    $client = ArrayHelper::convertToObject([
		    	'id'=>'wechat',
		    	'userAttributes'=>[
				    'openid'=>$attributes['openid'],
				    'unionid'=>$attributes['unionid'],
				    'nickname'=>$attributes['nickname'],
				    'headimgurl'=>$attributes['headimgurl'],
				    'sex'=>$attributes['sex'] == 2?'female':'male',
				    'country'=>$attributes['country'] == 'CN'?'中国':'其他',
				    'province'=>$attributes['province'],
				    'city'=>$attributes['city'],
				    'area'=>$attributes['area'],
			    ]
		    ]);

		    $model = new ThirdAuthForm();
		    $res = $model->auth($client,true);
	    }else{
		    return $app->oauth->redirect();
	    }
    }
}