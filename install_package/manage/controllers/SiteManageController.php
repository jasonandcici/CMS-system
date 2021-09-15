<?php
/**
 * @copyright
 * @link 
 * @create Created on 2016/12/20
 */

namespace manage\controllers;
use common\components\manage\ManageController;
use common\entity\models\AuthItemModel;
use common\entity\models\FragmentCategoryModel;
use common\entity\models\FragmentListModel;
use common\entity\models\FragmentModel;
use common\entity\models\PrototypeCategoryModel;
use common\entity\models\PrototypeModelModel;
use common\entity\models\PrototypePageModel;
use common\entity\models\SiteModel;
use common\entity\models\SystemLogModel;
use common\helpers\ArrayHelper;
use common\helpers\SystemHelper;
use common\helpers\UrlHelper;
use manage\models\DelCacheHelper;
use Yii;
use yii\db\Exception;
use yii\web\NotFoundHttpException;


/**
 * 站点管理
 *
 * @author 
 * @since 1.0
 */
class SiteManageController extends ManageController
{
    /**
     * 站点列表
     */
    public function actionIndex(){
        $model = $this->findModel();

        $userAccessButton = [
            'set-default'=>false,
            'update'=>false,
            'status'=>false,
        ];
        $userAccessList = Yii::$app->getAuthManager()->getPermissionsByUser(Yii::$app->getUser()->getId());
        foreach ($userAccessButton as $i=>$item){
            if($this->isSuperAdmin || array_key_exists('site-manage/'.$i,$userAccessList)){
                $userAccessButton[$i] = true;
            }
        }
        unset($userAccessList);

        return $this->render('index',[
            'dataList'=>$model::find()->all(),
            'userAccessButton'=>$userAccessButton
        ]);
    }

    /**
     * node权限列表
     * @var array
     */
    public $accessList = [
        'import/prototype'=>'批量导入数据',
    ];

    public $formAccessList = [];

    public function init()
    {
        parent::init();

        $this->accessList = ArrayHelper::merge($this->accessList,Yii::$app->params['authListCategory']);

        $this->formAccessList = Yii::$app->params['authListForm'];
    }

	/**
	 * Creates a new SystemRoleModel model.
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

                // 插入权限
                $authData = [];
                foreach ($this->accessList as $i=>$item){
                    $authData[] = [
                        'name'=>$i.'?site_id='.$model->primaryKey,
                        'type'=>2,
                        'description'=>$item,
                        'created_at'=>time(),
                        'updated_at'=>time()
                    ];
                }

                $modelList = PrototypeModelModel::find()->where(['type'=>1])->asArray()->all();
                foreach ($modelList as $item){
                    foreach ($this->formAccessList as $i=>$v){
                        $authData[] = [
                            'name'=>$i.'?site_id='.$model->primaryKey.'&model_id='.$item['id'],
                            'type'=>2,
                            'description'=>$v,
                            'created_at'=>time(),
                            'updated_at'=>time()
                        ];
                    }
                }
                unset($modelList,$formActions);

                if(!empty($authData)){
                    try{
                        Yii::$app->getDb()->createCommand()->batchInsert(AuthItemModel::tableName(),['name','type','description','created_at','updated_at'],$authData)->execute();
                    } catch(Exception $e){}
                }
                unset($authData);

                $this->deleteCache();
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
	 * Updates an existing SystemRoleModel model.
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
                $this->deleteCache();

                SystemLogModel::create('update','更新了站点“'.$model->title.'”信息');

                $this->success([Yii::t('common','Operation successful')]);
            }
            $this->error([Yii::t('common','Operation failed')]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

	/**
	 * Deletes an existing SystemRoleModel model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 *
	 * @param integer $id
	 *
	 * @return mixed
	 * @throws NotFoundHttpException
	 * @throws \Throwable
	 * @throws \yii\db\StaleObjectException
	 */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if(!$model->is_default && $this->siteInfo->id != $model->id){
            $categoryList = PrototypeCategoryModel::findCategory($model->id);
            $fragmentCategoryList = FragmentCategoryModel::find()->where(['site_id'=>$model->id])->asArray()->all();
            $modelList = PrototypeModelModel::find()->where(['type'=>1])->asArray()->all();


            if($model->delete()){

                // 删除权限
                $delAuth = [];
                foreach ($this->accessList as $i=>$item){
                    $delAuth[] = $i.'?site_id='.$model->primaryKey;
                }

                foreach ($categoryList as $item){
                    $item['type'] = intval($item['type']);
                    if($item['type'] > 1) continue;

                    if($item['type'] === 0){
                        foreach (Yii::$app->params['authListNode'] as $i=>$v){
                            if($i == 'prototype/node/page') continue;
                            $delAuth[] = $i.'?category_id='.$item['id'];
                        }
                    }elseif ($item['type'] === 1){
                        $delAuth[] = 'prototype/node/page?category_id='.$item['id'];
                    }
                }
                unset($categoryList);

                foreach ($fragmentCategoryList as $item){
                    $item['type'] = intval($item['type']);
                    if($item['type']){
                        $delAuth[] = 'fragment/fragment/edit?category_id='.$item['id'];
                    }else{
                        foreach (Yii::$app->params['authListFragment'] as $i=>$v){
                            if($i == 'fragment/fragment/edit') continue;
                            $delAuth[] = $i.'?category_id='.$item['id'];
                        }
                    }
                }
                unset($fragmentCategoryList);

                foreach ($modelList as $item){
                    foreach ($this->formAccessList as $i=>$v){
                        $delAuth[] = $i.'?site_id='.$id.'&model_id='.$item['id'];
                    }
                }
                unset($modelList);
                if(!empty($delAuth)) AuthItemModel::deleteAll(['name'=>$delAuth]);


                $this->run('site/clear-cache',['isReturn'=>false]);

                $this->success([Yii::t('common','Operation successful')]);
            }else{
                $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrorString()]);
            }
        }
        $this->error([Yii::t('common','Operation failed'),'message'=>'默认站点不可删除。']);
    }

	/**
	 * 设置默认站点
	 *
	 * @param $id
	 *
	 * @throws \yii\base\Exception
	 */
    public function actionSetDefault($id){
        $model = $this->findModel();
        $model::updateAll(['is_default'=>0]);
        $model::updateAll(['is_default'=>1],['id'=>$id]);

        Yii::$app->getSession()->set('siteInfo',SiteModel::find()->where(['id'=>$this->siteInfo->id])->asArray()->one());

        $this->deleteCache();
        $this->success([Yii::t('common','Operation successful')]);
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

        if($model->updateAll(['is_enable'=>Yii::$app->request->get('value',0)],['id'=>$id])){
            $this->deleteCache();
            $this->success([Yii::t('common','Operation successful')]);
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

    /**
     * Finds the SystemRoleModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SiteModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id = null)
    {
        $model = empty($id)? new SiteModel():SiteModel::findOne($id);
        if($model !== null){
            return $model;
        }else{
            throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }
    }

	/**
	 * 删除缓存
	 * @throws \yii\base\Exception
	 */
    public function deleteCache(){
	    DelCacheHelper::deleteCache('site');
    }

    /**
     * 复制一个站点
     * @param $id
     * @throws \yii\db\Exception
     * @throws NotFoundHttpException
     */
    public function actionCopy($id){
        $message = null;
        $db = Yii::$app->getDb();
        $transaction=$db->beginTransaction();
        try{
            $new = $this->findModel();
            $old = $this->findModel($id);
            $new->attributes = $old->attributes;
            $new->title = $new->title.'-副本';
            $new->slug = $new->slug.'-copy';
            $new->is_default = 0;
            $new->isNewRecord = true;
            if($new->save()){
                // 权限
                $time = time();
                $insertAuthData = [[
                    "name"=>'import/prototype?site_id='.$new->primaryKey,
                    "type"=>2,
                    "description"=>'批量导入数据',
                    "rule_name"=>null,
                    "data"=>null,
                    "created_at"=>$time,
                    "updated_at"=>$time,
                ]];
                foreach (Yii::$app->params['authListCategory'] as $k=>$v){
                    $insertAuthData[] = [
                        "name"=>$k.'?site_id='.$new->primaryKey,
                        "type"=>2,
                        "description"=>$v,
                        "rule_name"=>null,
                        "data"=>null,
                        "created_at"=>$time,
                        "updated_at"=>$time,
                    ];
                }
                foreach (PrototypeModelModel::find()->where(['type'=>1])->select(['id','type'])->asArray()->all() as $item){
                    foreach (Yii::$app->params['authListForm'] as $k=>$v){
                        $insertAuthData[] = [
                            "name"=>$k.'?site_id='.$new->primaryKey.'&model_id='.$item['id'],
                            "type"=>2,
                            "description"=>$v,
                            "rule_name"=>null,
                            "data"=>null,
                            "created_at"=>$time,
                            "updated_at"=>$time,
                        ];
                    }
                }

                // 复制栏目
                $categoryModel = new PrototypeCategoryModel();
                $categoryList = $categoryModel::find()->where(['site_id'=>$old->id])->joinWith('page')->orderBy(['id'=>SORT_ASC])->asArray()->all();
                $categoryPrimaryKey = SystemHelper::getTableAutoIncrement($categoryModel)+100;
                $newCategoryData = [];
                foreach ($categoryList as $item){
                    $item['id_old'] = $item['id'];
                    $item['id'] = $categoryPrimaryKey;
                    $item['site_id'] = $new->primaryKey;
                    $categoryPrimaryKey ++;
                    $newCategoryData[$item['id_old']] = $item;
                }
                $insertCategoryData = [];
                $insertSinglePageData = [];

                foreach ($newCategoryData as $item){
                    $item['sort'] = array_key_exists($item['sort'],$newCategoryData)?$newCategoryData[$item['sort']]['id']:$item['sort'];
                    $item['pid'] = !$item['pid']?0:$newCategoryData[$item['pid']]['id'];

                    if($item['type'] == 1){
                        $item['page']['category_id'] = $item['id'];
                        $insertSinglePageData[] = $item['page'];
                    }

                    unset($item['id_old'],$item['page']);
                    $insertCategoryData[] = $item;
                }
                unset($newCategoryData);
                $sql = '';
                if(!empty($categoryList)){
                    $sql = $db->createCommand()->batchInsert($categoryModel::tableName(),array_keys($insertCategoryData[0]),$insertCategoryData)->rawSql.';';
                    unset($categoryPrimaryKey,$categoryModel);
                    if(!empty($insertSinglePageData)) $sql .= $db->createCommand()->batchInsert(PrototypePageModel::tableName(),array_keys($insertSinglePageData[0]),$insertSinglePageData)->rawSql.';';
                    unset($insertSinglePageData);
                }
                unset($categoryList);
                if(!empty($sql) && !$db->createCommand($sql)->execute()){
                    $message = '复制站点下栏目时失败。';
                }

                // node操作权限
                foreach ($insertCategoryData as $item){
                    if($item['type']>1) continue;
                    foreach (Yii::$app->params['authListNode'] as $k=>$v){
                        if(($item['type'] && $k!='prototype/node/page') || (!$item['type'] && $k=='prototype/node/page')) continue;
                        $insertAuthData[] = [
                            "name"=>$k.'?category_id='.$item['id'],
                            "type"=>2,
                            "description"=>$v,
                            "rule_name"=>null,
                            "data"=>null,
                            "created_at"=>$time,
                            "updated_at"=>$time,
                        ];
                    }
                }
                unset($insertCategoryData);

                // 复制碎片栏目
                if($message === null){
                    $fragmentCategoryModel = new FragmentCategoryModel();
                    $fragmentCategoryList = $fragmentCategoryModel::find()->where(['site_id'=>$old->primaryKey])->orderBy(['id'=>SORT_ASC])->asArray()->all();
                    $fragmentCategoryPrimaryKey = SystemHelper::getTableAutoIncrement($fragmentCategoryModel)+10;
                    $newCategoryData = [];
                    foreach ($fragmentCategoryList as $i=>$item){
                        $item['id_old'] = $item['id'];
                        $fragmentCategoryList[$i]['id_new'] = $fragmentCategoryPrimaryKey;
                        $item['id'] = $fragmentCategoryPrimaryKey;
                        $item['site_id'] = $new->primaryKey;
                        $fragmentCategoryPrimaryKey ++;
                        $newCategoryData[$item['id_old']] = $item;
                    }
                    $insertFragmentCategoryData = [];
                    foreach ($newCategoryData as $item){
                        $item['sort'] = array_key_exists($item['sort'],$newCategoryData)?$newCategoryData[$item['sort']]['id']:$item['sort'];
                        unset($item['id_old']);
                        $insertFragmentCategoryData[] = $item;
                    }
                    unset($newCategoryData);
                    if(!empty($fragmentCategoryList) && !$db->createCommand()->batchInsert($fragmentCategoryModel::tableName(),array_keys($insertFragmentCategoryData[0]),$insertFragmentCategoryData)->execute()){
                        $message = '复制站点下碎片栏目时失败。';
                    }
                    unset($fragmentCategoryPrimaryKey,$fragmentCategoryModel);

                    // 碎片栏目权限
                    foreach ($insertFragmentCategoryData as $item){
                        foreach (Yii::$app->params['authListFragment'] as $k=>$v){
                            if(($item['type'] && $k!='fragment/fragment/edit') || (!$item['type'] && $k=='fragment/fragment/edit')) continue;
                            $insertAuthData[] = [
                                "name"=>$k.'?category_id='.$item['id'],
                                "type"=>2,
                                "description"=>$v,
                                "rule_name"=>null,
                                "data"=>null,
                                "created_at"=>$time,
                                "updated_at"=>$time,
                            ];
                        }
                    }
                    unset($insertFragmentCategoryData);

                    // 复制碎片数据
                    if($message === null){
                        $fragmentFieldCategory = [];
                        $fragmentListCategory = [];
                        $fragmentCategoryList = ArrayHelper::index($fragmentCategoryList,'id');
                        foreach ($fragmentCategoryList as $item){
                            if($item['type']){
                                $fragmentFieldCategory[] = $item['id'];
                            }else{
                                $fragmentListCategory[] = $item['id'];
                            }
                        }
                        $fragmentField = FragmentModel::find()->where(['category_id'=>$fragmentFieldCategory])->orderBy(['id'=>SORT_ASC])->asArray()->all();
                        unset($fragmentFieldCategory);

                        $fragmentPrimaryKey = SystemHelper::getTableAutoIncrement(new FragmentModel()) + 20;
                        $newFragmentData = [];
                        foreach ($fragmentField as $item){
                            $item['id_old'] = $item['id'];
                            $item['category_id'] = $fragmentCategoryList[$item['category_id']]['id_new'];
                            $item['id'] = $fragmentPrimaryKey;
                            $item['site_id'] = $new->primaryKey;
                            $fragmentPrimaryKey ++;
                            $newFragmentData[$item['id_old']] = $item;
                        }
                        $insertFragmentFieldData = [];
                        foreach ($newFragmentData as $item){
                            $item['sort'] = array_key_exists($item['sort'],$newFragmentData)?$newFragmentData[$item['sort']]['id']:$item['sort'];
                            unset($item['id_old'],$item['id_new']);
                            $insertFragmentFieldData[] = $item;
                        }
                        unset($newFragmentData,$fragmentPrimaryKey);


                        $fragmentList = FragmentListModel::find()->where(['category_id'=>$fragmentListCategory])->orderBy(['id'=>SORT_ASC])->asArray()->all();
                        unset($fragmentListCategory);
                        $fragmentPrimaryKey = SystemHelper::getTableAutoIncrement(new FragmentListModel()) + 20;
                        $newFragmentListData = [];
                        foreach ($fragmentList as $item){
                            $item['id_old'] = $item['id'];
                            $item['category_id'] = $fragmentCategoryList[$item['category_id']]['id_new'];
                            $item['id'] = $fragmentPrimaryKey;
                            $item['site_id'] = $new->primaryKey;
                            $fragmentPrimaryKey ++;
                            $newFragmentListData[$item['id_old']] = $item;
                        }
                        $insertFragmentListData = [];
                        foreach ($newFragmentListData as $item){
                            $item['sort'] = array_key_exists($item['sort'],$newFragmentListData)?$newFragmentListData[$item['sort']]['id']:$item['sort'];
                            unset($item['id_old'],$item['id_new']);
                            $insertFragmentListData[] = $item;
                        }
                        unset($newFragmentListData,$fragmentPrimaryKey);
                        unset($fragmentCategoryList);

                        if(!empty($insertFragmentFieldData) && !$db->createCommand()->batchInsert(FragmentModel::tableName(),array_keys($insertFragmentFieldData[0]),$insertFragmentFieldData)->execute()){
                            $message = '复制站点下碎片内容时失败。';
                        }
                        unset($insertFragmentFieldData);


                        if($message === null) {
                            if (!empty($insertFragmentListData) && !$db->createCommand()->batchInsert(FragmentListModel::tableName(), array_keys($insertFragmentListData[0]), $insertFragmentListData)->execute()) {
                                $message = '复制站点下碎片列表内容时失败。';
                            }
                            unset($insertFragmentListData);
                        }
                    }
                }

                // 复制权限节点
                if($message === null){
                    if(!$db->createCommand()->batchInsert(AuthItemModel::tableName(),array_keys($insertAuthData[0]),$insertAuthData)->execute()){
                        $message = '设置权限节点时失败。';
                    }
                }
            }else{
                $message = $new->getErrorString()?:'';
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
        }

        if($message === null){
            $this->run('site/clear-cache',['isReturn'=>false]);
            $this->success([Yii::t('common','Operation successful')]);
        }else{
            $this->error([Yii::t('common','Operation failed'),'message'=>$message]);
        }
    }
}