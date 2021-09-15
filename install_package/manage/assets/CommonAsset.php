<?php
// +----------------------------------------------------------------------
// | SimplePig
// +----------------------------------------------------------------------
// | Copyright (c) 2016-+ http://www.zhuyanjun.cn.
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/3/13 21:49.
// +----------------------------------------------------------------------

/**
 * 公用资源包
 */

namespace manage\assets;

use yii\web\AssetBundle;


class CommonAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/bootstrap.min.css',
        'css/main.css',
        'css/font_1474340650_52283.css'
    ];

    public $js = [
        'js/dookayui.min.js',
        'js/common.js'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];

}
