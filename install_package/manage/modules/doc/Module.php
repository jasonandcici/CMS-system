<?php

namespace manage\modules\doc;

use Yii;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'manage\modules\doc\controllers';

    public function init()
    {
        parent::init();

	    $this->layout = 'main';

	    // 重置错误模板 'u/default/error'
	    Yii::$app->getErrorHandler()->errorAction = null;
    }
}
