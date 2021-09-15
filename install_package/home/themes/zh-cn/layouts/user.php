<?php
/**
 * @var $content
 */
use common\helpers\ArrayHelper;
use common\helpers\HtmlHelper;

$this->beginContent(Yii::$app->layoutPath.'/main.php');
?>
<div class="col-sm-4 col-md-3">
    <div class="list-group">
        <?php
        // 利用参数设置用户中心侧导航激活
        if(!isset($this->params['active'])) $this->params['active'] = null;

        $navList = [
            ['id'=>'index',"slug"=>'index','title'=>'个人首页','type'=>null],
            ['id'=>'relation-collection',"slug"=>'collection','title'=>'我的收藏','type'=>'relation'],
            ['id'=>'relation-feedback',"slug"=>'feedback','title'=>'我的反馈','type'=>'relation'],
            ['id'=>'comment',"slug"=>'index','title'=>'我的评论','type'=>'comment'],
            ['id'=>'profile',"slug"=>'profile','title'=>'资料管理','type'=>null],
            ['id'=>'reset-password',"slug"=>'reset-password','title'=>'账号管理','type'=>null],
        ];
        foreach ($navList as $item){?>
            <a href="<?php if($item['type'] == 'relation') {
                echo $this->generateUserRelationUrl($item['slug']);
            } elseif ($item['type'] == 'comment'){
                echo $this->generateUserCommentUrl($item['slug']);
            }else{
                echo $this->generateUserUrl($item['slug']);
            }?>" class="list-group-item<?=$this->params['active'] == $item['id']?' active':''?>"><?=$item['title']?></a>
        <?php } ?>
    </div>
</div>
<div class="col-sm-8 col-md-9">
    <?=$content?>
</div>
<?php $this->endContent(); ?>