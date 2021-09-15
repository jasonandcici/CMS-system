<?php
// 获取具有层级表示的本站点栏目列表
$categoryLiner = \common\helpers\ArrayHelper::linear($categoryList);

// 当前栏目的父栏目, 包含当前栏目
$parentCates = \common\helpers\ArrayHelper::getParents($categoryList, $categoryCurrentId);

// 数组中最大层级
$max_count = 0;
$arr = [];
foreach ($categoryLiner as $k => $v) {
    array_push($arr, $v['count']);
}
$max_count = max($arr);


// 获取需要打上active类的 栏目id
function getActiveId($parentCates)
{
    $current_ids = array();
    foreach ($parentCates as $k => $v) {
        array_push($current_ids, $v['id']);
    }
    return $current_ids;
}

$currentIds = getActiveId($parentCates);

// 生成导航HTML
function generateNavHtml($categoryLiner, $deep = 0, $iter = 0, $max_count,
                         $pid = 0, $currentIds, $topNavClass, $topNavId,
                         $hasChildItemClass,$hasChildLinkClass,$childNavClass,
                         $hasChildLinkOption,$dropdownInsertDom )
{
    $html = '';

    // 限制遍历层次
    if ($iter < $deep){

        if ($pid != 0) {
            $html .= "<ul class='nav-" . $pid . " $childNavClass'> ";
        } else {
            $html .= "<ul class='nav-" . $pid . " $topNavClass'> ";
        }

        foreach ($categoryLiner as $k => $item) {
            if ($item['status'] == 1 && $item['pid'] == $pid) {

                if (in_array($item['id'], $currentIds)) {
                    if ($item['hasChild']){
                        $html .= "<li class='active item-" . $item['id'] . " $hasChildItemClass'>";
                    }else{
                        $html .= "<li class='active item-" . $item['id'] . " '>";
                    }
                } else {
                    if ($item['hasChild']){
                        $html .= "<li class='item-" . $item['id'] . " $hasChildItemClass'>";
                    }else{
                        $html .= "<li class='item-" . $item['id'] . " '>";
                    }
                }

                if ($item['hasChild']){
                    $html .= '<a class="'.$hasChildLinkClass.'" '.$hasChildLinkOption.' href="">' . $item['title'] .$dropdownInsertDom. '</a>';
                }else{
                    $html .= '<a href="">' . $item['title'] . '</a>';
                }

                if ($item['hasChild'] == true) {
                    $html .= GenerateNavHTML($categoryLiner, $deep, $iter + 1,
                        $max_count, $item['id'], $currentIds, $topNavClass,$topNavId,
                        $hasChildItemClass,$hasChildLinkClass,$childNavClass,$hasChildLinkOption,$dropdownInsertDom);
                }

                $html .= '</li>';
            }
        }
        $html .= "</ul>";
    } else if ($deep == 0){

        if ($pid != 0) {
            $html .= "<ul class='nav-" . $pid . " $childNavClass'> ";
        } else {
            $html .= "<ul id='".$topNavId."' class='nav-" . $pid . " $topNavClass ' > ";
        }
        foreach ($categoryLiner as $k => $item) {
            if ($item['status'] == 1 && $item['pid'] == $pid) {

                if (in_array($item['id'], $currentIds)) {
                    if ($item['hasChild']){
                        $html .= "<li class='active item-" . $item['id'] . " $hasChildItemClass'>";
                    }else{
                        $html .= "<li class='active item-" . $item['id'] . " '>";
                    }
                } else {
                    if ($item['hasChild']){
                        $html .= "<li class='item-" . $item['id'] . " $hasChildItemClass'>";
                    }else{
                        $html .= "<li class='item-" . $item['id'] . " '>";
                    }
                }
                if ($item['hasChild']) {
                    $html .= '<a class="'.$hasChildLinkClass.'" '.$hasChildLinkOption.' href="">' . $item['title'] . $dropdownInsertDom. '</a>';
                }else{
                    $html .= '<a href="">' . $item['title'] . '</a>';
                }

                if ($item['hasChild'] == true) {
                    $html .= GenerateNavHTML($categoryLiner, $deep, $iter + 1, $max_count,
                        $item['id'], $currentIds,$topNavClass,$topNavId,
                        $hasChildItemClass,$hasChildLinkClass,$childNavClass,$hasChildLinkOption,$dropdownInsertDom);
                }

                $html .= '</li>';
            }
        }
        $html .= "</ul>";
    }
    return $html;
}

$html = generateNavHtml($categoryLiner, $deep, 0, $max_count,
    0, $currentIds, $topNavClass,$topNavId,
    $hasChildItemClass,$hasChildLinkClass,$childNavClass,$hasChildLinkOption,$dropdownInsertDom);
?>


<?= $html ?>
