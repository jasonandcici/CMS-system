<?php
/**
 * @var $searchModel
 * @var $dataProvider
 * @var $commentModel
 * @var $enableComment
 * @var $contentDetail
 */

use common\widgets\ActiveForm;
use common\helpers\HtmlHelper;

$dataProvider->pagination = [
	'pageSize'=>5
];

$dataList = $dataProvider->getModels();
?>

<div class="page-header">
    <h2><?=$contentDetail->title?></h2>
    <p>共有 <b><?=$contentDetail->commentCount?></b> 条评论</p>
</div>

<div id="comment-main">
    <!-- 评论表单 -->
    <?php
    if($enableComment):
    $form = ActiveForm::begin([
        'id' => 'js-comment-form',
        'validateOnBlur'=>false,
        'validateOnSubmit'=>true
    ]); ?>
    <?= $form->field($commentModel, 'content')->textarea()?>
    <?php if(Yii::$app->getUser()->getIsGuest()){?>
        <span class="btn btn-primary disabled">未登录</span>
    <?php }else{?>
        <?= HtmlHelper::submitButton('提交',['class'=>'btn btn-primary'.(Yii::$app->getUser()->getIsGuest()?' disabled':''),'data-loading-text'=>'提交中...']) ?>
    <?php } ?>
    <?php ActiveForm::end();endif; ?>

    <!-- 评论列表 -->
    <?php
    if (!empty($dataList)) {
	    $relationCheck = $this->findCommentIsRelation(['like','bad'],$dataList);
        foreach ($dataList as $item) {
            ?>
            <div class="media mt-2">
                <div class="media-left pr-2">
                    <?=HtmlHelper::getImgHtml($item->userProfile->avatar?:'/images/avatar.png',['w/60/h/60','width'=>60])?>
                </div>
                <div class="media-body">
                    <h4 class="media-heading">
                        <?=$item->userProfile->nickname?>
                    </h4>
                    <p><a href="<?=$this->generateCommentDetailUrl($item->id)?>" target="_blank"><?=$item->content?></a></p>
                    <p>
                        <a class="btn btn-xs btn-default<?=$relationCheck['like'][$item->id]?' active':($relationCheck['bad'][$item->id]?' disabled':'')?> js-comment-relation" href="<?=$this->generateUserCommentUrl('like',$item->id)?>"><i class="glyphicon glyphicon-thumbs-up"></i><span><?=$item->count_like?></span></a>
                        <a class="btn btn-xs btn-default<?=$relationCheck['bad'][$item->id]?' active':($relationCheck['like'][$item->id]?' disabled':'')?> js-comment-relation" href="<?=$this->generateUserCommentUrl('bad',$item->id)?>"><i class="glyphicon glyphicon-thumbs-down"></i><span><?=$item->count_bad?></span></a>
                    </p>
                </div>
            </div>
            <?php
        }
    } else { ?>
        <div class="text-center p-3">
            <h3>暂无评论！</h3>
        </div>
    <?php } ?>
    <!-- 分页 -->
    <?= \yii\widgets\LinkPager::widget([
        'pagination' => $dataProvider->pagination,
        'firstPageCssClass' => 'first',
        'prevPageCssClass' => 'prev',
        'firstPageLabel' => '首页',
        'prevPageLabel' => '上一页',

        'nextPageCssClass' => 'next',
        'lastPageCssClass' => 'end',
        'nextPageLabel' => '下一页',
        'lastPageLabel' => '尾页',

        'maxButtonCount' => 8,
        'disabledPageCssClass' => true,
        'options' => ['class' => 'pagination']
    ]) ?>

</div>

<?php $this->beginBlock('endBody');?>
	<script>
        $(function () {
            // 表单提交
            $('#js-comment-form').on('beforeSubmit', function (e) {
                var $form = $(this);
                var $submit = $form.find('[type="submit"]');

                $submit.button('loading');
                $.post($form.attr('action'),$form.serialize(),function (response) {
                    if(typeof response === 'string') response = JSON.parse(response);
                    $submit.button('reset');
                    if(response.status){
                        alert('评论成功<?=$this->context->config->site->enableComment==2?'，审核通过后即可展现':''?>。');
                        history.go(0);
                    }else{
                        alert(response.message);
                    }
                });
            }).on('submit', function (e) {
                e.preventDefault();
            });

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
