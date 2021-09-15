<?php
/**
 * @block topButton 顶部按钮
 * @var $dataList
 */

use manage\assets\ListAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = '全局碎片设计';

ListAsset::register($this);
$this->registerJs("listApp.init();", View::POS_READY);
?>
<?php $this->beginBlock('topButton'); ?>
<?= Html::a('返回列表', ['index','scope'=>'custom'], ['class' => 'btn btn-default']) ?>
<?= Html::a('新增碎片', ['create'], ['class' => 'btn btn-primary']) ?>
<?php $this->endBlock(); ?>

<!-- 数据列表开始 -->
<div class="panel panel-default list-data">
    <div class="panel-body">
        <div class="table-responsive scroll-bar">
            <table class="table table-hover" id="list_data">
                <thead>
                <tr>
                    <td align="center" width="60"><?=Yii::t('common','Id')?></td>
                    <td>标题</td>
                    <td>标识</td>
                    <td align="center"><?=Yii::t('common','Operation')?></td>
                </tr>
                </thead>
                <tbody>
                <?php foreach($dataList as $item){ ?>
                    <tr>
                        <td align="center"><?=$item['id']?></td>
                        <td>
                            <a href="<?=Url::to(['update','id'=>$item['id']]);?>" class="text-primary"><?=Html::encode($item['title'])?></a>
                        </td>
                        <td><?=Html::encode($item['name'])?></td>
                        <td class="opt" align="center">
                            <?= Html::a(Yii::t('common','Modify'), ['update', 'id' => $item['id']], ['class' => 'text-primary']) ?>
                            <?= Html::a(Yii::t('common','Delete'), ['delete','id' => $item['id']],['class'=>'j_batch','data-action'=>'del']) ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?=empty($dataList)?'<p class="list-data-default">'.Yii::t('common','No Data Found !').'</p>':''?>
        </div>
    </div>
</div><!-- 数据列表结束 -->