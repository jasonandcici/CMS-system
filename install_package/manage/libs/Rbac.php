<?php
// +----------------------------------------------------------------------
// | forbeschina
// +----------------------------------------------------------------------
// | Copyright (c)
// +----------------------------------------------------------------------
// | Author:
// +----------------------------------------------------------------------
// | Created on 2015/10/29.
// +----------------------------------------------------------------------

/**
 * Rbac权限控制
 */

namespace manage\libs;
use common\entity\models\SystemNodeModel;
use common\entity\models\SystemRoleUserRelationModel;
use Yii;



class Rbac
{

    /**
     * 把用户权限保存到session中
     * @param integer $uid 用户ID
     */
    static public function saveAccessList($uid){
        $params = Yii::$app->params;


        $session = Yii::$app->session;
        if (!$session->isActive) $session->open();

        // 保存用户 id
        $session->set($params['USER_AUTH_KEY'],$uid);

        if(!$params['USER_AUTH_ON']) return;

        //保存用户权限
        if(!$session->get($params['ADMIN_AUTH_KEY'])){
            $session->set('_ACCESS_LIST_'.$uid,self::getAccessList($uid));
        }
    }

    /**
     * 检查用户是否已经登录
     * @param $module
     * @param $auth string 例如："shop/category/index"
     * @return bool
     */
    static public function checkLogin($module,$auth){
        $params = Yii::$app->params;

        if(!$params['USER_AUTH_ON']) return true;
        if(self::accessDecision($module,$auth)){
            $session = Yii::$app->session;
            if (!$session->isActive) $session->open();
            if($session->get($params['USER_AUTH_KEY'])){
                return true;
            }else{
                return false;
            }
        }else{
            return true;
        }
    }

    /**
     * 检查权限
     * @param $module
     * @param $auth string 例如："shop/category/index"
     * @return bool
     */
    static public function checkAccess($module,$auth){
        $params = Yii::$app->params;
        if(!$params['USER_AUTH_ON']) return true;

        $session = Yii::$app->session;

        if(!$session->get($params['ADMIN_AUTH_KEY'])){ // 判断超级管理员
            if(self::accessDecision($module,$auth)){
                $accessList = $session->get('_ACCESS_LIST_'.$session->get($params['USER_AUTH_KEY']));

                $temp = explode('/',$auth);
                if(count($temp) == 3){
                    return isset($accessList['_ACCESS_NAME'][strtoupper($module)][strtoupper($temp[0])][strtoupper($temp[1])][strtoupper($temp[2])])?true:false;
                }
                elseif(count($temp) == 2){
                    return isset($accessList['_ACCESS_NAME'][strtoupper($module)][strtoupper($temp[0])][strtoupper($temp[1])])?true:false;
                }
                return false;
            }
        }

        return true;
    }

    /**
     * 权限认证过滤器
     * @param $module string 模块id
     * @param $auth string 例如："shop/category/index"
     * @return bool
     */
    static private function accessDecision($module,$auth){
        $params = Yii::$app->params;
        if(!$params['USER_AUTH_ON']) return false;

        return in_array($module.'/'.$auth,explode(',',$params['NOT_AUTH_ACTION']))?false:true;
    }

    /**
     * 取得当前认证号的所有权限列表
     * @param integer $uid 用户ID
     * @return array
     */
    static private function getAccessList($uid){
        $role_data = SystemRoleUserRelationModel::find()->where(['user_id'=>$uid])
            ->with([
                'nodeids'=>function($query){
                    $query->select(['role_id','node_id']);
                }
            ])
            ->asArray()->all();

        $node_id = [];
        foreach($role_data as $value){
            if(count($value['nodeids']) < 1) continue;
            foreach($value['nodeids'] as $v){
                $node_id[] = $v['node_id'];
            }
        }
        array_unique($node_id);

        $node_list = SystemNodeModel::find()->select(['id','pid','name'])->where(['id'=>$node_id])->asArray()->all();
        $node_list = $node_list?$node_list:[];

        return [
            '_ACCESS_ID'=> $node_id ,
            '_ACCESS_NAME'=>self::treeAccess($node_list)
        ];
    }

    static private function treeAccess($data, $pid = 0){
        $tmp = array();
        foreach ($data as $v) {
            if ($v['pid'] == $pid) {
                $tmp[strtoupper($v['name'])] = self::treeAccess($data,$v['id']);
            }
        }
        return $tmp;
    }
}