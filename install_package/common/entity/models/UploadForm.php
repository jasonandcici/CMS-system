<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/10/25
 */

namespace common\entity\models;

use common\components\BaseModel;
use common\helpers\ArrayHelper;
use think\Image;
use Yii;


/**
 * 上传类
 *
 * @author 
 * @since 1.0
 */
class UploadForm extends BaseModel
{
    /**
     * @var mixed 上传的文件
     */
    public $file;

    /**
     * @var boolean 水印类型（允许的值 0-文字水印 1-图片水印）
     */
    public $_watermarkType = 0;

    /**
     * @var string 水印内容（文字或图片文件路径）
     */
    public $_watermarkContent;

    /**
     * @var int 水印位置(1~9)
     */
    public $_watermarkPosition = 5;

    /**
     * @var int 水印透明度（1~100），watermarkType=1时有效
     */
    public $_watermarkOpacity = 50;


    /**
     * @var int 文字水印大小，watermarkType=0时有效
     */
    public $_watermarkTextSize = 30;

    /**
     * @var string 文字水印颜色 16进制，watermarkType=0时有效
     */
    public $_watermarkTextColor = '#ffffff';

    /**
     * @var string 所在文件夹
     */
    private $_folder = 'default';

    /**
     * @var string 后缀
     */
    private $_extensions = 'jpg,jpeg,gif,bmp,png,ico,doc,docx,xls,xlsx,ppt,pptx,pdf,txt,rar,zip,tar,7-zip,gzip,apk';

    /**
     * @var int 允许的大小
     */
    private $_maxSize = 2097152; // 2M

    /**
     * @var string 路径
     */
    private $_rootPath;
    private $_filePath;

    public function init()
    {
        parent::init();

        $this->_rootPath = Yii::$app->getBasePath().'/..';
    }

    /**
     * 验证规则
     * @return array
     */
    public function rules()
    {
        return [
            [['file'], 'required','on'=>['default','remote','base64']],
            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => $this->_extensions,'maxSize'=> $this->_maxSize,'on'=>'default'],

            [['file'],'filter','filter'=>function($value){
                return is_array($value)?$value:[$value];
            }],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'file' => '文件',
        ];
    }

    /**
     * 上传
     * 需要 UploadedFile::getInstance($model, 'file')方法支持
     * @return array|bool
     * @internal param string $type
     */
    public function upload()
    {
        if (!$this->validate()) return false;

        $this->_filePath = '/uploads/'.$this->_folder.'/'.date('Ym',time()).'/';
        if(!$this->createFolder($this->_rootPath.$this->_filePath)){
            return false;
        }

        if($this->scenario === 'remote'){
            $res = $this->uploadRemote();
        }elseif ($this->scenario === 'base64'){
            $res = $this->uploadBase64();
        }else{
            $res = $this->uploadFile();
        }

        return $res;
    }

    /**
     * 上传文件
     * @return array|bool
     */
    protected function uploadFile(){
        $files = [];
        foreach($this->file as $file){
            $fileInfo = [];
            $fileInfo['status'] = 1;
            $fileInfo['name'] = md5(uniqid(mt_rand(),true));
            $file->saveAs($this->_rootPath.$this->_filePath.$fileInfo['name'].'.'.$file->extension);

            $fileInfo['title'] = $file->baseName;
            $fileInfo['path'] = $this->_filePath;
            $fileInfo['file'] = $fileInfo['path'].$fileInfo['name'].'.'.$file->extension;
            $fileInfo['ext'] = $file->extension;
            $fileInfo['size'] = floor($file->size/1024);

            if($this->isImage($file->extension)){
                $fileInfo = ArrayHelper::merge($fileInfo,$this->generateWatermark($this->_rootPath.$fileInfo['file']));
            }

            $files[] = $fileInfo;
        }

        return $files;
    }

    /**
     * 上传远程文件
     * @return array|bool
     */
    protected function uploadRemote(){
        $files = [];
        foreach($this->file as $fileUrl){
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
            if($fileInfo['size'] > $this->_maxSize){
                $fileInfo['message'] = '“'.$fileUrl.'”文件大小超出限制。';
                continue;
            }

            preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $fileUrl, $m);
            $fileInfo['title'] = $m ? $m[1]:"";
            $fileInfo['ext'] = $this->getFileExtension($fileInfo['title'],$fileStream);
            if(!$fileInfo['ext']){
                $fileInfo['message'] = '“'.$fileUrl.'”无法获取文件扩展名。';
                continue;
            }
            $fileInfo['title'] = str_replace('.'.$fileInfo['ext'],'',$fileInfo['title']);

            if(!in_array($fileInfo['ext'], explode(',',$this->_extensions))){
                $fileInfo['message'] = '“'.$fileUrl.'”文件类型不允许。';
                continue;
            }

            if (!isset($heads['Content-Type']) || ($this->isImage($fileInfo['ext']) && !stristr($heads['Content-Type'], "image"))) {
                $fileInfo['message'] = '“'.$fileUrl.'”链接contentType不正确。';
                continue;
            }

            $fileInfo['path'] = $this->_filePath;
            $fileInfo['name'] = md5(uniqid(mt_rand(),true));
            $fileInfo['file'] = $fileInfo['path'].$fileInfo['name'].'.'.$fileInfo['ext'];

            if (!(file_put_contents($this->_rootPath.$fileInfo['file'],$fileStream) && file_exists($this->_rootPath.$fileInfo['file']))) {
                $fileInfo['message'] = '“'.$fileUrl.'”写入文件内容错误。';
                continue;
            }

            $fileInfo['status'] = 1;

            if($this->isImage($fileInfo['ext'])){
                $fileInfo = ArrayHelper::merge($fileInfo,$this->generateWatermark($this->_rootPath.$fileInfo['file']));
            }

            $files[] = $fileInfo;
        }

        return $files;
    }

    /**
     * 上传base64（暂时只支持 base64编码的图片文件）
     * @return array|bool
     */
    protected function uploadBase64(){
        $files = [];
        foreach($this->file as $item){
            $fileInfo = [];
            $fileInfo['status'] = 0;

            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $item, $result)){
                if($result[2] == 'jpeg'){
                    $fileInfo['ext'] = 'jpg';
                }else{
                    $fileInfo['ext'] = $result[2];
                }
            }else{
                $fileInfo['message'] = '“'.$item.'”无法获取文件扩展名。';
                continue;
            }

            if(!in_array($fileInfo['ext'], explode(',',$this->_extensions))){
                $fileInfo['message'] = '“'.$item.'”文件类型不允许。';
                continue;
            }

            $fileInfo['name'] = md5(uniqid(mt_rand(),true));
            $fileInfo['title'] = $fileInfo['name'];
            $fileInfo['path'] = $this->_filePath;
            $fileInfo['file'] = $fileInfo['path'].$fileInfo['name'].'.'.$fileInfo['ext'];

            $file = base64_decode(str_replace($result[1],'',$item));
            $fileInfo['size'] = strlen($file);
            if($fileInfo['size'] > $this->_maxSize){
                $fileInfo['message'] = '“'.$item.'”文件大小超出限制。';
                continue;
            }

            if (!file_put_contents($this->_rootPath.$fileInfo['file'], $file) || !file_exists($this->_rootPath.$fileInfo['file'])) {
                $fileInfo['message'] = '“'.$item.'”写入文件内容错误。';
                continue;
            }

            $fileInfo['status'] = 1;

            if($this->isImage($fileInfo['ext'])){
                $fileInfo = ArrayHelper::merge($fileInfo,$this->generateWatermark($this->_rootPath.$fileInfo['file']));
            }

            $files[] = $fileInfo;
        }
        return $files;
    }

    /**
     * 生成水印
     */
    protected function generateWatermark($file){
        $res = [];
        $imgInfo = Image::open($file);
        $res['width'] = $imgInfo->width();
        $res['height'] = $imgInfo->height();

        if(!(empty($this->_watermarkContent) || ($this->_watermarkType && !file_exists($this->_rootPath.$this->_watermarkContent)))){
            if($this->_watermarkType){
                $imgInfo->water($this->_rootPath.$this->_watermarkContent,$this->_watermarkPosition,$this->_watermarkOpacity)
                    ->save($file);
            }else{
                $imgInfo->text(
                    $this->_watermarkContent,
                    Yii::getAlias('@common/assets/fonts/droidsansfallback.ttf'),
                    $this->_watermarkTextSize,$this->_watermarkTextColor,$this->_watermarkPosition,10
                )->save($file);
            }
        }

        return $res;
    }

    /**
     * 创建文件夹
     * @param $dirName
     * @return bool
     */
    private function createFolder($dirName){
        if (!file_exists($dirName) && !mkdir($dirName, 0777, true)) {
            $this->addError('folder','目录创建失败');
            return false;
        }
        else if (!is_writeable($dirName)) {
            $this->addError('folder','目录没有写权限');
            return false;
        }
        return true;
    }

    /**
     * 是否图片
     * @param $ext
     * @return bool
     */
    private function isImage($ext){
        return in_array($ext,['jpg','jpeg','gif','bmp','png']);
    }

    /**
     * 读取文件扩展名
     * @param $fileName
     * @param $stream
     * @return string
     */
    private function getFileExtension($fileName,$stream = ''){
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
     * @param $value
     */
    public function setFolder($value)
    {
        $this->_folder = trim($value);
    }

    /**
     * @return string
     */
    public function getFolder()
    {
        return $this->_folder;
    }

    /**
     * @param $value
     */
    public function setExtensions($value)
    {
        $this->_extensions = trim($value);
    }

    /**
     * @param $value
     */
    public function getExtensions($value)
    {
        $this->_extensions = trim($value);
    }

    /**
     * @param $value
     */
    public function setMaxSize($value)
    {
        $this->_maxSize = trim($value);
    }

    /**
     * @param $value
     */
    public function getMaxSize($value)
    {
        $this->_maxSize = trim($value);
    }

    /**
     * @param $value
     */
    public function setWatermarkContent($value)
    {
        $this->_watermarkContent = trim($value);
    }

    /**
     * @param $value
     */
    public function getWatermarkContent($value)
    {
        $this->_watermarkContent = trim($value);
    }
    /**
     * @param $value
     */
    public function setWatermarkPosition($value)
    {
        $this->_watermarkPosition = trim($value);
    }

    /**
     * @param $value
     */
    public function getWatermarkPosition($value)
    {
        $this->_watermarkPosition = trim($value);
    }

    /**
     * @param $value
     */
    public function setWatermarkType($value)
    {
        $this->_watermarkType = trim($value);
    }

    /**
     * @param $value
     */
    public function getWatermarkType($value)
    {
        $this->_watermarkType = trim($value);
    }

    /**
     * @param $value
     */
    public function setWatermarkOpacity($value)
    {
        $this->_watermarkOpacity = trim($value);
    }

    /**
     * @param $value
     */
    public function getWatermarkOpacity($value)
    {
        $this->_watermarkOpacity = trim($value);
    }

    /**
     * @param $value
     */
    public function setWatermarkTextSize($value)
    {
        $this->_watermarkTextSize = trim($value);
    }

    /**
     * @param $value
     */
    public function getWatermarkTextSize($value)
    {
        $this->_watermarkTextSize = trim($value);
    }

    /**
     * @param $value
     */
    public function setWatermarkTextColor($value)
    {
        $this->_watermarkTextColor = trim($value);
    }

    /**
     * @param $value
     */
    public function getWatermarkTextColor($value)
    {
        $this->_watermarkTextColor = trim($value);
    }
}