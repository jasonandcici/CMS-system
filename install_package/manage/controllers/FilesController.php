<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/10/25
 */

namespace manage\controllers;

use common\components\manage\ManageController;
use common\entity\models\FilesCategoryModel;
use common\entity\models\FilesModel;
use common\entity\models\SystemConfigModel;
use common\entity\models\UploadForm;
use common\entity\searches\FilesSearch;
use common\helpers\ArrayHelper;
use common\helpers\FileHelper;
use common\helpers\HtmlHelper;
use common\helpers\StringHelper;
use common\helpers\SystemHelper;
use Yii;
use yii\web\UploadedFile;


/**
 * FilesController
 *
 * @author 
 * @since 1.0
 */
class FilesController extends ManageController
{
    /**
     * @var array
     */
    private $_config;

    /**
     * ueditor统一入口
     * n/10929
     * @param $action
     * @return mixed
     */
    public function actionIndex($action)
    {
        $config = SystemConfigModel::findConfig();
        $this->_config = $config;
        $uEditorConfig = $this->getConfig();
        switch ($action) {
            case 'uploadimage':
                $uploadForm = new UploadForm();
                $uploadForm->setFolder($uEditorConfig['imagePathFormat']);
                $uploadForm->setExtensions($this->formatExtensions($uEditorConfig['imageAllowFiles']));
                $uploadForm->setMaxSize($uEditorConfig['imageMaxSize']);
                if($res = $this->uploadFile($uploadForm)){
                    return json_encode($res);
                }
                break;
            case 'uploadscrawl':
                $uploadForm = new UploadForm();
                $uploadForm->setFolder($uEditorConfig['scrawlPathFormat']);
                $uploadForm->setExtensions($this->formatExtensions($uEditorConfig['imageAllowFiles']));
                $uploadForm->setMaxSize($uEditorConfig['scrawlMaxSize']);
                if($res = $this->uploadFile($uploadForm)){
                    return json_encode($res);
                }

                break;
            case 'catchimage':
                $uploadForm = new UploadForm();
                $uploadForm->setScenario('remote');
                $uploadForm->setFolder($uEditorConfig['catcherPathFormat']);
                $uploadForm->setExtensions($this->formatExtensions($uEditorConfig['catcherAllowFiles']));
                $uploadForm->setMaxSize($uEditorConfig['catcherMaxSize']);

                if($res = $this->uploadFile($uploadForm,'image',true)){
                    $res = [
                      'state'=>count($res) ? 'SUCCESS':'ERROR',
                      'list'=> $res
                    ];

                    return json_encode($res);
                }
                break;
            case 'uploadvideo':
                $uploadForm = new UploadForm();
                $uploadForm->setFolder($uEditorConfig['videoPathFormat']);
                $uploadForm->setExtensions($this->formatExtensions($uEditorConfig['videoAllowFiles']));
                $uploadForm->setMaxSize($uEditorConfig['videoMaxSize']);
                if($res = $this->uploadFile($uploadForm,'media')){
                    return json_encode($res);
                }
                break;
            case 'uploadfile':
                $uploadForm = new UploadForm();
                $uploadForm->setFolder($uEditorConfig['filePathFormat']);
                $uploadForm->setExtensions($this->formatExtensions($uEditorConfig['fileAllowFiles']));
                $uploadForm->setMaxSize($uEditorConfig['fileMaxSize']);
                if($res = $this->uploadFile($uploadForm,'attachment')){
                    return json_encode($res);
                }
                break;
            case 'listimage':
                return $this->fileList($uEditorConfig['imageManagerListSize']);
                break;
            case 'listfile':
                $data = Yii::$app->getRequest()->post('data',[]);
                if($data) {
                    $action = ArrayHelper::getValue($data,'action');
                    if($action) unset($data['action']);
                    return json_encode($this->fileOperation($data,$action));
                }else{
                    return $this->fileList($uEditorConfig['fileManagerListSize'],'attachment');
                }

                break;
            case 'filecategory':
                return json_encode($this->categoryOperation());
                break;
            default:
                return json_encode(ArrayHelper::merge(
                    ["formData"=>[Yii::$app->getRequest()->csrfParam=>Yii::$app->getRequest()->getCsrfToken()]],
                    $uEditorConfig
                ));
                break;
        }

        return json_encode(['state'=>$uploadForm->getErrorString()?:'ERROR']);
    }

    /**
     * 查找文件列表
     * @param $pageSize
     * @param string $type
     * @return string
     */
    protected function fileList($pageSize,$type = 'image'){
        $request = Yii::$app->getRequest();
        $searchModel = new FilesSearch();

        $data = $request->get('data',[]);
        $cid = ArrayHelper::getValue($data,'category_id');
        $childIds = ArrayHelper::getChildesId(FilesCategoryModel::getFileCategory(),$cid);
        if(!empty($childIds)){
            $childIds[] = $cid;
            $data['category_id'] = $childIds;
        }

        $dataProvider = $searchModel->search([StringHelper::basename($searchModel::className())=>$data]);

        $dataProvider->query
            ->andFilterWhere(['type'=>$type])
            ->asArray();

        $dataProvider->sort = [
            'defaultOrder' => [
                'sort'=> SORT_DESC,
            ]
        ];

        $start = Yii::$app->getRequest()->get('start',0);

        $dataProvider->pagination = ['pageSize'=>$pageSize];

        $dataList = $dataProvider->getModels();

        foreach ($dataList as $i=>$item){
            $dataList[$i]['url'] = $item['file'];
            $dataList[$i]['mtime'] = $item['create_time'];
        }

        return json_encode([
            "state" => "SUCCESS",
            'list'=>$dataList,
            "start" => $start,
            "total"=> $dataProvider->pagination->totalCount,

            "pageCount"=>$dataProvider->pagination->getPageCount(),
            "page"=>$dataProvider->pagination->getPage()+1,
            "perPage"=>$dataProvider->pagination->getPageSize(),
        ]);
    }

    /**
     * 文件上传
     * @param $model
     * @param string $type
     * @param bool $isMultiple
     * @return array|bool
     */
    protected function uploadFile($model,$type = 'image',$isMultiple = false){
        if(!$isMultiple){
            $model->file = UploadedFile::getInstance($model, 'file');
        }else{
            $loadData = $model->load(Yii::$app->getRequest()->post());
            if(!$loadData) return false;
        }

        $data = Yii::$app->getRequest()->post('data',[]);
        if(ArrayHelper::getValue($data,'enable_watermark')){
            $model->setWatermarkPosition(intval(ArrayHelper::getValue($data,'watermark_position','5')));
            $watermarkType = intval($this->_config['upload']['watermarkType']);
            $model->setWatermarkType($watermarkType);
            if($watermarkType){
                $model->setWatermarkOpacity(intval($this->_config['upload']['watermarkOpacity']));
                $model->setWatermarkContent(HtmlHelper::getFileItem($this->_config['upload']['watermarkPath']));
            }else{
                $model->setWatermarkContent($this->_config['upload']['watermarkText']);
                $model->setWatermarkTextSize($this->_config['upload']['watermarkTextSize']);
                $model->setWatermarkTextColor($this->_config['upload']['watermarkTextColor']);
            }
        }

        if ($files = $model->upload()) {
            $res = [];

            $insertData = [];
            $username = Yii::$app->getUser()->getIdentity()->username;
            $fileModel = new FilesModel();
            $autoIncrement = SystemHelper::getTableAutoIncrement($fileModel);
            $time = time();

            foreach ($files  as $item){
                $tmp = [
                    'id'=>$autoIncrement,
                    'state'=>$item['status']?'SUCCESS':$item['message'],
                    'url'=>$item['file'],
                    'title'=>$item['name'],
                    'original'=>$item['title'],
                    'type'=>$item['ext'],
                    'size'=>$item['size'],
                ];

                $tmpInsertData = [
                    'id'=>$autoIncrement,
                    'sort'=>$autoIncrement,
                    'create_time'=>$time,
                    'type'=>$type,
                    'title'=>$item['title'],
                    'username'=>$username,
                    'path'=>$item['path'],
                    'filename'=>$item['name'],
                    'extension'=>$item['ext'],
                    'file'=>$item['file'],
                    'size'=>$item['size'],
                    'width'=>null,
                    'height'=>null,
                ];
                if(ArrayHelper::getValue($data,'category_id')) $tmpInsertData['category_id'] = ArrayHelper::getValue($data,'category_id');
                $autoIncrement ++;

                if(array_key_exists('width',$item)){
                    $tmp['width'] = $item['width'];
                    $tmp['height'] = $item['height'];

                    $tmpInsertData['width'] = $item['width'];
                    $tmpInsertData['height'] = $item['height'];
                }

                if(array_key_exists('originalFileUrl',$item)){
                    $tmp['source'] = $item['originalFileUrl'];
                }

                $res[] = $tmp;
                $insertData[] = $tmpInsertData;

                if(!$isMultiple) break;
            }

            // 保存记录到数据库
            if(!empty($insertData))
                Yii::$app->getDb()->createCommand()->batchInsert($fileModel::tableName(),array_keys($insertData[0]),$insertData)->execute();

            return $isMultiple?$res:(array_key_exists(0,$res)?$res[0]:false);
        }else{
            return false;
        }
    }

    /**
     * 格式化扩展
     * @param string|array $extensions
     * @param bool $reverse
     * @return mixed
     */
    protected function formatExtensions($extensions,$reverse = false){
        if($reverse){
            $arr = explode(',',$extensions);
            foreach ($arr as $i=>$item){
                $arr[$i] = '.'.$item;
            }
            return $arr;
        }else{
            $str = implode(',',$extensions);
            return str_replace('.','',$str);
        }
    }

    /**
     * 编辑器配置
     * @return array
     */
    protected function getConfig()
    {
        //$phpMaxSize = intval(ini_get('upload_max_filesize'))*1024*1024;
        $imageMaxSize = intval($this->_config['upload']['imageMaxSize'])*1024*1024;
        $imageAllowFiles = $this->formatExtensions($this->_config['upload']['imageAllowFiles'],true);

        $uEditorConfig = [
            // 文件管理分类的action
            "fileManagerCategoryAction"=>'filecategory',
            "fileManagerCategoryList"=>FilesCategoryModel::getFileCategory(),

            // 上传图片配置项
            "imageActionName" => "uploadimage", // 执行上传图片的action名称
            "imageFieldName" => "UploadForm[file]", // 提交的图片表单名称
            "imageMaxSize" => $imageMaxSize, // 上传大小限制，单位B
            "imageAllowFiles" => $imageAllowFiles,
            "imageCompressEnable" => (boolean)$this->_config['upload']['imageCompressEnable'], // 是否压缩图片,默认是true
            "imageCompressBorder" => intval($this->_config['upload']['imageCompressBorder']), // 图片压缩最长边限制
            "imageInsertAlign" => "none", // 插入的图片浮动方式
            "imageUrlPrefix" => "", // 图片访问路径前缀
            "imagePathFormat" => "images", // 上传保存路径,可以自定义保存路径和文件名格式

            "snapscreenActionName" => "uploadimage", // 执行上传截图的action名称
            "snapscreenPathFormat" => "images", // 上传保存路径,可以自定义保存路径和文件名格式
            "snapscreenUrlPrefix" => "", // 图片访问路径前缀
            "snapscreenInsertAlign" => "none", // 插入的图片浮动方式

            // 涂鸦图片上传配置项
            "scrawlActionName" => "uploadscrawl", // 执行上传涂鸦的action名称
            "scrawlFieldName" => "UploadForm[file]", // 提交的图片表单名称
            "scrawlPathFormat" => "images/scrawl", // 上传保存路径,可以自定义保存路径和文件名格式
            "scrawlMaxSize" => $imageMaxSize, // 上传大小限制，单位B
            "scrawlUrlPrefix" => "", // 图片访问路径前缀
            "scrawlInsertAlign" => "none",

            // 抓取远程图片配置
            "catcherLocalDomain" => ["127.0.0.1", "localhost"],
            "catcherActionName" => "catchimage", // 执行抓取远程图片的action名称
            "catcherFieldName" => "UploadForm[file]", // 提交的图片列表表单名称
            "catcherPathFormat" => "images/catcher", // 上传保存路径,可以自定义保存路径和文件名格式
            "catcherUrlPrefix" => "", // 图片访问路径前缀
            "catcherMaxSize" => $imageMaxSize, // 上传大小限制，单位B
            "catcherAllowFiles" => $imageAllowFiles,

            // 上传视频配置
            "videoActionName" => "uploadvideo", // 执行上传视频的action名称
            "videoFieldName" => "UploadForm[file]", // 提交的视频表单名称
            "videoPathFormat" => "files/video", // 上传保存路径,可以自定义保存路径和文件名格式
            "videoUrlPrefix" => "", // 视频访问路径前缀
            "videoMaxSize" => intval($this->_config['upload']['videoMaxSize'])*1024*1024, // 上传大小限制，单位B，默认500MB
            "videoAllowFiles" => $this->formatExtensions($this->_config['upload']['videoAllowFiles'],true),

            // 上传文件配置
            "fileActionName" => "uploadfile", // controller里,执行上传视频的action名称
            "fileFieldName" => "UploadForm[file]", // 提交的文件表单名称
            "filePathFormat" => "files", // 上传保存路径,可以自定义保存路径和文件名格式
            "fileUrlPrefix" => "", // 文件访问路径前缀
            "fileMaxSize" => intval($this->_config['upload']['fileMaxSize'])*1024*1024, // 上传大小限制，单位B，默认500MB
            "fileAllowFiles" =>$this->formatExtensions($this->_config['upload']['fileAllowFiles'],true),

            // 列出指定目录下的图片
            "imageManagerActionName" => "listimage", // 执行图片管理的action名称
            "imageManagerListPath" => "", // 指定要列出图片的目录
            "imageManagerListSize" => 30, // 每次列出文件数量
            "imageManagerUrlPrefix" => "", // 图片访问路径前缀
            "imageManagerInsertAlign" => "none", // 插入的图片浮动方式
            "imageManagerAllowFiles" => [],

            // 列出指定目录下的文件
            "fileManagerActionName" => "listfile", // 执行文件管理的action名称
            "fileManagerListPath" => "", // 指定要列出文件的目录
            "fileManagerUrlPrefix" => "", // 文件访问路径前缀
            "fileManagerListSize" => 30, // 每次列出文件数量
            "fileManagerAllowFiles" => []

        ];
        return $uEditorConfig;
    }

    /**
     * 文件分类管理操作
     */
    protected function categoryOperation(){
        $res = ['state'=>'ERROR'];
        $data = Yii::$app->getRequest()->post('data',[]);
        $action = ArrayHelper::getValue($data,'action');
        if($action) unset($data['action']);
        $model = new FilesCategoryModel();
        switch ($action){
            case 'create':
                if($model->load([StringHelper::basename($model::className())=>$data]) && $model->save()){
                    $model->sort = $model->primaryKey;
                    $model->save();
                    $res['state'] = 'SUCCESS';
                    $res['data'] = ArrayHelper::toArray($model);
                }else{
                    $res['state'] = $model->getErrorString();
                }
                break;
            case 'update':
                $model = $model::findOne(ArrayHelper::getValue($data,'id'));
                if($model && $model->load([StringHelper::basename($model::className())=>$data]) && $model->save()){
                    $res['state'] = 'SUCCESS';
                    $res['data'] = ArrayHelper::toArray($model);
                }else{
                    $res['state'] = $model->getErrorString();
                }
                break;

            case 'delete':
                $model = $model::findOne(ArrayHelper::getValue($data,'id'));
                if($model){
                    $subCateIds = ArrayHelper::getChildesId(FilesCategoryModel::getFileCategory(),$model->id);
                    $subCateIds[] = $model->id;
                    FilesModel::updateAll(['category_id'=>null],['category_id'=>$subCateIds]);
                    FilesCategoryModel::deleteAll(['id'=>$subCateIds]);

                    $res['state'] = 'SUCCESS';
                }else{
                    $res['state'] = "数据不存在。";
                }
                break;
            case 'empty':
                $id = ArrayHelper::getValue($data,'id');
                $subCateIds = ArrayHelper::getChildesId(FilesCategoryModel::getFileCategory(),$id);
                $subCateIds[] = $id;
                // 物理删除图片
                $files = FilesModel::find()->where(['category_id'=>$subCateIds])->select(['file'])->asArray()->all();
                foreach ($files as $item){
                    $file = Yii::$app->getBasePath().'/..'.$item['file'];
                    if(file_exists($file)){
	                    FileHelper::unlink($file);
                    }
                }
                FilesModel::deleteAll(['category_id'=>$subCateIds]);
                $res['state'] = 'SUCCESS';
                break;
        }
        if($res['state'] === 'SUCCESS') Yii::$app->getCache()->delete('fileCategory');
        return $res;
    }

    /**
     * 文件管理操作
     * @param $data
     * @param $action
     * @return array
     */
    protected function fileOperation($data,$action){
        $res = ['state'=>'ERROR'];
        switch ($action){
            case 'delete':
                $id = ArrayHelper::getValue($data,'id');
                $files = FilesModel::find()->where(['id'=>$id])->select(['file'])->asArray()->all();
                foreach ($files as $item){
                    $file = Yii::$app->getBasePath().'/..'.$item['file'];
                    if(file_exists($file)){
	                    FileHelper::unlink($file);
                    }
                }
                FilesModel::deleteAll(['id'=>$id]);
                $res['state'] = 'SUCCESS';
                break;
            case 'update':
                $model = FilesModel::findOne(ArrayHelper::getValue($data,'id'));
                if($model && $model->load([StringHelper::basename($model::className())=>$data]) && $model->save()){
                    $res['state'] = 'SUCCESS';
                    $res['data'] = ArrayHelper::toArray($model);
                }else{
                    $res['state'] = $model->getErrorString();
                }
                break;

            case 'move':
                FilesModel::updateAll(['category_id'=>ArrayHelper::getValue($data,'category_id')],['id'=>ArrayHelper::getValue($data,'id')]);
                $res['state'] = 'SUCCESS';
                break;
        }

        return $res;
    }
}