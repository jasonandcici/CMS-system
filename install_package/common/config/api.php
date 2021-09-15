<?php

return [
	[
		'class' => 'yii\rest\UrlRule',
		'controller' => [
			'api/node',
			'api/site',
			'api/form',
			'api/fragment',
			'api/comment'
		],
	],
	'GET api/categories' => 'api/site/categories',
	'GET api/tag-search' => 'api/site/tag-search',
	'GET api/search' => 'api/site/search',
	'GET api/config' => 'api/site/config',

	'POST api/forms/send-sms' => 'api/form/send-sms',
	'POST api/forms/upload' => 'api/form/upload',

	'POST api/passport/login' => 'api/user/login',
	'POST api/passport/third-login' => 'api/user/third-login',
	'POST api/passport/register' => 'api/user/register',
	'POST api/passport/find-password' => 'api/user/find-password',
	'GET api/passport/is-logged' => 'api/user/is-logged',
	'POST api/passport/logout' => 'api/user/logout',

	'GET,PATCH,PUT api/user/profile' => 'api/user/profile',
	'POST api/user/reset-password' => 'api/user/reset-password',
	'POST api/user/reset-username' => 'api/user/reset-username',
	'POST api/user/bind' => 'api/user/bind',
	'GET,POST api/user/third-account' => 'api/user/third-account',

	'POST api/comments/relation' => 'api/comment/relation',
	'GET api/user/comment' => 'api/comment/user-comment',

	'GET api/relation/list' => 'api/user/relation-list',
	'POST api/relation/operation' => 'api/user/relation-operation',

	'GET api/html5/<action>' => 'api/html5/<action>',
];
