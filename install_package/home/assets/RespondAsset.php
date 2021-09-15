<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/7/15
 */

namespace home\assets;

use yii\web\AssetBundle;


/**
 * RespondAsset
 *
 * @author 
 * @since 1.0
 */
class RespondAsset extends AssetBundle
{
    public $sourcePath = '@common/assets/js';
    public $jsOptions = ['position' => \yii\web\View::POS_HEAD,'condition' => 'lte IE 9'];
    public $js = [
        'respond.min.js',
    ];
}