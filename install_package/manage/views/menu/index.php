<?php
/**
 * @block topButton 顶部按钮
 * @var $searchModel
 * @var $dataProvider
 * @var $menuList
 * @var $pid
 */

use common\helpers\ArrayHelper;
use manage\assets\ListAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use common\widgets\ActiveForm;

$this->title = '后台菜单管理';

ListAsset::register($this);
$this->registerJs("listApp.init();", View::POS_READY);
?>
<?php $this->beginBlock('topButton'); ?>
<?= Html::a('新增菜单', ['create'], ['class' => 'btn btn-primary']) ?>
<?php $this->endBlock(); ?>

<!-- 搜索框开始 -->
<?php $form = ActiveForm::begin([
    'action' => [Yii::$app->controller->action->id],
    'method' => 'get',
    'options'=>['class' => 'form-inline search-data'],
]); ?>
<!-- 表单控件开始 -->
<div class="form-group">
    <label class="control-label">父级菜单</label>
    <?= Html::dropDownList('pid', $pid, ArrayHelper::map($menuList, 'id', 'title'),['prompt'=>'—不限—','class'=>'form-control']) ?>
    <div class="help-block"></div>
</div>
<?= $form->field($searchModel, 'status')->dropDownList([1=>'启用',0=>'禁用'], ['prompt'=>'—不限—','class'=>'form-control']) ?>
<!-- 表单控件结束 -->
<?= Html::submitButton(Yii::t('common','Filter'), ['class' => 'btn btn-info']) ?>
<?php ActiveForm::end(); ?><!-- 搜索框结束 -->

<!-- 数据列表开始 -->
<div class="panel panel-default list-data">
    <div class="panel-body">
        <div class="table-responsive scroll-bar">
            <table class="table table-hover" id="list_data">
                <thead>
                <tr>
                    <td width="60"><?=Yii::t('common','Select')?></td>
                    <td align="center" width="60"><?=Yii::t('common','Id')?></td>
                    <td>菜单名称</td>
                    <td width="100">菜单类型</td>
                    <td align="center" width="60">状态</td>
                    <td align="center" width="200"><?=Yii::t('common','Operation')?></td>
                </tr>
                </thead>
                <tbody>
                <?php
                $dataList = ArrayHelper::linear($dataProvider->models, '&nbsp;&nbsp;├&nbsp;',$pid?:0);
                foreach($dataList as $item){ ?>
                    <tr class="level_<?=$item['count']?>" <?php if($item['count'] > 1){echo 'style="display:none;"';}?>>
                        <td><?= Html::checkbox('choose',false,['value'=>$item['id']])?></td>
                        <td align="center"><?=$item['id']?></td>
                        <td>
                            <?php if($item['hasChild']){?>
                                <span class="list-icon list-icon-ch spacing-<?=$item['count']?> j_list_tree" data-id="<?=$item['id']?>" data-level="<?=$item['count']?>"></span>
                            <?php }else{?>
                                <span class="list-icon list-icon-nch spacing-<?=$item['count']?>"></span>
                            <?php }?>
                            <a href="<?=Url::to(['update','id'=>$item['id']]);?>" class="text-primary"><?=Html::encode($item['title'])?></a>
                        </td>
                        <td><span class="label label-info"><?=$this->context->menuTypeList[$item['type']]?></span></td>
                        <td align="center">
                            <?php if($item['status'] == 1){?>
                                <?= Html::a('<span class="iconfont">&#xe62a;</span>', ['status', 'id' => $item['id']], ['class' => 'j_batch status-'.$item['id'],'data-action'=>'status','data-value'=>0,'title'=>Yii::t('common','Disable')]) ?>
                            <?php }else{?>
                                <?= Html::a('<span class="iconfont">&#xe625;</span>', ['status', 'id' => $item['id']], ['class' => 'j_batch status-'.$item['id'],'data-action'=>'status','data-value'=>1,'title'=>Yii::t('common','Enable')]) ?>
                            <?php }?>
                        </td>
                        <td class="opt" align="center">
                            <?= Html::a('添加子菜单', ['create', 'pid' => $item['id']], ['class' => 'text-primary']) ?>
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
        <a href="javascript:;" id="j_choose_all"><?=Yii::t('common','Select All')?></a>
        <a href="javascript:;" id="j_choose_reverse"><?=Yii::t('common','Select Invert')?></a>
        <a href="javascript:;" id="j_choose_empty"><?=Yii::t('common','Clears all')?></a>
        <span>|</span>
        <?= Html::a(Yii::t('common','Batch sort'), ['sort'],['id'=>'j_sort_batch','data-deep'=>'true','data-pid'=>$pid?:0,'data-empty'=>empty($dataList)?1:0]) ?>
        <?= Html::a('&#xe62a;', ['status','value'=>1],['class'=>'iconfont j_batch','data-action'=>'batchStatus','data-value'=>1,'title'=>Yii::t('common','Batch enable')]) ?>
        <?= Html::a('&#xe625;', ['status','value'=>0],['class'=>'iconfont j_batch','data-action'=>'batchStatus','data-value'=>0,'title'=>Yii::t('common','Batch disable')]) ?>
    </div>
</nav><!-- 数据分页结束 -->

<!-- 排序 -->
<script type="text/html" id="tpl_sort_batch">
    <div class="dd">
        <?=Html::hiddenInput('sort',implode(',',ArrayHelper::getColumn($dataList,'sort')),['class'=>'input-sort'])?>
        <ol class="dd-list">
            <?php
            function sortHtml($data, $pid = 0, $count = 0){
                $_html = '';
                foreach($data as $key=>$value){
                    // 生成li
                    $_html .= '<li class="dd-item" data-id="'.$value['id'].'"><div class="dd-handle">'.$value['id'].'：'.$value['title'].'</div>';
                    if($value['pid'] == $pid){
                        $_html .= sortHtml($value['child'],$value['id'],$count+1);
                    }
                    $_html .='</li>';
                }

                return $_html?($pid==0?$_html:'<ol class="dd-list" style="display: none;">'.$_html.'</ol>'):'';
            }
            echo sortHtml(ArrayHelper::tree($dataList,$pid?:0));
            unset($dataList);?>
        </ol>
        <form action="javascript:;" method="post">
            <?=Html::hiddenInput(Yii::$app->request->csrfParam,Yii::$app->request->csrfToken)?>
            <?=Html::hiddenInput('data',null,['class'=>'input-data'])?>
        </form>
    </div>
</script>