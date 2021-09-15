<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=website',
            'username' => 'root',
            'password' => '100001',
            'charset' => 'utf8',
            'tablePrefix' => 'wp_',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail'
        ],
    ],
];
