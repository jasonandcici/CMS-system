<?php
return [
    // 用户token有效时间(秒)
    'user.verificationCodeTokenExpire' => 3*60,// 3分钟
    'user.identityByAccessTokenExpire' => 2592000,// 30天

	// 第三方账号

	// 登录的密钥，适用于/api/user/passport/third-login.html接口
    'third.loginKey'=>'N5feTMt9QZMwYImWO3SxDdnmEbCkNmkp',
	'third.thirdAllowList'=>['qq','weibo','wechat'],

	// 默认分页大小
    'page_size' => 10,

	/**
     * common/base/ControllerBase的success和error方法配置参数
     */
    // 'message_success_tpl' => 'message', 成功信息模板
    // 'message_error_tpl' => 'message', 错误信息模板
    // 'message_reader_method' => 'reader', 错误渲染视图方法模板
    // 'message_reader_layout' =>'main' , 渲染视图布局

	// easy wechat配置
    'WECHAT' =>[
	    'app_id' => '',
	    'secret' => '',

	    'response_type' => 'array',

	    'log' => [
		    'level' => 'debug',
		    'permission' => 0777,
		    'file' => __DIR__.'/log/wechat.log',
	    ],

	    'oauth' => [
		    // 授权类型 snsapi_base,snsapi_userinfo
		    'scopes'   => ['snsapi_base'],
		    'callback' => '',
	    ],
    ],

    'WECHAT_SHARE_API' => []
];
