<?php

use manage\assets\IndexAsset;
use manage\helpers\NavHelper;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $navList
 * @var $siteList
 */

$this->title = $this->context->config['site']['site_name'].' - 系统管理中心';
$userInfo = Yii::$app->getUser()->getIdentity();

IndexAsset::register($this);
$this->registerJs("
indexApp.init();
$('#changeSiteModel').on('shown.bs.modal', function () {
    $.get('".Url::current(['getSiteList'=>true])."',function(res){
        $('#j_changeSite').html(res);
    });
});
", View::POS_READY);

$this->registerCss("
#yii-debug-toolbar{display:none!important;}
.brand-inner .tooltip{opacity:1;}
.brand-inner .tooltip-inner{background-color:#fff;color:#000;}
.brand-inner .tooltip.bottom .tooltip-arrow{border-bottom-color:#fff;}
.modal-backdrop{background-color:#000;}
");
?>
<!-- 头部开始 -->
<header>
    <div class="brand hidden-sm" data-welcome="<?=Url::to(['welcome'])?>">
        <div class="brand-inner">
            <a href="<?=Url::to(['/site/welcome'])?>" target="mainFrame">
                <?=\common\helpers\HtmlHelper::getImgHtml($this->context->config['site']['logo'])?>
            </a>
            <?php if(count($siteList)>1){?>
            <a href="#changeSiteModel" class="j_tooltip" data-toggle="modal" data-placement="bottom" title="点击切换站点"><h1><?=$this->context->siteInfo->title?><span class="caret"></span></h1></a>
            <?php }else{ ?>
                <h1><?=$this->context->config['site']['site_name']?></h1>
            <?php } ?>
        </div>
    </div>
    <button class="nav-aside-btn visible-xs visible-sm" id="nav-aside-btn" type="button">
        <span class="iconfont status-1">&#xe61b;</span>
        <span class="iconfont status-2">&#xe603;</span>
    </button>
    <nav>
        <ul class="nav-main nav-main-special" id="nav-main" role="tablist">
            <?php foreach($navList as $key=>$value){
                if(empty($value['child'])) continue;
                ?>
                <li>
                    <a href="<?=$value['url']?>" aria-controls="<?=substr($value['url'],1);?>" role="tab" data-toggle="tab" aria-expanded="false"><?=$value['title']?></a>
                </li>
            <?php } ?>
        </ul>
        <div class="dropdown nav-right" id="nav-right-dropdown">
            <button type="button" data-toggle="dropdown">
                <span class="iconfont">&#xe604;</span>
                <span class="t">你好，<?= $userInfo->username?> <span class="caret"></span></span>
            </button>
            <ul class="dropdown-menu">
                <li><a href="<?=$this->context->siteInfo->is_default?'/':'/'.$this->context->siteInfo->slug.'/index'.$this->context->config['site']['urlSuffix']?>" target="_blank">站点首页</a></li>
                <li class="divider"></li>
                <li><a href="<?=Url::to(['clear-cache'])?>" id="js-clear-cache">清除缓存</a></li>

                <?php if(!$this->context->isSuperAdmin):?>
                <li><a href="<?=Url::to(['user/reset-password'])?>" target="mainFrame">重置密码</a></li>
                <?php endif;?>
                <li class="divider"></li>
                <li><a href="<?=Url::to(['passport/logout'])?>" id="js-logout">安全退出</a></li>
            </ul>
        </div>
    </nav>
</header><!-- 头部结束 -->

<!-- 侧边导航开始 -->
<aside class="main-aside" id="main-aside">
    <!-- pc端左侧导航 -->
    <nav class="scroll-bar" id="accordion">
        <?php foreach($navList as $key=>$value){
            if(empty($value['child'])) continue;
            ?>
            <div class="accordion-wrap fade" id="<?=substr($value['url'],1);?>" role="tabpanel">
                <h4 class="accordion-header"><?=$value['title']?></h4>
                <?= NavHelper::generateNavHtml($value['child'],$value['id'])?>
            </div>
        <?php } ?>
    </nav>
    <!-- 移动端导航 -->

</aside><!-- 侧边导航结束 -->

<!-- 主内容开始 -->
<div class="main-wraper">
    <iframe class="main" id="mainFrame" name="mainFrame" frameborder="0" data-src="<?=Url::to(['welcome']);?>"></iframe>
</div><!-- 主内容结束 -->

<!-- 切换站点弹出框 -->
<div class="modal fade" id="changeSiteModel" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><strong>切换站点</strong></h4>
            </div>
            <div class="modal-body">
                <!--srart-->
                <div class="list-group list-site" id="j_changeSite">
                    <div class="text-center" style="height: 39px;line-height: 38px;">数据加载中，请稍后...</div>
                </div>
                <!--end-->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关 闭</button>
            </div>
        </div>
    </div>
</div>