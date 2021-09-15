<?php
/**
 * @copyright
 * @link 
 * @create Created on 2016/9/12
 */

namespace home\modules\u\models;

use common\components\BaseModel;
use common\helpers\SecurityHelper;
use Yii;

/**
 * 重置密码
 * @package home\modules\u\models
 */
class ResetPasswordForm extends BaseModel
{
    public $password_old;
    public $password;
    public $password_repeat;
    public $captcha;

    /**
     * @var string 用户
     */
    private $_user;
    public function beforeValidate()
    {
        $this->_user = Yii::$app->getUser()->getIdentity();

        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password_old','password','password_repeat'],'trim'],
            [['password','password_repeat'], 'required'],

	        [['password_old'], 'required','on'=>['password']],
	        [['captcha'], 'required','on'=>['cellphone','email']],

            ['captcha',function($attribute, $params){
                if (!$this->hasErrors()) {
                    if(empty($this->_user->cellphone)){
                        $this->addError($attribute,'您没有绑定手机号码。');
                    }
                }
            },'on'=>['cellphone']],

	        ['captcha',function($attribute, $params){
		        if (!$this->hasErrors()) {
			        if (empty($this->_user->email)){
				        $this->addError($attribute,'您没有绑定邮箱。');
			        }
		        }
	        },'on'=>['email']],

			['password_old',function($attribute, $params){
				if (!$this->hasErrors()) {
					if (!Yii::$app->getSecurity()->validatePassword($this->$attribute,$this->_user->password)){
						$this->addError($attribute,'旧密码输入错误。');
					}
				}
			},'on'=>['password']],

            ['password', 'string', 'min' => 6],
	        ['password_repeat', 'compare','compareAttribute'=>'password'],
            [['password'], 'filter','filter'=>function($value){
                return Yii::$app->getSecurity()->generatePasswordHash($value);
            }],

            ['captcha', 'validateVerificationCode','on'=>['cellphone','email']],
        ];
    }

    /**
     * 校验手机验证码
     * @param $attribute
     * @param $params
     */
    public function validateVerificationCode($attribute, $params){
        if (!$this->hasErrors()) {
            $token = SecurityHelper::validateAuthToken(md5($this->getScenario() == 'cellphone'?$this->_user->cellphone_code.'-'.$this->_user->cellphone:$this->_user->email),'reset');
            if($token === null || intval($token->value) != $this->captcha){
                $this->addError($attribute, '验证码填写错误。');
            }else{
                $token->delete();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'password' => '新密码',
            'password_repeat'=>'确认密码',
            'captcha' => '验证码',
            'password_old' => '旧密码',
        ];
    }

    /**
     * 保存用户数据
     */
    public function reset(){
        if(!$this->validate()) return false;

        $this->_user->password = $this->password;
        if($this->_user->save()){
            return $this->_user;
        }else{
            $this->addErrors($this->_user->getErrors());
            return false;
        }
    }
}