<?php
// +----------------------------------------------------------------------
// | forgetwork
// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/5/20.
// +----------------------------------------------------------------------

/**
 * node 扩展方法
 *
 * 以下是基本用法：
 *
 * 前台：
 * 在news模型表单中 HtmlHelper::hiddenInput('expand[]','Test');
 * 后台：
 * public function newsTest($scenarios = 'update'|'create',$params = ['categoryInfo','nodeModel','oldData'])
 *
 * 固定扩展：
 * public function newsDelete($scenarios = 'delete',$params = ['categoryInfo','nodeModel','ids','delData','recycleBin'])
 * public function newsStatus($scenarios = 'status',$params = ['categoryInfo','nodeModel','oldData','status'])
 * public function relation($scenarios = 'status',$params = ['categoryInfo','nodeModel'])
 */

namespace manage\modules\prototype\models;

use common\helpers\StringHelper;
use Yii;
use yii\base\Component;

class ExpandService extends Component
{
	/**
	 * 关联
	 * @param $scenarios
	 * @param $params = ['categoryInfo','nodeModel']
	 *
	 * @throws \yii\db\Exception
	 */
    public function relation($scenarios,$params){
        $model = $params['nodeModel'];
        $sql = '';
        foreach (Yii::$app->getRequest()->post('relation',[]) as $i=>$relationIds){
            $relationModelName = str_replace('Model','',StringHelper::basename($model::className()));
            if(in_array($relationModelName,['User','Category'])){
                $relationModelName = '\common\entity\models\\'.$relationModelName.ucwords($i).'RelationModel';
            }else{
                $relationModelName = '\common\entity\nodes\\'.$relationModelName.ucwords($i).'RelationModel';
            }
            $relationModel = new $relationModelName();

            if($scenarios == 'update'){
                // 删除旧关联
                $relationModel::deleteAll(['parent_id'=>$model->id]);
            }
            // 设置新关联
            $newData = [];
            if(empty($relationIds)){
                $relationIds = [];
            }else{
                $relationIds = explode(',',$relationIds);
            }
            foreach($relationIds as $item) {
                $newData[] = [
                    'relation_id'=>$item,
                    'parent_id'=>$model->id
                ];
            }
            if(!empty($newData)){
                $sql .= Yii::$app->db->createCommand()->batchInsert($relationModel::tableName(), ['relation_id','parent_id'], $newData)->rawSql.';';
            }
        }
        Yii::$app->db->createCommand($sql)->execute();
    }
}