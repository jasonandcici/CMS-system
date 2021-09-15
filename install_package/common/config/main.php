<?php
return [
	'bootstrap' => [
		'queue',
	],
	'aliases' => [
		'@bower' => '@vendor/bower-asset',
		'@npm'   => '@vendor/npm-asset',
	],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        // 开启缓存
        'cache' => [
            'class' => 'yii\caching\FileCache',
            'keyPrefix' => 'coralNode',
        ],

        //国际化
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages'
                ],
            ],
        ],
	    // 消息队列
        'queue' => [
	        'class' => 'yii\queue\file\Queue',
	        'path' => '@console/runtime/queue',
	        'ttr' => 5 * 60,
	        'attempts' => 3,
        ],
    ],
    // 设置语言为中文
    'language'=>'zh-CN',
    'timeZone'=>'PRC',
];
