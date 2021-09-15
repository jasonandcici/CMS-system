<?php

use common\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * tree数组输出html
 * @param $data
 * @param int $pid
 * @return string
 */
function navHtml($data, $pid = 0, $count = 0){
    $_html = '';
    foreach($data as $key=>$value){
        // class类名
        $_class = '';
        $childHasNode = false;
        if(!empty($value['child'])){
            $childHasNode = childHasNode($value['child']);
        }

        if((empty($value['child']) || (!empty($value['child']) && !$childHasNode)  ) &&  $value['type'] > 1){
            continue;
        }elseif(empty($value['child']) || !$childHasNode){
            $_class = 'class="tree-nch"';
        }

        // 生成url
        switch($value['type']){
            case 0:
                $_url = 'href="'.Url::to([($value['model']['type'] == 2?'/'.$value['model']['route']:'node/index'),'category_id'=>$value['id']]).'"';
                break;
            case 1:
                $_url = 'href="'.Url::to(['node/page','category_id'=>$value['id']]).'"';
                break;
            default:
                $_url = 'href="javascript:;"';
                break;
        }

        // 生成li
        $_html .= '<li id="tree-category-'.$value['id'].'" '.$_class.'><a '.$_url.' target="mainFrame"><span class="tree-icon"></span>'.$value['title'].'</a>';
        if($value['pid'] == $pid){
            $_html .= navHtml($value['child'],$value['id'],$count+1);
        }

        $_html .='</li>';
    }

    return $_html?($pid==0?$_html:'<ul>'.$_html.'</ul>'):'';
}
function childHasNode($child){
    $return = false;
    foreach($child as $item){
        if($item['type'] < 2){
            $return = true;
        }elseif(!empty($item['child'])){
            $return = childHasNode($item['child']);
        }
        if($return) break;
    }
    return $return;
}
echo navHtml($dataList);
?>