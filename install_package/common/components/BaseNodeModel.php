<?php
// +----------------------------------------------------------------------
// | nantong
// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/4/7.
// +----------------------------------------------------------------------

/**
 * node模型基类
 */

namespace common\components;

use common\entity\models\CommentModel;
use common\entity\models\PrototypeCategoryModel;
use common\entity\models\PrototypeModelModel;
use common\entity\models\SiteModel;
use common\entity\models\TagModel;
use common\entity\models\TagRelationModel;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use yii\web\Link;
use yii\web\Linkable;

class BaseNodeModel extends BaseArModel implements Linkable
{
    /**
     * 自动填充时间
     * @return array
     */
    public function behaviors()
    {

        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['create_time','update_time'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['update_time'],
                ],
            ],
        ];
    }

    /**
     * @return array 生成链接(用于restful接口)
     */
    public function getLinks(){
        return [
            Link::REL_SELF => Url::toRoute(['/api/html5/view','sid'=>$this->site_id,'category_id'=>$this->category_id,'id'=>$this->id],true),
        ];
    }

    /**
     * @return array
     */
    public function extraFields(){
        return ['categoryInfo','modelInfo','siteInfo','tagRelations','commentInfo','commentCount'];
    }

    /**
     * 栏目信息
     * @return \yii\db\ActiveQuery
     */
    public function getCategoryInfo(){
        return $this->hasOne(PrototypeCategoryModel::className(),['id'=>'category_id']);
    }

    /**
     * 模型信息
     * @return \yii\db\ActiveQuery
     */
    public function getModelInfo(){
        return $this->hasOne(PrototypeModelModel::className(),['id'=>'model_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSiteInfo()
    {
        return $this->hasOne(SiteModel::className(), ['id' => 'site_id']);
    }

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getCommentInfo()
	{
		return $this->hasMany(CommentModel::className(), ['category_id' => 'category_id','data_id'=>'id'])
		            ->andWhere(['pid'=>0,'is_enable'=>1]);
	}

	/**
	 * 统计node的评论数
	 * @return int|string
	 */
	public function getCommentCount()
	{
		return $this->hasMany(CommentModel::className(), ['category_id' => 'category_id','data_id'=>'id'])
		            ->andWhere(['pid'=>0,'is_enable'=>1])->count();
	}

    /**
     * 标签
     * @return \yii\db\ActiveQuery
     */
    public function getTagRelation(){
        return $this->hasOne(TagRelationModel::className(),['model_id'=>'model_id','data_id'=>'id']);
    }

    public function getTagRelations(){
        return $this->hasMany(TagRelationModel::className(),['model_id'=>'model_id','data_id'=>'id']);
    }

    public function getTagRelationsInfo(){
        return $this->hasMany(TagModel::className(), ['id' => 'tag_id'])
            ->viaTable(TagRelationModel::tableName(),['model_id'=>'model_id','data_id'=>'id']);
    }
}