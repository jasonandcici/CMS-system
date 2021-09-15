<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/7/15
 */

namespace home\assets;

use yii\web\AssetBundle;


/**
 * Html5shivAsset
 *
 * @author 
 * @since 1.0
 */
class WebuploaderAsset extends AssetBundle
{
	public $sourcePath = '@common/assets/js';

    public $js = [
        'webuploader/webuploader.html5only.min.js',
    ];

    public $css = [
        'webuploader/webuploader.css',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}