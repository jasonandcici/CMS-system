<?php
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author:
// +----------------------------------------------------------------------
// | Created on 2016/6/14.
// +----------------------------------------------------------------------

/**
 * 数组助手类
 */

namespace common\helpers;

class ArrayHelper extends \yii\helpers\ArrayHelper
{

    /**
     * 返回扁平化有层次排序的一维数组
     * @param $data
     * @param string $str
     * @param int $parentId
     * @param int $count
     * @param string $parentName
     * @return array
     */
    static public function linear($data, $str = '-', $parentId = 0, $count = 0, $parentName = 'pid')
    {
        $arr = array();
        foreach ($data as $v) {
            if($v['id'] == 0) continue;
            if ($v[$parentName] == $parentId) {
                $v['str'] = str_repeat($str, $count);
                $v['count'] = $count + 1;
                $v['hasChild'] = true;
                $arr[] = $v;
                $arr = array_merge($arr, self::linear($data, $str, $v['id'], $count + 1, $parentName));
                $arr[count($arr)-1]['hasChild'] = false;
            }
        }
        return $arr;
    }

    /**
     * 返回树状有层次排序的多维数组
     * @param $data
     * @param int $parentId
     * @param string $parentName
     * @return array
     */
    static public function tree($data, $parentId = 0, $parentName = 'pid')
    {
        $arr = array();
        foreach ($data as $v) {
            if($v['id'] == 0) continue;
            if ($v[$parentName] == $parentId) {
                $v['child'] = self::tree($data, $v['id'], $parentName);
                if(!empty($v['child'])){
                    // 加入是否有父元素标识
                    foreach($v['child'] as $i=>$item){
                        $v['child'][$i]['hasParent'] = true;
                    }
                }
                $arr[] = $v;
            }
        }
        return $arr;
    }

    /**
     * 传递一个子级id,返回父级数据
     * @param $data
     * @param $id
     * @param string $parentName
     * @return array
     */
    static public function getParents($data, $id, $parentName = 'pid'){
        $arr = array();
        foreach ($data as $v) {
            if($v['id'] == 0) continue;
            if ($v['id'] == $id) {
                $arr[] = $v;
                $arr = array_merge(self::getParents($data, $v[$parentName], $parentName), $arr);
            }
        }
        return $arr;
    }

    /**
     * 传递一个子级id,返回父级id
     * @param $data
     * @param $id
     * @param string $parentName
     * @return array
     */
    static public function getParentsId($data, $id, $parentName = 'pid'){
        $arr = array();
        foreach ($data as $v) {
            if($v['id'] == 0) continue;
            if ($v['id'] == $id) {
                $arr[] = $v['id'];
                $arr = array_merge(self::getParentsId($data, $v[$parentName], $parentName), $arr);
            }
        }
        return $arr;
    }

    /**
     * 传递一个父级id,返回子级数据
     * @param $data
     * @param $parentId
     * @param string $parentName
     * @return array
     */
    static public function getChildes($data, $parentId, $parentName = 'pid'){
        $arr = array();
        foreach ($data as $v) {
            if($v['id'] == 0) continue;
            if ($v[$parentName] == $parentId) {
                $arr[] = $v;
                $arr = array_merge($arr, self::getChildes($data, $v['id'], $parentName));
            }
        }
        return $arr;
    }

    /**
     * 传递一个父级id,返回子级id
     * @param $data
     * @param $parentId
     * @param string $parentName
     * @return array
     */
    static public function getChildesId($data, $parentId, $parentName = 'pid'){
        $arr = array();
        foreach ($data as $v) {
            if($v['id'] == 0) continue;
            if ($v[$parentName] == $parentId) {
                $arr[] = $v['id'];
                $arr = array_merge($arr, self::getChildesId($data, $v['id'], $parentName));
            }
        }
        return $arr;
    }

    /**
     * 将数组转换成对象
     * @param $array
     * @return object
     */
    public static function convertToObject($array){
        if(is_array($array)){
            return (object)array_map('self::convertToObject',$array);
        }else{
            return $array;
        }
    }

    /**
     * 让数组的键值和键名保持相同
     * @param $array
     * @return array
     */
    public static function unifyKeyValue($array){
        $arr = array();
        foreach ($array as $item){
            $arr[$item] = $item;
        }
        return $arr;
    }
}