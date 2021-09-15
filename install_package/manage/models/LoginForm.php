<?php
// +----------------------------------------------------------------------
// | nantong
// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/4/4.
// +----------------------------------------------------------------------

/**
 * 登陆表单
 */

namespace manage\models;


use common\entity\models\SystemLogModel;
use common\entity\models\SystemUserModel;
use manage\libs\Rbac;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class LoginForm extends Model
{
    public $username;
    public $password;

    public $captcha;
    public $rememberMe;

    /**
     * @var array 标签
     */
    public $attributeLabels = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username','password','captcha'], 'required'],
            ['password', 'string', 'min' => 6],
            ['captcha','captcha','captchaAction'=>'/site/captcha'],
            ['rememberMe', 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'username' => '用户名',
            'password' => '登录密码',
            'captcha'=>'验证码',
            'rememberMe'=>'一周内免登录'
        ],$this->attributeLabels);
    }

    /**
     * 用户登录
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function signIn(){
        if(!$this->validate()) return false;

        $userModel = UserIdentity::findByUsername($this->username);

        if(!$userModel){
            $this->addError('username','用户不存在。');
            return false;
        }

        if(!$userModel->validatePassword($this->password)){
            $this->addError('password','密码错误。');
            return false;
        }

        Yii::$app->getUser()->login($userModel,($this->rememberMe?3600*24*7:0));

        $this->setUserPermission($userModel->id);

        return $userModel;
    }

    /**
     * 设置用户权限
     * @param $userId
     */
    public function setUserPermission($userId){
        if($userId === 0){
            Yii::$app->getSession()->set('userIsSuperAdmin',true);
        }else{
            //$auth = Yii::$app->getAuthManager();
            //Yii::$app->getSession()->set('userPermissionList',ArrayHelper::toArray($auth->getPermissionsByUser($userId)));
        }
    }
}