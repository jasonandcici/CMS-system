<?php
/**
 * @var $dataDetail
 * @var $prevLink
 * @var $nextLink
 */

use common\helpers\HtmlHelper;

$isCollection = $this->findDataIsRelation('collection',$dataDetail);
$dataDetail = $this->countUserRelationsFilter($dataDetail);
?>
<!-- 面包屑 -->
<?=$this->render('_breadcrumb')?>

<article class="bs-docs-section">
    <h1 class="page-header"><?=$dataDetail->title?></h1>
    <p class="text-muted mb-3"><a href="<?=$this->generateUserRelationUrl('collection',$dataDetail->id)?>" class="pull-right collection<?=$isCollection[$dataDetail->id]?' active':''?> js-relation" data-relation-text="点击收藏,取消收藏"><i class="glyphicon glyphicon-heart"></i></a> 时间：<?=date('Y-m-d',$dataDetail->create_time)?> <span class="ml-3">浏览量：<?=$dataDetail->views?></span> <span class="ml-3">收藏数：<?=$dataDetail->count_user_relations->collection?></span></p>

    <!-- 图集 -->
    <?php if(!empty($dataDetail->atlas)){ ?>
    <div id="carousel-example-generic" class="carousel slide mb-3" data-ride="carousel">
        <ol class="carousel-indicators">
            <?php
            $atlas = HtmlHelper::fileDataHandle($dataDetail->atlas);
            foreach ($atlas as $i=>$item){
            ?>
            <li data-target="#carousel-example-generic" data-slide-to="<?=$i?>"<?=$i==0?' class="active"':''?>></li>
            <?php }?>
        </ol>
        <div class="carousel-inner" role="listbox">
            <?php
            foreach ($atlas as $i=>$item){
            ?>
            <div class="item<?=$i==0?' active':''?>">
                <?=HtmlHelper::getImgHtml($item,['w/1140/h/500','class'=>'center-block'])?>
            </div>
            <?php }?>
        </div>

        <!-- Controls -->
        <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
            <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
            <span class="sr-only">上一张</span>
        </a>
        <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
            <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
            <span class="sr-only">下一张</span>
        </a>
    </div>
    <?php } ?>
    <!-- 文章内容 -->
    <?=$dataDetail->content?>

    <?php if(!empty($dataDetail->attachment)):?>
    <div class="mt-3 mb-3">
        <h3>附件下载</h3>
        <p>
            <u>
                <a href="<?=$this->generateDownloadUrl($dataDetail->category_id,$dataDetail->attachment);?>" target="_blank" title="点击下载">
                    <?=HtmlHelper::getFileItem($dataDetail->attachment,'title')?>
                </a>
            </u>
        </p>

    </div>
    <?php endif;?>

    <!-- 翻页 -->
    <?php if(!empty($prevLink)){?><p>上一条：<a class="text-primary" href="<?=$this->generateDetailUrl($prevLink)?>"><?=$prevLink->title?></a></p><?php } ?>
    <?php if(!empty($nextLink)){?><p>下一条：<a class="text-primary" href="<?=$this->generateDetailUrl($nextLink)?>"><?=$nextLink->title?></a></p><?php } ?>
</article>

<h2 class="page-header">评论 <small><a href="<?=$this->generateCommentListUrl($dataDetail->category_id,$dataDetail->id)?>" class="text-primary">（点击查看评论页）</a> </small></h2>
<div id="comment">评论加载中...</div>

<?php $this->beginBlock('endBody');?>
<script>
    $(function () {
        $('.carousel').carousel();

        // 加载评论
        var $comment = $('#comment');
        $comment.load('<?=$this->generateCommentListUrl($dataDetail->category_id,$dataDetail->id)?> #comment-main');

        $comment.on('submit','#js-comment-form', function (e) {
            var $form = $(this);
            var $submit = $form.find('[type="submit"]');

            $submit.button('loading');
            $.post($form.attr('action'),$form.serialize(),function (response) {
                if(typeof response === 'string') response = JSON.parse(response);
                $submit.button('reset');
                if(response.status){
                    $comment.load('<?=$this->generateCommentListUrl($dataDetail->category_id,$dataDetail->id)?> #comment-main');
                    alert('评论成功<?=$this->context->config->site->enableComment==2?'，审核通过后即可展现':''?>。');
                }else{
                    alert(response.message);
                }
            });

            e.preventDefault();
        });

        // 评论关联点赞操作
        $comment.on('click','.js-comment-relation',function () {
            var $this = $(this);
            if($this.hasClass('disabled')) return false;
            $this.addClass('disabled');
            $.ajax({
                url:$this.attr("href"),
                dataType:"json",
                success:function(res){
                    if(res.status){
                        var $number = $this.find('span');
                        if(res.action){
                            $number.text(parseInt($number.text())+1);
                            $this.addClass('active').siblings().addClass('disabled');
                        }else{
                            $number.text(parseInt($number.text())-1);
                            $this.removeClass('active').siblings().removeClass('disabled');
                        }
                    }else{
                        alert(res.message);
                    }
                },
                error:function(XMLHttpRequest, textStatus, errorThrown){
                    if(XMLHttpRequest.status === 302){
                        location.href = "<?=$this->generateUserUrl('login')?>?jumpLink="+location.href;
                    }else{
                        alert("操作失败！");
                    }
                },
                complete:function(){
                    $this.removeClass("disabled");
                }
            });
            return false;
        });

        // 分页
        $comment.on('click','.pagination a',function () {
            $comment.load($(this).attr('href')+' #comment-main');
            return false;
        });
    });
</script>
<?php $this->endblock();?>


