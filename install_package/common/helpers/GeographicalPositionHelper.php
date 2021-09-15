<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/7/21
 */

namespace common\helpers;

use yii\base\Component;


/**
 * 地理位置帮助类
 *
 * @author 
 * @since 1.0
 */
class GeographicalPositionHelper extends Component
{

    /**
     *计算某个经纬度的周围某段距离的正方形的四个点
     *
     * @param float $lng 经度
     * @param float $lat 纬度
     * @param float $distance 该点所在圆的半径，该圆与此正方形内切，默认值为0.5千米
     * @return array 正方形的四个点的经纬度坐标
     */
    static public function returnSquarePoint($lng, $lat,$distance = 0.5){
        $dlng =  2 * asin(sin($distance / (2 * 6378.138)) / cos(deg2rad($lat)));
        $dlng = rad2deg($dlng);

        $dlat = $distance/6378.138;
        $dlat = rad2deg($dlat);

	    return array(
		    'leftTop'=>array('lng'=>$lng-$dlng,'lat'=>$lat + $dlat),
		    'rightTop'=>array('lng'=>$lng + $dlng,'lat'=>$lat + $dlat),
		    'leftBottom'=>array('lng'=>$lng - $dlng,'lat'=>$lat - $dlat),
		    'rightBottom'=>array('lng'=>$lng + $dlng,'lat'=>$lat - $dlat)
	    );
    }

    /**
     * 计算两点地理坐标之间的距离
     *
     * 扩展出distance字段,并按distance正序（$lat,$lng当前定位点的经纬度值，lat,lng数据库中的经纬度自定名）
     $dataProvider->query->select(['*',"ROUND(6378.138 * 2 * ASIN(SQRT(POW(SIN(($lat * PI() / 180 - lat * PI() / 180) / 2),2) + COS($lat * PI() / 180) * COS(lat * PI() / 180) * POW(SIN(($lng * PI() / 180 - lng * PI() / 180) / 2),2))),2) AS distance"])
    ->orderBy(['distance'=>SORT_ASC])
    ->asArray()
     *
     * @param  float $startLng 起点经度
     * @param  float $startLat  起点纬度
     * @param  float $endLng 终点经度
     * @param  float $endLat  终点纬度
     * @param  Int     $unit       单位 1:米 2:公里
     * @param  Int     $decimal    精度 保留小数位数
     * @return float
     */
    static public function getDistance($startLng, $startLat, $endLng, $endLat, $unit=2, $decimal=2){

        $EARTH_RADIUS = 6378.138; // 地球半径系数
        $PI = 3.1415926;

        $radLat1 = $startLat * $PI / 180.0;
        $radLat2 = $endLat * $PI / 180.0;

        $radLng1 = $startLng * $PI / 180.0;
        $radLng2 = $endLng * $PI /180.0;

        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;

        $distance = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
        $distance = $distance * $EARTH_RADIUS * 1000;

        if($unit==2){
            $distance = $distance / 1000;
        }

        return round($distance, $decimal);

    }

}