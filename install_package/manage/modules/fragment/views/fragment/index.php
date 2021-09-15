<?php
/**
 * @block topButton 顶部按钮
 * @var $dataList
 * @var $categoryInfo
 */

use common\helpers\ArrayHelper;
use common\helpers\StringHelper;
use manage\assets\ListAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = '碎片管理';
$this->params['subTitle'] = '（'.$categoryInfo->title.'）';

ListAsset::register($this);
$this->registerJs("listApp.init();", View::POS_READY);
?>
<?php $this->beginBlock('topButton'); ?>
<?= Html::a('返回列表', ['category/index'], ['class' => 'btn btn-default j_goback']) ?>
<?= Html::a('新增碎片', ['create','category_id'=>$categoryInfo->id], ['class' => 'btn btn-primary']) ?>
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
                    <td>碎片标识</td>
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

<!-- 数据分页开始 -->
<nav class="nav-operation clearfix">
    <div class="tools">
        <?= Html::a(Yii::t('common','Batch sort'), ['sort'],['id'=>'j_sort_batch','data-depth'=>1,'data-pid'=>0,'data-empty'=>empty($dataList)?1:0]) ?>
    </div>
</nav><!-- 数据分页结束 -->
<!-- 排序 -->
<script type="text/html" id="tpl_sort_batch">
    <div class="dd">
        <?=Html::hiddenInput('sort',implode(',',ArrayHelper::getColumn($dataList,'sort')),['class'=>'input-sort'])?>
        <ol class="dd-list">
            <?php foreach ($dataList as $item){?>
                <li class="dd-item" data-id="<?=$item->id?>">
                    <div class="dd-handle"><?=$item->id.'：'.StringHelper::truncate($item->title,24)?></div>
                </li>
            <?php }unset($dataList);?>
        </ol>
        <form action="javascript:;" method="post">
            <?=Html::hiddenInput(Yii::$app->request->csrfParam,Yii::$app->request->csrfToken)?>
            <?=Html::hiddenInput('data',null,['class'=>'input-data'])?>
        </form>
    </div>
</script>