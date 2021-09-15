<?php
/**
 * @copyright
 * @link
 * @create Created on 2016/12/21
 */

namespace home\controllers;

use Yii;


/**
 * 自由页
 *
 * @author
 * @since 1.0
 */
class FreeController extends \common\components\home\NodeController
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'index' => 'common\components\home\FreeAction',
        ];
    }
}