<?php
// +----------------------------------------------------------------------
// | forgetwork
// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/5/24.
// +----------------------------------------------------------------------

/**
 * 核心控制器
 */

namespace home\controllers;

use common\helpers\ArrayHelper;
use common\helpers\FileHelper;
use common\helpers\SecurityHelper;
use common\helpers\UrlHelper;
use Yii;
use yii\web\NotFoundHttpException;

class NodeController extends \common\components\home\NodeController
{
    /**
     * 栏目页面
     */
    public function actionIndex(){
        if($this->categoryInfo->type == 0){
            return $this->nodeList();
        }
        elseif($this->categoryInfo->type == 1){
            return $this->nodePage();
        }
    }

    /**
     * 内容详情
     * @throws NotFoundHttpException
     */
    public function actionDetail(){
        return $this->nodeDetail();
    }

    /**
     * 附件下载
     * @param $file
     * @param string $name
     * @throws NotFoundHttpException
     * @internal param string $field
     * @internal param null $cid
     */
    public function actionDownload($file,$name=null){
        $file = SecurityHelper::decrypt($file,date('dYm'));

        FileHelper::outPutFile($file,$name);
    }
}