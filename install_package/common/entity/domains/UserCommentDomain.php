<?php

namespace common\entity\domains;

use common\entity\models\CommentModel;
use common\entity\models\UserModel;
use Yii;

/**
 * This is the model class for table "{{%user_comment}}".
 *
 * @property string $comment_id
 * @property string $user_id
 * @property string $type
 *
 * @property CommentModel $comment
 * @property UserModel $user
 */
class UserCommentDomain extends \common\components\BaseArModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_comment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['comment_id','user_id'], 'required'],
            [['comment_id', 'user_id'], 'integer'],
            [['type'], 'string'],
            [['comment_id'], 'exist', 'skipOnError' => true, 'targetClass' => CommentModel::className(), 'targetAttribute' => ['comment_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserModel::className(), 'targetAttribute' => ['user_id' => 'id']],
	        ['comment_id', 'unique', 'targetAttribute' => ['comment_id', 'user_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'comment_id' => 'Comment ID',
            'user_id' => 'User ID',
            'type' => 'Type',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComment()
    {
        return $this->hasOne(CommentModel::className(), ['id' => 'comment_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(UserModel::className(), ['id' => 'user_id']);
    }
}
