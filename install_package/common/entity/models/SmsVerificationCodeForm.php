<?php
/**
 * @copyright
 * @link 
 * @create Created on 2016/9/12
 */

namespace common\entity\models;

use common\components\BaseModel;
use common\entity\domains\UserAuthTokenDomain;
use common\helpers\ArrayHelper;
use common\helpers\SecurityHelper;
use common\jobs\SmsJob;
use common\libs\dysmsapi\Sms;
use Exception;
use Qcloud\Sms\SmsSingleSender;
use Yii;
use yii\db\StaleObjectException;


/**
 * model基类
 * 可选的场景cellphone、email
 * @author 
 * @since 1.0
 */
class SmsVerificationCodeForm extends BaseModel
{

    public $cellphone_code;
    public $account;

    /**
     * @var string 'register'注册验证,'reset' 重置验证,'login'登录
     */
    public $type;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account','type'], 'required'],
            ['type', 'in', 'range' => ['register', 'reset','login']],

            ['account',function($attribute, $params){
                if (!$this->hasErrors()) {
                    $user = UserModel::findByEmail($this->$attribute,[0,1]);
                    if($this->type =='register' && $user){
                        $this->addError($attribute,'此邮箱已被注册。');
                    }elseif (($this->type =='reset' || $this->type =='login') && !$user){
                        $this->addError($attribute,'此邮箱未注册。');
                    }
                }
            },'on'=>'email'],

            ['cellphone_code','default','value'=>'0086'],
            ['account',function($attribute, $params){
                if (!$this->hasErrors()) {
                    $user = UserModel::findByMobile($this->cellphone_code,$this->$attribute,[0,1]);
                    if($this->type =='register' && $user){
                        $this->addError($attribute,'此手机号已被注册。');
                    }elseif (($this->type =='reset' || $this->type =='login') && !$user){
                        $this->addError($attribute,'此手机号未注册。');
                    }
                }
            },'on'=>'cellphone']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'cellphone_code'=>'国际区号',
            'account' => $this->getScenario() == 'email'?"邮箱":"手机号码",
            'type' => '验证类型',
        ];
    }

	/**
	 * 生成验证码
	 * @return bool|int
	 * @throws \yii\base\Exception
	 * @throws Exception
	 * @throws \Throwable
	 */
    public function generateCode(){
        if(!$this->validate()) return false;

        $config = SystemConfigModel::findConfig();

        if($this->getScenario() == 'email' && !intval($config['email']['enable'])){
            $this->addError('account','发送邮件功能未启用。');
            return false;
        }elseif ($this->getScenario() == 'cellphone' && !intval($config['sms']['enable'])){
            $this->addError('account','发送短信功能未启用。');
            return false;
        }

        // 生成验证码
        $verificationCode = rand(100000,999999);

        // 生成token
        $token = SecurityHelper::generateAuthToken($this->type,[
            'account'=>($this->getScenario() == 'cellphone'?$this->cellphone_code.'-':'').$this->account,
            'verificationCode'=>$verificationCode,
        ]);

        if($token['status']){
        	// 加入消息队列，队列开启需要控制台支持：https://github.com/yiisoft/yii2-queue/blob/master/docs/guide-zh-CN/driver-file.md
	        if($this->getScenario() == 'email'){
	        	$newConfig = $config['email'];
	        	$newConfig['site_name'] = $config['site']['site_name'];
	        }else{
	        	$newConfig = $config['sms'];
	        }

	        Yii::$app->queue->push(new SmsJob([
	        	'sendType'=>$this->getScenario(),
	        	'config'=>$newConfig,
		        'code'=>$verificationCode,
		        'cellphone_code'=>$this->cellphone_code,
		        'account'=>$this->account
	        ]));

            return $verificationCode;
        }else{
            if(is_string($token['error'])){
                $this->addError('account',$token['error']);
            }else{
                $this->addError($token['error']);
            }
            return false;
        }
    }
}