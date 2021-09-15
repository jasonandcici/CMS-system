<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/11/20
 */

namespace home\controllers;

use common\components\BaseController;
use common\helpers\ArrayHelper;
use common\helpers\FileHelper;
use Yii;
use yii\web\NotFoundHttpException;
use Exception;
use yii\web\Response;

/**
 * 资源控制
 *
 * @author 
 * @since 1.0
 */
class UploadFilesController extends BaseController{

	public $layout = null;

	/**
	 * @var string
	 */
	protected $_basePath;

	/**
	 * @var string 文件路径
	 */
	protected $_file;

	/**
	 * @throws \yii\base\InvalidConfigException
	 */
	public function init()
	{
		parent::init();

		$this->_basePath = Yii::$app->getBasePath().'/..';
		$this->_file = '/'.Yii::$app->getRequest()->getPathInfo();
	}

	/**
	 * @param null $thumb
	 *
	 * @throws NotFoundHttpException
	 */
	public function actionIndex($thumb = null){
		// todo::api调用验证密钥 或 web访问防盗链

		if(file_exists($this->_basePath.$this->_file)){
			$contentType = mime_content_type($this->_basePath.$this->_file);
			// 生成缩略图
			if(!empty($thumb)){
				$isImg = false;
				$imgExt = ArrayHelper::merge(['jpg','jpeg','png','gif'],explode(',',$this->config['upload']['imageAllowFiles']));
				foreach (FileHelper::getExtensionsByMimeType($contentType) as $v){
					if(in_array($v,$imgExt)){
						$isImg = true;
						break;
					}
				}
				unset($imgExt);
				if($isImg){
					$this->_file = $this->generateThumb($this->_file,$thumb);
				}
				unset($isImg);
			}

			$response = Yii::$app->getResponse();
			$response->headers->set('Content-Type', $contentType);
			$response->format = Response::FORMAT_RAW;

			$response->stream = fopen($this->_basePath.$this->_file, 'r');
			return $response->send();

		}else{
			throw new NotFoundHttpException();
		}
	}

	/**
	 * 生成缩略图
	 *
	 * @param $file
	 * @param $thumb
	 *
	 * @return mixed
	 */
	protected function generateThumb($file,$thumb){
		// 格式化缩略图配置信息
		$thumbInfoKey = $thumbInfoValue = [];
		foreach (explode('/',$thumb) as $i=>$item){
			($i+1)%2 !== 0?$thumbInfoKey[] = $item:$thumbInfoValue[] = $item;
		}
		$thumbInfo = [];
		foreach ($thumbInfoKey as $i=>$item){
			$thumbInfo[$item] = ArrayHelper::getValue($thumbInfoValue,$i);
		}
		unset($thumbInfoKey,$thumbInfoValue);
		if(array_key_exists('w',$thumbInfo)) $thumbInfo['w'] = abs(intval($thumbInfo['w']));
		if(array_key_exists('h',$thumbInfo)) $thumbInfo['h'] = abs(intval($thumbInfo['h']));

		$thumbInfo['m'] = ArrayHelper::getValue([1=>'outbound',2=>'inset'],ArrayHelper::getValue($thumbInfo,'m',1),'outbound');
		$thumbInfo['q'] = abs(intval(ArrayHelper::getValue($thumbInfo,'q',90)));
		if($thumbInfo['q'] > 100 || $thumbInfo['q'] === 0) $thumbInfo['q'] = 100;

		// 缩略图资源地址
		$fileName = substr($file,strrpos($file,'/')+1);
		$start = strpos($fileName,'.');

		if(!array_key_exists('w',$thumbInfo) && !array_key_exists('h',$thumbInfo)){
			return $file;
		}elseif (array_key_exists('w',$thumbInfo) && array_key_exists('h',$thumbInfo)){
			$thumbSrc = str_replace($fileName,substr_replace($fileName,'_'.$thumbInfo['w'].'x'.$thumbInfo['h'].'x'.$thumbInfo['q'].'.',$start,1),$file);
			if(file_exists($this->_basePath.$thumbSrc)){
				return $thumbSrc;
			}
		}elseif(array_key_exists('w',$thumbInfo) || array_key_exists('h',$thumbInfo)){
			$imgInfo = \think\Image::open($this->_basePath.$file);
			$w = $imgInfo->width();
			$h = $imgInfo->height();

			if(!array_key_exists('w',$thumbInfo)){
				$thumbInfo['w'] = intval($w/$h*$thumbInfo['h']);
			}elseif (!array_key_exists('h',$thumbInfo)){
				$thumbInfo['h'] = intval($h/$w*$thumbInfo['w']);
			}

			$thumbSrc = str_replace($fileName,substr_replace($fileName,'_'.$thumbInfo['w'].'x'.$thumbInfo['h'].'x'.$thumbInfo['q'].'.',$start,1),$file);
			if(file_exists($this->_basePath.$thumbSrc)){
				return $thumbSrc;
			}
			unset($imgInfo);
		}

		unset($fileName,$start);

		if(empty($thumbSrc) || !in_array($thumbInfo['w'].'*'.$thumbInfo['h'],(empty($this->config['upload']['imageAllowSize'])?[]:explode(',',$this->config['upload']['imageAllowSize'])))) return $file;

		// 生成缩略图
		try{
			$image = new \yii\imagine\Image();
			$image::$thumbnailBackgroundColor = ArrayHelper::getValue($thumbInfo,'bg','FFF');
			$thumb = $image::thumbnail($this->_basePath.$file, $thumbInfo['w'],$thumbInfo['h'],$thumbInfo['m']);
			$thumb->save($this->_basePath.$thumbSrc, [
					'quality' => $thumbInfo['q']
				]
			);
			return $thumbSrc;
		}catch (Exception $e){
			return $file;
		}
	}
}