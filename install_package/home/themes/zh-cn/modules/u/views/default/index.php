<?php
use common\helpers\HtmlHelper;

$this->params['active'] = 'index';

// 获取用户资料
$user = Yii::$app->getUser()->getIdentity();
$userInfo = $user->userProfile;
?>
<div class="media">
    <div class="media-left">
        <a href="<?=$this->generateUserUrl('profile',['params'=>['view'=>'avatar']])?>"><?=empty($userInfo->avatar)?HtmlHelper::img('@web/images/avatar.png',['class'=>'media-object','height'=>90]):HtmlHelper::getImgHtml($userInfo->avatar,['w/90/h/90'])?></a>
    </div>
    <div class="media-body">
        <h4 class="media-heading">您好，<?=$user->username?>！</h4>
        <p>昵称：<?=$userInfo->nickname?></p>
        <p><?=$userInfo->signature?></p>
        <p>
            <?php if(stripos($user->username,'u_',0)===0){?><a href="<?=$this->generateUserUrl('reset-username')?>" class="text-primary mr-1">修改用户名</a><?php }?>
            <a href="<?=$this->generateUserUrl('profile')?>" class="text-primary mr-1">修改资料</a>
            <a href="<?=$this->generateUserUrl('reset-password')?>" class="text-primary">重置密码</a>
        </p>
    </div>
</div>
