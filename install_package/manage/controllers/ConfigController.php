<?php

namespace manage\controllers;

use common\components\CurdInterface;
use common\components\manage\ManageController;
use common\entity\models\SystemConfigModel;
use common\helpers\UrlHelper;
use manage\models\DelCacheHelper;
use Yii;
use yii\base\Model;
use yii\web\NotFoundHttpException;

/**
 * ConfigController implements the CRUD actions for SystemConfigModel.
 */
class ConfigController extends ManageController implements CurdInterface
{
    /**
     * @var array 配置类型
     */
    private $configTitle = array(
        'site'=>'系统设置',
        'email'=>'邮件设置',
        'sms'=>'短信设置',
        'third'=>'第三方账号的设置',
        'upload'=>'上传设置',
        'custom'=>'全局碎片',
        'member'=>'用户配置',
    );

	/**
	 * 配置更新
	 * @return string
	 * @throws \yii\base\Exception
	 */
    public function actionIndex()
    {
        $assign['scope'] = Yii::$app->request->get('scope','site');
        $assign['config'] = SystemConfigModel::find()->where(['scope'=>$assign['scope']])->indexBy('id')->all();

        if (Yii::$app->request->isPost) {
            if(Model::loadMultiple($assign['config'], Yii::$app->request->post()) && Model::validateMultiple($assign['config'])){
                foreach ($assign['config'] as $item) {
                    $item->save(false);
                }
                $this->deleteCache();
                $this->success(['操作成功','jumpLink'=>'javascript:;']);
            }else{
                $this->error(['操作失败']);
            }
        }

        if($assign['scope'] === 'upload') $assign['maxFileSize'] = ini_get('upload_max_filesize');
        $assign['title'] = $this->configTitle[$assign['scope']];

        return $this->render($this->action->id, $assign);
    }

    /**
     *  自定义配置管理
     */
    public function actionCustom(){
        return $this->render('custom', [
            'dataList' => SystemConfigModel::find()->where(['scope'=>'custom'])->all(),
        ]);
    }

	/**
	 * 添加自定义配置
	 * @return mixed|string
	 * @throws \yii\base\Exception
	 */
    public function actionCreate(){
        $model = $this->findModel();
        if(Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post())) {
                $model->scope = 'custom';
                if($model->save()){
                    $this->deleteCache();
                    $this->success([Yii::t('common','Operation successful'),'updateUrl'=>UrlHelper::toRoute(['update','id'=>$model->primaryKey])]);
                }
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrors()]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

	/**
	 * 修改自定义配置
	 *
	 * @param int $id
	 *
	 * @return mixed|void
	 * @throws \yii\base\Exception
	 */
    public function actionUpdate($id){
        $model = $this->findModel($id);

        if(Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post())) {
                $model->scope = 'custom';
                if($model->save()){
                    $this->deleteCache();
                    $this->success([Yii::t('common','Operation successful')]);
                }
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrors()]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

	/**
	 * 删除自定义配置
	 *
	 * @param int|string $id
	 *
	 * @return mixed|void
	 * @throws NotFoundHttpException
	 * @throws \Throwable
	 * @throws \yii\base\Exception
	 * @throws \yii\db\StaleObjectException
	 */
    public function actionDelete($id){
        $model = $this->findModel($id);

        if($model->delete()){
            $this->deleteCache();
            $this->success([Yii::t('common','Operation successful')]);
        }
        $this->error([Yii::t('common','Operation failed')]);
    }

    /**
     * 查找模型
     * @param null $id
     * @return SystemConfigModel|null
     * @throws NotFoundHttpException
     */
    protected function findModel($id = null)
    {
        $model = empty($id)? new SystemConfigModel():SystemConfigModel::findOne($id);
        if($model !== null){
            return $model;
        }else{
            throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }
    }

    /**
     * @param int|string $id
     * @return mixed|void
     */
    public function actionStatus($id){}

    /**
     * @param int|string $id
     * @return mixed|void
     */
    public function actionSort($id){}

	/**
	 * 删除缓存
	 * @throws \yii\base\Exception
	 */
    public function deleteCache(){
        DelCacheHelper::deleteCache('config');
    }
}
