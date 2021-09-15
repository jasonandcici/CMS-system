<?php

namespace manage\modules\prototype\controllers;

use common\components\CurdInterface;
use common\components\manage\ManageController;
use common\entity\models\AuthItemChildModel;
use common\entity\models\AuthItemModel;
use common\entity\models\PrototypeCategoryModel;
use common\entity\models\PrototypeModelModel;
use common\entity\models\PrototypePageModel;
use common\entity\models\SystemLogModel;
use common\entity\searches\PrototypeCategorySearch;
use common\helpers\ArrayHelper;
use common\helpers\UrlHelper;
use Exception;
use manage\models\DelCacheHelper;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * CategoryController implements the CRUD actions for PrototypeCategoryModel model.
 */
class CategoryController extends ManageController implements CurdInterface
{
    /**
     * @var array 菜单类型列表
     */
    public $categoryTypeList = [
        0 => '数据列表',
        1 => '单网页',
        2 => '自由页',
        3 => '外部链接'
    ];

    /**
     * node权限列表
     * @var array
     */
    public $accessList = [];

    public function init()
    {
        parent::init();

        $this->accessList = Yii::$app->params['authListNode'];
    }

    /**
     * Lists all PrototypeCategoryModel models.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionIndex()
    {
        $searchModel = new PrototypeCategorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['site_id'=>$this->siteInfo->id])->with(['model'])->asArray();

        $userAccessButton = [
            'create'=>false,
            'update'=>false,
            'delete'=>false,
            'sort'=>false,
            'status'=>false,
        ];
        $userAccessList = Yii::$app->getAuthManager()->getPermissionsByUser(Yii::$app->getUser()->getId());
        foreach ($userAccessButton as $i=>$item){
            if($this->isSuperAdmin || array_key_exists('prototype/category/'.$i.'?site_id='.$this->siteInfo->id,$userAccessList)){
                $userAccessButton[$i] = true;
            }
        }
        unset($userAccessList);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'categoryList'=>$this->getCategory(),
            'modelList'=>$this->getModel(),
            'pid'=>Yii::$app->request->get('pid',0),
            'userAccessButton'=>$userAccessButton
        ]);
    }

    /**
     * Creates a new PrototypeCategoryModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param int $type
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionCreate($type = 0)
    {
        $model = $this->findModel();
        $model->type = intval($type);
        $model->setScenario('type'.$model->type);

        $hasSetRoleAuth = true;
        //$hasSetRoleAuth = $this->isSuperAdmin || Yii::$app->getUser()->can('auth/role-auth');

        if(Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post())) {
                $model->site_id = $this->siteInfo->id;
                if(is_array($model->expand)){
                    $model->expand = json_encode($model->expand);
                }else{
                    $model->expand = null;
                }
                if($model->save()){
                    $model->sort = $model->primaryKey;
                    $model->save();

                    // 生成单页
                    $model->type = intval($model->type);
                    if($model->type == 1){
                        $pageModel = new PrototypePageModel();
                        $pageModel->category_id = $model->primaryKey;
                        $pageModel->title = $model->title;
                        $pageModel->save();
                    }


                    // 插入权限
                    if($model->type < 2){
                        $authData = [];
                        foreach ($this->accessList as $i=>$item){
                            if(($model->type === 0 && $i == 'prototype/node/page') || ($model->type === 1 && $i != 'prototype/node/page')) continue;
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
                    }

                    // 设置权限
                    if($hasSetRoleAuth) $this->setAuth(Yii::$app->getRequest()->post('auth',[]),$model);


                    // 删除栏目缓存
                    $this->delCategoryCache();

                    SystemLogModel::create('create','新增栏目“'.$model->title."”");

                    $this->success([Yii::t('common','Operation successful'),'updateUrl'=>UrlHelper::toRoute(['update','id'=>$model->primaryKey])]);
                }
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrorString()]);
        }

        $currUserRoles = [];
        if(!$this->isSuperAdmin){
            $currUserRoles = Yii::$app->getAuthManager()->getRolesByUser(Yii::$app->getUser()->getId());
        }


        $pid = Yii::$app->request->get('pid',0);
        if(Yii::$app->getRequest()->get('action') == 'copy'){
            $model = $this->findModel($pid);
            if($model->slug) $model->slug = $model->slug.'-copy';
            if(!empty($model->expand)){
                $model->expand = json_decode($model->expand);
            }
        }else{
            $model->pid = $pid;
            $model->expand = ArrayHelper::convertToObject(['enable_detail'=>1,'enable_admin'=>1]);
        }

        return $this->render('create', [
            'model' => $model,
            'categoryList'=>$this->getCategory(),
            'allCategoryList'=>$this->getCategory(true,false),
            'modelList'=>$this->getModel(),
            'roleList'=>$this->findRole(),
            'auth'=>[],
            'currUserRoles'=>$currUserRoles,
            'hasSetRoleAuth'=>$hasSetRoleAuth
        ]);
    }

    /**
     * Updates an existing PrototypeCategoryModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->type = intval($model->type);
        $model->setScenario('type'.$model->type);

        $hasSetRoleAuth = $this->isSuperAdmin || Yii::$app->getUser()->can('auth/role-auth');

        if(Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post())){
                if(is_array($model->expand)){
                    $model->expand = json_encode($model->expand);
                }else{
                    $model->expand = null;
                }

                if($model->save()){
                    // todo::栏目类型修改
                    // todo::模型修改造成已存在数据的切换bug

                    // 设置权限
                    if($hasSetRoleAuth){
                        $model->type = intval($model->type);
                        $this->setAuth(Yii::$app->getRequest()->post('auth',[]),$model);
                    }

                    // 删除栏目缓存
                    $this->delCategoryCache();

                    SystemLogModel::create('update','编辑了栏目“'.$model->title."”");

                    $this->success([Yii::t('common','Operation successful')]);
                }
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrorString()]);
        }

        if(!empty($model->expand)){
            $model->expand = json_decode($model->expand);
        }

        $authList = $hasSetRoleAuth?$this->findAuthList($model):[];

        return $this->render('update', [
            'model' => $model,
            'categoryList'=>$this->getCategory(),
            'allCategoryList'=>$this->getCategory(true,false),
            'modelList'=>$this->getModel(),
            'roleList'=>$this->findRole(),
            'auth'=>$authList,
            'currUserRoles'=>[],
            'hasSetRoleAuth'=>$hasSetRoleAuth
        ]);
    }

    /**
     * Deletes an existing PrototypeCategoryModel model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $title = $model->title;

        // 获取子菜单
        $categoryList = $this->getCategory();

        $children = ArrayHelper::getChildes($categoryList,$id);
        if(empty($children)) $children[] = ArrayHelper::toArray($model);

        $ids = [];
        $delAuth = [];
        foreach($children as $item){
            $ids[] = $item['id'];
            if($item['type'] === 0){
                foreach ($this->accessList as $i=>$v){
                    if($i == 'prototype/node/page') continue;
                    $delAuth[] = $i.'?category_id='.$item['id'];
                }
            }elseif ($item['type'] === 1){
                $delAuth[] = 'prototype/node/page?category_id='.$item['id'];
            }
        }

        if($model->deleteAll(['id'=>$ids])){
            // 删除权限
            if(!empty($delAuth)) AuthItemModel::deleteAll(['name'=>$delAuth]);

            // 删除栏目缓存
            $this->delCategoryCache();

            SystemLogModel::create('delete','删除栏目“'.$title."”及其子栏目");

            $this->success([Yii::t('common','Operation successful'),'jumpLink'=>'javascript:history.go(0)']);
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

    /**
     * 状态设置
     * @param int|string $id
     * @return mixed|void
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     */
    public function actionStatus($id){
        $model = $this->findModel();
        $id = explode(',',$id);

        if($model->updateAll(['status'=>Yii::$app->request->get('value',0)],['id'=>$id])){

            // 删除栏目缓存
            $this->delCategoryCache();

            SystemLogModel::create('update','更新了栏目状态');

            $this->success([Yii::t('common','Operation successful')]);
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

    /**
     * 数据排序
     * @return mixed|void
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     * @throws \yii\base\Exception
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
                $this->delCategoryCache();

                SystemLogModel::create('update','对栏目进行了排序');

                $this->success([Yii::t('common','Operation successful')]);
            }
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

    /**
     * 返回栏目左侧导航菜单
     * @param bool $render
     * @return array|string
     * @throws NotFoundHttpException
     */
    public function actionExpand_nav($render = true){
        Yii::$app->response->format = Response::FORMAT_JSON;

        $dataList = $this->getCategory(false);
        $newDataList = [];
        foreach ($dataList as $item){
            $item['expand'] = empty($item['expand'])?[]:json_decode($item['expand'],true);
            $enableAdmin = ArrayHelper::getValue($item['expand'],'enable_admin');
            $enableAdmin = $enableAdmin === null?1:intval($enableAdmin);
            if(!$enableAdmin) continue;
            $newDataList[] = $item;
        }
        unset($dataList);

        if($render){
            return $this->renderPartial('expand_nav',['dataList'=>ArrayHelper::tree($newDataList)]);
        }else{
            return $newDataList;
        }
    }

    /**
     * Finds the PrototypeCategoryModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PrototypeCategoryModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id = null)
    {
        $model = empty($id)? new PrototypeCategoryModel():PrototypeCategoryModel::findOne(['id'=>$id,'site_id'=>$this->siteInfo->id]);
        if($model !== null){
            return $model;
        }else{
            throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }
    }

    /**
     * 获取模型列
     * @return array|\common\entity\domains\PrototypeModelDomain[]
     */
    protected function getModel(){
        return PrototypeModelModel::find()->where(['type'=>[0,2]])->all();
    }

	/**
	 * 获取菜单列
	 *
	 * @param bool $titleHandle 是否对标题进行处理
	 * @param bool $isAuth
	 *
	 * @return array
	 * @throws NotFoundHttpException
	 */
    protected function getCategory($titleHandle = true,$isAuth = true){
        $category = Yii::$app->cache->get('category'.$this->siteInfo->id);
        if($category == null){
            $category = $this->findModel()->find()->where(['site_id'=>$this->siteInfo->id])->indexBy('id')->with(['model'])->orderBy(['sort'=>SORT_ASC,'id'=>SORT_ASC])->asArray()->all();
            Yii::$app->cache->set('category'.$this->siteInfo->id,$category);
        }

        // 权限控制栏目显示
        $userPermissionList = $this->isSuperAdmin?[]:Yii::$app->getAuthManager()->getPermissionsByUser(Yii::$app->getUser()->getId());
        $categoryList = [];
        foreach($category as $i=>$item){
            if($this->isSuperAdmin || ($item['type'] == 0 && array_key_exists('prototype/node/index?category_id='.$item['id'],$userPermissionList)) || ($item['type'] == 1 && array_key_exists('prototype/node/page?category_id='.$item['id'],$userPermissionList)) || $item['type'] == 2 || $item['type'] == 3){
                $categoryList[$item['id']] = $item;
            }
        }
        unset($category);

        $categoryList =  ArrayHelper::linear($categoryList,' ├ ');
        if($titleHandle){
            foreach($categoryList as $i=>$item){
                $categoryList[$i]['title'] = $item['str'].$item['title'];
            }
        }
        return $categoryList;
    }

    /**
     * 删除栏目缓存
     * @throws \yii\base\Exception
     */
    protected function delCategoryCache(){
        $cacheName = 'category';

        Yii::$app->cache->delete($cacheName.$this->siteInfo->id);

	    DelCacheHelper::deleteCache($cacheName);
    }

    /**
     * 查找角色列表
     * @return array|\yii\db\ActiveRecord[]
     */
    protected function findRole(){
        return AuthItemModel::find()->where(['type'=>1])->all();
    }

    /**
     * 查找当前栏目的权限
     * @param $categoryInfo
     * @return array|\yii\db\ActiveRecord[]
     */
    protected function findAuthList($categoryInfo){
        $list = [];
        if($categoryInfo->type === 0){
            foreach ($this->accessList as $i=>$v){
                if($i == 'prototype/node/page') continue;
                $list[] = $i.'?category_id='.$categoryInfo->id;
            }
        }elseif ($categoryInfo->type === 1){
            $list[] = 'prototype/node/page?category_id='.$categoryInfo->id;
        }

        $authList = [];
        foreach (AuthItemChildModel::find()->where(['child'=>$list])->all() as $item){
            $authList[] = $item->parent.','.$item->child;
        }

        return $authList;
    }

    /**
     * 设置权限
     * @param $cAuth
     * @param $model
     * @param bool $isUpdate
     * @return bool
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    protected function setAuth($cAuth,$model){
        if($model->type > 1 || empty($cAuth)) return true;

        foreach ($cAuth as $i=>$item){
            $cAuth[$i] = $item.'?category_id='.$model->id;
        }

        $authList = $this->findAuthList($model);

        $commonData = array_intersect($authList,$cAuth);
        $db = Yii::$app->getDb();
        $sql = '';
        $auth = Yii::$app->authManager;

        // 删除取消的
        $delData = array_diff($authList,$commonData);

        foreach ($delData as $item){
            $tmp = explode(',',$item);
            $sql .= $db->createCommand()->delete(AuthItemChildModel::tableName(),['parent'=>$tmp[0],'child'=>$tmp[1]])->rawSql.';';
        }
        unset($delData);
        if(!empty($sql)) $db->createCommand($sql)->execute();

        // 新增勾选的
        $insert = array_diff($cAuth,$commonData);
        if(!empty($insert)){
            $permissions = ArrayHelper::index($auth->getPermissions(),'name');
            foreach ($insert as $item){
                $tmp = explode(',',$item);
                $role = $auth->getRole($tmp[0]);
                if(array_key_exists($tmp[1],$permissions))
                    $auth->addChild($role,$permissions[$tmp[1]]);
            }
        }
    }
}
