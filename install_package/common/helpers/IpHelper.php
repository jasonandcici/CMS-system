<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/6/22
 */

namespace common\helpers;

use wsl\ip2location\Ip2Location;


/**
 * IPhelper
 *
 * @author 
 * @since 1.0
 */
class IpHelper extends \yii\helpers\IpHelper {

	/**
	 * 获取IP地址
	 * 因Yii内部提供的getUserIP()访问获取IP地址不准确，故有此方法
	 * @return mixed
	 */
	static public function getIpAddress(){
		if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$ip = getenv('HTTP_CLIENT_IP');
		} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$ip = getenv('REMOTE_ADDR');
		} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}else{
			$ip = '';
		}
		$res =  preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
		return $res;
	}

	/**
	 * 根据ip获取所在地
	 *
	 * @param $ipAddress
	 *
	 * @return array
	 */
	static public function getIpLocation($ipAddress){
		$ipLocation = new Ip2Location('@common/assets/ipData/qqwry.dat');
		$locationModel = $ipLocation->getLocation($ipAddress);
		$locationModel = $locationModel->toArray();

		$res = [];
		foreach (self::$provinces as $key =>$value){
			if(stripos($locationModel['country'],$value) === 0){
				$res['country'] = '中国';
				$res['province'] = $value;
				$res['city'] = str_replace($value,'',$locationModel['country']);
				if(empty($res['city'])) $res['city'] = $key>2?$res['province']:null;
				break;
			}
		}

		if(empty($res)){
			$res['country'] = $locationModel['country'] === '局域网' || $locationModel['country'] === 'IANA'?null:$locationModel['country'];
			$res['province'] = null;
			$res['city'] = null;
		}

		$res['description'] = $locationModel['area']?:null;

		return $res;
	}

	/**
	 * @var array 中国省
	 */
	private static $provinces = ["香港", "澳门", "台湾省","黑龙江省", "辽宁省", "吉林省", "河北省", "河南省", "湖北省", "湖南省", "山东省", "山西省", "陕西省",
		"安徽省", "浙江省", "江苏省", "福建省", "广东省", "海南省", "四川省", "云南省", "贵州省", "青海省", "甘肃省",
		"江西省", "内蒙古", "宁夏", "新疆", "西藏", "广西", "北京市", "上海市", "天津市", "重庆市"];
}