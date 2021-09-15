<?php
// +----------------------------------------------------------------------
// | forgetwork
// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/5/19.
// +----------------------------------------------------------------------

/**
 * 原型node基类
 */

namespace common\components\manage;


use common\entity\models\PrototypeCategoryModel;
use common\entity\models\PrototypeModelModel;
use common\helpers\ArrayHelper;
use Yii;
use yii\web\NotFoundHttpException;

class PrototypeController extends ManageController
{
    /**
     * @var array 栏目列表
     */
    public $categoryList;

    /**
     * @var object 当前栏目信息
     */
    public $categoryInfo;

    /**
     * init
     */
    public function init()
    {
        parent::init();

        // 读取栏目列表
        $this->categoryList = Yii::$app->cache->get('category'.$this->siteInfo->id);
        if($this->categoryList == null){
            $this->categoryList = PrototypeCategoryModel::find()->where(['site_id'=>$this->siteInfo->id])->indexBy('id')->with(['model'])->orderBy(['sort'=>SORT_ASC,'id'=>SORT_ASC])->asArray()->all();
            Yii::$app->cache->set('category'.$this->siteInfo->id,$this->categoryList);
        }

        // 当前栏目信息
        $categoryId = Yii::$app->request->get('category_id');
        if($categoryId) $this->categoryInfo = $this->getCategoryInfo($categoryId);
    }

    /**
     * 返回一当前栏目相同模型的子栏目
     * @param $id
     * @param array $params
     * @return array
     */
    public function getSubCategoriesId($id,$params = []){
        $params = array_merge(['isSelf'=>true],$params);

        $parent = $this->categoryList[$id];
        $result = [];
        foreach(ArrayHelper::getChildes($this->categoryList,$id) as $item){
            if($item['type'] == $parent['type'] && $item['model_id'] == $parent['model_id']) $result[] = $item['id'];
        }

        if($params['isSelf']) $result[] = $id;

        return $result;
    }

    /**
     * 获取栏目信息
     * @param $category_id
     * @return object
     * @throws NotFoundHttpException
     */
    public function getCategoryInfo($category_id){
        if(!$category_id) throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));

        // 获取栏目信息
        $categoryInfo = ArrayHelper::convertToObject($this->categoryList[$category_id]);

        return $categoryInfo;
    }

    /**
     * 获取模型列表
     * @return array|int|\yii\db\ActiveRecord[]
     */
    public function getModelList(){
        return PrototypeModelModel::find()->all();
    }

    /**
     * 查找数据列表
     * @param $modelName
     * @param bool $isNode 是否原型node下的模型
     * @return
     */
    public function findDataList($modelName,$isNode = false){
        $modelName = '\\common\\entity\\'.($isNode?'nodes':'models').'\\'.ucfirst($modelName).'Model';
        $model = new $modelName();
        return $model->find();
    }

	/**
	 * 根据栏目system_mark字段获取栏目
	 * @param string|array $mark
	 *
	 * @return mixed|null
	 */
	public function findCategoryByMark($mark){
		if(is_string($mark)) $mark = [$mark];

		$resArr = [];
		foreach ($this->categoryList as $item){
			if(in_array($item['system_mark'],$mark)){
				$resArr[$item['system_mark']] = $item;
			}
		}

		$res = [];
		foreach ($mark as $item){
			$res[$item] = array_key_exists($item,$resArr)?$resArr[$item]:null;
		}

		return count($res) === 1?$res[$mark[0]]:$res;
	}
}