<?php
/**
 * @var $parameters
 * @var $fields
 */

use common\helpers\ArrayHelper;
use common\helpers\HtmlHelper;
use common\helpers\UrlHelper;

$action = Yii::$app->getRequest()->get('action');
$actionName = str_replace('-','_',Yii::$app->controller->action->id);
function getFormControl($p){
    $html = '';
	if(isset($p['value'])){
		$formName = str_replace('-','_',Yii::$app->controller->action->id).'['.$p['name'].']';
		$options = ['class'=>'form-control input-sm js-change','data-name'=>$p['name']];
		// 别名
		if(isset($p['alias'])) $options['data-alias'] = $p['alias'];
		// 小数点
		if(isset($p['decimalPlace'])){
			$options['step'] = '0.';
		    for ($i=0;$i<$p['decimalPlace'];$i++){
			    $options['step'] .= '0';
            }
			$options['step'] .= '1';
		}
		if(is_array($p['value'])){
            // 是否多选
		    if(isset($p['multipleSelect'])){
                $options['multiple'] = 'true';
            }else{
	            $options['prompt'] = '请选择';
            }
            if(isset($p['disabled'])) $options['options'] = $p['disabled'];
		    $html = HtmlHelper::dropDownList($formName,null,$p['value'],ArrayHelper::merge($options,[]));
		}elseif ($p['value'] == 'int'){
			$html = HtmlHelper::input('number',$formName,null,ArrayHelper::merge($options,[]));
		}else{
			$html = HtmlHelper::input('text',$formName,null,ArrayHelper::merge($options,[]));
		}
	}
    return $html;
}
?>
<?php if(!empty($parameters)){?>
<h4>参数</h4>
<ul class="nav nav-tabs mt-1" role="tablist">
    <?php $i=0;foreach ($parameters as $k=>$v){ if($k == 'getFields' || $k == 'postFields' || empty($v)) continue;?>
    <li role="presentation"<?=$i===0?' class="active"':''?>><a href="#<?=$actionName?>-<?=$action?>-<?=$k?>" role="tab" data-toggle="tab"><?=$k?>参数</a></li>
    <?php $i++;} ?>
</ul>
<form action="javascript:void(0);" class="tab-content">
	<?php $i=0;foreach ($parameters as $k=>$v){ if(empty($v)) continue; if($k == 'get' || $k == 'post'){?>
    <div role="tabpanel" class="tab-pane<?=$i===0?' active':''?>" id="<?=$actionName?>-<?=$action?>-<?=$k?>">
        <table class="table table-bordered mt-1 mb-0">
            <thead>
            <tr>
                <td>参数名</td>
                <?php if($k == 'get'){?>
                <td>值</td>
                <?php }?>
                <td>描述</td>
                <td>类型</td>
                <td>备注</td>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($v as $p){?>
            <tr>
                <td class="get-parameters-name"><?=$p['name']?></td>
	            <?php if($k == 'get'){?>
                <td><?=getFormControl($p)?></td>
	            <?php }?>
                <td><?=$p['title']?></td>
                <td><?=$p['type']?></td>
                <td<?=$p['isRequired']?' style="color:red;"':''?>><?=$p['remark']?></td>
            </tr>
            <?php }?>
            </tbody>
        </table>
    </div>
	<?php $i++;}elseif($k == 'postFields'){?>
        <a class="btn btn-primary btn-sm mt-1 js-get-fields" data-action="<?=$actionName?>-<?=$v?>-post" data-loading-text="获取中..." href="<?=UrlHelper::current(['action'=>$v])?>">获取POST参数</a>
    <?php }else{?>
        <a class="btn btn-primary btn-sm mt-1 js-get-fields" data-action="get" data-loading-text="获取中..." href="<?=UrlHelper::current(['action'=>$v])?>">获取字段</a>
    <?php }} ?>
</form>
<?php } ?>
<?php if(!empty($fields)){?>
<h4>字段</h4>
<pre class="field-list"><?=$fields?></pre>
<?php }?>
