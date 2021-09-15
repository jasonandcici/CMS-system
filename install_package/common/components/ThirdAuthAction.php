<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/10/9
 */

namespace common\components;

use yii\base\InvalidConfigException;
use yii\web\Response;
use Yii;


/**
 * 第三方授权action
 *
 * @property string $config 配置
 *
 * @author 
 * @since 1.0
 */
class ThirdAuthAction extends \yii\authclient\AuthAction{

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
	 * 若果是api则重置跳转页
	 */
	public function init() {
		parent::init();

		if(Yii::$app->getRequest()->get('api',false)){
			$this->config->third->thirdJumpLink = json_decode($this->config->third->thirdJumpLink);

			$this->successUrl = $this->config->third->thirdJumpLink->success;
			$this->cancelUrl = $this->config->third->thirdJumpLink->fail;
		}
	}

	/**
	 * 重载此方法
	 * @param \yii\authclient\ClientInterface $client
	 *
	 * @return mixed|Response
	 * @throws InvalidConfigException
	 */
	protected function authSuccess($client)
	{
		if (!is_callable($this->successCallback)) {
			throw new InvalidConfigException('"' . get_class($this) . '::$successCallback" should be a valid callback.');
		}

		$response = call_user_func($this->successCallback, $client);
		if($response){
			if ($response instanceof Response) {
				return $response;
			}else{
				// 如果是登录，则get返回access-token
				$successUrl = explode('?',$this->successUrl);
				if(array_key_exists(1,$successUrl)){
					$this->successUrl = $successUrl[0].'?slug='.$response['access-token'].'&'.$successUrl[1];
				}else{
					$successUrl = explode('#',$successUrl[0]);
					if(array_key_exists(1,$successUrl)){
						$successUrl[0] = $successUrl[0].'?slug='.$response['access-token'];
						$this->successUrl = implode('#',$successUrl);
					}else{
						$this->successUrl = $successUrl[0].'?slug='.$response['access-token'];
					}
				}
			}
		}

		return $this->redirectSuccess();
	}
}