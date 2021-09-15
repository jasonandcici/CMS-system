<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/7/24
 */

namespace common\helpers;


/**
 * 字符串帮助类
 *
 * @author 
 * @since 1.0
 */
class StringHelper extends \yii\helpers\StringHelper
{
    /**
     * 驼峰字符串转换成有指定连接符和小写字母
     * @param string $str
     * @param string $connector
     * @return string
     */
    static public function humpTo($str,$connector = '_'){
        $array = array();
        for($i=0;$i<strlen($str);$i++){
            if($str[$i] == strtolower($str[$i])){
                $array[] = $str[$i];
            }else{
                if($i>0){
                    $array[] = $connector;
                }
                $array[] = strtolower($str[$i]);
            }
        }

        return implode('',$array);
    }

    /**
     * 有指定连接符转成驼峰字符串
     * @param string $str
     * @param string $connector
     * @return string
     */
    static public function toHump($str,$connector = '_'){
        $array = explode($connector, $str);
        $result = '';
        foreach($array as $value){
            $result.= ucfirst($value);
        }

        return $result;
    }

    /**
     * 文本域中的换行符替换
     * @param $string
     * @param string $replaceStr
     * @return array|string
     */
    static public function textareaBreakReplace($string,$replaceStr = null){
        $string = str_replace(array("\r\n", "\r", "\n"),'$_break_tag_$',$string);
        $res = [];
        foreach (explode('$_break_tag_$',$string) as $item){
            $res[] = $item;
        }
        return $replaceStr === null?$res:implode($replaceStr,$res);
    }
}