<?php
// +----------------------------------------------------------------------
// | forgetwork
// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | Created on 2016/5/25.
// +----------------------------------------------------------------------

/**
 * 评论
 */

namespace home\controllers;

use common\entity\models\PrototypeModelModel;
use common\entity\models\SmsVerificationCodeForm;
use common\entity\models\UserRelationModel;
use common\helpers\StringHelper;
use Yii;
use yii\web\NotFoundHttpException;

class FormController extends \common\components\home\HomeController
{

	/**
	 * node表单模型表单提交
	 * @throws NotFoundHttpException
	 * @throws \yii\db\Exception
	 */
    public function actionIndex(){
        $modelInfo = PrototypeModelModel::findModel(Yii::$app->getRequest()->get('model_id',0));

        if($modelInfo && $modelInfo->type && Yii::$app->getRequest()->getIsPost()){
            $model = $this->findModel($modelInfo->name);
            if(array_key_exists('form',$model->scenarios())) $model->setScenario('form');
            if($modelInfo->is_login && Yii::$app->getUser()->getIsGuest()){
                $model->addError('user_id',Yii::t('yii','Login Required'));
            }else{
                if(Yii::$app->getRequest()->post(StringHelper::basename($model::className())) === null){
                    $model->addError('id',Yii::t('yii','No results found.'));
                }else{
                    if($model->load(Yii::$app->getRequest()->post())){
	                    if($modelInfo->is_login && array_key_exists('user_id',$model->attributes)) $model->user_id = Yii::$app->getUser()->getId();
                        $model->site_id = $this->siteInfo->id;
                        $model->model_id = $modelInfo->id;
                        if($model->save()){

	                        // 检测关联
	                        if($modelInfo->is_login){
		                        $sql = '';
		                        $db = Yii::$app->getDb();
		                        foreach ($this->config->member->relationContent as $item){
			                        if($item->model_id == $modelInfo->id){
				                        $sql .= $db->createCommand()->insert(UserRelationModel::tableName(),[
						                        'user_id'=>Yii::$app->getUser()->getId(),
						                        'user_model_id'=>$modelInfo->id,
						                        'user_data_id'=>$model->id,
						                        'relation_type'=>$item->slug,
						                        'relation_create_time'=>$model->create_time,
					                        ])->rawSql.';';
			                        }
		                        }
		                        if(!empty($sql)) $db->createCommand($sql)->execute();
	                        }

                            $this->success([Yii::t('common','Operation successful'),'jumpLink'=>Yii::$app->getRequest()->post('jumpLink')?:"javascript:void(history.go(-1));"]);
                        }
                    }
                }
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrorString()]);
        }
        throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
    }

	/**
	 * 发送短信
	 *
	 * @param $mode string 可选的值 email、cellphone
	 *
	 * @throws \Throwable
	 * @throws \yii\base\Exception
	 */
    public function actionSendSms($mode){
        $request = Yii::$app->getRequest();

        $model = new SmsVerificationCodeForm();
        $model->setScenario($mode);

        if($request->getIsPost()){
            if ($model->load($request->post()) && $model->generateCode()) {
                $this->success([Yii::t('common','Operation successful')]);
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrorString()]);
        }
    }

    /**
     * 上传文件
     * 此示例适用于百度上传插件： http://fex.baidu.com/webuploader/getting-started.html
     * 注意上传的字段名为“UploadForm[file]”，且传入crf验证，例如：
     * <script>
     * var uploader = WebUploader.create({
     *   fileVal:"UploadForm[file]",
     *   formData:{
     *      "<?=Yii::$app->getRequest()->csrfParam?>":"<?=Yii::$app->getRequest()->getCsrfToken()?>",
     *      "folderName":"user" // 上传到的文件夹名，默认user
     *   }
     *   // 其他配置
     * });
     * </script>
     * @param string $type 上传类型 允许的值“image”、“attachment”、“media”
     * @param bool $isMultiple 是否多文件上传
     * @param string $mode file、base64、remote 允许的值
     * @return array|mixed
     */
    public function actionUpload($type = 'image',$mode= 'file',$isMultiple = false){
        // 在后台上传配置中“是否开放前台上传”进行设置
        if(!intval($this->config->upload->enableFrontUpload) && Yii::$app->getUser()->getIsGuest()){
            return json_encode([
                'status'=>0,
                'message'=>'您无权进行此操作。',
                'state'=>'您无权进行此操作。'
            ]);
        }

        if(Yii::$app->getRequest()->getIsPost()){
            $result = $this->upload($type,$isMultiple,Yii::$app->getRequest()->post('folderName','user'),$mode);
            if($result['status']){
                return json_encode([
                    'status'=>1,
                    'state'=>'SUCCESS',
                    'files'=>$result['message']
                ]);
            }else{
                return json_encode([
                    'status'=>0,
                    'message'=>$result['message'],
                    'state'=>$result['message']
                ]);
            }
        }
    }
}