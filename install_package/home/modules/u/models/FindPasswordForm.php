<?php
/**
 * @copyright
 * @link 
 * @create Created on 2016/9/12
 */

namespace home\modules\u\models;

use common\components\BaseModel;
use common\entity\models\UserModel;
use common\helpers\SecurityHelper;
use Yii;

/**
 * 找回密码表单
 * 通过切换场景切换找回密码方式 email、cellphone
 *
 * @author 
 * @since 1.0
 */
class FindPasswordForm extends BaseModel
{
    public $cellphone_code;
    public $account;
    public $password;
    public $password_repeat;
    public $captcha;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account','password','password_repeat'],'trim'],
            [['account','password','password_repeat','captcha'], 'required'],
            ['password_repeat', 'compare','compareAttribute'=>'password'],

            ['cellphone_code','default','value'=>'0086','on'=>'cellphone'],
            ['account','match','pattern'=>'/^1[0-9]{10}$/','message'=>'{attribute}格式错误。','when'=>function(){
                return empty($this->cellphone_code) || $this->cellphone_code == '0086';
            },'on'=>'cellphone'],
            ['account',function($attribute, $params){
                if(!$this->hasErrors()){
                    if(UserModel::find()->where(['cellphone'=>$this->$attribute,'cellphone_code'=>$this->cellphone_code])->count()<1){
                        $this->addError($attribute,'此号码不存在。');
                    }
                }
            },'on'=>'cellphone'],

            ['account','email','on'=>'email'],
            ['account', 'exist','targetAttribute' => 'email','targetClass' => '\common\entity\domains\UserDomain', 'message' => '此邮箱不存在。','on'=>'email'],

            ['password', 'string', 'min' => 6],
            [['password'], 'filter','filter'=>function($value){
                return Yii::$app->getSecurity()->generatePasswordHash($value);
            }],

            ['captcha', 'validateVerificationCode','on'=>['email','cellphone']],
        ];
    }

    /**
     * 校验手机验证码
     * @param $attribute
     * @param $params
     */
    public function validateVerificationCode($attribute, $params){
        if (!$this->hasErrors()) {
            $token = SecurityHelper::validateAuthToken(md5(($this->getScenario() == 'cellphone'?$this->cellphone_code.'-':'').$this->account),'reset');
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
            'cellphone_code'=>'国际区号',
            'account' => $this->getScenario() == 'cellphone'?'手机号':'邮箱',
            'password' => '登录密码',
            'password_repeat'=>'确认密码',
            'captcha' => '验证码',
        ];
    }

    /**
     * 重置密码
     */
    public function reset(){
        if(!$this->validate()) return false;

        if($this->getScenario() == 'cellphone'){
            $userModel = UserModel::findByMobile($this->cellphone_code,$this->account);
        }else{
            $userModel = UserModel::findByEmail($this->account);
        }

        if($userModel){
            $userModel->password = $this->password;
            if($userModel->save()){
                return $userModel;
            }else{
                $this->addErrors($userModel->getErrors());
            }
        }else{
            $this->addError('account','用户不存在。');
        }
        return false;
    }

}