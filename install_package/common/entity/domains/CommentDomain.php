<?php

namespace common\entity\domains;

use common\entity\models\PrototypeCategoryModel;
use common\entity\models\UserModel;
use common\entity\models\UserProfileModel;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%comment}}".
 *
 * @property string $id
 * @property string $pid
 * @property integer $category_id
 * @property string $data_id
 * @property string $content
 * @property string $atlas
 * @property string $user_id
 * @property integer $is_enable
 * @property string $create_time
 * @property string $count_like
 * @property string $count_bad
 *
 * @property PrototypeCategoryModel $category
 * @property UserModel $user
 * @property UserModel $toUser
 * @property UserCommentDomain[] $userComments
 */
class CommentDomain extends \common\components\BaseArModel
{
	/**
	 * @return array
	 */
	public function behaviors()
	{
		return [
			[
				'class' => TimestampBehavior::className(),
				'attributes' => [
					ActiveRecord::EVENT_BEFORE_INSERT => ['create_time'],
				],
			],
		];
	}

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%comment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['data_id', 'content', 'user_id',], 'required'],
            [['pid', 'category_id', 'data_id', 'user_id', 'is_enable', 'create_time','count_bad','count_like',], 'integer'],
            [['content', 'atlas'], 'string'],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => PrototypeCategoryModel::className(), 'targetAttribute' => ['category_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserModel::className(), 'targetAttribute' => ['user_id' => 'id']],
	        ['pid','default','value'=>0],
	        [['atlas'], 'filter','filter'=>function($value){
		        if(empty($value)) return $value;
		        if(strpos($value,'[{') === 0){
		        	return $value;
		        }else{
			        $value = explode(',',$value);
			        $new = [];
			        foreach ($value as $item){
				        $new[] = [
					        'file'=>$item,
					        'alt'=>''
				        ];
			        }
			        return json_encode($new);
		        }
	        }, 'on'=>'api'],

	        /**
	         * 敏感词检测
	         */
	        [['content'],function($attribute, $params){
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pid' => '父级评论',
            'category_id' => 'Category ID',
            'data_id' => '评论对象id',
            'content' => '评论内容',
            'atlas' => '图集',
            'user_id' => 'User ID',
            'is_enable' => '状态',
            'create_time' => '评论时间',
            'count_bad' => '差评数',
            'count_like' => '点赞数',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(PrototypeCategoryModel::className(), ['id' => 'category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(UserModel::className(), ['id' => 'user_id']);
    }

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getUserProfile()
	{
		return $this->hasOne(UserProfileModel::className(), ['user_id' => 'user_id']);
	}

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserComments()
    {
        return $this->hasMany(UserCommentDomain::className(), ['comment_id' => 'id']);
    }

	/**
	 * @return array
	 */
	public function extraFields(){
		return ["userLikeCount","userBadCount","userProfile","category"];
	}
}
