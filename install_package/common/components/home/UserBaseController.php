<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/11/23
 */

namespace common\components\home;
use common\helpers\ArrayHelper;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;


/**
 * 用户模块权限基类
 *
 * @author 
 * @since 1.0
 */
class UserBaseController extends NodeController
{
    /**
     * 用户中心权限控制
     * @param \yii\base\Action $action
     * @return bool|void
     * @throws NotFoundHttpException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $parent = parent::beforeAction($action);
        $id = $action->getUniqueId();

        $allowList = ArrayHelper::toArray($this->config->member->actionList);
        $allowList[] = 'u/relation/list';
        $allowList[] = 'u/relation/operation';
        $allowList[] = 'u/comment/relation';
        $allowList[] = 'u/comment/index';
        $allowList[] = 'u/comment/delete';

        if(!in_array($id,$allowList)){
            throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }

        return $parent;
    }

}