<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/11/5
 */

namespace home\assets;

use yii\web\AssetBundle;


/**
 * 重置编辑器内容样式资源
 *
 * @author 
 * @since 1.0
 */
class ResetArticleStyleAsset extends AssetBundle
{
	/* 开始 */
	public $css = [];

	public $sourcePath = '@common/assets/js/resetArticleStyle';
	public $js = [
		'resetArticleStyle.js',
	];
	public $depends = [
		'yii\web\JqueryAsset'
	];
}