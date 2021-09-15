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


class ListAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [];

    public $js = [
        'js/plugins/nestable-master/jquery.nestable.js',
        'js/common.list.js',
    ];

    public $depends = [
        'manage\assets\CommonAsset',
    ];

}
