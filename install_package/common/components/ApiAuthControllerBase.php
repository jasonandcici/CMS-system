<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2016/7/26
 * Time: 14:51
 */

namespace common\components;


use common\entity\models\UserModel;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\QueryParamAuth;

class ApiAuthControllerBase extends ApiControllerBase
{

	/**
	 * @var array 无需验证的action
	 */
	protected $exclude = [];

	/**
	 * 授权认证
	 * @return array
	 */
	public function behaviors()
	{
		$behaviors = parent::behaviors();

		if(!in_array($this->action->id,$this->exclude )){
			$behaviors['authenticator'] = [
				'class' => CompositeAuth::className(),
				'authMethods' => [
					[
						'class'=>QueryParamAuth::className(),
						'tokenParam'=> 'access-token',
					],
					[
						'class'=>HttpBasicAuth::className(),
						'auth'  =>  [ $this ,  'auth' ],
					],
					// HttpBearerAuth::className(),
				],
			];
		}

		return $behaviors;
	}

	/**
	 * BaseAuth 授权校验
	 *
	 * @param $username
	 * @param $password
	 *
	 * @return null|static
	 */
	public function auth ($username, $password)
	{
		$userInfo = UserModel::findByUsername($username);


		return  $userInfo->validatePassword($password);
	}

}