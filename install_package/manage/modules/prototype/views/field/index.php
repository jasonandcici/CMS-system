<?php
/**
 * @block topButton 顶部按钮
 * @var $model
 * @var $filedList
 */

use common\helpers\ArrayHelper;
use manage\assets\ListAsset;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\web\View;
use common\widgets\ActiveForm;
use yii\widgets\LinkPager;

$this->title = '字段管理';
$this->params['subTitle'] = '('.$model->title.')';

ListAsset::register($this);
$this->registerJs("listApp.init();", View::POS_READY);
?>
<?php $this->beginBlock('topButton'); ?>
<a href="<?=Url::to(['model/index'])?>" class="btn btn-default">返回列表</a>
<?= Html::a('新增字段', ['create','model_id'=>$model->id], ['class' => 'btn btn-primary']) ?>
<?php $this->endBlock(); ?>

<!-- 数据列表开始 -->
<div class="panel panel-default list-data">
    <div class="panel-body">
        <div class="table-responsive scroll-bar">
            <table class="table table-hover" id="list_data">
                <thead>
                <tr>
                    <td width="60"><?=Yii::t('common','Select')?></td>
                    <td><?=Yii::t('common','Id')?></td>
                    <td>字段标题</td>
                    <td>字段名称</td>
                    <td>字段类型</td>
                    <td align="center">是否必填</td>
                    <td align="center">排序</td>
                    <td align="center"><?=Yii::t('common','Operation')?></td>
                </tr>
                </thead>
                <tbody>
                <?php foreach($filedList as $i=>$item){?>
                    <tr>
                        <td><?= Html::checkbox('choose',false,['value'=>$item->id])?></td>
                        <td><?=$item->id?></td>
                        <td><?=$item->title?></td>
                        <td><?=$item->name?></td>
                        <td><?=$item->filedTypeText[$item->type]?></td>
                        <td align="center"><?=$item->is_required?'<span class="label label-danger">是</span>':'<span class="label label-info">否</span>'?></td>
                        <td align="center">
                            <span class="sort j_sort">
                                <?php
                                $_tag = $i == 0?' disabled':'';
                                echo Html::tag('a',Html::tag('span','&#xe62e;',['class'=>'iconfont']),['class'=>'sort-up'.$_tag,'href'=>Url::to(['sort','id'=>$item->id,'mode'=>1]),'title'=>'上移']);
                                $_tag = $i+1 == count($filedList)?' disabled':'';
                                echo Html::tag('a',Html::tag('span','&#xe62d;',['class'=>'iconfont']),['class'=>'sort-down'.$_tag,'href'=>Url::to(['sort','id'=>$item->id,'mode'=>0]),'title'=>'下移']);
                                unset($_tag);
                                ?>
                            </span>
                        </td>
                        <td class="opt" align="center">
                            <?= Html::a(Yii::t('common','Modify'), ['update','model_id'=>$model->id, 'id' => $item->id], ['class' => 'text-primary']) ?>
                            <?= Html::a(Yii::t('common','Delete'), ['delete','id' => $item->id],['class'=>'j_batch','data-action'=>'del']) ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?=empty($model)?'<p class="list-data-default">'.Yii::t('common','No Data Found !').'</p>':''?>
        </div>
    </div>
</div><!-- 数据列表结束 -->

<!-- 数据分页开始 -->
<nav class="nav-operation clearfix">
    <div class="tools">
        <a href="javascript:;" id="j_choose_all"><?=Yii::t('common','Select All')?></a>
        <a href="javascript:;" id="j_choose_reverse"><?=Yii::t('common','Select Invert')?></a>
        <a href="javascript:;" id="j_choose_empty"><?=Yii::t('common','Clears all')?></a>
        <span>|</span>
        <?= Html::a(Yii::t('common','Batch sort'), ['sort'],['id'=>'j_sort_batch','data-depth'=>1,'data-pid'=>0,'data-empty'=>empty($filedList)?1:0]) ?>
    </div>
</nav><!-- 数据分页结束 -->

<!-- 排序 -->
<script type="text/html" id="tpl_sort_batch">
    <div class="dd">
        <?=Html::hiddenInput('sort',implode(',',ArrayHelper::getColumn($filedList,'sort')),['class'=>'input-sort'])?>
        <ol class="dd-list">
            <?php foreach ($filedList as $item){?>
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