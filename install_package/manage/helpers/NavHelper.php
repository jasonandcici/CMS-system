<?php
// +----------------------------------------------------------------------
// | nantong
// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/4/5.
// +----------------------------------------------------------------------

/**
 * 管理后台导航生成
 */

namespace manage\helpers;


class NavHelper
{

    /**
     * 生成导航html
     * @param array $data 导航数数据格式必须为数组tree
     * @param int $pid 父级Id
     * @param int $count
     * @return string
     */
    static public function generateNavHtml($data, $pid = 0, $count = 0){
        $_html = '';
        foreach($data as $key=>$value){

            // 去除外部空连接
           if($value['type'] == 1 && empty($value['child'])) continue;

            // class类名
            $_class = '';
            if($value['type'] == 0 && empty($value['child'])){
                $_class = 'class="tree-nch"';
            }

            // url
            $_url = 'href="'.$value['url'].'"';
            $_extendnav = '';
            if($value['type'] == 2){
                $_extendnav = ' extend_nav';
                $_url = 'href="javascript:;" data-url="'.$value['url'].'"';
            }
            $_html .= '<li id="tree-nav-'.$value['id'].'" '.$_class.'><a '.$_url.' target="mainFrame"><span class="tree-icon'.$_extendnav.'"></span>'.$value['title'].'</a>';

            if($value['pid'] == $pid){
                $_html .= self::generateNavHtml($value['child'],$value['id'],$count+1);
            }
            if($value['type'] == 2) $_html.='<ul></ul>';

            $_html .='</li>';
        }
        $ul_class = '';
        if($count === 0) $ul_class = 'class="nav-aside accordion-body"';
        return $_html?'<ul '.$ul_class.'>'.$_html.'</ul>':'';
    }
}