<?php
use yii\helpers\Url;

/**
 * tree数组输出html
 * @param $data
 * @return string
 */
function navHtml($data){
    $_html = '';
    $class='class="tree-nch" ';
    foreach($data as $key=>$value){
        if($key == 'config/index?scope=custom'){
            $_html .= '<li id="tree-slide-config-custom" '.$class.'><a href="'.$value.'" target="mainFrame"><span class="tree-icon"></span>全局碎片</a></li>';
        }else{
            if($value['type']){
                $url = Url::toRoute(['/fragment/fragment/edit','category_id'=>$value['id']]);
            }else{
                $url = Url::toRoute(['/fragment/fragment-list/index','category_id'=>$value['id']]);
            }

            $_html .= '<li id="tree-slide-'.$value['id'].'" '.$class. '><a '.'href="'.$url.'"'.' target="mainFrame"><span class="tree-icon"></span>'.$value['title'].'</a>';
            $_html .='</li>';
        }

    }

    return $_html;
}
echo navHtml($dataList);
?>