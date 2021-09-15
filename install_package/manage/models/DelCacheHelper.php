<?php
/**
 * @copyright
 * @link
 * @create Created on 2018/10/23
 */

namespace manage\models;
use common\helpers\SystemHelper;
use Yii;


/**
 * DelCacheHelper
 *
 * @author
 * @since 1.0
 */
class DelCacheHelper {

	/**
	 * 清除缓存
	 *
	 * @param $cacheName
	 *
	 * @return bool
	 * @throws \yii\base\Exception
	 */
	static public function deleteCache($cacheName){
		if(is_string($cacheName)) $cacheName = [$cacheName];

		foreach ($cacheName as $item){
			if($item == 'all') continue;
			Yii::$app->cache->delete($item);
		}

		// 系统默认是文件缓存的
		if(!SystemHelper::isEnableRedis()){
			$cacheName = implode(',',$cacheName);

			$token = Yii::$app->getSecurity()->generatePasswordHash($cacheName.date('Ymdh',time()));
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => Yii::$app->getRequest()->getHostInfo()."/home-api/del-cache.html",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"type\"\r\n\r\n$cacheName\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"token\"\r\n\r\n$token\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
				CURLOPT_HTTPHEADER => array(
					"cache-control: no-cache",
					"content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
					"postman-token: 77c9c75a-c15c-6033-a9fb-fcbe9ad87c86"
				),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);

			if(!$err){
				$response = json_decode($response);
				if($response->status){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return true;
		}
	}
}