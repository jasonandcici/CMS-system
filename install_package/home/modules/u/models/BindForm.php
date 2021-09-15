<?php
/**
 * @copyright
 * @link 
 * @create Created on 2016/9/12
 */

namespace home\modules\u\models;

use common\components\BaseModel;
use common\entity\domains\UserDomain;
use common\helpers\SecurityHelper;
use Yii;


/**
 * 账号绑定
 * 场景 email、cellphone
 *
 * @author 
 * @since 1.0
 */
class BindForm extends BaseModel
{
    /**
     * @var string 执行的操作
     */
    public $action;

    public $cellphone_code;
    public $account;

    public $captcha;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['action','cellphone_code','account','captcha'],'trim'],
            [['action','account','captcha'], 'required'],
            ['action','in','range'=>['bind','unbind']],

            ['cellphone_code','default','value'=>'0086','on'=>['cellphone']],
            ['account','match','pattern'=>'/^1[0-9]{10}$/','message'=>'{attribute}格式错误。','when'=>function(){
                return $this->action == 'bind' && (empty($this->cellphone_code) || $this->cellphone_code == '0086');
            },'on'=>['cellphone']],

            [['account'], 'email','on'=>['email']],

            ['captcha', 'validateVerificationCode'],
        ];
    }

    /**
     * 校验手机验证码
     * @param $attribute
     * @param $params
     */
    public function validateVerificationCode($attribute, $params){
        if (!$this->hasErrors()) {
            $token = SecurityHelper::validateAuthToken(md5(($this->getScenario() == 'cellphone'?$this->cellphone_code.'-':'').$this->account),$this->action == 'bind'?'register':'reset');
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
            'action'=>'操作',
            'cellphone_code'=>'国际区号',
            'account'=>$this->getScenario() == 'cellphone'?'手机号':'邮箱',
            'captcha'=>'验证码',
        ];
    }

    /**
     * 绑定手机或邮箱
     */
    public function save(){
        if(!$this->validate()) return false;

        $model = UserDomain::findOne(Yii::$app->getUser()->getId());
        if($this->action == 'bind'){
            if($this->getScenario() == 'cellphone'){
                $model->cellphone_code = $this->cellphone_code;
                $model->cellphone = $this->account;
            }else{
                $model->email = $this->account;
            }
        }else{
            if($this->getScenario() == 'cellphone'){
                $model->cellphone_code = '';
                $model->cellphone = '';
            }else{
                $model->email = '';
            }
        }

        if($model->save()){
            return $model;
        }else{
            $this->addErrors($model->getErrors());
            return false;
        }
    }
}