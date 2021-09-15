<?php
/**
 * @copyright
 * @link 
 * @create Created on 2016/10/8
 */

namespace home\modules\u\models;

use common\components\BaseModel;
use common\entity\models\UserProfileModel;
use common\helpers\ArrayHelper;
use Yii;


/**
 * 用户信息表单
 *
 * @author 
 * @since 1.0
 */
class ProfileFrom extends BaseModel
{
    public $nickname;
    public $avatar;
    public $gender;
    public $birthday;
    public $blood;
    public $country;
    public $province;
    public $city;
    public $area;
    public $street;
    public $signature;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['nickname'],'trim'],
            [['nickname'], 'required'],
            [['birthday'], 'string','max'=>10],
            [['gender', 'blood'], 'string'],
            ['gender','in','range'=>['male','female','secrecy']],
            ['blood','in','range'=>['A','B','O','AB']],
            [['nickname'], 'string', 'max' => 30],
	        [['avatar'], 'filter','filter'=>function($value){
        	    if(empty($value)) return $value;
        	    return strpos($value,'[{') === 0?$value:json_encode([['file'=>$value,'alt'=>$this->nickname]]);
	        }, 'on'=>'api'],
            [['avatar'], 'string', 'max' => 255],
            [['country','province', 'city', 'area', 'street'], 'string', 'max' => 100],
            [['signature'], 'string', 'max' => 70],

	        /**
	         * 敏感词检测
	         */
	        [['nickname','signature'],function($attribute, $params){
		        if (!$this->hasErrors()) {
			        $res = \common\helpers\SecurityHelper::checkSensitiveWords($this->$attribute);
			        if($res !== false){
				        $this->addError($attribute,$this->getAttributeLabel($attribute)."存在敏感词“".implode('、',$res)."”。");
			        }
		        }
	        }],
        ];
    }

    /**
     * @var array 自定义属性标签
     */
    public $customAttributeLabels = [];

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'nickname' => '昵称',
            'avatar' => '头像',
            'gender' => '性别',
            'birthday' => '生日',
            'blood' => '血型',
            'country' => '国家',
            'province' => '省',
            'city' => '市',
            'area' => '县区',
            'street' => '街道',
            'signature' => '签名',
        ],$this->customAttributeLabels);
    }

    /**
     * @param null $userId
     * @return bool|UserProfileModel
     */
    public function save($userId = null){
        if(!$this->validate()) return false;

        $model = UserProfileModel::findOne($userId?:Yii::$app->getUser()->getId());
        if($model){
            $model->attributes = $this->getNonnullAttributes();
            if($model->save()){
                return $model;
            }else{
                $this->addErrors($model->getErrors());
            }
        }else{
            $this->addError('id','用户不存在。');
        }

        return false;
    }

    /**
     * 查询用户资料
     * @param null $userId
     * @return $this
     */
    public function findOne($userId = null){
        $profile = UserProfileModel::findOne($userId?:Yii::$app->getUser()->getId());
        if($profile){
            $this->attributes = ArrayHelper::toArray($profile);
        }
        return $this;
    }

	/**
	 * 返回用户信息
	 * @param $profileModel
	 *
	 * @return array
	 */
    static public function getApiUserInfo($profileModel){
	    $userModel = ArrayHelper::toArray(Yii::$app->getUser()->identity);
	    unset($userModel['account_type'],$userModel['password'],$userModel['create_time'],$userModel['auth_key'],$userModel['is_enable']);

	    $userModel['userProfile'] = ArrayHelper::toArray($profileModel);

	    unset($userModel['userProfile']['customAttributeLabels']);

	    return $userModel;
    }
}