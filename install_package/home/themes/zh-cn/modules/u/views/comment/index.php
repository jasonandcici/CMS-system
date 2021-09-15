<?php
/**
 * @var $searchModel
 * @var $dataProvider
 */

$this->params['active'] = 'comment';

$dataProvider->pagination = [
	'pageSize'=>5
];

$dataList = $dataProvider->getModels();
?>
<div class="row">
	<?php
	if (!empty($dataList)) {
		foreach ($dataList as $item) {
			?>
			<div class="media" style="padding-bottom: 10px;border-bottom: 1px dashed #ddd;margin-bottom: 10px;">
				<div class="media-body">
					<p>
                        <?=$item->content?>
                        <br>
                        <span><i class="glyphicon glyphicon-thumbs-up"></i><?=$item->count_like?></span>
                        <span><i class="glyphicon glyphicon-thumbs-down"></i><?=$item->count_bad?></span>
                    </p>
					<p>
                        <a class="btn btn-xs btn-info" href="<?=$this->generateCommentDetailUrl($item->id)?>" target="_blank">查看详情</a>
						<a class="btn btn-xs btn-primary js-delete" href="<?=$this->generateUserCommentUrl('delete',$item->id)?>">删除</a>
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
        $('.js-delete').click(function () {
            var $this = $(this);
            if($this.hasClass('disabled'))  return false;
            $this.addClass('disabled');
            if(confirm('您确定要删除这条评论吗？')){
                $.ajax({
                    url:$this.attr("href"),
                    dataType:"json",
                    success:function(res){
                        if(res.status){
                            $this.parents('.media').fadeOut(function () {
                                $(this).remove();
                            });
                        }else{
                            alert(res.message);
                        }
                    },
                    complete:function(){
                        $this.removeClass("disabled");
                    }
                });
            }
            return false;
        });
    });
</script>
<?php $this->endBlock();?>
