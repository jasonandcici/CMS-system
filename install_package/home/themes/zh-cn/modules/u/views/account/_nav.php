<ul class="nav nav-tabs">
    <?php
    $navList = ['reset-password'=>'修改密码','bind'=>'账号绑定','third-bind'=>'第三方账号管理'];
    if(stripos(Yii::$app->getUser()->getIdentity()->username,'u_',0)===0){
        $navList['reset-username'] = '修改用户名';
    }
    foreach($navList as $k=>$v){?>
        <li<?=$this->context->categoryInfo->slug == $k?' class="active"':''?>><a href="<?=$this->generateUserUrl($k)?>"><?=$v?></a></li>
    <?php }?>
</ul>
<br>
