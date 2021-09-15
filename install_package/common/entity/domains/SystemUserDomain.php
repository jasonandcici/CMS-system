<?php

namespace common\entity\domains;

use manage\models\UserIdentity;
use Yii;

/**
 * This is the model class for table "{{%system_user}}".
 *
 * @property string $id
 * @property string $username
 * @property string $password
 * @property string $mobile
 * @property string $email
 * @property integer $status
 * @property string $auth_key
 * @property string $create_time
 */
class SystemUserDomain extends \common\components\BaseArModel
{

    public $password_repeat;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%system_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username','password'],'trim'],
            [['username'], 'required','on'=>['create']],
            ['username', 'compare', 'compareValue' => base64_decode(UserIdentity::$superAdminName), 'operator' => '!='],
            [['password'], 'required','on'=>['create','reset']],
            [['status', 'create_time'], 'integer'],
            [['username'], 'string', 'max' => 80],
            [['password', 'auth_key'], 'string', 'max' => 70],
            [['mobile'], 'string', 'max' => 11],
            [['email'], 'string', 'max' => 100],
            [['username'], 'unique'],
            ['password_repeat','compare','compareAttribute'=>'password', 'on' => ['create','reset']],
            [['password'], 'filter','filter'=>function($value){
                return Yii::$app->getSecurity()->generatePasswordHash($value);
            },'on'=>['create','reset']],
            [['create_time'],'default','value'=>time()]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '用户id',
            'username' => '用户名',
            'password' => '密码',
            'mobile' => '手机号码',
            'email' => 'Email',
            'status' => '用户状态',
            'auth_key' => '用户的（cookie）认证密钥',
            'create_time' => '添加时间',
            'password_repeat'=>'确认密码'
        ];
    }
}
