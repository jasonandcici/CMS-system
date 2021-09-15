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
class Html5shivAsset extends AssetBundle
{
	public $sourcePath = '@common/assets/js';
    public $jsOptions = ['position' => \yii\web\View::POS_HEAD,'condition' => 'lte IE 9'];
    public $js = [
        'html5shiv.min.js',
    ];
}