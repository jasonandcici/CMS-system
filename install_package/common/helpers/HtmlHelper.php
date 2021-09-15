<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/8/4
 */

namespace common\helpers;

use Exception;
use Yii;
use yii\helpers\Html;
use yii\imagine\Image;

/**
 * Html帮助类
 *
 * @author 
 * @since 1.0
 */
class HtmlHelper extends Html
{

    /**
     * 获取文件项目信息
     * @param string|array $fileData 如果多图片请用fileDataHandle()方法处理,单图片直接传入
     * @param string $item 获取项
     * @param null $default
     * @return string
     */
    public static function getFileItem($fileData,$item = 'file',$default = ''){
        if(!is_array($fileData)){
            if(is_string($fileData) && strpos($fileData,'[{') === 0){
                $fileData = self::fileDataHandle($fileData, false);
            }else{
                return $fileData?:'';
            }
        }

        $result = '';

        if(array_key_exists($item,$fileData)){
            $result = $fileData[$item];
        }

        return empty($result)?$default:$result;
    }

    /**
     * 上传的文件数据处理
     * @param string $fileData 原始文件数据
     * @param bool|false $multiple 是否单文件
     * @return array|string
     */
    public static function fileDataHandle($fileData, $multiple = true){
        if(strpos($fileData,'[{') === 0){
            $json_image_data = json_decode($fileData,true);
            if($json_image_data == null) return [];
            $resultImg = [];
            foreach($json_image_data as $i=>$item){
                if(!$multiple){
                    $resultImg = self::fileDataItem($item);
                    break;
                }else{
                    $resultImg[] = self::fileDataItem($item);
                }
            }


            return $resultImg;
        }

        return [];
    }

    private static function fileDataItem($fileItem){
        $result = [];
        $result['isWeb'] = stripos($fileItem['file'],'http://',0)===0 || stripos($fileItem['file'],'https://',0) === 0;
        foreach((array)$fileItem as $i=>$item){
            $result[$i] = $item;
        }
        return $result;
    }

    /**
     * 生成缩略图
     * @param $fileData
     * @param array $options 索引0的值为缩略图配置"w{宽}/300/h{高}/300/q{质量0~100}/80/m{1居中裁剪|2缩放裁剪}/1/bg/{16进制色值}"，其他为html::img()方法的options
     * @param bool $returnPath 是否返回路径
     * @return string
     */
	public static function getImgHtml($fileData,$options=[],$returnPath = false){
		if(!is_array($fileData)){
			$tmp = self::fileDataHandle($fileData,false);
			if(!empty($tmp)) $fileData = $tmp;
		}
		$src = self::getFileItem($fileData);

		if(!$src) return '';

		$options = ArrayHelper::merge(['alt'=>self::getFileItem($fileData,'alt')],$options);

		$srcArray = parse_url($src);

		if(!array_key_exists(0,$options) || array_key_exists('host',$srcArray) && $srcArray['host'] != Yii::$app->getRequest()->hostName){
			if(array_key_exists(0,$options)) unset($options[0]);
			return $returnPath?$src:Html::img($src,$options);
		}

		if(array_key_exists('host',$srcArray) && $srcArray['host'] == Yii::$app->getRequest()->hostName){
			$src = ArrayHelper::getValue($srcArray,'path');
		}
		unset($srcArray);

		// 格式化缩略图配置信息
		$thumbInfoKey = $thumbInfoValue = [];
		foreach (explode('/',$options[0]) as $i=>$item){
			($i+1)%2 !== 0?$thumbInfoKey[] = $item:$thumbInfoValue[] = $item;
		}
		$thumbInfo = [];
		foreach ($thumbInfoKey as $i=>$item){
			$thumbInfo[$item] = ArrayHelper::getValue($thumbInfoValue,$i);
		}
		unset($thumbInfoKey,$thumbInfoValue,$options[0]);

		if(!array_key_exists('w',$thumbInfo) && !array_key_exists('h',$thumbInfo)){
			return $returnPath?$src:Html::img($src,$options);
		}else{
			$w = (float)self::getFileItem($fileData,'width',0);
			$h = (float)self::getFileItem($fileData,'height',0);
			if(!array_key_exists('w',$thumbInfo)){
				//只有高度
				if($w && $h){
					$thumbInfo['w'] = $w/$h*$thumbInfo['h'];
				}else{
					return $returnPath?$src:Html::img($src,$options);
				}
			}elseif (!array_key_exists('h',$thumbInfo)){
				//只有宽度
				if($w && $h){
					$thumbInfo['h'] = $h/$w*$thumbInfo['w'];
				}else{
					return $returnPath?$src:Html::img($src,$options);
				}
			}
		}



		$thumbInfo['m'] = ArrayHelper::getValue([1=>'outbound',2=>'inset'],ArrayHelper::getValue($thumbInfo,'m',1),'outbound');
		$thumbInfo['q'] = abs(ArrayHelper::getValue($thumbInfo,'q',90));
		if($thumbInfo['q'] > 100 || $thumbInfo['q'] === 0) $thumbInfo['q'] = 100;

		// 生成缩略图资源地址
		$fileName = substr($src,strrpos($src,'/')+1);
		$start = strpos($fileName,'.');
		$thumbSrc = str_replace($fileName,substr_replace($fileName,'_'.$thumbInfo['w'].'x'.$thumbInfo['h'].'x'.$thumbInfo['q'].'.',$start,1),$src);
		unset($fileName,$start);

		// 生成缩略图
		$base = \Yii::$app->basePath.'/..';
		if(!file_exists($base.$thumbSrc)){
			if(file_exists($base.$src)){
				try{
					$image = new Image();
					$image::$thumbnailBackgroundColor = ArrayHelper::getValue($thumbInfo,'bg','FFF');
					$thumb = $image::thumbnail($base.$src, $thumbInfo['w'],$thumbInfo['h'],$thumbInfo['m']);
					$thumb->save($base.$thumbSrc, [
							'quality' => $thumbInfo['q']
						]
					);
				}catch (Exception $e){
					$thumbSrc = $src;
				}
			}else{
				$thumbSrc = $src;
			}
		}

		return $returnPath?$thumbSrc:Html::img($thumbSrc,$options);
	}

}