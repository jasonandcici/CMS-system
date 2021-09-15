<?php
// +----------------------------------------------------------------------
// | nantong
// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author:
// +----------------------------------------------------------------------
// | Created on 2016/4/4.
// +----------------------------------------------------------------------

/**
 * LoginAsset.php
 */

namespace manage\assets;


use yii\web\AssetBundle;

class LoginAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/bootstrap.min.css',
        'css/login.css',
        'css/font_1474340650_52283.css'
    ];

    public $js = [
        'js/dookayui.min.js',
        'js/common.js',
        'js/pages/login.js'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];

}