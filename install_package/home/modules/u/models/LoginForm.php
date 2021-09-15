<?php
/**
 * @copyright
 * @link 
 * @create Created on 2016/9/12
 */

namespace home\modules\u\models;


use common\components\BaseModel;
use common\entity\models\SystemConfigModel;
use common\entity\models\UserModel;
use common\helpers\ArrayHelper;
use common\helpers\SecurityHelper;
use Yii;

/**
 * 登陆表单
 * 通过场景来切换登录方式“password、passwordApi、cellphone、email”
 *
 * @author
 * @since 1.0
 */
class LoginForm extends BaseModel
{
    public $cellphone_code;
    public $account;
    public $password;

    public $captcha;
    public $rememberMe;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account'], 'required'],
            [['account','password'],'trim'],

            ['cellphone_code','default','value'=>'0086','on'=>['password','cellphone','passwordApi']],
            ['account','match','pattern'=>'/^1[0-9]{10}$/','message'=>'{attribute}格式错误。','when'=>function(){
                return empty($this->cellphone_code) || $this->cellphone_code == '0086';
            },'on'=>['cellphone']],

            [['account'], 'email','on'=>['email']],

            [['captcha'], 'required','on'=>['password','cellphone','email']],
            [['password'], 'required','on'=>['password','passwordApi']],

            ['captcha','captcha','captchaAction'=>'/site/captcha','on'=>['password']],
            ['rememberMe', 'boolean'],
        ];
    }

    /**
     * @var array 自定义标签名
     */
    public $attributeLabels = [];

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        switch ($this->getScenario()) {
            case 'cellphone':
                $accountName = '手机号';
                break;
            case 'email':
                $accountName = '邮箱';
                break;
            default:
                $accountName = '账户名';
                break;
        }

        return ArrayHelper::merge([
        	'cellphone_code'=>'区号',
            'account' => $accountName,
            'password' => '登录密码',
            'captcha'=>'验证码',
            'rememberMe'=>'自动登录'
        ],$this->attributeLabels);
    }

    /**
     * 用户登录
     * @param bool $isApi 是否api登陆
     * @return array|bool|null|\yii\db\ActiveRecord
     * @throws \yii\base\Exception
     */
    public function signIn($isApi = false){
        if(!$this->validate()) return false;

        switch ($this->getScenario()){
            case 'cellphone':
                $userModel = UserModel::findByMobile($this->cellphone_code,$this->account);
                break;
            case 'email':
                $userModel = UserModel::findByEmail($this->account);
                break;
            default:
	            $userModel = UserModel::findByUsername($this->account);
            	if(!$userModel){
	                if(preg_match('/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/',$this->account)){
	                    $userModel = UserModel::findByEmail($this->account);
	                }elseif (preg_match('/^1[0-9]{10}$/',$this->account)){
	                    $userModel = UserModel::findByMobile($this->cellphone_code,$this->account);
	                }
	            }
                break;
        }

        if(!$userModel){
            $config = SystemConfigModel::findConfig();
            // 检测是否开启短信快速登录
            if($this->getScenario() == 'cellphone' && in_array('fast',$config['member']['registerMode'])){
                $registerForm = new RegisterForm();
                $registerForm->setVerificationCodeType('login');
                $registerForm->setScenario('cellphone');
                $registerForm->cellphone_code = $this->cellphone_code;
                $registerForm->account = $this->account;
                $registerForm->password = $this->password;
                $registerForm->password_repeat = $this->password;
                $registerForm->captcha = $this->captcha;
                if($user = $registerForm->save()){
                    return $this->login($user,$isApi);
                }else{
                    $this->addErrors($registerForm->getErrors());
                    return false;
                }
            }else{
                $this->addError('account','用户不存在。');
                return false;
            }

        }else{
            if(in_array($this->getScenario(),['cellphone','email'])){
                if(!$this->validateToken('login')){
                    return false;
                }
            }else{
                if(!$userModel->validatePassword($this->password)){
                    $this->addError('password','密码错误。');
                    return false;
                }
            }

            return $this->login($userModel,$isApi);
        }
    }

    /**
     * 验证token
     * @param $type
     * @return bool
     */
    protected function validateToken($type){
        $token = SecurityHelper::validateAuthToken(md5(($this->getScenario() == 'cellphone'?$this->cellphone_code.'-':'').$this->account),$type);
        if($token === null || intval($token->value) != $this->captcha){
            $this->addError('captcha', '验证码填写错误。');
            return false;
        }
        $token->delete();
        return true;
    }

    /**
     * 用户登陆
     * @param $userModel
     * @param $isApi
     * @return array|bool
     * @throws \yii\base\Exception
     */
    public function login($userModel,$isApi = false){
        if($isApi) {
            // 生成授权token
            $token = SecurityHelper::generateAuthToken('loginApi', [
                'user_id' => $userModel->id
            ]);

            if ($token['status']) {
	            $userProfile = ArrayHelper::toArray($userModel->userProfile);
	            $userModel = ArrayHelper::toArray($userModel);
	            unset($userModel['account_type'],$userModel['password'],$userModel['create_time'],$userModel['auth_key'],$userModel['is_enable']);

	            $userModel['userProfile'] = $userProfile;
	            $userModel['access-token'] = $token['token'];

                return $userModel;
            }
            $this->addError($token['error']);
            return false;
        }else{
            Yii::$app->getUser()->login($userModel,$this->rememberMe?3600*24*30:0);
            return $userModel;
        }
    }
}