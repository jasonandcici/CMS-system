<?php
// 导航使用示例(请在模板中使用)
echo \common\widgets\nav\NavWidget::widget(['deep' => 3,
    'categoryList'=>$this->context->categoryList,   // 当前站点的所有栏目
    'categoryCurrentId' => $this->context->categoryInfo->id,  // 当前页面的栏目
    'topNavClass' => "nav navbar-nav",  // 顶级ul的class
    'topNavId' => 'none',               // 顶级ul的id
    'hasChildItemClass' => 'dropdown',  // 拥有子级的li的class
    'hasChildLinkClass' => 'dropdown-toggle',   // 拥有子级的a的class
    'hasChildLinkOption'=> 'data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"', // 拥有子级a的其他属性
    'childNavClass' => 'dropdown-menu', // 子级ul的class
    'dropdownInsertDom' => '<span class="caret"></span>',   // 有子级的a后插入的dom
]);

?>


