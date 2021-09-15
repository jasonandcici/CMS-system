<?php
/**
 * @copyright
 * @link
 * @create Created on 2017/9/15
 */

namespace manage\models;

use common\entity\models\PrototypeCategoryModel;
use common\entity\models\PrototypeFieldModel;
use common\helpers\ArrayHelper;
use common\helpers\FileHelper;
use common\helpers\HtmlHelper;
use common\helpers\SystemHelper;
use common\helpers\UrlHelper;
use Exception;
use moonland\phpexcel\Excel;
use PHPExcel_IOFactory;
use PHPExcel_Style_NumberFormat;
use Yii;
use yii\base\Model;
use yii\web\NotFoundHttpException;
use ZipArchive;


/**
 * 原型数据导入
 *
 * @author
 * @since 1.0
 */
class ImportPrototypeForm extends Model
{

    public $categoryId;
    /**
     * @var string 附件
     */
    public $attachment;
    /**
     * @var string 数据
     */
    public $excelFile;

    /**
     * @var object 栏目信息
     */
    private $categoryInfo;

	/**
	 * @var array
	 */
    private $categoryList;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['categoryId','excelFile'], 'required'],
            [['attachment'],'string'],
            ['categoryId',function($attribute, $params){
                if (!$this->hasErrors()) {
                    $this->categoryInfo = PrototypeCategoryModel::find()->where(['id'=>$this->$attribute,'type'=>0])->with(['model'])->one();
                    if(!$this->categoryInfo){
                    	$this->addError($attribute, '该栏目无法导入数据。');
                    }else{
	                    $this->categoryList = PrototypeCategoryModel::findCategory($this->categoryInfo->site_id);
                    }
                }
            }],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'categoryId' => '栏目',
            'attachment' => '附件',
            'excelFile'=>'Excel',
        ];
    }

    /**
     * @var string 上传目录
     */
    public $uploadsPath;

    /**
     * @var string 导入时唯一文件夹
     */
    private $_uniqueFolder;

    public function init()
    {
        parent::init();

        $this->uploadsPath = Yii::$app->getBasePath().'/../uploads';
    }

	/**
	 * 导入
	 * @return bool
	 * @throws \yii\db\Exception
	 */
    public function save(){
        if(!$this->validate()) return false;
        $this->_uniqueFolder = time();

        $fields = PrototypeFieldModel::find()->where(['model_id'=>$this->categoryInfo->model_id])->orderBy(['sort'=>SORT_ASC])->select(['history'])->asArray()->all();
        $modelColumns = ['标题'=>'title'];
        $fieldType = [];
        $relationsFieldColumns = [];
        foreach ($fields as $item){
            if(!empty($item['history'])){
                $fieldInfo = json_decode($item['history'],true);
                if($fieldInfo['type'] !== 'relation_data' || !$fieldInfo['setting']['relationType']){
                    $modelColumns[$fieldInfo['title']] = $fieldInfo['name'];
                    $fieldType[$fieldInfo['name']] = $fieldInfo['type'];
                }else{
	                $relationsFieldColumns[$fieldInfo['title']] = $fieldInfo['name'];
                }
            }
        }
        $modelColumns['SEO标题'] = 'seo_title';
        $modelColumns['SEO关键词'] = 'seo_keywords';
        $modelColumns['SEO描述'] = 'seo_description';
        unset($fields);

        $data = Excel::import(Yii::$app->getBasePath().'/..'.HtmlHelper::getFileItem($this->excelFile),['setFirstRecordAsKeys' => false,])?:[];
        $dataKeys = [];
        foreach ($data as $i=>$item){
            foreach ($item as $k=>$v){
                if(empty($v)) unset($item[$k]);
            }
            $dataKeys = $item;
            unset($data[$i]);
            break;
        }


        // 验证导入的字段是否和系统一致
        if(count($modelColumns)+count($relationsFieldColumns) !== count($dataKeys)){
            $this->addError('excelFile','Excel表头和模型字段不一致。');
            return false;
        }

        $relationFieldKeys = [];
        foreach ($dataKeys as $i=>$item){
        	if(array_key_exists($item,$modelColumns)){
		        $dataKeys[$i] = $modelColumns[$item];
	        }elseif (array_key_exists($item,$relationsFieldColumns)){
		        $relationFieldKeys[$i] = $relationsFieldColumns[$item];
	        }else{
		        $this->addError('excelFile','Excel表头和模型字段不一致。');
		        return false;
	        }
        }
        unset($modelColumns,$relationsFieldColumns);

        // 插入数据
        $modelName = '\\common\\entity\\nodes\\'.ucwords($this->categoryInfo->model->name).'Model';
        $model = new $modelName();
        $tableName = $model::tableName();
        $autoIncrement = SystemHelper::getTableAutoIncrement($model);
        unset($modelName,$model);
	    $insertFields = ['id','site_id','model_id','category_id','sort','is_login','layouts','create_time','update_time'];
	    foreach ($dataKeys as $kk=>$kv){
		    if(!array_key_exists($kk,$relationFieldKeys)){
			    $insertFields[] = $kv;
		    }
	    }

        $db = Yii::$app->getDb();
        $time = time();
        $data = array_chunk($data,500);
        foreach($data as $g=>$group){
        	$sql = '';
        	$relationSql = '';
            $insertData = [];
            foreach ($group as $item){
                $temp = [
                    'id' => $autoIncrement,
                    'site_id' => $this->categoryInfo->site_id,
                    'model_id' => $this->categoryInfo->model_id,
                    'category_id' => $this->categoryInfo->id,
                    'sort' => $autoIncrement,
	                'is_login'=>$this->categoryInfo->is_login_content,
	                'layouts'=>$this->categoryInfo->layouts_content,
                    'create_time' => $time,
                    'update_time' => $time,
                ];

                foreach ($item as $k=>$v){
                    if(!array_key_exists($k,$dataKeys)) continue;
                    if(array_key_exists($k,$relationFieldKeys)){
						if(!empty($v)){
							$insertRelationsData = [];
							foreach (explode(',',$v) as $rv){
								$insertRelationsData[] = [
									'parent_id'=>$autoIncrement,
									'relation_id'=>intval($rv),
								];
							}
							$relationSql .= str_replace('`___','_',$db->createCommand()->batchInsert($tableName.'___'.substr_replace($relationFieldKeys[$k],'',-4).'_relation`',['parent_id','relation_id'],$insertRelationsData)->rawSql.';');
						}
                    }else{
	                    switch (ArrayHelper::getValue($fieldType,$dataKeys[$k])){
		                    case 'int':
			                    $v = intval($v);
			                    break;
		                    case 'image':
		                    case 'image_multiple':
			                    $v = $this->handleAttachment($v);
			                    break;
		                    case 'attachment':
		                    case 'attachment_multiple':
			                    $v = $this->handleAttachment($v,1);
			                    break;
		                    case 'number':
			                    $v = floatval($v);
			                    break;
		                    case 'relation_data':
			                    if(empty($v)) $v = null;
			                    break;
		                    case 'radio':
		                    case 'radio_inline':
		                    case 'select':
			                    $v = (string)$v;
		                    	break;
	                    }
	                    $temp[$dataKeys[$k]] = $v;
                    }
                }
                $insertData[] = $temp;
                $autoIncrement++;
            }
            unset($data[$g]);

	        $sql .= $db->createCommand()->batchInsert($tableName,$insertFields,$insertData)->rawSql.';';

	        if(!empty($sql)) $db->createCommand($sql.$relationSql)->execute();
        }
        unset($data,$dataKeys);

        // 解压附件
        $file = new FileHelper();
	    //FileHelper::unlink(Yii::$app->getBasePath().'/..'.HtmlHelper::getFileItem($this->excelFile));
        if(!empty($this->attachment)){
            $attachmentPath = $this->uploadsPath.'/imports/'.$this->_uniqueFolder;
            $file->createFolder($attachmentPath);
            $zip = new ZipArchive;
            foreach (HtmlHelper::fileDataHandle($this->attachment) as $item){
                $res = $zip->open($f = Yii::$app->getBasePath().'/..'.HtmlHelper::getFileItem($item));
                if($res === true){
                    $zip->extractTo($attachmentPath);
                    $zip->close();
                }
	            FileHelper::unlink($f);
            }

            /*try{
                FileHelper::chmodr($attachmentPath,0755);
            }catch (Exception $e){
                Yii::error('批量上传时，目录“'.$attachmentPath.'”设置读写权限失败。');
            }*/
        }

        return true;
    }

    public function handleAttachment($file,$type = 0){
        if(empty($file)) return $file;
        $file = explode(',',$file);
        $res = [];
        foreach ($file as $item){
            if(empty($item)) continue;
            $tmp = explode('/',$item);
            $res[] = [
                'file'=>(stripos($item,'http://',0)===0 || stripos($item,'https://',0) === 0)?$item:'/uploads/imports/'.$this->_uniqueFolder.(preg_match("/^\//",$item)?'':'/').$item,
                ($type?'title':'alt')=>$tmp[count($tmp)-1]
            ];
        }
        return empty($res)?'':json_encode($res);
    }

	/**
	 * 返回栏目模板
	 *
	 * @param $categoryId
	 *
	 * @throws NotFoundHttpException
	 * @throws \PHPExcel_Exception
	 * @throws \PHPExcel_Reader_Exception
	 * @throws \PHPExcel_Writer_Exception
	 */
    public function getTpl($categoryId){
        $categoryInfo = PrototypeCategoryModel::find()->where(['id'=>$categoryId,'type'=>0])
            ->select(['id','model_id','title','site_id'])->asArray()->one();
        if(!$categoryInfo) throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
		$this->categoryInfo = $categoryInfo;
        $this->categoryList = PrototypeCategoryModel::findCategory($categoryInfo['site_id']);

        $fields = PrototypeFieldModel::find()->where(['model_id'=>$categoryInfo['model_id']])->orderBy(['sort'=>SORT_ASC])->select(['history'])->asArray()->all();

        // 导出Excel和说明文档
        $doc = "数据批量导入模板说明\r\n \r\n此模板适用于栏目【".$categoryInfo['title']."】\r\n \r\n1、标题（文本框）：\r\n必填项、长度不超过255个字符。";

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
            ->setTitle($categoryInfo['title'].'_批量导入模板')
            ->setCreator('测试站点')
            ->setLastModifiedBy('测试站点')
            ->setCompany('测试站点')
            ->setKeywords('批量导入')
            ->setCategory($categoryInfo['title'])
            ->setDescription('本文档为数据批量导入模板，适用于栏目“'.$categoryInfo['title'].'”。');

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1','标题');
        $count = 1;

        $fieldModel = new PrototypeFieldModel();
        foreach ($fields as $item){
            if(!empty($item['history'])){
                $fieldInfo = json_decode($item['history'],true);
                //if($fieldInfo['type'] !== 'relation_data'){
                    // 模板
                    $columnIndex = \PHPExcel_Cell::stringFromColumnIndex($count);
                    switch ($fieldInfo['type']){
                        case 'int':
                            $objPHPExcel->getActiveSheet()->getStyle($columnIndex)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
                            break;
                        case 'number':
                            $objPHPExcel->getActiveSheet()->getStyle($columnIndex)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER.($fieldInfo['field_decimal_place']>0?'.'.str_repeat('0',$fieldInfo['field_decimal_place']):''));
                            break;
                        case 'date':
                            $objPHPExcel->getActiveSheet()->getStyle($columnIndex)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
                            break;
                        case 'datetime':
                            $objPHPExcel->getActiveSheet()->getStyle($columnIndex)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2.' '.PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME3);
                            break;
                    }

                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($columnIndex.'1', $fieldInfo['title']);
                    $count ++;

                    // 文档
                    $doc .="\r\n \r\n".$count."、".$fieldInfo['title']."（".$fieldModel->filedTypeText[$fieldInfo['type']]."）：\r\n";
                    $doc .= implode('；',$this->generateDocItem($fieldInfo)).'。';
                //}
            }
        }
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue(\PHPExcel_Cell::stringFromColumnIndex($count).'1', 'SEO标题');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue(\PHPExcel_Cell::stringFromColumnIndex($count+1).'1', 'SEO关键词');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue(\PHPExcel_Cell::stringFromColumnIndex($count+2).'1', 'SEO描述');

        $file = new FileHelper();

        $importPath = $this->uploadsPath.'/temps/import'.$categoryId;

        if(!$file->createFolder($importPath)) throw new NotFoundHttpException($file->getError());

        $doc .="\r\n \r\n".($count+1)."、SEO标题（文本框）：\r\n长度不超过255个字符。";
        $doc .="\r\n \r\n".($count+2)."、SEO关键词（文本框）：\r\n长度不超过255个字符。";
        $doc .="\r\n \r\n".($count+3)."、SEO描述（文本域）：\r\n长度不超过255个字符。";
        $doc .="\r\n \r\n注意：\r\nExcel中的第一栏不要修改和删除，会导致数据无法导入。\r\n数据中的“图片”或“附件”请打包成“zip”文件，在数据导入时点击“附件上传”按钮进行上传。\r\n图片名、附件名不要包含中文字符。\r\n此模板适用于栏目【".$categoryInfo['title']."】。";
        $file->put($importPath.'/'.'doc.txt',$doc);
        unset($doc);

        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($importPath.'/template_v'.substr(time(),5).'.xlsx');
        unset($objWriter);

        // 打包zip并下载
        $file->createFolderZip($importPath,'import'.$categoryId);
        FileHelper::delDirAndFile($importPath);
        FileHelper::outPutFile('/uploads/temps/import'.$categoryId.'.zip');
	    FileHelper::unlink($this->uploadsPath.'/temps/import'.$categoryId.'.zip');
    }

    /**
     * 字段验证说明
     * @param $fieldInfo
     * @return array
     */
    private function generateDocItem($fieldInfo){
        $res = [];
        $verification = empty($fieldInfo['custom_verification_rules'])?[]:$fieldInfo['custom_verification_rules'];

        if($fieldInfo['is_required']) $res[] = '必填项';

        switch ($fieldInfo['type']){
            case 'text':
            case 'textarea':
                if($len = $this->lengthDoc($fieldInfo['field_length'],$verification)) $res[] = $len;
                if(ArrayHelper::getValue($verification,'unique',false)) $res[] = '值必须唯一';
                if(ArrayHelper::getValue($verification,'email',false)) $res[] = '电子邮箱格式';
                if(ArrayHelper::getValue($verification,'ip',false)) $res[] = 'IP格式';
                if(ArrayHelper::getValue($verification,'url',false)) $res[] = '以http://或https://开头的链接格式';
                break;
            case 'int':
                //if($len = $this->lengthDoc($fieldInfo['field_length'],$verification,'大小')) $res[] = $len;
                if(ArrayHelper::getValue($verification,'unsigned',false)) $res[] = '值必须为大于等于0的正整数';
                $compare = $this->compareDoc(ArrayHelper::getValue($verification,'compare',false));
                if($compare) $res[] = $compare;
                break;
            case 'editor':
                $_str = "富文本编辑器必须遵循一定格式，请可使用系统提供的富文本编辑器（".UrlHelper::toRoute(['import/ueditor'],true)."）编辑完成后切换到html视图，复制html代码并粘贴即可";
                $res[] = $_str;
                break;
            case 'image':
                $res[] = "图片地址格式“图片文件夹/图片名称.jpg”，图片格式“jpg|png|gif，图片名称不能包含中文字符”";
                break;
            case 'image_multiple':
                $res[] = "图片地址格式“图片文件夹/图片名称.jpg”，图片格式“jpg|png|gif”，多个图片用“,”英文逗号分隔，图片名称不能包含中文字符";
                break;
            case 'attachment':
                $res[] = "附件地址格式“附件文件夹/附件名称.zip，文件名称不能包含中文字符”";
                break;
            case 'attachment_multiple':
                $res[] = "附件地址格式“附件文件夹/附件名称.zip”，多个附件用“,”英文逗号分隔，文件名称不能包含中文字符";
                break;
            case 'radio':
            case 'radio_inline':
                $res[] = $this->itemDoc($fieldInfo['options']);
                break;
            case 'checkbox':
            case 'checkbox_inline':
                $res[] = $this->itemDoc($fieldInfo['options']).'，且多个值用“,”英文逗号分隔';
                break;
            case 'select':
                $res[] = $this->itemDoc($fieldInfo['options']);
                break;
            case 'select_multiple':
                $res[] = $this->itemDoc($fieldInfo['options']).'，且多个值用“,”英文逗号分隔';
                break;
            case 'tag':
                $res[] = '多个用“,”英文逗号分隔';
                break;
            case 'passport':
                if($len = $this->lengthDoc($fieldInfo['field_length'],$verification)) $res[] = $len;
                break;
            case 'date':
                $res[] = '日期格式必须为“yyyy-mm-dd”，例如“'.date('Y-m-d').'”';
                break;
            case 'datetime':
                $res[] = '时间格式必须为“yyyy-mm-dd h:i”，例如“'.date('Y-m-d H:i').'”';
                break;
            case 'number':
                //if($len = $this->lengthDoc($fieldInfo['field_length'],$verification,'大小')) $res[] = $len;
                $res[] = '小数点精确到'.$fieldInfo['field_decimal_place'].'位';
                if(ArrayHelper::getValue($verification,'unsigned',false)) $res[] = '值必须为大于等于0的正数';
                $compare = $this->compareDoc(ArrayHelper::getValue($verification,'compare',false));
                if($compare) $res[] = $compare;
                break;
	        case 'relation_data':
	        	$_str = '';
	        	if($fieldInfo['setting']['isNodeModel']){
			        $_str = [];
	        		foreach ($this->categoryList as $item){
				        if($item['model']['name'] == $fieldInfo['setting']['modelName']){
					        $_str[] = $item['title'];
				        }
			        }
			        $_str = empty($_str)?'':'只能填写“'.implode('、',$_str).'”栏目下的内容数据ID';
		        }else{
			        $_str .= '只能填写“'.($fieldInfo['setting']['modelName'] == 'user'?'用户':'栏目').'”ID';
		        }

		        if($fieldInfo['setting']['relationType']){
			        $_str .= '，多个用“,”英文逗号分隔';
		        }

				$res[] = $_str;
	        	break;
        }

        return $res;
    }

    private function lengthDoc($fieldLength,$verification,$unit = '长度|个字符'){
        $unit = explode('|',$unit);
        if(!array_key_exists(1,$unit)) $unit[1] = '';
        if(!empty($verification)){
            $temp = explode(',',ArrayHelper::getValue($verification,'length','255'));
            if(array_key_exists(1,$temp)){
                return $unit[0].'必须在'.$temp[0].' ~ '.$temp[1].$unit[1].'之间';
            }else{
                return $unit[0].'不超过'.$temp[0].$unit[1];
            }
        }else{
            return $unit[0].'不超过'.($fieldLength?:255).$unit[1];
        }
    }

    private function itemDoc($options){
        return "值必须在“".implode('、',ArrayHelper::getColumn($options['list'],'value'))."”之间";
    }

    private function compareDoc($compare){
        if(!empty($compare) && $compare['enable']){
            $str = [];
            foreach ($compare['rules'] as $item){
                $str[] = '值必须'.$item['operator'].$item['compareValue'];
            }
            return empty($str)?null:implode('，',$str);
        }
        return null;
    }
}