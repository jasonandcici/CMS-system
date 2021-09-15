<?php

namespace manage\modules\fragment\controllers;

use common\components\CurdInterface;
use common\components\manage\ManageController;
use common\entity\models\AuthItemModel;
use common\entity\models\FragmentCategoryModel;
use common\entity\searches\FragmentCategorySearch;
use common\helpers\UrlHelper;
use manage\models\DelCacheHelper;
use Yii;
use yii\db\Exception;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * CategoryController implements the CRUD actions for FragmentCategoryModel model.
 */

class CategoryController extends ManageController implements CurdInterface
{

    /**
     * node权限列表
     * @var array
     */
    public $accessList = [];

    public function init()
    {
        parent::init();

        $this->accessList = Yii::$app->params['authListFragment'];
    }

    /**
     * Lists all FragmentCategoryModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new FragmentCategorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['site_id'=>$this->siteInfo->id]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

	/**
	 * Creates a new FragmentCategoryModel model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 * @throws NotFoundHttpException
	 * @throws \yii\db\Exception
	 * @throws \yii\base\Exception
	 */
    public function actionCreate()
    {
        $model = $this->findModel();
        if(Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post())) {
                $model->site_id = $this->siteInfo->id;
                if($model->save()){
                    $model->sort = $model->primaryKey;
                    $model->save();

                    // 插入权限
                    $model->type = intval($model->type);
                    $authData = [];
                    foreach ($this->accessList as $i=>$item){
                        if(($model->type === 0 && $i == 'fragment/fragment/edit') || ($model->type === 1 && $i != 'fragment/fragment/edit')) continue;
                        $authData[] = [
                            'name'=>$i.'?category_id='.$model->primaryKey,
                            'type'=>2,
                            'description'=>$item,
                            'created_at'=>time(),
                            'updated_at'=>time()
                        ];
                    }
                    if(!empty($authData)){
                        try{
                            Yii::$app->getDb()->createCommand()->batchInsert(AuthItemModel::tableName(),['name','type','description','created_at','updated_at'],$authData)->execute();
                        } catch(Exception $e){}
                    }

                    $this->delCategoryCache();

                    $this->success([Yii::t('common','Operation successful'),'updateUrl'=>UrlHelper::toRoute(['update','id'=>$model->primaryKey])]);
                }
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrors()]);
        }

        $model->loadDefaultValues();
        return $this->render('create', [
            'model' => $model,
        ]);
    }

	/**
	 * Updates an existing FragmentCategoryModel model.
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

        if(Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post())) {
                if($model->save()){
                    $this->delCategoryCache();
                    $this->success([Yii::t('common','Operation successful')]);
                }
            }
            $this->error([Yii::t('common','Operation failed')]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * @param int|string $id
     * @return mixed|void
     */
    public function actionStatus($id){}

	/**
	 * 数据排序
	 *
	 * @param int|null $id
	 * @param int|null $mode 0|1
	 *
	 * @return mixed|void
	 * @throws \yii\base\Exception
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
                $this->delCategoryCache();
                $this->success([Yii::t('common','Operation successful')]);
            }
            $this->error([Yii::t('common','Operation failed')]);
        }

        // 单排序
        if($id === null) $this->error(['操作失败','message'=>'缺少参数id']);
        $currData = $model->find()->where(['id'=>$id])->select(['id','sort'])->asArray()->one();

        $sign = $mode?'<':'>';
        $sort = $mode?['sort'=>SORT_DESC]:['sort'=>SORT_ASC];
        $previewData = $model->find()
            ->where([$sign,'sort',$currData['sort']])
            ->orderBy($sort)->select(['id','sort'])->asArray()->one();

        if($previewData){
            $db = Yii::$app->db;
            $sql = $db->createCommand()->update($model->tableName(),['sort'=>$currData['sort']],['id'=>$previewData['id']])->rawSql.';';
            $sql .= $db->createCommand()->update($model->tableName(),['sort'=>$previewData['sort']],['id'=>$currData['id']])->rawSql.';';

            if($db->createCommand($sql)->execute()){
                $this->delCategoryCache();
                $this->success([Yii::t('common','Operation successful')]);
            }
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

	/**
	 * Deletes an existing FragmentCategoryModel model.
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
        $id = explode(',',$id);

        $delAuth = [];
        foreach (FragmentCategoryModel::find()->where(['id'=>$id])->asArray()->all() as $item){
            if($item['type']){
                $delAuth[] = 'fragment/fragment/edit?category_id='.$item['id'];
            }else{
                foreach ($this->accessList as $i=>$v){
                    if($i == 'fragment/fragment/edit') continue;
                    $delAuth[] = $i.'?category_id='.$item['id'];
                }
            }
        }

        if($model->deleteAll(['id'=>$id])){
            // 删除权限
            if(!empty($delAuth)) AuthItemModel::deleteAll(['name'=>$delAuth]);

            $this->delCategoryCache();

            $this->success([Yii::t('common','Operation successful')]);
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

    /**
     * 返回栏目左侧导航菜单
     * @throws NotFoundHttpException
     */
    public function actionExpand_nav(){
        Yii::$app->response->format = Response::FORMAT_JSON;

        $category = $this->findCategory();
        // 权限过滤
        $userPermissionList = $this->isSuperAdmin?[]:Yii::$app->getAuthManager()->getPermissionsByUser(Yii::$app->getUser()->getId());
        $categoryList = [];
        foreach($category as $i=>$item){
            $item['type'] = intval($item['type']);
            if($this->isSuperAdmin || ($item['type'] === 0 && array_key_exists('fragment/fragment-list/index?category_id='.$item['id'],$userPermissionList)) || ($item['type'] === 1 && array_key_exists('fragment/fragment/edit?category_id='.$item['id'],$userPermissionList))){
                $categoryList[$item['id']] = $item;
            }
        }
        if($this->isSuperAdmin || array_key_exists('config/index?scope=custom',$userPermissionList)){
            $categoryList['config/index?scope=custom'] = UrlHelper::to(['/config/index','scope'=>'custom']);
        }

        return $this->renderPartial('expand_nav',['dataList'=>$categoryList]);
    }

    /**
     * Finds the FragmentCategoryModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return FragmentCategoryModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id = null)
    {
        $model = empty($id)? new FragmentCategoryModel():FragmentCategoryModel::findOne(['id'=>$id,'site_id'=>$this->siteInfo->id]);
        if($model !== null){
            return $model;
        }else{
            throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }
    }

    /**
     * 获取分类
     * @return array
     * @throws NotFoundHttpException
     */
    protected function findCategory(){
        return $this->findModel()->find()->where(['site_id'=>$this->siteInfo->id])->indexBy('id')->orderBy(['sort'=>SORT_ASC,'id'=>SORT_ASC])->asArray()->all();
    }

	/**
	 * 删除幻灯片分类缓存
	 * @throws \yii\base\Exception
	 */
    protected function delCategoryCache(){
	    DelCacheHelper::deleteCache('fragment');
    }
}