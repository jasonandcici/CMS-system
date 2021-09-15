<?php
/**
 * 公用资源包
 */

namespace home\assets;

use yii\web\AssetBundle;


class CommonAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    /* 开始 */
    public $css = [
        'css/bootstrap.css',
        'css/main.css',
    ];

    public $js = [
        'js/bootstrap.min.js',
        'js/jquery.plug-in.js',
        'js/main.js',
    ];
    /* 结束 */

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
