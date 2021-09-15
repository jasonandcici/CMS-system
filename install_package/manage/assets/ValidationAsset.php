<?php
// +----------------------------------------------------------------------
// | nantong
// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author:
// +----------------------------------------------------------------------
// | Created on 2016/4/14.
// +----------------------------------------------------------------------

/**
 * jquery.validationAsset资源
 */

namespace manage\assets;

use yii\web\AssetBundle;

class ValidationAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [];

    public $js = [
        'js/plugins/jquery-validation/jquery.validate.min.js',
        'js/plugins/jquery-validation/additional-methods.min.js',
    ];

    public $depends = [
        'manage\assets\CommonAsset',
    ];
}