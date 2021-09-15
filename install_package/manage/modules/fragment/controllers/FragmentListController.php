<?php

namespace manage\modules\fragment\controllers;

use common\components\CurdInterface;
use common\components\manage\ManageController;
use common\entity\models\FragmentCategoryModel;
use common\entity\models\FragmentListModel;
use common\entity\models\PrototypeCategoryModel;
use common\entity\models\SystemLogModel;
use common\entity\searches\FragmentListSearch;
use common\helpers\ArrayHelper;
use common\helpers\UrlHelper;
use manage\models\DelCacheHelper;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * SlideController implements the CRUD actions for SlideModel model.
 */

class FragmentListController extends ManageController implements CurdInterface
{
    /**
     * Lists all SlideModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new FragmentListSearch();
        $searchModel->category_id = Yii::$app->request->get('category_id');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $userAccessButton = [
            'create'=>false,
            'update'=>false,
            'delete'=>false,
            'sort'=>false,
            'status'=>false,
        ];
        $userAccessList = Yii::$app->getAuthManager()->getPermissionsByUser(Yii::$app->getUser()->getId());
        foreach ($userAccessButton as $i=>$item){
            if($this->isSuperAdmin || array_key_exists('fragment/fragment-list/'.$i.'?category_id='.$searchModel->category_id,$userAccessList)){
                $userAccessButton[$i] = true;
            }
        }
        unset($userAccessList);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'categoryInfo' => $this->findCategoryInfo($searchModel->category_id),
            'userAccessButton'=>$userAccessButton
        ]);
    }

	/**
	 * Creates a new SlideModel model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 * @throws NotFoundHttpException
	 * @throws \yii\base\Exception
	 */
    public function actionCreate()
    {
        $model = $this->findModel();
        $model->category_id = Yii::$app->request->get('category_id');
        $categoryInfo = $this->findCategoryInfo($model->category_id);
        if(Yii::$app->request->isPost){
            if($model->load(Yii::$app->request->post())){
                if(empty($model->link) && $categoryInfo->enable_link && $model->related_data_model == 0){
                    $model->link = 'javascript:void(0);';
                }
                $model->site_id = $this->siteInfo->id;
                if ($model->save()) {
                    $model->sort = $model->primaryKey;
                    $model->save();

                    if($categoryInfo->is_global) $this->deleteCache();

                    SystemLogModel::create('create','在广告“'.$this->findCategoryInfo($model->category_id)->title.'”新增了内容“'.$model->title.'”');

                    $this->success([Yii::t('common','Operation successful'),'updateUrl'=>UrlHelper::toRoute(['update','id'=>$model->primaryKey])]);
                }
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrors()]);
        }

        return $this->render('create', [
            'model' => $model,
            'categoryInfo' => $categoryInfo,
            'modelList'=>$this->findModelList()
        ]);
    }

	/**
	 * Updates an existing SlideModel model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param int $id
	 *
	 * @return mixed
	 * @throws NotFoundHttpException
	 * @throws \yii\base\Exception
	 */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $categoryInfo = $this->findCategoryInfo($model->category_id);
        if(Yii::$app->request->isPost){
            if($model->load(Yii::$app->request->post())){
                /*if($categoryInfo->enable_link && $model->related_data_model == 0){
                    $model->link = 'javascript:;';
                }else{
                    $model->link = null;
                }*/
                if(!$categoryInfo->enable_link && $model->related_data_model == 0){
                    $model->link = 'javascript:void(0);';
                }
                $model->site_id = $this->siteInfo->id;
                if ($model->save()) {

                    if($categoryInfo->is_global) $this->deleteCache();

                    SystemLogModel::create('update','在广告“'.$this->findCategoryInfo($model->category_id)->title.'”修改了内容“'.$model->title.'”');

                    $this->success([Yii::t('common','Operation successful')]);
                }
            }
            $this->error([Yii::t('common','Operation failed')]);
        }

        return $this->render('update', [
            'model' => $model,
            'categoryInfo' => $categoryInfo,
            'modelList'=>$this->findModelList()
        ]);
    }

	/**
	 * 状态设置
	 *
	 * @param int|string $id
	 *
	 * @return mixed|void
	 * @throws NotFoundHttpException
	 * @throws \yii\base\Exception
	 */
    public function actionStatus($id){
        $model = $this->findModel();
        $id = explode(',',$id);

        if($model->updateAll(['status'=>Yii::$app->request->get('value',0)],['id'=>$id])){

            $this->deleteCache();

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
	 * @throws \yii\base\Exception
	 * @throws \yii\db\Exception
	 */
    public function actionSort($id = null,$mode = null){
        $model = $this->findModel();

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

                $this->deleteCache();

                $this->success([Yii::t('common','Operation successful')]);
            }
            $this->error([Yii::t('common','Operation failed')]);
        }

        // 单排序
        if($id === null) $this->error(['操作失败','message'=>'缺少参数id']);
        $currData = $model->find()->where(['id'=>$id])->select(['id','sort'])->asArray()->one();

        $sign = $mode?'>':'<';
        $sort = $mode?['sort'=>SORT_ASC]:['sort'=>SORT_DESC];

        $searchModel = new FragmentListSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query
            ->andFilterWhere(['category_id'=>Yii::$app->request->get('category_id')])
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

                $this->deleteCache();

                $this->success([Yii::t('common','Operation successful')]);
            }
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

	/**
	 * Deletes an existing SlideModel model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 * @throws NotFoundHttpException
	 * @throws \yii\base\Exception
	 */
    public function actionDelete($id)
    {
        $model = $this->findModel();
        $ids = explode(',',$id);

        $delData = $model->find()->where(['id'=>$ids])->with(['categoryInfo'])->select(['id','category_id','title'])->asArray()->all();

        if($model->deleteAll(['id'=>$ids])){
            $this->deleteCache();

            foreach ($delData as $item){
                SystemLogModel::create('delete','在广告“'.$item['categoryInfo']['title'].'”下删除内容“'.$item['title'].'”');
            }

            $this->success([Yii::t('common','Operation successful')]);
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

    /**
     * Finds the SlideModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return FragmentListModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id = null)
    {
        $model = empty($id)? new FragmentListModel():FragmentListModel::findOne($id);
        if($model !== null){
            return $model;
        }else{
            throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }
    }

    /**
     * 获取node栏目列表
     * @return array|\yii\db\ActiveRecord[]
     */
    protected function findModelList(){
	    $category = PrototypeCategoryModel::find()
			->where(['site_id'=>$this->siteInfo->id])
			->select(['id','pid','title','type','model_id','sort'])
		    ->with(['model'=>function($query){
		    	$query->select(['id','title','name']);
		    }])
			->orderBy(['sort'=>SORT_ASC,'id'=>SORT_ASC])->asArray()->all();

        $category =  ArrayHelper::linear($category,' ├ ');

        foreach($category as $i=>$item){
            $category[$i]['title'] = $item['str'].$item['title'];
        }

	    $category = ArrayHelper::index($category,'id');

        return $category;
    }

    /**
     * 获取幻灯片栏目信息
     * @param $id
     * @return FragmentCategoryModel
     */
    protected function findCategoryInfo($id){
        return FragmentCategoryModel::findOne($id);
    }

	/**
	 * 删除缓存
	 * @throws \yii\base\Exception
	 */
    public function deleteCache(){
	    DelCacheHelper::deleteCache('fragment');
    }

}