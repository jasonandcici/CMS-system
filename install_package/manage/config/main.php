<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'manage',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'manage\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        // 原型
        'prototype' => [
            'class' => 'manage\modules\prototype\Module',
        ],
        // 碎片
        'fragment' => [
            'class' => 'manage\modules\fragment\Module',
        ],
	    // api文档
        'doc' => [
	        'class' => 'manage\modules\doc\Module',
        ],
    ],

    'components' => [

        // 用户认证
        'user' => [
            'identityClass' => 'manage\models\UserIdentity',
            'loginUrl'=>['passport/login'],
            'enableAutoLogin' => true,
            'identityCookie' => [
                'name' => '_manageIdentity',
                'path' => '/manage',
                'httpOnly' => true,
            ],
        ],

        // 设置独立的session和cookie运行域,避免冲突
        'request' => [
            'csrfParam' => '_manageCSRF',
            'csrfCookie' => [
                'httpOnly' => true,
                'path' => '/manage',
            ],
        ],
        'session' => [
            'name' => 'MANAGESESSID',
            'cookieParams' => [
                'path' => '/manage',
            ],
            'timeout'=>21600,
        ],

        // 日志
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        // 错误输出
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        // 重置资源包的jquery版本
        'assetManager' => [
            'assetMap' => [
                'jquery.js' => '@web/js/dookayui.min.js',
            ],
        ],

        // 权限管理
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
    ],
    'params' => $params,
];
