<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'home',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'home\controllers',
    'aliases' => [
        '@api' => '@home/api',
    ],
    'modules' => [
        // 用户模块
        'u' => [
            'class' => 'home\modules\u\Module',
        ],
        'api' => [
            'class' => 'api\Module',
        ],
    ],
    'components' => [
        'user' => [
            'identityClass' => 'common\entity\models\UserModel',
            'enableAutoLogin' => true,
            'loginUrl'=>'/u/passport/login',
            'identityCookie' => [
                'name' => '_homeIdentity',
            ],
        ],
        // 设置独立的session和cookie运行域,避免冲突
        'request' => [
            'baseUrl' => '',
            'csrfParam' => '_homeCSRF',
            'csrfCookie' => [
                'httpOnly' => true,
            ],
            'parsers' => [
	            'application/json' => 'yii\web\JsonParser',
            ]
        ],

        'session' => [
            'name' => 'HOMESESSID',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        // 重置视图组件基类
        'view' => [
            'class' => 'common\components\home\HomeView',
            'theme'=>[
                'basePath' => '',
                'baseUrl' => '',
                'pathMap' => [],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'class'=>'common\components\UrlManager',
            'rules' => require(__DIR__ . '/../../common/config/api.php'),
        ],
        'mobileDetect' => [
            'class' => '\skeeks\yii2\mobiledetect\MobileDetect'
        ],
        // 第三方授权
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [],
        ]
    ],
    'params' => $params,
];
