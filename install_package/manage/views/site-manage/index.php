<?php
/**
 * @block topButton 顶部按钮
 * @var $dataList
 * @var $userAccessButton
 */

use manage\assets\ListAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = '站点管理';

ListAsset::register($this);
$this->registerJs("listApp.init();", View::POS_READY);
?>
<?php if($this->context->isSuperAdmin){ $this->beginBlock('topButton'); ?>
<?= Html::a('新增站点', ['create'], ['class' => 'btn btn-primary']) ?>
<?php $this->endBlock(); } ?>

<!-- 数据列表开始 -->
<div class="panel panel-default list-data">
    <div class="panel-body">
        <div class="table-responsive scroll-bar">
            <table class="table table-hover" id="list_data">
                <thead>
                <tr>
                    <td><?=Yii::t('common','Id')?></td>
                    <td>站点名</td>
                    <td>标识</td>
                    <td>主题</td>
                    <td class="text-center">独立移动设备主题</td>
                    <?php if($userAccessButton['set-default']){?>
                    <td class="text-center">是否默认</td>
                    <?php }if($userAccessButton['status']){?>
                    <td align="center" width="60">状态</td>
                    <?php } if($userAccessButton['update'] || $this->context->isSuperAdmin){ ?>
                    <td align="center"><?=Yii::t('common','Operation')?></td>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach($dataList as $item){ ?>
                    <tr>
                        <td><?=$item->id?></td>
                        <td><?=Html::encode($item->title)?></td>
                        <td><?=$item->slug?></td>
                        <td><?=$item->theme?></td>
                        <td align="center"><?=$item->enable_mobile?"是":"否"?></td>
                        <?php if($userAccessButton['set-default']){?>
                        <td align="center"><?=Html::radio('is_default',$item->is_default,['value'=>$item->id,'class'=>'j_setDefault'])?></td>
                        <?php }if($userAccessButton['status']){?>
                        <td align="center">
                            <?php if($item->is_enable == 1){?>
                                <?= Html::a('<span class="iconfont">&#xe62a;</span>', ['status', 'id' => $item->id], ['class' => 'j_batch status-'.$item->id,'data-action'=>'status','data-value'=>0,'title'=>Yii::t('common','Disable')]) ?>
                            <?php }else{?>
                                <?= Html::a('<span class="iconfont">&#xe625;</span>', ['status', 'id' => $item->id], ['class' => 'j_batch status-'.$item->id,'data-action'=>'status','data-value'=>1,'title'=>Yii::t('common','Enable')]) ?>
                            <?php }?>
                        </td>
                        <?php } if($userAccessButton['update'] || $this->context->isSuperAdmin){ ?>
                        <td class="opt" align="center">
                            <?php if($this->context->isSuperAdmin) echo Html::a(Yii::t('common','Replication site'), ['copy','id' => $item->id],['class'=>'j_copy']).'<span class="text-info">|</span>'; ?>
                            <?php if($userAccessButton['update']){?>
                            <?= Html::a(Yii::t('common','Modify'), ['update', 'id' => $item->id], ['class' => 'text-primary']) ?>
                            <?php } if($this->context->isSuperAdmin) echo Html::a(Yii::t('common','Delete'), ['delete','id' => $item->id],['class'=>'j_batch','data-action'=>'del']); ?>
                        </td>
                        <?php } ?>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?=empty($dataList)?'<p class="list-data-default">'.Yii::t('common','No Data Found !').'</p>':''?>
        </div>
    </div>
</div><!-- 数据列表结束 -->
<?php $this->beginBlock('endBlock');?>
<script>
    $(function () {
        $('.j_setDefault').change(function () {
            var $this = $(this);
            commonApp.fieldUpdateRequest($this,{
                url:'<?=Url::to(['set-default'])?>',
                data:{id:$this.val()}
            });
        });

        $('.j_copy').click(function () {
            var $this = $(this);
            commonApp.dialog.warning('您确定要执行此操作吗？',{
                confirm:function () {
                    commonApp.fieldUpdateRequest($this,{
                        successCallback:function () {
                            setTimeout(function () {
                                history.go(0);
                            },1500);
                        }
                    });
                }
            });
            return false;
        });
    });
</script>
<?php $this->endBlock();?>
