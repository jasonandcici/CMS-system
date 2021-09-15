<?php

namespace manage\modules\prototype\controllers;

use common\components\CurdInterface;
use common\components\manage\PrototypeController;
use common\entity\models\CommentModel;
use common\entity\models\PrototypeModelModel;
use common\entity\models\PrototypePageModel;
use common\entity\models\SystemLogModel;
use common\entity\models\TagModel;
use common\entity\models\TagRelationModel;
use common\entity\models\UserRelationModel;
use common\helpers\ArrayHelper;
use common\helpers\UrlHelper;
use manage\modules\prototype\models\ExpandService;
use Yii;
use yii\helpers\Inflector;
use yii\web\NotFoundHttpException;

class NodeController extends PrototypeController implements CurdInterface
{
	/**
	 * 内容列表
	 * @return string
	 */
    public function actionIndex()
    {
        $request = Yii::$app->getRequest();

        $modelName = '\\common\\entity\\nodes\\'.ucwords($this->categoryInfo->model->name).'Search';
        $searchModel = new $modelName();

        $dataProvider = $searchModel->search($request->queryParams);
        $childesId = $this->getSubCategoriesId($this->categoryInfo->id);
        $dataProvider->query->andFilterWhere(['in','category_id',$childesId]);

        if(!$searchModel->status){
            $dataProvider->query->andFilterWhere(['status'=>[0,1,2]]);
        }

        $dataProvider->sort = [
            'defaultOrder' => [
                'sort'=> SORT_DESC,
            ]
        ];

        if($pageSize = $request->get('per-page')){
            $dataProvider->pagination = ['pageSize'=>intval($pageSize)];
        }else{
            $dataProvider->pagination = ['pageSize'=>array_key_exists('page_size',Yii::$app->params)?Yii::$app->params['page_size']:15];
        }

        $userAccessButton = [
            'create'=>false,
            'update'=>false,
            'delete'=>false,
            'sort'=>false,
            'status'=>false,
            'move'=>false,
        ];
        $userAccessList = Yii::$app->getAuthManager()->getPermissionsByUser(Yii::$app->getUser()->getId());
        foreach ($userAccessButton as $i=>$item){
            if($this->isSuperAdmin || array_key_exists('prototype/node/'.$i.'?category_id='.$this->categoryInfo->id,$userAccessList)){
                $userAccessButton[$i] = true;
            }
        }
        unset($userAccessList);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'categoryInfo' => $this->categoryInfo,
            'showCreateButton'=> count($childesId)>1?false:true,
            'userAccessButton'=>$userAccessButton
        ]);
    }

	/**
	 * 创建新内容
	 *
	 * @param bool $tags
	 *
	 * @return mixed
	 * @throws NotFoundHttpException
	 */
    public function actionCreate($tags = false)
    {
        $request = Yii::$app->getRequest();
        if($tags && $tagTitle = $request->get('title')){
            return json_encode(TagModel::find()->where(['like','title',$tagTitle])->limit(5)->asArray()->all());
        }


        $category_id = $request->get('category_id');

        $model = $this->findModel($this->categoryInfo->model->name);
        if($request->isPost){
            if ($model->load($request->post())) {
                $model->site_id = $this->siteInfo->id;
                if($model->save()){
                    $model->sort = $model->primaryKey;
                    $postTags = Yii::$app->getRequest()->post('tags');
                    if(!empty($postTags) && empty($model->seo_keywords)) $model->seo_keywords = $postTags;

                    if($model->save()){
                        // tag 标签
                        $this->saveTags($model->primaryKey,$postTags);

                        //使用扩展方法
                        $this->expandMethod(Yii::$app->getRequest()->post('expand'),$model,'create',['oldData'=>null]);

                        SystemLogModel::create('create','在栏目“'.$this->categoryInfo->title.'”下新增内容“'.$model->title."”");

                        $this->success([Yii::t('common','Operation successful'),'updateUrl'=>UrlHelper::toRoute(['update','category_id'=>$category_id,'id'=>$model->primaryKey])]);
                    }


                }
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrorString()]);
        }

        $model->loadDefaultValues();
        $model->category_id = $category_id;
        $model->model_id = $this->categoryInfo->model_id;

        return $this->render('create', [
            'model' => $model,
            'categoryInfo' => $this->categoryInfo,
            'tags'=>[],
        ]);
    }

    /**
     * 更新内容
     * @param int $id
     * @param bool $tags
     * @return mixed
     */
    public function actionUpdate($id,$tags=false)
    {
        $request = Yii::$app->getRequest();
        if($tags && $tagTitle = $request->get('title')){
            return json_encode(TagModel::find()->where(['like','title',$tagTitle])->limit(5)->asArray()->all());
        }

	    $model = $this->findModel($this->categoryInfo->model->name,$id);

        if($request->isPost){
        	$oldData = ArrayHelper::toArray($model);

	        $relationFields = $this->findRelationFields($this->categoryInfo->model_id,false);
			if(!empty($relationFields)){
				foreach ($relationFields as $item){
					$oldData[$item] = ArrayHelper::toArray($model->$item);
				}
			}
			unset($relationFields);

            if ($model->load($request->post())) {
                $postTags = Yii::$app->getRequest()->post('tags');
                if(!empty($postTags) && empty($model->seo_keywords)) $model->seo_keywords = $postTags;

                $model->site_id = $this->siteInfo->id;
                if($model->save()){
                    // tag 标签
                    $this->saveTags($model->primaryKey,$postTags);

                    //使用扩展方法
                    $this->expandMethod(Yii::$app->getRequest()->post('expand'),$model,'update',['oldData'=>$oldData]);

                    SystemLogModel::create('update','在栏目“'.$this->categoryInfo->title.'”下编辑内容“'.$model->title."”");

                    $this->success([Yii::t('common','Operation successful')]);
                }
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrorString()]);
        }

        return $this->render('update', [
            'model' => $model,
            'categoryInfo' => $this->categoryInfo,
            'tags'=>$this->findNodeTags($model->primaryKey),
        ]);
    }

	/**
	 * 删除内容
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 * @throws NotFoundHttpException
	 */
    public function actionDelete($id)
    {
        $model = $this->findModel($this->categoryInfo->model->name);
        $ids = explode(',',$id);

		$delDataQuery = $model->find()->where(['id'=>$ids]);
		$relationFields = $this->findRelationFields($this->categoryInfo->model_id);
		if(!empty($relationFields)) $delDataQuery->with($relationFields);

        $delData = [];
        $recycleBin = [];
        foreach ($delDataQuery->asArray()->all() as $item){
            if($item['status'] < 2){
                $recycleBin[] = $item;
            }else{
                $delData[] = $item;
            }
        }

        $delRes = 0;
        if(!empty($delData)){
	        $delRes = $model->deleteAll(['id'=>ArrayHelper::getColumn($delData,'id')]);
        }

        $recycleBinRes = 0;
        if(!empty($recycleBin)){
	        $recycleBinRes = $model::updateAll(['status'=>3],['id'=>ArrayHelper::getColumn($recycleBin,'id')]);
        }

        if($delRes || $recycleBinRes){
            // 删除标签关联
            TagRelationModel::deleteAll(['model_id'=>$this->categoryInfo->model_id,'data_id'=>$ids]);

            UserRelationModel::deleteAll(['user_model_id'=>$this->categoryInfo->model_id,'user_data_id'=>$ids]);

            // 删除评论
			if(!empty($delData)){
				CommentModel::deleteAll(['category_id'=>ArrayHelper::getColumn($delData,'category_id'),'data_id'=>ArrayHelper::getColumn($delData,'id')]);
			}

            //使用扩展方法
            $this->expandMethod('SYSTEM',$model,'delete',['ids'=>$ids,'delData'=>$delData,'recycleBin'=>$recycleBin]);

            if(!empty($recycleBin)) SystemLogModel::create('delete','在栏目“'.$this->categoryInfo->title.'”下删除内容“'.implode("、",ArrayHelper::getColumn($recycleBin,'title')).'”');
            if(!empty($delData)) SystemLogModel::create('delete','在栏目“'.$this->categoryInfo->title.'”下删除回收站内容“'.implode("、",ArrayHelper::getColumn($delData,'title')).'”');

            $this->success([Yii::t('common','Operation successful')]);
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

	/**
	 * 获取模型的关联字段
	 *
	 * @param $modelId
	 *
	 * @param bool $isInfo
	 *
	 * @return array
	 */
    protected function findRelationFields($modelId,$isInfo = true){
	    $relationFields = [];
	    $modelInfo = PrototypeModelModel::findModel($modelId);
	    foreach ($modelInfo->fields as $item){
		    if($item->type == 'relation_data' && !empty($item->setting)){
			    $item->setting = json_decode($item->setting);
			    if($item->setting->relationType){
				    $modelName = ($item->setting->modelName==='category'?'Prototype':'').ucwords(Inflector::pluralize($item->setting->modelName));
				    $relationFields[] = lcfirst($modelName).($isInfo?'List':'');
			    }
		    }
	    }
	    return $relationFields;
    }

	/**
	 * 状态设置
	 *
	 * @param int|string $id
	 *
	 * @return mixed|void
	 * @throws NotFoundHttpException
	 */
    public function actionStatus($id){
        $model = $this->findModel($this->categoryInfo->model->name);
        $ids = explode(',',$id);
		$value = Yii::$app->request->get('value',0);

	    $oldData = $model::find()->where(['id'=>$ids])->asArray()->all();

        if($model->updateAll(['status'=>$value],['id'=>$ids])){
            //使用扩展方法
            $this->expandMethod('SYSTEM',$model,'status',['oldData'=>$oldData,'status'=>$value]);

            SystemLogModel::create('update','更新了数据状态');

            $this->success([Yii::t('common','Operation successful')]);
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

	/**
	 * 数据排序
	 *
	 * @param int|null $id
	 * @param int|null $mode 0|1
	 *
	 * @return mixed|void
	 * @throws NotFoundHttpException
	 * @throws \yii\db\Exception
	 */
    public function actionSort($id = null,$mode = null){
        $request = Yii::$app->getRequest();
        $model = $this->findModel($this->categoryInfo->model->name);

        // 批量排序
        if(Yii::$app->getRequest()->getIsPost()){
            $postData = json_decode(Yii::$app->getRequest()->post('data'));
            $db = Yii::$app->db;
            $sql = '';
            foreach ($postData as $item){
                $sql .= $db->createCommand()->update($model->tableName(),['sort'=>intval($item->sort)],['id'=>$item->id])->rawSql.';';
            }
            if($sql){
                $db->createCommand($sql)->execute();

                SystemLogModel::create('update','对数据进行了排序');
                $this->success([Yii::t('common','Operation successful')]);
            }
            $this->error([Yii::t('common','Operation failed')]);
        }

        // 单排序
        if($id === null) $this->error(['操作失败','message'=>'缺少参数id']);
        $currData = $model->find()->where(['id'=>$id])->select(['id','sort'])->asArray()->one();

        $sign = $mode?'>':'<';
        $sort = $mode?['sort'=>SORT_ASC]:['sort'=>SORT_DESC];
        /*$previewData = $model->find()->where(['category_id'=>$this->getSubCategoriesId($this->categoryInfo->id)])
            ->andWhere([$sign,'sort',$currData['sort']])
            ->orderBy($sort)->select(['id','sort'])->asArray()->one();*/

        $modelName = '\\common\\entity\\nodes\\'.ucwords($this->categoryInfo->model->name).'Search';
        $searchModel = new $modelName();
        $dataProvider = $searchModel->search($request->queryParams);
        $dataProvider->query
            ->andFilterWhere(['category_id'=>$this->getSubCategoriesId($this->categoryInfo->id)])
            ->andWhere([$sign,'sort',$currData['sort']])
            ->select(['id','sort'])->asArray();
        $dataProvider->pagination = ['pageSize'=>1];
        $dataProvider->sort = [
            'defaultOrder' => $sort
        ];

        $previewData = $dataProvider->getModels();
        if(count($previewData) < 1){
            $previewData = null;
        }else{
            $previewData = $previewData[0];
        }


        if($previewData){
            $db = Yii::$app->db;
            $sql = $db->createCommand()->update($model->tableName(),['sort'=>$currData['sort']],['id'=>$previewData['id']])->rawSql.';';
            $sql .= $db->createCommand()->update($model->tableName(),['sort'=>$previewData['sort']],['id'=>$currData['id']])->rawSql.';';

            if($db->createCommand($sql)->execute()){

                SystemLogModel::create('update','对数据进行了排序');
                $this->success([Yii::t('common','Operation successful')]);
            }
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

	/**
	 * 移动到
	 *
	 * @param $id
	 * @param $cid
	 *
	 * @throws NotFoundHttpException
	 */
    public function actionMove($id,$cid){
        $model = $this->findModel($this->categoryInfo->model->name);
        $model::updateAll(['category_id'=>$cid],['id'=>explode(',',$id)]);

        SystemLogModel::create('update','对数据进行了移动');
        $this->success([Yii::t('common','Operation successful')]);
    }

    /**
     * 更新单网页内容
     * @return string
     */
    public function actionPage(){
        $model = PrototypePageModel::findOne($this->categoryInfo->id);

        if(Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post())) {
                $model->update_time = time();
                if($model->save()){

                    SystemLogModel::create('update','编辑了单网页“'.$this->categoryInfo->title."”");

                    $this->success([Yii::t('common','Operation successful'),'jumpLink'=>'javascript:;']);
                }
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrors()]);
        }

        return $this->render('page', [
            'model' => $model,
            'categoryInfo' => $this->categoryInfo,
        ]);
    }

    /**
     * 查找一个模型
     * @param $modelName
     * @param null $id
     * @return PrototypeModelModel|null|static
     * @throws NotFoundHttpException
     */
    protected function findModel($modelName,$id = null)
    {
        $modelName = '\\common\\entity\\nodes\\'.ucwords($modelName).'Model';
        $model = empty($id)? new $modelName():$modelName::findOne($id);
        if($model !== null){
            return $model;
        }else{
            throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }
    }

	/**
	 * 扩展方法调用
	 *
	 * @param $expand
	 * @param $model
	 * @param $scenarios
	 * @param array $params
	 *
	 * @return bool
	 */
    public function expandMethod($expand,$model,$scenarios,$params=[]){
        if(empty($expand)) return false;
        $expandService = new ExpandService();

        if(!is_array($expand)) $expand = [$expand];

        foreach ($expand as $item){
	        if($item === 'SYSTEM'){
		        $action = $this->categoryInfo->model->name.ucfirst($scenarios);
	        }elseif ($item === 'RELATION'){
		        $action = 'relation';
	        }else{
		        $action = $this->categoryInfo->model->name.ucfirst($item);
	        }
	        if(method_exists($expandService,$action)){
		        $expandService->$action($scenarios,ArrayHelper::merge([
			        'categoryInfo'=>$this->categoryInfo,
			        'nodeModel'=>$model
		        ],$params));
	        }
        }
    }

    /**
     * 查找标签
     * @param $nodeId
     * @param bool $returnTags
     * @return array
     */
    protected function findNodeTags($nodeId,$returnTags = true){
        $list = TagRelationModel::find()->where(['model_id'=>$this->categoryInfo->model_id,'data_id'=>$nodeId])->with(['tag'])->asArray()->all();
        $tags = [];
        foreach ($list as $item){
            $tags[] = $returnTags?$item['tag']['title']:$item['tag'];
        }
        return $tags;
    }

    /**
     * 保存标签
     * @param $nodeId
     * @param $tags
     * @return bool
     */
    protected function saveTags($nodeId,$tags){
        if(empty($tags)) return true;
        $tags = explode(',',$tags);
        $oldTagsList = $this->findNodeTags($nodeId,false);

        $oldTags = ArrayHelper::getColumn($oldTagsList,'title');
        $commonTags = array_intersect($tags,$oldTags);

        $db = Yii::$app->getDb();
        $sql = '';

        // 待删除标签
        $delTags = array_diff($oldTags,$commonTags);
        if(!empty($delTags)){
            $delTagsId = [];
            foreach ($oldTagsList as $item){
                if(in_array($item['title'],$delTags)) $delTagsId[] = $item['id'];
            }
            $sql .= $db->createCommand()->delete(TagRelationModel::tableName(),['data_id'=>$nodeId,'model_id'=>$this->categoryInfo->model_id,'tag_id'=>$delTagsId])->rawSql.';';
            unset($delTagsId,$delTags);
        }

        // 新标签
        $newTags = array_diff($tags,$commonTags);
        if(!empty($newTags)){
            $oldTag = ArrayHelper::getColumn(TagModel::find()->where(['title'=>$newTags])->asArray()->all(),'title');

            $insetTagData = [];
            foreach (array_diff($newTags,$oldTag) as $item){
                $insetTagData[] = ['title'=>$item];
            }

            if(!empty($insetTagData)) $db->createCommand()->batchInsert(TagModel::tableName(),['title'],$insetTagData)->execute();
            unset($insetTagData,$oldTag);

            $insetTagNodeData = [];
            foreach (ArrayHelper::getColumn(TagModel::find()->where(['title'=>$newTags])->asArray()->all(),'id') as $item){
                $insetTagNodeData[] = [
                    'model_id'=>$this->categoryInfo->model_id,
                    'tag_id'=>$item,
                    'data_id'=>$nodeId,
                ];
            }
            $sql .=$db->createCommand()->batchInsert(TagRelationModel::tableName(),['model_id','tag_id','data_id'],$insetTagNodeData)->rawSql.';';
            unset($newTags,$insetTagNodeData);
        }

        unset($oldTagsList,$oldTags,$commonTags);
        if(!empty($sql)) $db->createCommand($sql)->execute();
        return true;
    }
}