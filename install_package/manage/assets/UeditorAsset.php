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

class UeditorAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [];

    public $js = [
        'js/plugins/ueditor/ueditor.config.js',
        //'js/plugins/ueditor/ueditor.all.min.js',
        'js/plugins/ueditor/ueditor.all.js',
        'js/plugins/coralueditor/coralueditor.js',
    ];

    public $depends = [];

}