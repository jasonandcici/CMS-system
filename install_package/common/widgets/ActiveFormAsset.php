<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/9/6
 */

namespace common\widgets;


/**
 * ActiveFormAsset
 *
 * @author 
 * @since 1.0
 */
class ActiveFormAsset extends \yii\widgets\ActiveFormAsset
{
    public $sourcePath = '@common/assets/js';
    public $js = [
        'dookay.activeForm.js',
    ];
}