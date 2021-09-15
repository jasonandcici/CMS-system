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

class ZtreeAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'js/plugins/zTree/metroStyle/metroStyle.css',
    ];

    public $js = [
        'js/plugins/zTree/jquery.ztree.core-3.5.min.js',
        'js/plugins/zTree/jquery.ztree.excheck-3.5.min.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];

}