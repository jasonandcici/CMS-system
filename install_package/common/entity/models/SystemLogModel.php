<?php

namespace common\entity\models;

use Yii;

/**
 * This is the model class for table "{{%system_log}}".
 *
 */
class SystemLogModel extends \common\entity\domains\SystemLogDomain
{
    /**
     * 记录操作日志
     * @param string $type
     * @param string $content
     * @param null $siteName
     * @return bool
     */
    public static function create($type,$content,$siteName = null){
        $config = SystemConfigModel::findConfig();
        if(Yii::$app->getSession()->get('userIsSuperAdmin') || !intval($config['site']['log'])) return true;

        $model = new self();

        $siteInfo = Yii::$app->getSession()->get('siteInfo');
        $model->site_name = $siteName===null?$siteInfo['title']:$siteName;

        $model->operation_type = $type;
        $model->content = $content;
        $model->create_time  = time();

        $model->crate_user = Yii::$app->getUser()->getIdentity()->username;

        $model->save();
    }

}
