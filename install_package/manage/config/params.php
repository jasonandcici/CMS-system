<?php
return [
    // 默认分页大小
    'page_size' => 15,

    // 无需验证的action
    'authNoActions'=>[
        'passport/login',
        'site/captcha',
        'site/error'
    ],

    // 权限验证只验证是否游客的action
    'authIsGuestActions'=>[
        'site/index','site/welcome','site/clear-cache',
        'import/ueditor',
        'passport/logout','user/reset-password','prototype/category/expand_nav',
        'fragment/category/expand_nav','prototype/form/expand_nav',
        'assets/node','assets/user','assets/category',
        'files/index',
        'editor/index','editor/batch-operation','editor/category','editor/category-batch-operation',
        'auth/role-user'
    ],

    // 权限列表
    'authListNode'=>[
        'prototype/node/index'=>'列表',
        'prototype/node/create'=>'添加',
        'prototype/node/update'=>'修改',
        'prototype/node/delete'=>'删除',
        'prototype/node/page'=>'修改',
        'prototype/node/sort'=>'排序',
        'prototype/node/status'=>'状态',
        'prototype/node/move'=>'移动',
    ],

    'authListFragment'=>[
        'fragment/fragment-list/index'=>'列表',
        'fragment/fragment-list/create'=>'添加',
        'fragment/fragment-list/update'=>'修改',
        'fragment/fragment-list/delete'=>'删除',
        'fragment/fragment/edit'=>'修改',
        'fragment/fragment-list/sort'=>'排序',
        'fragment/fragment-list/status'=>'状态',
    ],

    'authListForm'=>[
        'prototype/form/delete'=>'删除',
        'prototype/form/index'=>'列表',
        'prototype/form/status'=>'状态',
        'prototype/form/view'=>'详情',
    ],

    'authListCategory'=>[
        'prototype/category/create'=>'添加栏目',
        'prototype/category/delete'=>'删除栏目',
        'prototype/category/index'=>'栏目列表',
        'prototype/category/sort'=>'栏目排序',
        'prototype/category/status'=>'栏目状态',
        'prototype/category/update'=>'修改栏目',
    ],
];
