<?php

namespace api;
use Yii;

/**
 * api module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'api\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->layout = 'main';

        // 重置错误模板 'u/default/error'
        Yii::$app->getErrorHandler()->errorAction = null;
    }
}
