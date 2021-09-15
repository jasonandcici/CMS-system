<?php
/**
 * @copyright
 * @link
 * @create Created on 2016/9/12
 */

namespace home\modules\u\models;

use common\components\BaseModel;
use common\entity\domains\UserDomain;
use common\entity\models\UserModel;
use common\entity\models\UserProfileModel;
use common\helpers\ArrayHelper;
use common\helpers\SecurityHelper;
use common\helpers\SequenceNumberHelper;
use Faker\Provider\Uuid;
use Yii;
use yii\db\Exception;

/**
 * 注册表单
 * 通过场景来切换注册方式“cellphone,username,email”
 *
 * @author
 * @since 1.0
 */
class RegisterForm extends BaseModel
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
	        [['account','captcha'],'required'],
            [['password','password_repeat'], 'required','on'=>['email','cellphone','username']],

            ['password_repeat', 'compare','compareAttribute'=>'password','on'=>['email','cellphone','username']],

            ['cellphone_code','default','value'=>'0086','on'=>['cellphone','fast']],
            ['account','match','pattern'=>'/^1[0-9]{10}$/','message'=>'{attribute}格式错误。','when'=>function(){
                return empty($this->cellphone_code) || $this->cellphone_code == '0086';
            },'on'=>['cellphone','fast']],
            ['account',function($attribute, $params){
                if(!$this->hasErrors()){
                    if(UserModel::find()->where(['cellphone'=>$this->$attribute,'cellphone_code'=>$this->cellphone_code])->count()>0){
                        $this->addError($attribute,'此号码已经被注册。');
                    }
                }
            },'on'=>['cellphone','fast']],

            ['account','email','on'=>'email'],
            ['account', 'unique','targetAttribute' => 'email','targetClass' => '\common\entity\domains\UserDomain', 'message' => '此邮箱已被注册。','on'=>'email'],

            ['account','match','pattern'=>'/^(?![u_|\d]).+$/','message'=>'{attribute}格式错误。','on'=>'username'],
            ['account', 'string', 'length' => [3, 15],'on'=>'username'],
            ['account', 'unique','targetAttribute' => 'username','targetClass' => '\common\entity\domains\UserDomain', 'message' => '此用户名已被注册。','on'=>'username'],
            ['captcha','captcha','captchaAction'=>'/site/captcha','on'=>'username'],

            ['password', 'string', 'min' => 6,'on'=>['email','cellphone','username']],
            [['password'], 'filter','filter'=>function($value){
                return Yii::$app->getSecurity()->generatePasswordHash($value);
            },'on'=>['email','cellphone','username']],

            ['captcha', 'validateVerificationCode','on'=>['email','cellphone','fast']],
        ];
    }

    private $_verificationCodeType = 'register';

    /**
     * 校验手机或邮箱验证码
     * @param $attribute
     * @param $params
     */
    public function validateVerificationCode($attribute, $params){
        if (!$this->hasErrors()) {
            $token = SecurityHelper::validateAuthToken(md5(($this->getScenario() == 'cellphone'||$this->getScenario() == 'fast'?$this->cellphone_code.'-':'').$this->account),$this->_verificationCodeType);
            if($token === null || intval($token->value) != $this->captcha){
                $this->addError($attribute, '验证码填写错误。');
            }else{
                $token->delete();
            }
        }
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
        if($this->getScenario() == 'cellphone' || $this->getScenario() == 'fast'){
            $accountName = '手机号';
        }elseif ($this->getScenario() == 'email'){
            $accountName = '邮箱';
        }else{
            $accountName = '用户名';
        }

        return ArrayHelper::merge([
            'cellphone_code'=>'国际区号',
            'account' => $accountName,
            'password' => '登录密码',
            'password_repeat'=>'确认密码',
            'captcha'=>'验证码',
        ],$this->attributeLabels);
    }

    /**
     * 保存用户
     * @throws \yii\base\Exception
     */
    public function save(){
        if(!$this->validate()) return false;
        $result = null;

        $model = new UserModel();
        $transaction= Yii::$app->db->beginTransaction();
        try {
            switch ($this->getScenario()){
                case 'cellphone':
                case 'fast':
                    $model->username = Uuid::uuid();
                    $model->cellphone_code = $this->cellphone_code;
                    $model->cellphone = $this->account;
                    break;
                case 'email':
                    $model->username = Uuid::uuid();
                    $model->email = $this->account;
                    break;
                default:
                    $model->username = $this->account;
                    break;
            }

	        $model->auth_key = Yii::$app->getSecurity()->generateRandomString();
            $model->password = $this->getScenario() == 'fast'?Yii::$app->getSecurity()->generatePasswordHash($model->auth_key):$this->password;
            $model->create_time = time();

            if($model->save()){
                $db = Yii::$app->getDb();
                $sql = '';
                if($this->getScenario() != 'username'){
                    $model->username = 'u_'.SequenceNumberHelper::get($model->primaryKey,8);
                    $sql .= $db->createCommand()->update(UserDomain::tableName(),['username'=>$model->username],['id'=>$model->primaryKey])->rawSql.';';
                }
                $sql .= $db->createCommand()->insert(UserProfileModel::tableName(),['user_id'=>$model->primaryKey,'nickname'=>$model->username])->rawSql.';';
                $db->createCommand($sql)->execute();

                $result = $model;
            }else{
                $this->addErrors($model->getErrors());
                $transaction->rollBack();
            }
            $transaction->commit();
        } catch(Exception $e){
            $transaction->rollBack();
        }

        return $result;
    }


    /**
     * @param $value
     */
    public function setVerificationCodeType($value)
    {
        $this->_verificationCodeType = trim($value);
    }

    /**
     * @param $value
     */
    public function getVerificationCodeType($value)
    {
        $this->_verificationCodeType = trim($value);
    }
}