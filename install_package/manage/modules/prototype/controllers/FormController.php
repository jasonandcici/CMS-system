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
 * 表单原型节点
 */

namespace manage\modules\prototype\controllers;

use common\components\CurdInterface;
use common\components\manage\ManageController;
use common\entity\models\PrototypeModelModel;
use common\entity\models\SystemLogModel;
use common\entity\models\UserRelationModel;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class FormController extends ManageController implements CurdInterface
{

    /**
     * @var object 表单模型信息
     */
    public $modelInfo;

    /**
     * 初始化
     */
    public function init()
    {
        parent::init();

        $this->modelInfo = $this->getModelInfo(Yii::$app->getRequest()->get('model_id'));
    }

    /**
     * 数据列表
     * @param bool $export
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex($export = false){
        $modelName = '\\common\\entity\\nodes\\'.ucwords($this->modelInfo->name).'Search';
        $searchModel = new $modelName();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['site_id'=>$this->siteInfo->id]);

        $dataProvider->sort = [
            'defaultOrder' => [
                'id'=> SORT_DESC,
            ]
        ];

        if($export){
            $dataProvider->pagination = ['pageSize'=>0];
        }else{
            $dataProvider->pagination = ['pageSize'=>array_key_exists('page_size',Yii::$app->params)?Yii::$app->params['page_size']:15];
        }


        $userAccessButton = [
            'view'=>false,
            'delete'=>false,
            'status'=>false,
        ];

        if($export){
            $render = 'renderPartial';
        }else{
            $render = 'render';

            $userAccessList = Yii::$app->getAuthManager()->getPermissionsByUser(Yii::$app->getUser()->getId());
            foreach ($userAccessButton as $i=>$item){
                if($this->isSuperAdmin || array_key_exists('prototype/form/'.$i.'?site_id='.$this->siteInfo->id.'&model_id='.$this->modelInfo->id,$userAccessList)){
                    $userAccessButton[$i] = true;
                }
            }
            unset($userAccessList);
        }

        return $this->$render('index_'.$this->modelInfo->name, [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'formModel'=>$this->findModel($this->modelInfo->name),
            'userAccessButton'=>$userAccessButton
        ]);
    }

    /**
     * 数据详情
     * @param $id
     * @param string $layout
     * @return string
     */
    public function actionView($id,$layout = 'main'){
        $model = $this->findModel($this->modelInfo->name,$id);

        $this->layout = '/'.$layout;

        return $this->render('view_'.$this->modelInfo->name, [
            'model' => $model,
            'formModel'=>$this->findModel($this->modelInfo->name)
        ]);
    }

    /**
     * 状态
     * @param int|string $id
     * @return mixed|void
     */
    public function actionStatus($id){
        $model = $this->findModel($this->modelInfo->name);
        $id = explode(',',$id);

        if($model->updateAll(['status'=>Yii::$app->request->get('value',0)],['id'=>$id])){

            SystemLogModel::create('update','更新了“'.$this->modelInfo->name.'”数据状态');

            $this->success([Yii::t('common','Operation successful'),'jumpLink'=>'javascript:void(history.go(0));']);
        }
        $this->error([Yii::t('common','Operation failed'),'jumpLink'=>'javascript:void(history.go(0));']);
    }

    /**
     * 删除
     * @param int|string $id
     * @return mixed|void
     * @throws NotFoundHttpException
     */
    public function actionDelete($id){
        $model = $this->findModel($this->modelInfo->name);
        $ids = explode(',',$id);

        if($model->deleteAll(['id'=>$ids])){

            UserRelationModel::deleteAll(['user_model_id'=>$this->modelInfo->id,'user_data_id'=>$ids]);

            SystemLogModel::create('delete','在表单“'.$this->modelInfo->title.'”下删除了Id分别为“'.$id.'”的内容');

            $this->success([Yii::t('common','Operation successful')]);
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

    /**
     * 查找一个模型
     * @param $modelName
     * @param null $id
     * @return null|static
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
     * 左侧扩展菜单
     */
    public function actionExpand_nav(){
        Yii::$app->response->format = Response::FORMAT_JSON;

        $dataList = PrototypeModelModel::find()->where(['type'=>1])->asArray()->all();

        $userPermissionList = $this->isSuperAdmin?[]:Yii::$app->getAuthManager()->getPermissionsByUser(Yii::$app->getUser()->getId());
        $categoryList = [];
        foreach($dataList as $i=>$item){
            if($this->isSuperAdmin || array_key_exists('prototype/form/index?site_id='.$this->siteInfo->id.'&model_id='.$item['id'],$userPermissionList)){
                $categoryList[] = $item;
            }
        }
        unset($dataList);

        return $this->renderPartial('expand_nav',['dataList'=>$categoryList]);
    }

    /**
     * 返回模型信息
     * @param $modelId
     * @return PrototypeModelModel
     */
    protected function getModelInfo($modelId){
        return PrototypeModelModel::findOne($modelId);
    }

    public function actionCreate()
    {

    }

    public function actionUpdate($id)
    {
        // TODO: Implement actionUpdate() method.
    }

    public function actionSort($id)
    {
        // TODO: Implement actionSort() method.
    }
}