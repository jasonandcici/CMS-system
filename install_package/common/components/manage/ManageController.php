<?php
// +----------------------------------------------------------------------
// | nantong
// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/4/11.
// +----------------------------------------------------------------------

/**
 * 后台控制器基类
 */

namespace common\components\manage;

use common\components\BaseController;
use common\entity\models\SiteModel;
use common\helpers\ArrayHelper;
use manage\libs\Rbac;
use Yii;

class ManageController extends BaseController
{
    /**
     * @var bool 是否超级管理员
     */
    public $isSuperAdmin = false;

    /**
     * init
     */
    public function init()
    {
        parent::init();

        $session = Yii::$app->getSession();

        $this->isSuperAdmin = $session->get('userIsSuperAdmin') && YII_DEBUG;

        if($siteInfo = $session->get('siteInfo')){
            $this->siteInfo = ArrayHelper::convertToObject($siteInfo);
        }else{
            $this->siteInfo = SiteModel::find()->where(['is_default'=>1])->one();
            $session->set('siteInfo',ArrayHelper::toArray($this->siteInfo));
        }
    }

    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action){
        $parent = parent::beforeAction($action);

        // 只需验证是否游客
        $route = $action->getUniqueId();
        if(Yii::$app->getUser()->getIsGuest()){
            if(in_array($route,Yii::$app->params['authNoActions'])){
                return $parent;
            }else{
                return $this->redirect(ArrayHelper::merge(Yii::$app->getUser()->loginUrl,['callback'=>Yii::$app->getRequest()->getAbsoluteUrl()]))->send();
            }
        }else{
            if(in_array($route,Yii::$app->params['authNoActions'])){
                return $parent;
            }

            // 只需验证是否游客
            $noAuthentication = Yii::$app->params['authIsGuestActions'];
            if(!$this->isSuperAdmin && !in_array($route,$noAuthentication)){
                if(strpos($route,'prototype/node/') === 0 || strpos($route,'fragment/fragment-list/') === 0 || $route === 'fragment/fragment/edit'){
                    $route = $route.'?category_id='.Yii::$app->getRequest()->get('category_id');
                }elseif (strpos($route,'prototype/form/') === 0){
                    $route = $route.'?site_id='.$this->siteInfo->id.'&model_id='.Yii::$app->getRequest()->get('model_id');
                }elseif($route === 'config/index'){
                    $route = $route.'?scope='.Yii::$app->getRequest()->get('scope');
                }elseif (strpos($route,'prototype/category/') === 0 || $route === 'import/prototype'){
                    $route = $route.'?site_id='.$this->siteInfo->id;
                }

                if(!Yii::$app->getUser()->can($route)){
                    $this->error(['您没有足够的权限访问或操作。']);
                }
            }
        }

        return $parent;
    }
}