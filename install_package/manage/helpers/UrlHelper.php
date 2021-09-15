<?php
// +----------------------------------------------------------------------
// | nantong
// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/5/3.
// +----------------------------------------------------------------------

/**
 * url生成
 */

namespace manage\helpers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class UrlHelper extends Url
{

    /**
     * 生成一个返回链接（自动获取url参数并生成）
     * @param array $url
     * @param bool $scheme
     * @return string
     */
    static public function backUrl($url = [], $scheme = false){
        $get = Yii::$app->request->get();
        unset($get['r']);
        return self::to(ArrayHelper::merge($url,$get),$scheme);
    }
}