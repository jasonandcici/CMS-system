<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/11/7
 */

namespace home\modules\u\models;
use common\components\BaseModel;
use common\entity\models\UserThirdAccountModel;
use Yii;


/**
 * 第三方登录
 *
 * @author 
 * @since 1.0
 */
class ThirdLoginForm extends BaseModel{

	public $timestamp;
	/**
	 * token值等于md5(密钥+open_id+时间戳)
	 * @var string
	 */
	public $token;
	public $open_id;
	public $client_id;

	public $nickname;
	public $avatar;
	public $gender;
	public $birthday;
	public $blood;
	public $country;
	public $province;
	public $city;
	public $area;
	public $street;
	public $signature;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['timestamp','token','open_id'], 'trim'],
			[['timestamp','token','open_id','client_id'], 'required'],
			[['timestamp'], 'integer'],
			[['client_id'], 'in','range'=>Yii::$app->params['third.thirdAllowList']],
			['token'=>function($attribute, $params){
				if (!$this->hasErrors()) {
					if($this->$attribute != md5(Yii::$app->params['third.loginKey'].$this->open_id.$this->timestamp)){
						return $this->addError($attribute,'Token验证失败。');
					}
				}
			}],
			[['avatar'], 'string','on'=>'api'],
			[['nickname','avatar','gender','birthday','blood','country','province','city','area','street','signature'],'safe'],
		];
	}

	/**
	 * 登录
	 * @return bool
	 * @throws \yii\base\Exception
	 * @throws \yii\db\Exception
	 */
	public function login(){
		if(in_array($this->province,['北京','天津','上海','重庆'])){
			$this->area = $this->city;
			$this->city = $this->province;
		}else{
			$this->area = null;
		}

		$isApi = $this->getScenario() == 'api';
		$model = new ThirdAuthForm();
		$data = [
			'user_id'=>null,
			'client_id'=>$this->client_id,
			'open_id'=>$this->open_id,
			'token'=>null,
			'raw_data'=>null,
			'nickname'=>$this->nickname,
			'avatar'=>$this->avatar,
			'gender'=>$this->gender,
			'birthday'=>$this->birthday,
			'blood'=>$this->blood,
			'country'=>$this->country,
			'province'=>$this->province,
			'city'=>$this->city,
			'area'=>$this->area,
			'street'=>$this->street,
			'signature'=>$this->signature,
		];
		$res = $model->thirdLogin($data,$isApi);
		if($res){
			return $res;
		}else{
			$this->addErrors($model->getErrors());
			return false;
		}
	}
}