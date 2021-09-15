<?php
/**
 * This is the template for generating the model class of a specified table.
 */

use common\entity\models\PrototypeModelModel;
use common\helpers\ArrayHelper;

/* @var $model  */
/* @var $fields  */

$newFields = [];
foreach ($fields as $item){
    if(in_array($item->type,['editor','image','image_multiple','attachment','attachment_multiple','passport','captcha','relation_category','city','city_multiple'])) continue;
    $newFields[] = $item;
}

echo "<?php
/**
 * @var \$searchModel
 * @var \$searchForm
 * @var \$dataList
 * @var \$categoryInfo
 */";

foreach ($newFields as $item){
	if(in_array($item->type,['radio','select','radio_inline','checkbox','checkbox_inline','select_multiple'])){
		$options = $item->options;
		echo "\n\n$".$item->name.'OptionLabels = [';
		foreach ($options['list'] as $list){
			echo '"'.$list['value'].'" => "'.$list['title'].'",';
		}
		echo "];\n";
	}
}

echo "?>\n";

// 搜索
echo  "<?php \$this->beginBlock('search');?>\n";
foreach ($newFields as $item){
    if(in_array($item->type,['relation_data','date','datetime']) || !$item->is_search) continue;

    if(in_array($item->type,['radio','radio_inline','checkbox','checkbox_inline','select','select_multiple'])){
        echo "<?= \$searchForm->field(\$searchModel, '".$item->name."')->dropDownList([".\common\entity\models\PrototypeModelModel::optionsMap($item->options)."],['prompt'=>'请选择','data-placeholder'=>'请选择','prety'=>true]) ?>\n";
    }else{
        echo "<?= \$searchForm->field(\$searchModel, '".$item->name."')->textInput() ?>\n";
    }
}
echo "<?php \$this->endBlock();?>\n";

// 列表显示标题
echo  "<?php \$this->beginBlock('thead');?>\n";
foreach ($newFields as $item){
    if(!$item->is_show_list) continue;
    echo "<td>".$item->title."</td>\n";
}
echo "<?php \$this->endBlock();?>\n";

// 列表显示内容
echo "<?php foreach (\$dataList as \$item):?>\n";
echo "<?php \$this->beginBlock('tbody'.\$item->id);?>\n";
foreach ($newFields as $item){
    if(!$item->is_show_list) continue;
    if($item->type == 'relation_data' && !$item->setting['relationType']){
        if($item->setting['modelName'] == 'user'){
            echo "<td><?=\$item->userInfo->username.\"(\".\$item->userInfo->userProfile->nickname.\")\"?></td>\n";
        }else{
            echo "<td><?=\yii\helpers\StringHelper::truncate(\$item->".$item->setting['modelName']."Info->title,48)?></td>\n";
        }
    }elseif(in_array($item->type,['radio','select','radio_inline'])){
    	echo "<td><?=\$".$item->name."OptionLabels[\$item->".$item->name."]?></td>\n";
    }elseif(in_array($item->type,['checkbox','checkbox_inline','select_multiple'])){
	    echo "<td><?php \$".$item->name."Options = [];foreach (empty(\$item->".$item->name.")?[]:explode(',',\$item->".$item->name.") as \$vvv){ \$".$item->name."Options[] = \$".$item->name."OptionLabels[\$vvv];} echo empty(\$".$item->name."Options)?':':implode(',',\$".$item->name."Options);?></td>\n";
    }else{
        echo "<td><?=\yii\helpers\StringHelper::truncate(\$item->".$item->name.",48)?></td>\n";
    }
}
echo "<?php \$this->endBlock();?>\n";
echo "<?php endforeach;?>";

?>