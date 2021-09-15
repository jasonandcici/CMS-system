<?php
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/6/30.
// +----------------------------------------------------------------------

/**
 * 授权管理
 */

namespace manage\controllers;

use common\components\manage\ManageController;
use common\entity\models\AuthItemModel;
use common\entity\models\FragmentCategoryModel;
use common\entity\models\PrototypeCategoryModel;
use common\entity\models\PrototypeModelModel;
use common\entity\models\SiteModel;
use common\entity\searches\SystemUserSearch;
use common\helpers\ArrayHelper;
use Yii;

class AuthController extends ManageController
{
    /**
     * 角色管理
     */
    public function actionRole(){
        $roleList = AuthItemModel::find()->where(['type'=>1])->all();

        $userAccessButton = [
            'role-auth'=>false,
            'role-create'=>false,
            'role-delete'=>false,
            'role-update'=>false,
        ];
        $userAccessList = Yii::$app->getAuthManager()->getPermissionsByUser(Yii::$app->getUser()->getId());
        foreach ($userAccessButton as $i=>$item){
            if($this->isSuperAdmin || array_key_exists('auth/'.$i,$userAccessList)){
                $userAccessButton[$i] = true;
            }
        }
        unset($userAccessList);

        return $this->render('role_index',[
            'roleList'=>$roleList,
            'userAccessButton'=>$userAccessButton
        ]);
    }

    /**
     * 新增角色
     * @return string
     */
    public function actionRoleCreate(){
        $model = new AuthItemModel();

        if(Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post())) {
                $auth = Yii::$app->authManager;
                $newRole = $auth->createRole($model->name);
                $newRole->description = $model->description;
                $newRole->data = $model->data;
                if($auth->add($newRole)){
                    // 设置用户角色
                    $this->roleSetUser(Yii::$app->getRequest()->post('user'),$newRole->name);

                    $this->success([Yii::t('common','Operation successful')]);
                }
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrors()]);
        }

        return $this->render('role_create', [
            'model' => $model,
            'userList'=>[],
        ]);
    }

    /**
     * 修改角色
     * @param $name
     * @return string
     */
    public function actionRoleUpdate($name){
        $model = new AuthItemModel();
        $auth = Yii::$app->authManager;

        if(Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post())) {

                $roleInfo = $auth->getRole($name);
                $roleInfo->description = $model->description;
                $roleInfo->data = $model->data;
                if($auth->update($name,$roleInfo)){

                    // 设置用户角色
                    $this->roleSetUser(Yii::$app->getRequest()->post('user'),$roleInfo->name);

                    $this->success([Yii::t('common','Operation successful')]);
                }
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrors()]);
        }

        return $this->render('role_update', [
            'model' => $model->findOne($name),
            'userList'=>$auth->getUserIdsByRole($name),
        ]);
    }

    /**
     * 删除角色
     * @param $name
     */
    public function actionRoleDelete($name){
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($name);
        if($auth->remove($role)){
            $this->success([Yii::t('common','Operation successful')]);
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

    /**
     * 为角色分配权限
     * @param $name
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionRoleAuth($name){

        $auth = Yii::$app->authManager;
        // 保存权限
        if(Yii::$app->request->isPost){
            $newData = Yii::$app->getRequest()->post('auth');
            $newData = $newData?explode(',',$newData):[];

            // 删除老权限
            $role = $auth->getRole($name);
            $auth->removeChildren($role);

            // 新增分配权限
            $permissions = ArrayHelper::index($auth->getPermissions(),'name');

            foreach($newData as $item){
                if(array_key_exists($item,$permissions))
                    $auth->addChild($role,$permissions[$item]);
            }
            $this->success([Yii::t('common','Operation successful')]);
        }

        // 权限列表
        $newAuthItemGroup = [
            ['id'=>1000001,'pid'=>0,'name'=>'站点管理','key'=>'site-manage/','auth'=>false,'checked'=>false],
            'category'=>['id'=>1000006,'pid'=>0,'name'=>'栏目管理','key'=>'prototype/category/','auth'=>false,'checked'=>false,'open'=>true],
            'content'=>['id'=>1000011,'pid'=>0,'name'=>'内容管理','key'=>'prototype/node/','auth'=>false,'checked'=>false,'open'=>true],
            'fragment'=>['id'=>1000016,'pid'=>0,'name'=>'碎片管理','key'=>'fragment/','auth'=>false,'checked'=>false,'open'=>true],
            'form'=>['id'=>1000021,'pid'=>0,'name'=>'表单管理','key'=>'prototype/form/','auth'=>false,'checked'=>false,'open'=>true],
            'import'=>['id'=>1000026,'pid'=>0,'name'=>'数据导入','key'=>'import/prototype','auth'=>false,'checked'=>false,'open'=>true],
            ['id'=>1000031,'pid'=>0,'name'=>'用户管理','key'=>'member/','auth'=>false,'checked'=>false],
            ['id'=>1000036,'pid'=>0,'name'=>'管理员管理','key'=>'user/','auth'=>false,'checked'=>false],
            ['id'=>1000041,'pid'=>0,'name'=>'管理员角色管理','key'=>'auth/role','auth'=>false,'checked'=>false],
            ['id'=>1000046,'pid'=>0,'name'=>'配置管理','key'=>'config/index?scope=','auth'=>false,'checked'=>false],
            ['id'=>1000056,'pid'=>0,'name'=>'标签管理','key'=>'tag/','auth'=>false,'checked'=>false],
            ['id'=>1000071,'pid'=>0,'name'=>'敏感词管理','key'=>'sensitive-words/','auth'=>false,'checked'=>false],
        ];

        if($this->config['site']['log']) $newAuthItemGroup[] = ['id'=>1000051,'pid'=>0,'name'=>'日志管理','key'=>'log/','auth'=>false,'checked'=>false];
        if($this->config['site']['enableComment']) $newAuthItemGroup[] = ['id'=>1000061,'pid'=>0,'name'=>'评论管理','key'=>'comment/','auth'=>false,'checked'=>false];

        $count = 1000300;

        // 对部分有站点之分的权限进处理
        $siteList = SiteModel::findSite();

        $modelList = PrototypeModelModel::findModel();
        $fragmentCategoryList = FragmentCategoryModel::find()->orderBy(['sort'=>SORT_DESC])->asArray()->all();
        foreach (['import','form','fragment','content','category'] as $v){
            foreach ($siteList as $site){
                $parent = [
                    "id"=>$count,
                    "pid"=>$newAuthItemGroup[$v]['id'],
                    "name"=>$site['title'],
                    "key"=>false,
                    "auth"=>false,
                    "checked"=>false
                ];
                $count++;

                if($v === 'content'){
                    $categoryList = PrototypeCategoryModel::findCategory($site['id']);
                    if(!empty($categoryList)) $categoryList = $this->filterCategory(ArrayHelper::tree($categoryList));
                    foreach ($categoryList as $category){
                        if($category['site_id'] != $site['id']) continue;
                        $newAuthItemGroup[] = [
                            "id"=>$category['id'],
                            "pid"=>$category['pid'] == 0?$parent['id']:$category['pid'],
                            "name"=>$category['title'],
                            "key"=>$category['type'] >0?false:$newAuthItemGroup[$v]['key'].','.$category['id'],
                            "auth"=>$category['type'] == 1?$newAuthItemGroup[$v]['key'].'page?category_id='.$category['id']:false,
                            "checked"=>false
                        ];
                        unset($categoryList);
                        //$count++;
                    }
                }elseif ($v==='fragment'){
                    foreach ($fragmentCategoryList as $category){
                        if($category['site_id'] != $site['id']) continue;
                        $tmp = [
                            "id"=>$count,
                            "pid"=>$parent['id'],
                            "name"=>$category['title'],
                            "key"=>false,
                            "auth"=>false,
                            "checked"=>false
                        ];
                        if($category['type']){
                            $tmp['auth'] = $newAuthItemGroup[$v]['key'].'fragment/edit?category_id='.$category['id'];
                        }else{
                            $tmp['key'] = $newAuthItemGroup[$v]['key'].'fragment-list/,'.$category['id'];
                        }
                        $newAuthItemGroup[] = $tmp;
                        $count++;
                    }

                }elseif ($v === 'form'){
                    foreach ($modelList as $model){
                        if($model['type'] == 0) continue;
                        $newAuthItemGroup[] = [
                            "id"=>$count,
                            "pid"=>$parent['id'],
                            "name"=>$model['title'],
                            "key"=>$newAuthItemGroup[$v]['key'].','.$site['id'].','.$model['id'],
                            "auth"=>false,
                            "checked"=>false
                        ];
                        $count++;
                    }
                }else{
                    $parent['key'] = $newAuthItemGroup[$v]['key'].','.$site['id'];
                }

                $newAuthItemGroup[] = $parent;
            }
            $newAuthItemGroup[$v]['key'] = false;
            $newAuthItemGroup = array_merge([$newAuthItemGroup[$v]],$newAuthItemGroup);
            unset($newAuthItemGroup[$v]);
        }
        unset($siteList,$fragmentCategoryList,$modelList);

        // 为权限进行分组
        $newAuthItemList = [];
        foreach ($auth->getPermissions() as $item){
            foreach ($newAuthItemGroup as $group){
                if(!$group['key']) continue;
                $key = explode(',',$group['key']);
                if(stripos($item->name,$key[0],0) === 0){
                    $keyCount= count($key);
                    if($keyCount === 2){
                        $t = explode('?',$item->name);
                        if($t[0] == 'prototype/node/page') continue;
                        $t = explode('=',$t[1]);
                        if($t[1] != $key[1]) continue;
                    }elseif($keyCount ===3){
                        $t = explode('?',$item->name);
                        $t = explode('&',$t[1]);
                        $tt = explode('=',$t[0]);
                        if($tt[1] != $key[1]) continue;
                        $tt = explode('=',$t[1]);
                        if($tt[1] != $key[2]) continue;
                    }

                    // 特殊分组
                    $pid = false;
                    if($item->name=='config/index?scope=custom'){
                        $pid = 1000016;
                    }elseif ($item->name=='config/index?scope=member'){
                        $pid = 1000031;
                    }

                    $newAuthItemList[] = [
                        "id"=>$count,
                        "pid"=>$pid?:$group['id'],
                        "name"=>$item->description,
                        "key"=>false,
                        "auth"=>$item->name,
                        "checked"=>false
                    ];

                    $count++;
                }
            }
        }
        $newAuthItemList = ArrayHelper::merge($newAuthItemGroup,$newAuthItemList);
        unset($newAuthItemGroup);

        // 已拥有权限选中
        $authList = [];
        foreach ($auth->getPermissionsByRole($name) as $item){
            $authList[] = $item->name;
        }

        foreach ($newAuthItemList as $i=>$item){
            unset($newAuthItemList[$i]['key']);
            if(in_array($item['auth'],$authList)) $newAuthItemList[$i]['checked'] = true;
        }
        unset($authList);

		// 分类节点勾选
	    foreach ($newAuthItemList as $i=>$item){
	    	if($item['checked']) continue;
	    	if($this->childIsChecked(ArrayHelper::tree($newAuthItemList,$item['id']))){
			    $newAuthItemList[$i]['checked'] = true;
		    }
	    }

	   unset($treeAuthItemList);


        $this->layout = 'base';

        return $this->render('role_auth',[
            'authItemList'=>$newAuthItemList,
        ]);
    }

    private function childIsChecked($dataList){
    	foreach ($dataList as $item){
    		if($item['checked']){
    			return true;
		    }elseif (!empty($item['child'])){
    			return $this->childIsChecked($item['child']);
		    }
	    }
    }

    /**
     * 栏目过滤
     * @param $data
     * @param int $pid
     * @param int $count
     * @return array
     */
    protected function filterCategory($data, $pid = 0, $count = 0){
        $newData = [];
        foreach($data as $key=>$value){
            $childHasNode = false;
            if(!empty($value['child'])){
                $childHasNode = $this->childHasNode($value['child']);
            }

            if((empty($value['child']) || (!empty($value['child']) && !$childHasNode)  ) &&  $value['type'] > 1){
                continue;
            }

            $newData[] = $value;

            if($value['pid'] == $pid){
                $newData = ArrayHelper::merge($newData,$this->filterCategory($value['child'],$value['id'],$count+1));
            }
        }

        return $newData;
    }

    /**
     * 栏目过滤查找是否子节点
     * @param $child
     * @return bool
     */
    protected function childHasNode($child){
        $return = false;
        foreach($child as $item){
            if($item['type'] < 2){
                $return = true;
            }elseif(!empty($item['child'])){
                $return = $this->childHasNode($item['child']);
            }
            if($return) break;
        }
        return $return;
    }

    /**
     * 角色选择
     * @param string $name
     * @param null $filter
     * @return string
     */
    public function actionRoleUser($name = '',$filter = null){
        $this->layout = 'base';

        $searchModel = new SystemUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if($filter != '' && ($filter == 1 || $filter == 0)){
            $auth = Yii::$app->authManager;
            $uid = empty($name)?[]:$auth->getUserIdsByRole($name);

            $dataProvider->query
                ->andFilterWhere([($filter == 1?'in':'not in'),'id',$uid]);
        }

        return $this->render('role_user', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 角色设置用户
     * @param $postData
     * @param $roleName
     * @throws \Exception
     */
    protected function roleSetUser($postData,$roleName){
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($roleName);

        $oldData = $auth->getUserIdsByRole($roleName);
        $newData = explode(',',$postData);

        $commonData = array_intersect($oldData,$newData);

        // 删除旧
        $delData = array_diff($oldData,$commonData);
        foreach($delData as $uid) {
            $auth->revoke($role,$uid);
        }

        ///插入新
        $insert = array_diff($newData,$commonData);
        foreach($insert as $uid){
            $auth->assign($role,$uid);
        }
    }

    /**
     * 权限点修复
     * @throws \yii\db\Exception
     */
    public function actionRepair(){
        $insertAuthData = [];
        $authList = Yii::$app->getAuthManager()->getPermissions();
        $time = time();

        $modelList = PrototypeModelModel::find()->where(['type'=>1])->select(['id','type'])->asArray()->all();
        $fragmentCategory = FragmentCategoryModel::find()->asArray()->all();
        Yii::$app->getCache()->delete('category');
        foreach (SiteModel::findSite() as $site){

            if(!array_key_exists('import/prototype?site_id='.$site['id'],$authList)){
                $insertAuthData[] = [
                    "name"=>'import/prototype?site_id='.$site['id'],
                    "type"=>2,
                    "description"=>'批量导入数据',
                    "rule_name"=>null,
                    "data"=>null,
                    "created_at"=>$time,
                    "updated_at"=>$time,
                ];
            }

            foreach ($modelList as $item){
                foreach (Yii::$app->params['authListForm'] as $k=>$v){
                    if(array_key_exists($k.'?site_id='.$site['id'].'&model_id='.$item['id'],$authList)) continue;
                    $insertAuthData[] = [
                        "name" => $k.'?site_id='.$site['id'].'&model_id='.$item['id'],
                        "type" => 2,
                        "description" => $v,
                        "rule_name" => null,
                        "data" => null,
                        "created_at" => $time,
                        "updated_at" => $time,
                    ];
                }
            }

            foreach (Yii::$app->params['authListCategory'] as $k=>$v){
                if(array_key_exists($k.'?site_id='.$site['id'],$authList)) continue;
                $insertAuthData[] = [
                    "name"=>$k.'?site_id='.$site['id'],
                    "type"=>2,
                    "description"=>$v,
                    "rule_name"=>null,
                    "data"=>null,
                    "created_at"=>$time,
                    "updated_at"=>$time,
                ];
            }

            foreach (PrototypeCategoryModel::findCategory($site['id']) as $item){
                if($item['type']>1) continue;
                foreach (Yii::$app->params['authListNode'] as $k=>$v){
                    if(($item['type'] && $k!='prototype/node/page') || (!$item['type'] && $k=='prototype/node/page') || array_key_exists($k.'?category_id='.$item['id'],$authList)) continue;
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

            foreach ($fragmentCategory as $item){
                if($item['site_id'] != $site['id']) continue;
                foreach (Yii::$app->params['authListFragment'] as $k=>$v){
                    if(($item['type'] && $k!='fragment/fragment/edit') || (!$item['type'] && $k=='fragment/fragment/edit') || array_key_exists($k.'?category_id='.$item['id'],$authList)) continue;
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

        }
        unset($modelList,$time,$authList,$fragmentCategory);

        if(!empty($insertAuthData)){
            if(!Yii::$app->getDb()->createCommand()->batchInsert(AuthItemModel::tableName(),array_keys($insertAuthData[0]),$insertAuthData)->execute()){
                $this->error([Yii::t('common','Operation failed'),'message'=>'插入丢失的数据时失败。']);
            }
        }
        $this->success([Yii::t('common','Operation successful')]);
    }

}