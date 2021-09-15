<?php
/**
 * gii配置
 */
return [
    'model' => [
        'class' => 'yii\gii\generators\model\Generator',
        'templates' => [
            'default' => '@common/assets/gii',
        ],
        'useTablePrefix'=>true,
        'generateRelations'=>'all',
        'generateLabelsFromComments'=>true,
        'baseClass'=>'common\components\BaseArModel',
        'ns'=>'common\entity\models',
    ],
    'crud' => [
        'class' => 'yii\gii\generators\crud\Generator',
        /*'templates' => [
            'default' => '@common/assets/gii',
        ],*/
        'modelClass'=>'common\entity\models\\Model',
        'searchModelClass'=>'common\entity\searches\\Search',
        'controllerClass'=>'manage\controllers\SiteController',
        //'viewPath'=>'',
        //'baseControllerClass'=>'',
    ],
];