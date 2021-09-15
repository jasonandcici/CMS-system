<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/12/20
 */

namespace home\modules\u\models;

use common\components\BaseModel;
use common\entity\domains\UserDomain;
use common\entity\models\UserModel;
use common\entity\models\UserProfileModel;
use Yii;


/**
 * 修改用户名
 *
 * @author 
 * @since 1.0
 */
class ResetUsernameForm extends BaseModel
{
    public $username;
    public $captcha;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username'],'trim'],
            [['username'],'required'],
	        [['captcha'],'required','on'=>'web'],
            ['username','match','pattern'=>'/^(?![u_|\d]).+$/','message'=>'{attribute}格式错误。'],
            ['username', 'string', 'length' => [3, 15]],
            ['username', 'unique','targetAttribute' => 'username','targetClass' => '\common\entity\domains\UserDomain', 'message' => '此用户名已被使用。'],
            ['captcha','captcha','captchaAction'=>'/site/captcha','on'=>'web'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username'=>'用户名',
            'captcha' => '验证码',
        ];
    }

    /**
     * 重置用户名
     * @throws \yii\db\Exception
     */
    public function reset(){
        if(!$this->validate()) return false;

        $model = UserModel::find()->where(['id'=>Yii::$app->getUser()->getId()])->joinWith('userProfile')->one();
        if($model){
            if(stripos($model->username,'u_',0)!==0){
                $this->addError('username','无法修改用户名。');
                return false;
            }

            $db = Yii::$app->getDb();
            $sql = $db->createCommand()->update(UserModel::tableName(),['username'=>$this->username],['id'=>$model->primaryKey])->rawSql.';';
            if($model->userProfile->nickname == $model->username){
                $sql .= $db->createCommand()->update(UserProfileModel::tableName(),['nickname'=>$this->username],['user_id'=>$model->primaryKey])->rawSql.';';
            }
            $db->createCommand($sql)->execute();
            return true;
        }else{
            $this->addError('username','用户不存在。');
            return false;
        }
    }
}