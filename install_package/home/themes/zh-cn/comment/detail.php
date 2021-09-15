<?php
/**
 * @var $dataDetail
 * @var $enableComment
 * @var $contentDetail
 */

use common\helpers\HtmlHelper;

$relationCheck = $this->findCommentIsRelation(['like','bad'],$dataDetail);
?>

<div class="media mt-2">
	<div class="media-left pr-2">
		<?=HtmlHelper::getImgHtml($dataDetail->userProfile->avatar?:'/images/avatar.png',['w/60/h/60','width'=>60])?>
	</div>
	<div class="media-body">
		<h4 class="media-heading">
			<?=$dataDetail->userProfile->nickname?>
		</h4>
		<p><?=$dataDetail->content?></p>
		<p>
			<a class="btn btn-xs btn-default<?=$relationCheck['like'][$dataDetail->id]?' active':($relationCheck['bad'][$dataDetail->id]?' disabled':'')?> js-comment-relation" href="<?=$this->generateUserCommentUrl('like',$dataDetail->id)?>"><i class="glyphicon glyphicon-thumbs-up"></i><span><?=$dataDetail->count_like?></span></a>
			<a class="btn btn-xs btn-default<?=$relationCheck['bad'][$dataDetail->id]?' active':($relationCheck['like'][$dataDetail->id]?' disabled':'')?> js-comment-relation" href="<?=$this->generateUserCommentUrl('bad',$dataDetail->id)?>"><i class="glyphicon glyphicon-thumbs-down"></i><span><?=$dataDetail->count_bad?></span></a>
		</p>
	</div>
</div>

<?php $this->beginBlock('endBody');?>
    <script>
        $(function () {
            // 评论关联操作
            $('.js-comment-relation').click(function () {
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
        });
    </script>
<?php $this->endBlock();?>