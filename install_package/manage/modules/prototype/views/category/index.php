<?php
/**
 * @block topButton 顶部按钮
 * @var $searchModel
 * @var $dataProvider
 * @var $group
 * @var $categoryList
 * @var $modelList
 * @var $pid
 * @var $userAccessButton
 */

use common\helpers\ArrayHelper;
use manage\assets\ListAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use common\widgets\ActiveForm;

/**
 * 获取node List视图
 * @param $siteInfo
 * @param $categoryList
 * @param $categoryInfo
 * @param string|null $defaultView
 * @return string
 */
function findNodeListView($siteInfo,$categoryList,$categoryInfo,$defaultView = 'index'){
    if($categoryInfo['type'] == 3) return '--';

    $parentCategoryList = ArrayHelper::getParents($categoryList,$categoryInfo['id']);

    $viewName = '';
    foreach(array_reverse($parentCategoryList,false) as $item){
        if(!empty($item['template'])){
            $viewName = $item['template'];
            break;
        }
    }
    if(strpos($viewName,'//') === 0){
        $view = str_replace('//','',$viewName);
    }else{
        switch($categoryInfo['type']){
            case 1:
                $view = 'page/'.(empty($viewName)?$defaultView:$viewName);
                break;
            case 2:
                if(empty($viewName)){
                    $view = $categoryInfo['slug_rules'];
                }else{
                    $temp = explode('/',$categoryInfo['slug_rules']);
                    $view = $temp[0].'/'.$viewName;
                }

                break;
            default:
                $view = $categoryInfo['model']['name'].'/'.(empty($viewName)?$defaultView:$viewName);
                break;
        }
    }

    return $siteInfo->theme.'/'.$view.'.php';
}

/**
 * 获取node 列表类容视图
 * @param $siteInfo
 * @param $categoryList
 * @param $categoryInfo
 * @param string $defaultView
 * @return string
 */
function findNodeDetailView($siteInfo,$categoryList,$categoryInfo,$defaultView = 'detail'){
    if($categoryInfo['type']) return '--';

    $parentCategoryList = ArrayHelper::getParents($categoryList,$categoryInfo['id']);

    $viewName = '';
    foreach(array_reverse($parentCategoryList,false) as $item){
        if(!empty($item['template_content'])){
            $viewName = $item['template_content'];
            break;
        }
    }
    if(strpos($viewName,'//') === 0){
        $view = str_replace('//','',$viewName);
    }else{
        $view = $categoryInfo['model']['name'].'/'.(empty($viewName)?$defaultView:$viewName);
    }

    return $siteInfo->theme.'/'.$view.'.php';
}

$this->title = '栏目管理';

ListAsset::register($this);
$this->registerJs("listApp.init();", View::POS_READY);
?>
<?php if($userAccessButton['create']){ $this->beginBlock('topButton'); ?>
<div class="btn-group">
    <?= Html::button('新增栏目', ['class' => 'btn btn-primary dropdown-toggle','data-toggle'=>'dropdown']) ?>
    <ul class="dropdown-menu">
        <?php foreach($this->context->categoryTypeList as $i=>$item){?>
        <li><a href="<?=Url::to(['create','type'=>$i])?>"><?=$item?></a></li>
        <?php } ?>
    </ul>
</div>

<?php $this->endBlock(); } ?>

<!-- 搜索框开始 -->
<?php $form = ActiveForm::begin([
    'action' => [Yii::$app->controller->action->id],
    'method' => 'get',
    'options'=>['class' => 'form-inline search-data'],
]); ?>
<!-- 表单控件开始 -->
<div class="form-group">
    <label class="control-label">父级栏目</label>
    <?= Html::dropDownList('pid', $pid, ArrayHelper::map($categoryList, 'id', 'title'),['prompt'=>'—不限—','class'=>'form-control']) ?>
    <div class="help-block"></div>
</div>
<?= $form->field($searchModel, 'status')->dropDownList([1=>'显示',0=>'隐藏'], ['prompt'=>'—不限—','class'=>'form-control'])->label('前台是否显示') ?>
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
                    <td>栏目标题</td>
                    <td width="100">栏目类型</td>
                    <?php if($userAccessButton['status']){?>
                    <td align="center" width="100">前台是否显示</td>
                    <?php } ?>
                    <?php if($this->context->isSuperAdmin):?>
                        <td align="center">开发数据</td>
                    <?php endif;?>
                    <?php if($userAccessButton['create'] || $userAccessButton['update'] || $userAccessButton['delete']){?>
                    <td align="center" width="200"><?=Yii::t('common','Operation')?></td>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php
                $systemMarksId = [];
                $dataList = ArrayHelper::linear($dataProvider->models, '&nbsp;&nbsp;├&nbsp;',$pid?:0);
                foreach($dataList as $item){
                    if(!empty($item['system_mark'])) $systemMarksId[] = '#tr-'.$item['system_mark'];
                    ?>
                    <tr<?=empty($item['system_mark'])?'':' id="tr-'.$item['system_mark'].'"'?> class="level_<?=$item['count']?>" <?php if($item['count'] > 1){echo 'style="display:none;"';}?>>
                        <td><?= Html::checkbox('choose',false,['value'=>$item['id']])?></td>
                        <td align="center"><?=$item['id']?></td>
                        <td>
                            <?php if($item['hasChild']){?>
                                <span class="list-icon list-icon-ch spacing-<?=$item['count']?> j_list_tree" data-id="<?=$item['id']?>" data-level="<?=$item['count']?>"></span>
                            <?php }else{?>
                                <span class="list-icon list-icon-nch spacing-<?=$item['count']?>"></span>
                            <?php }?>

                            <?php if($item['type'] == 3 && (strpos($item['link'], 'javascript',0) === 0 || strpos($item['link'], '#',0) === 0)){?>
                                <span><?=Html::encode($item['title'])?></span>
                            <?php }else{
                                if(empty($item['link'])){
                                    $suffix = $this->context->config['site']['urlSuffix'];
                                    switch ($item['type']){
                                        case 0:
                                        case 1:
                                            $url = empty($item['slug'])?'/category_'.$item['id'].$suffix:'/'.$item['slug'].$suffix;
                                            break;
                                        case 2:
                                            if($item['slug_rules'] == 'site/index'){
                                                $url = '/index'.$suffix;
                                            }else{
                                                $url = empty($item['slug'])?'/'.$item['slug_rules'].$suffix:'/'.$item['slug'].$suffix;
                                            }

                                            break;
                                        default:
                                            $url = $item['link'];
                                            break;
                                    }
                                }else{
                                    $url = $item['link'];
                                }

                                if(!$this->context->siteInfo->is_default){
                                    if(!(stripos($url,'javascript:',0) === 0 || stripos($url,'#',0) === 0))
                                        $url = '/'.$this->context->siteInfo->slug.$url;
                                }
                                echo $item['slug_rules'] == 'search/index'?'<span>'.Html::encode($item['title']).'</span>':Html::a(strip_tags($item['title']),$url,['target'=>'_blank']);
                            } ?>
                        </td>
                        <td><span class="label label-primary"><?=$this->context->categoryTypeList[$item['type']]?></span></td>

                        <?php if($userAccessButton['status']){?>
                        <td align="center">
                            <?php if($item['status'] == 1){?>
                                <?= Html::a('<span class="iconfont">&#xe62a;</span>', ['status', 'id' => $item['id']], ['class' => 'j_batch status-'.$item['id'],'data-action'=>'status','data-value'=>0,'title'=>'隐藏']) ?>
                            <?php }else{?>
                                <?= Html::a('<span class="iconfont">&#xe625;</span>', ['status', 'id' => $item['id']], ['class' => 'j_batch status-'.$item['id'],'data-action'=>'status','data-value'=>1,'title'=>'显示']) ?>
                            <?php }?>
                        </td>
                        <?php } ?>

                        <?php if($this->context->isSuperAdmin):?>
                            <td align="center"><a href="javascript:void(0);" class="js-dev-info" data-id="<?=$item['id']?>" data-model="<?=$item['model']["name"]?$item['model']["title"].' / '.$item['model']["name"].' / '.$item['model']['id']:'--';?>" data-tpl="<?=findNodeListView($this->context->siteInfo,$dataList,$item).','.findNodeDetailView($this->context->siteInfo,$dataList,$item)?>">点击查看</a></td>
                        <?php endif;?>

                        <?php if($userAccessButton['create'] || $userAccessButton['update'] || $userAccessButton['delete']){?>
                        <td class="opt" align="center">
                            <?php if($userAccessButton['create']){?>
                            <div class="btn-group">
                                <?= Html::button('新增子栏目', ['class' => 'btn btn-link dropdown-toggle','data-toggle'=>'dropdown']) ?>
                                <ul class="dropdown-menu">
                                    <?php foreach($this->context->categoryTypeList as $k=>$value){?>
                                        <li><a href="<?=Url::to(['create','type'=>$k,'pid'=>$item['id']])?>"><?=$value?></a></li>
                                    <?php } ?>
                                    <li class="divider"></li>
                                    <li><a href="<?=Url::to(['create','type'=>$item['type'],'pid'=>$item['id'],'action'=>'copy'])?>">复制该栏目</a></li>
                                </ul>
                            </div>
                            <?php } if($userAccessButton['update']){?>
                            <?= Html::a(Yii::t('common','Modify'), ['update', 'id' => $item['id']], ['class' => 'text-primary']) ?>
                            <?php } if($userAccessButton['delete']){?>
                                <?= Html::a(Yii::t('common','Delete'), ['delete','id' => $item['id']],['class'=>'j_batch','data-action'=>'del']) ?>
                            <?php }?>
                        </td>
                        <?php }?>
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
        <?php if($userAccessButton['status']){?>
        <span>|</span>
        <?= Html::a('&#xe62a;', ['status','value'=>1],['class'=>'iconfont j_batch','data-action'=>'batchStatus','data-value'=>1,'title'=>Yii::t('common','Batch enable')]) ?>
        <?= Html::a('&#xe625;', ['status','value'=>0],['class'=>'iconfont j_batch','data-action'=>'batchStatus','data-value'=>0,'title'=>Yii::t('common','Batch disable')]) ?>
        <?php } if($userAccessButton['sort']){?>
        <span>|</span>
        <?= Html::a(Yii::t('common','Batch sort'), ['sort'],['id'=>'j_sort_batch','class'=>' btn btn-xs btn-primary','data-deep'=>'true','data-pid'=>0,'data-empty'=>empty($dataList)?1:0]) ?>
        <?php }?>
    </div>
</nav><!-- 数据分页结束 -->

<?php if($userAccessButton['sort']){?>
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
            echo sortHtml(ArrayHelper::tree($dataList));
            unset($dataList);?>
        </ol>
        <form action="javascript:;" method="post">
            <?=Html::hiddenInput(Yii::$app->request->csrfParam,Yii::$app->request->csrfToken)?>
            <?=Html::hiddenInput('data',null,['class'=>'input-data'])?>
        </form>
    </div>
</script>
<?php }?>

<?php $this->beginBlock('endBlock');?>
<script>
    $(function () {
        $('.js-dev-info').click(function () {
            var html = '',$this = $(this);

            var tpl = $this.data('tpl');
            tpl = tpl.split(',');

            html += '<p style="margin-bottom: 5px;"><b>模型信息：</b>'+$this.data('model')+'</p><p style="margin-bottom: 5px;"><b>'+(tpl[1]==='--'?'内容':'列表')+'模板：</b>'+tpl[0]+'</p>';
            if(tpl[1]!=='--'){
                html += '<p style="margin-bottom: 5px;"><b>内容模板：</b>'+tpl[1]+'</p>';
            }

            commonApp.dialog.default(['查看开发数据',html],{
                'confirmClass':'hide',
                'cancelButton':'关闭'
            });
        });

        // 禁用必要数据删除
	    <?php
            if(!empty($systemMarksId)){
        ?>
        $('<?=implode(',',array_unique($systemMarksId))?>').find('.j_batch[data-action="del"]').each(function (i,n) {
            var $this = $(n);
            $this.addClass('disabled').removeAttr('href').unbind('click').css({'color':'#999','text-decoration':'none'});
        });
        <?php } ?>
    });
</script>
<?php $this->endBlock();?>

