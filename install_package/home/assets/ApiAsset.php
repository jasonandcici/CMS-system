<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/11/5
 */

namespace home\assets;

use yii\web\AssetBundle;


/**
 * ApiAsset
 *
 * @author 
 * @since 1.0
 */
class ApiAsset extends AssetBundle
{
	public $basePath = '@webroot';
	public $baseUrl = '@web';

	/* 开始 */
	public $css = [
		'api/css/bootstrap.min.css',
		'api/css/main.css',
	];

	public $js = [
		'api/js/bootstrap.min.js',
	];
	/* 结束 */

	public $depends = [
		'yii\web\JqueryAsset',
	];
}