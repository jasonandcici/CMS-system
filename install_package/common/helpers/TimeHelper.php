<?php
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/6/16.
// +----------------------------------------------------------------------

/**
 * 处理时间
 */

namespace common\helpers;


use yii\base\Component;

class TimeHelper extends Component
{
    /**
     *
     * @param int $time 时间戳
     * @return string
     */
    static public function timeDifferenceFormat($time) {
        $nowtime = time();
        $difference = $nowtime - $time;

        switch ($difference) {

            case $difference <= '60' :
                $msg = '刚刚';
                break;

            case $difference > '60' && $difference <= '3600' :
                $msg = floor($difference / 60) . '分钟前';
                break;

            case $difference > '3600' && $difference <= '86400' :
                $msg = floor($difference / 3600) . '小时前';
                break;

            case $difference > '86400' && $difference <= '2592000' :
                $msg = floor($difference / 86400) . '天前';
                break;

            case $difference > '2592000' &&  $difference <= '7776000':
                $msg = floor($difference / 2592000) . '个月前';
                break;
            default:
                $msg = '很久以前';
                break;
        }

        return $msg;
    }
}