<?php

namespace common\entity\domains;

use Yii;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property string $id
 * @property string $account_type
 * @property string $username
 * @property string $password
 * @property string $email
 * @property string $cellphone
 * @property integer $is_enable
 * @property string $create_time
 * @property string $auth_key
 * @property string $cellphone_code
 *
 * @property CommentDomain[] $comments
 * @property CommentDomain[] $comments0
 * @property UserProfileDomain $userProfile
 * @property UserRelationDomain[] $userRelations
 * @property UserThirdAccountDomain[] $userThirdAccounts
 */
class UserDomain extends \common\components\BaseArModel
{
    public $password_repeat;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username','password'],'trim'],
            [['account_type'], 'string'],
            [['is_enable', 'create_time'], 'integer'],
            [['username'], 'string', 'max' => 36],
            [['auth_key'], 'string', 'max' => 70],
            [['password'], 'string', 'max' => 60],
            [['email'], 'string', 'max' => 100],
            [['cellphone','cellphone_code'], 'string', 'max' => 11],

            [['username','password'], 'required','on'=>['adminCreate','adminReset']],
            ['password_repeat','compare','compareAttribute'=>'password', 'on' => ['adminCreate','adminReset']],
            [['email'], 'unique','when'=>function($model,$attribute){
                return !empty($this->$attribute);
            },'on'=>['adminCreate','adminReset']],
            [['email'], 'email','on'=>['adminCreate','adminReset']],
            [['cellphone'],'unique','targetAttribute' => ['cellphone', 'cellphone_code'],'when'=>function($model,$attribute){
                return !empty($this->$attribute);
            },'on'=>['adminCreate','adminReset']],
            [['password'], 'filter','filter'=>function($value){
                return Yii::$app->getSecurity()->generatePasswordHash($value);
            },'on'=>['adminCreate','adminReset']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account_type' => '账户类型',
            'username' => '用户名',
            'password' => '密码',
            'email' => '邮箱',
            'cellphone' => '手机',
            'is_enable' => '是否启用',
            'create_time' => '注册时间',
            'auth_key' => 'Auth Key',
            'cellphone_code' => '手机号国家代码',
            'password_repeat' => '确认密码',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(CommentDomain::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments0()
    {
        return $this->hasMany(CommentDomain::className(), ['to_user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfile()
    {
        return $this->hasOne(UserProfileDomain::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserRelations()
    {
        return $this->hasMany(UserRelationDomain::className(), ['uesr_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserThirdAccounts()
    {
        return $this->hasMany(UserThirdAccountDomain::className(), ['user_id' => 'id']);
    }
}
