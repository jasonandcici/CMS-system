<?php

namespace manage\controllers;

use common\components\CurdInterface;
use common\components\manage\ManageController;
use common\entity\models\SystemMenuModel;
use common\entity\searches\SystemMenuSearch;
use common\helpers\ArrayHelper;
use common\helpers\UrlHelper;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * MenuController implements the CRUD actions for SystemMenuModel model.
 */
class MenuController extends ManageController implements CurdInterface
{
    /**
     * @var array 菜单类型列表
     */
    public $menuTypeList = [
        '站内链接',
        '文件夹',
        '动态菜单'
    ];

    /**
     * Lists all SystemMenuModel models.
     * @return mixed
     */
    public function actionIndex()
    {

        $searchModel = new SystemMenuSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->asArray();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'menuList'=>$this->getMenu(),
            'pid'=>Yii::$app->request->get('pid',0)
        ]);
    }

    /**
     * Creates a new SystemMenuModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = $this->findModel();
        if(Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $model->sort = $model->primaryKey;
                $model->save();
                $this->success([Yii::t('common','Operation successful'),'updateUrl'=>UrlHelper::toRoute(['update','id'=>$model->primaryKey])]);
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrors()]);
        }

        $model->pid = Yii::$app->request->get('pid',0);
        return $this->render('create', [
            'model' => $model,
            'menuList'=>$this->getMenu(),
        ]);
    }

    /**
     * Updates an existing SystemMenuModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if(Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $this->success([Yii::t('common','Operation successful')]);
            }
            $this->error([Yii::t('common','Operation failed')]);
        }

        return $this->render('update', [
            'model' => $model,
            'menuList'=>$this->getMenu(),
        ]);
    }

    /**
     * Deletes an existing SystemMenuModel model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // 获取子菜单
        $ids = ArrayHelper::getChildesId($this->getMenu(),$id);
        $ids[] = $id;

        if($model->deleteAll(['id'=>$ids])){
            $this->success([Yii::t('common','Operation successful'),'jumpLink'=>'javascript:history.go(0)']);
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

    /**
     * 状态设置
     * @param int|string $id
     * @return mixed|void
     */
    public function actionStatus($id){
        $model = $this->findModel();
        $id = explode(',',$id);

        if($model->updateAll(['status'=>Yii::$app->request->get('value',0)],['id'=>$id])){
            $this->success([Yii::t('common','Operation successful')]);
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

    /**
     * 数据排序
     * @return mixed|void
     */
    public function actionSort(){
        $model = $this->findModel();

        // 批量排序
        if(Yii::$app->getRequest()->getIsPost()){
            $postData = json_decode(Yii::$app->getRequest()->post('data'));
            $db = Yii::$app->db;
            $sql = '';
            foreach ($postData as $item){
                $sql .= $db->createCommand()->update($model->tableName(),['sort'=>$item->sort,'pid'=>$item->pid],['id'=>$item->id])->rawSql.';';
            }
            if($sql){
                $db->createCommand($sql)->execute();
                $this->success([Yii::t('common','Operation successful')]);
            }
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

    /**
     * Finds the SystemMenuModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SystemMenuModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id = null)
    {
        $model = empty($id)? new SystemMenuModel():SystemMenuModel::findOne($id);
        if($model !== null){
            return $model;
        }else{
            throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }
    }

    /**
     * 获取所有菜单
     * @return array|\common\entity\domains\SystemMenuDomain[]
     * @throws NotFoundHttpException
     */
    protected function getMenu(){
        $list = ArrayHelper::linear($this->findModel()->find()->asArray()->all(),' ├ ');
        foreach($list as $i=>$item){
            $list[$i]['title'] = $item['str'].$item['title'];
        }
        return $list;
    }
}
