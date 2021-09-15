<?php

namespace manage\modules\prototype\controllers;

use common\components\CurdInterface;
use common\components\manage\ManageController;
use common\entity\models\AuthItemModel;
use common\entity\models\PrototypeModelModel;
use common\entity\models\SiteModel;
use common\entity\searches\PrototypeModelSearch;
use common\helpers\ArrayHelper;
use common\helpers\FileHelper;
use common\helpers\UrlHelper;
use manage\models\DelCacheHelper;
use Yii;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

/**
 * ModelController implements the CRUD actions for PrototypeModelModel model.
 */
class ModelController extends ManageController implements CurdInterface
{
    /**
     * @var array 模型类型列表
     */
    public $modelTypeList = [
        0 => '内容类型',
        1 => '表单类型',
        //2 =>'自由类型'
    ];

    /**
     * node权限列表
     * @var array
     */
    public $accessList = [];

    public function init()
    {
        parent::init();

        $this->accessList = Yii::$app->params['authListForm'];
    }

    /**
     * Lists all PrototypeModelModel models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PrototypeModelSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

	/**
	 * Creates a new PrototypeModelModel model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 * @throws NotFoundHttpException
	 * @throws \yii\base\Exception
	 */
    public function actionCreate()
    {
        $model = $this->findModel();
        if(Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post()) && $model->save()) {

                //插入权限
                if($model->type){
                    $authData = [];
                    $siteList = SiteModel::findSite();
                    foreach ($this->accessList as $i=>$item){
                        foreach ($siteList as $v){
                            $authData[] = [
                                'name'=>$i.'?site_id='.$v['id'].'&model_id='.$model->primaryKey,
                                'type'=>2,
                                'description'=>$item,
                                'created_at'=>time(),
                                'updated_at'=>time()
                            ];
                        }
                    }
                    if(!empty($authData)){
                        try{
                            Yii::$app->getDb()->createCommand()->batchInsert(AuthItemModel::tableName(),['name','type','description','created_at','updated_at'],$authData)->execute();
                        } catch(Exception $e){}
                    }
                    unset($authData);
                }

	            $this->delCategoryCache();

                $this->success([Yii::t('common','Operation successful'),'updateUrl'=>UrlHelper::toRoute(['update','id'=>$model->primaryKey])]);
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrors()]);
        }

        $model->loadDefaultValues();

        return $this->render('create', [
            'model' => $model,
        ]);
    }

	/**
	 * Updates an existing PrototypeModelModel model.
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
            if ($model->load(Yii::$app->request->post()) && $model->save()) {

                $this->delCategoryCache();

                $this->success([Yii::t('common','Operation successful')]);
            }
            $this->error([Yii::t('common','Operation failed')]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

	/**
	 * Deletes an existing PrototypeModelModel model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 * @throws Exception
	 * @throws NotFoundHttpException
	 * @throws \Throwable
	 * @throws \yii\base\Exception
	 * @throws \yii\db\StaleObjectException
	 */
    public function actionDelete($id)
    {
        $modelPath = Yii::$app->getBasePath().'/../common/entity/nodes';
        $formPath = Yii::$app->getBasePath().'/modules/prototype/views/form';
        $nodePath = Yii::$app->getBasePath().'/modules/prototype/views/node';

        if(!is_writable($modelPath) || !is_writable($formPath) || !is_writable($nodePath)){
            $this->error(['没有权限操作模型文件夹。']);
        }

        $model = $this->findModel($id);

        // 删除表
        $relationName = [];
        if(!empty($model->setting)){
            $model->setting = json_decode($model->setting,true);
            foreach (ArrayHelper::getValue($model->setting,'delRelationField',[]) as $item){
                if($item['relationType']){
                    $relationName[] = $item['modelName'];
                }
            }
        }

        foreach ($model->fields as $item){
            $item->setting = empty($item->setting)?[]:json_decode($item->setting,true);
            $item->history = empty($item->history)?[]:json_decode($item->history,true);
            if(!empty($item->history)){
                $historySetting = empty($item->history['setting'])?[]:$item->history['setting'];
            }else{
                $historySetting = [];
            }
            if(ArrayHelper::getValue($item->setting,'relationType')){
                $relationName[] = ArrayHelper::getValue($item->setting,'modelName');
            }
            if(ArrayHelper::getValue($historySetting,'relationType')){
                $relationName[] = ArrayHelper::getValue($historySetting,'modelName');
            }
        }

        $db = Yii::$app->getDb();
        foreach (array_unique($relationName) as $item){
            if(file_exists($modelPath.'/'.ucwords($model->name).ucwords($item).'RelationModel.php')){
                $db->createCommand("DROP TABLE IF EXISTS `".$db->tablePrefix."node_".$model->name."_".$item."_relation`;")->execute();
	            FileHelper::unlink($modelPath.'/'.ucwords($model->name).ucwords($item).'RelationModel.php');
            }
        }

        if($model->delete()){
            // 删除文件
            if($model->type === 0){
	            FileHelper::unlink($nodePath.'/_form_'.$model->name.'.php');
	            FileHelper::unlink($nodePath.'/_list_'.$model->name.'.php');
            }else{
	            FileHelper::unlink($formPath.'/index_'.$model->name.'.php');
	            FileHelper::unlink($formPath.'/view_'.$model->name.'.php');
            }
	        FileHelper::unlink($modelPath.'/'.ucwords($model->name).'Model.php');
	        FileHelper::unlink($modelPath.'/'.$model->name.'Search.php');

            $db->createCommand("SET FOREIGN_KEY_CHECKS=0;DROP TABLE IF EXISTS `".$db->tablePrefix."node_".$model->name."`;SET FOREIGN_KEY_CHECKS=1;")->execute();


            // 删除权限
            $delAuth = [];
            $siteList = SiteModel::findSite();
            foreach ($this->accessList as $i=>$item){
                foreach ($siteList as $v){
                    $delAuth[] = $i.'?site_id='.$v['id'].'&model_id='.$id;
                }
            }
            if(!empty($delAuth)) AuthItemModel::deleteAll(['name'=>$delAuth]);
            unset($siteList,$delAuth);

            $this->delCategoryCache();
            $this->success([Yii::t('common','Operation successful')]);
        }else{
            $this->error(['存在外键约束，无法删除此模型。']);
        }
    }

    /**
     * Finds the PrototypeModelModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PrototypeModelModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id = null)
    {
        $model = empty($id)? new PrototypeModelModel():PrototypeModelModel::findOne($id);
        if($model !== null){
            return $model;
        }else{
            throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }
    }

    /**
     * @param int|string $id
     * @return mixed|void
     */
    public function actionStatus($id){}

    /**
     * @param int|string $id
     * @return mixed|void
     */
    public function actionSort($id){}


	/**
	 * 生成模型
	 *
	 * @param $id
	 *
	 * @throws NotFoundHttpException
	 */
    public function actionGenerate($id){
        $model = $this->findModel($id);
        if($model->generate()){
            $this->success([Yii::t('common','Operation successful')]);
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

	/**
	 * 删除栏目缓存
	 * @throws \yii\base\Exception
	 */
    protected function delCategoryCache(){
	    DelCacheHelper::deleteCache(['model','category']);
    }
}