<?php
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/7/4.
// +----------------------------------------------------------------------

/**
 * 文件帮助类
 */

namespace common\helpers;


use Yii;
use yii\web\NotFoundHttpException;
use ZipArchive;

class FileHelper extends \yii\helpers\FileHelper
{
    private $contents = array();
    private $errorInfo = array();

    /**
     * 文件内容读取
     * @access public
     * @param string $filename 文件名
     * @param string $type
     * @return string
     */
    public function read($filename, $type = ''){
        if($type == 'web'){
            return $this->getWeb($filename);
        }else{
            return $this->get($filename,'content');
        }
    }

    /**
     * 文件写入
     * @access public
     * @param string $filename  文件名
     * @param string $content  文件内容
     * @return boolean
     */
    public function put($filename,$content){
        $dir         =  dirname($filename);
        if(!is_dir($dir)){
            mkdir($dir,0777,true);
        }
        if(false === file_put_contents($filename,$content)){
            $this->errorInfo[] = '文件写入错误：'.$filename;
            return false;
        }else{
            $this->contents[$filename]=$content;
            return true;
        }
    }

    /**
     * 文件追加写入
     * @access public
     * @param string $filename  文件名
     * @param string $content  追加的文件内容
     * @return boolean
     */
    public function append($filename,$content){
        if(is_file($filename)){
            $content =  $this->read($filename).$content;
        }
        return $this->put($filename,$content);
    }

    /**
     * 文件是否存在
     * @access public
     * @param string $filename  文件名
     * @return boolean
     */
    public function has($filename){
        return is_file($filename);
    }

    /**
     * 读取文件信息
     * @access public
     * @param string $filename  文件名
     * @param string $name  信息名 mtime或者content
     * @return boolean
     */
    public function get($filename,$name){
        if(!isset($this->contents[$filename])){
            if(!is_file($filename)) return false;
            $this->contents[$filename]=file_get_contents($filename);
        }
        $content=$this->contents[$filename];
        $info   =   array(
            'mtime'     =>  filemtime($filename),
            'content'   =>  $content
        );
        return $info[$name];
    }

    /**
     * 读取网页文件
     * @param $url
     * @return string
     */
    public function getWeb($url){
        $handle = fopen($url, "rb");
        $contents = stream_get_contents($handle);
        fclose($handle);
        return $contents;
    }

    /**
     * 返回错误信息
     * @return string
     */
    public function getError(){
        $str = '';
        foreach ($this->errorInfo as $item){
            $str .= $item;
        }

        return $str;
    }

    /**
     * 删除目录及目录下所有文件或删除指定文件
     * @param $path string 待删除目录路径
     * @param bool $delDir 是否删除目录，true删除目录，false则只删除文件保留目录（包含子目录）
     * @return bool
     */
    static public function delDirAndFile($path, $delDir = true) {
        $handle = opendir($path);
        if ($handle) {
            while (false !== ( $item = readdir($handle) )) {
                if ($item != "." && $item != "..")
                    is_dir("$path/$item") ? self::delDirAndFile("$path/$item", $delDir) : self::unlink("$path/$item");
            }
            closedir($handle);
            if ($delDir)
                return rmdir($path);
        }else {
            if (file_exists($path)) {
                return self::unlink($path);
            } else {
                return false;
            }
        }
    }

    /**
     * 获取给定文件夹下的子文件夹名
     * @param $dirName string 包含路径的文件夹名
     * @param bool $recursive 是否迭代查找子文件夹
     * @param string $parentFolder
     * @return array|string
     */
    static public function findChildFolder($dirName,$recursive = false,$parentFolder = '')
    {
        $folderName = array();
        if (!is_dir($dirName)) return $folderName;

        $handle = opendir($dirName);
        while (false !== ($file = readdir($handle))) {
            if ($file == '.' || $file == '..') continue;
            $newPath = $dirName . "/" . $file;
            if (is_dir($newPath)) {
                $file = iconv("GB2312", "UTF-8", $file);
                if($recursive){
                    $symbol = (empty($parentFolder)?'':'/');
                    $folderName[] = $parentFolder.$symbol.$file;
                    $childes = self::findChildFolder($dirName.'/'.$file,$recursive,$parentFolder.$symbol.$file);
                    $folderName = array_merge($folderName,$childes);
                }else{
                    $folderName[] = $file;
                }
            }
        }
        closedir($handle);

        return $folderName;
    }

    /**
     * 批量修改文件权限
     * @param $path
     * @param $filemode
     * @return bool
     */
    static public function chmodr($path, $filemode) {
        if (!is_dir($path))
            return chmod($path, $filemode);
        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if($file != '.' && $file != '..') {
                $fullpath = $path.'/'.$file;
                if(is_link($fullpath))
                    return false;
                elseif(!is_dir($fullpath) && !chmod($fullpath, $filemode))
                    return false;
                elseif(!self::chmodr($fullpath, $filemode))
                    return false;
            }
        }
        closedir($dh);
        if(chmod($path, $filemode))
            return true;
        else
            return false;
    }

    /**
     * 创建文件夹
     * @param $dirName string 包含路径的文件夹名
     * @return bool
     */
    public function createFolder($dirName){
        if (!file_exists($dirName) && !mkdir($dirName, 0777, true)) {
            $this->errorInfo[] = '目录创建失败。';
            return false;
        }
        else if (!is_writeable($dirName)) {
            $this->errorInfo[] = '目录没有写权限。';
            return false;
        }
        return true;
    }

    /**
     * 查找文件夹下的文件列表
     * @param $dir string 包含路径的文件夹名
     * @return array
     */
    static public function findFileList($dir){
        if(!preg_match("/\/$/",$dir)) $dir = $dir.'/';
        $result = array();
        if (is_dir($dir)){
            $file_dir = scandir($dir);
            foreach($file_dir as $file){
                if ($file == '.' || $file == '..'){
                    continue;
                }
                elseif (is_dir($dir.$file)){
                    $result = array_merge($result, self::findFileList($dir.$file.'/'));
                }
                else{
                    array_push($result, $dir.$file);
                }
            }
        }
        return $result;
    }

    /**
     * 保存base64编码图片到本地
     * @param $base64
     * @param string $folder
     * @return bool
     */
    public function uploadBase64Image($base64,$folder='default'){
        $base64_image = str_replace(' ', '+', $base64);

        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)){
            if($result[2] == 'jpeg'){
                $image_name = md5(uniqid()).'.jpg';
            }else{
                $image_name = md5(uniqid()).'.'.$result[2];
            }

            $rootPath = \Yii::$app->getBasePath().'/..';
            $filePath = '/uploads/'.$folder.'/'.date('Ym',time());

            if($this->createFolder($rootPath.$filePath)){
                if (file_put_contents($rootPath.$filePath.'/'.$image_name, base64_decode(str_replace($result[1], '', $base64_image)))){
                    return $filePath.'/'.$image_name;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }
        $this->errorInfo[] = '图片格式错误。';
        return false;
    }

    /**
     * 浏览器下载文件
     * @param $file string 文件名，示例："/uploads/files/test.zip"
     * @param null $name
     * @throws NotFoundHttpException
     */
    static public function outPutFile($file,$name=null){
        //一次返回102400个字节
        $buffer = 102400;

        // 文件名
        if(empty($name)) $name = time();
        $ext = explode('?',pathinfo($file,PATHINFO_EXTENSION));
        $namePathInfo = pathinfo($name,PATHINFO_EXTENSION);
        if($namePathInfo !== $ext[0]){
            $name = $name.'.'.$ext[0];
        }
        unset($namePathInfo);

        $ua = $_SERVER["HTTP_USER_AGENT"];
        $encoded_filename = urlencode($name);
        $encoded_filename = str_replace("+", "%20", $encoded_filename);


        // 网络文件
        $urlArray = parse_url($file);
        if(array_key_exists('host',$urlArray) && $urlArray['host'] != Yii::$app->getRequest()->hostName){
            if(stripos($file,'//',0)===0) $file = 'http:'.$file;
            $file = @ fopen($file, "r");
            if (!$file) {
                throw new NotFoundHttpException(Yii::t('common','File does not exist or has been deleted.'));
            } else {
                header("Content-type: application/octet-stream");
                // 浏览器判断
                if(preg_match("/MSIE/", $ua) || preg_match("/Trident\/7.0/", $ua) || preg_match("/Edge/", $ua)){
                    header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
                } else if (preg_match("/Firefox/", $ua)) {
                    header('Content-Disposition: attachment; filename*="utf8\'\'' . $name . '"');
                } else {
                    header('Content-Disposition: attachment; filename="' . $name . '"');
                }
                while (!feof($file)) {
                    echo fread($file, $buffer);
                }
                fclose($file);
            }
        }
        // 本地文件
        else{
            $file = ArrayHelper::getValue($urlArray,'path');
            $file = Yii::$app->basePath.'/..'.str_replace(['../','.php','robots.txt','.htaccess','.gitignore'],'',$file);
            if (!file_exists($file)) throw new NotFoundHttpException(Yii::t('common','File does not exist or has been deleted.'));

            $fp = fopen($file, "r");
            $fileSize = filesize($file);

            $fileData = '';
            while (!feof($fp)) {
                $fileData .= fread($fp, $buffer);
            }
            fclose($fp);

            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-type:application/octet-stream;");
            header("Accept-Ranges:bytes");
            header("Accept-Length:{$fileSize}");
            // 浏览器判断
            if(preg_match("/MSIE/", $ua) || preg_match("/Trident\/7.0/", $ua) || preg_match("/Edge/", $ua)){
                header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
            } else if (preg_match("/Firefox/", $ua)) {
                header('Content-Disposition: attachment; filename*="utf8\'\'' . $name . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $name . '"');
            }

            header("Content-Transfer-Encoding: binary");
            echo $fileData;
        }
    }

    /**
     * 文件夹打包成zip
     * @param $dirPath string 文件夹路径
     * @param null $fileName 打包后的文件名，不包含路径(文件名不能包涵中文名)
     * @return bool|string
     */
    public function createFolderZip($dirPath,$fileName = null){
        $fileList= self::findFileList($dirPath);
        if(!$fileName){
            $fileName = time();
        }
        $fileName = $dirPath.'/../'.$fileName.(preg_match("/\.zip$/i",$fileName)?'':'.zip');

        if(file_exists($fileName)){
            self::unlink($fileName);
        }

        $zip = new ZipArchive(); //使用本类，linux需开启zlib，windows需取消php_zip.dll前的注释
        if ($zip->open($fileName, ZIPARCHIVE::CREATE)!==TRUE) {
            if(!$this->createFolder($dirPath)) return false;
        }
        foreach($fileList as $val){
            if(file_exists($val)){
                $zip->addFile( $val, basename($val));
            }
        }
        $zip->close();
        if(file_exists($fileName)){
            return true;
        }

        return false;
    }


    public static function uploadRemote($fileUrls){
        if(is_string($fileUrls)) $fileUrls = [$fileUrls];

        $files = [];
        foreach($fileUrls as $fileUrl){
            $fileInfo = [];
            $fileInfo['status'] = 0;
            $fileInfo['originalFileUrl'] = $fileUrl;

            if(stripos($fileUrl,'//',0)===0) $fileUrl = 'http:'.$fileUrl;
            if (strpos($fileUrl, "http") !== 0) {
                $fileInfo['message'] = '“'.$fileUrl.'”不是一个有效的链接。';
                continue;
            }

            $heads = get_headers($fileUrl, 1);
            if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
                $fileInfo['message'] = '“'.$fileUrl.'”链接不可用。';
                continue;
            }

            ob_start();
            $context = stream_context_create(['http' => ['follow_location' => false]]);
            readfile($fileUrl, false, $context);
            $fileStream = ob_get_contents();
            ob_end_clean();

            $fileInfo['size'] = strlen($fileStream);

            preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $fileUrl, $m);
            $fileInfo['title'] = $m ? $m[1]:"";
            $fileInfo['ext'] = self::getFileExtension($fileInfo['title'],$fileStream);
            if(!$fileInfo['ext']){
                $fileInfo['message'] = '“'.$fileUrl.'”无法获取文件扩展名。';
                continue;
            }
            $fileInfo['title'] = str_replace('.'.$fileInfo['ext'],'',$fileInfo['title']);

            if (!isset($heads['Content-Type']) || (self::isImage($fileInfo['ext']) && !stristr($heads['Content-Type'], "image"))) {
                $fileInfo['message'] = '“'.$fileUrl.'”链接contentType不正确。';
                continue;
            }

            $fileInfo['path'] = '/uploads/images/'.date('Ym',time()).'/';
            $fileInfo['name'] = md5(uniqid(mt_rand(),true));
            $fileInfo['file'] = $fileInfo['path'].$fileInfo['name'].'.'.$fileInfo['ext'];

            if (!(file_put_contents(Yii::$app->getBasePath().'/..'.$fileInfo['file'],$fileStream) && file_exists(Yii::$app->getBasePath().'/..'.$fileInfo['file']))) {
                $fileInfo['message'] = '“'.$fileUrl.'”写入文件内容错误。';
                continue;
            }

            $fileInfo['status'] = 1;

            $files[] = $fileInfo;
        }

        return $files;
    }

    /**
     * 读取文件扩展名
     * @param $fileName
     * @param $stream
     * @return string
     */
    private static function getFileExtension($fileName,$stream = ''){
        $ext = strtolower(str_replace('.','',strrchr($fileName, '.')));;
        if(!$ext && $stream){
            $strInfo = @unpack("C2chars", $stream);
            $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);
            switch ($typeCode) {
                case 7790:
                    $ext = 'exe';
                    break;
                case 7784:
                    $ext = 'midi';
                    break;
                case 8297:
                    $ext = 'rar';
                    break;
                case 255216:
                    $ext = 'jpg';
                    break;
                case 7173:
                    $ext = 'gif';
                    break;
                case 6677:
                    $ext = 'bmp';
                    break;
                case 13780:
                    $ext = 'png';
                    break;
            }
        }
        return $ext;
    }

    /**
     * 是否图片
     * @param $ext
     * @return bool
     */
    private static function isImage($ext){
        return in_array($ext,['jpg','jpeg','gif','bmp','png']);
    }
}