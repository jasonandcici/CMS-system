<?php
/**
 * @copyright
 * @link 
 * @create Created on 2016/12/13
 */

namespace manage\modules\fragment\controllers;
use common\components\manage\ManageController;
use common\entity\models\FragmentCategoryModel;
use common\entity\models\FragmentModel;
use common\entity\models\SystemLogModel;
use common\helpers\UrlHelper;
use manage\models\DelCacheHelper;
use Yii;
use yii\base\Model;
use yii\web\NotFoundHttpException;


/**
 * 碎片管理
 *
 * @author 
 * @since 1.0
 */
class FragmentController extends ManageController
{
	/**
	 *  碎片管理
	 *
	 * @param $category_id
	 *
	 * @return string
	 * @throws NotFoundHttpException
	 */
    public function actionIndex($category_id){
        $model = $this->findModel();
        return $this->render('index', [
            'dataList' => $model::find()->where(['site_id'=>$this->siteInfo->id,'category_id'=>$category_id])->orderBy(['sort'=>SORT_ASC])->all(),
            'categoryInfo'=>$this->findCategoryInfo($category_id)
        ]);
    }

	/**
	 * 碎片设置
	 *
	 * @param $category_id
	 *
	 * @return string
	 * @throws NotFoundHttpException
	 * @throws \yii\base\Exception
	 */
    public function actionEdit($category_id){
        $model = $this->findModel();
        $dataList = $model::find()->where(['site_id'=>$this->siteInfo->id,'category_id'=>$category_id])->orderBy(['sort'=>SORT_ASC])->all();

        if (Yii::$app->request->isPost) {
            if(Model::loadMultiple($dataList, Yii::$app->request->post()) && Model::validateMultiple($dataList)){
                foreach ($dataList as $item) {
                    $item->save(false);
                }

                $this->deleteCache();

                SystemLogModel::create('update','更新了碎片内容');

                $this->success(['操作成功','jumpLink'=>'javascript:;']);
            }else{
                $this->error(['操作失败','message'=>$model->getErrorString()]);
            }
        }

        return $this->render($this->action->id, [
            'dataList'=>$dataList,
            'categoryInfo'=>$this->findCategoryInfo($category_id)
        ]);
    }

	/**
	 * 添加碎片
	 *
	 * @param $category_id
	 *
	 * @return mixed
	 * @throws NotFoundHttpException
	 * @throws \yii\base\Exception
	 */
    public function actionCreate($category_id){
        $model = $this->findModel();
        $model->category_id = $category_id;
        if(Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post())) {
                $model->site_id = $this->siteInfo->id;
                if($model->setting && strpos($model->setting,'{') === 0){
                    $model->setting = serialize(json_decode(trim($model->setting),true));
                }else{
                    $model->setting = '';
                }
                if($model->save()){
                    $model->sort = $model->primaryKey;
                    $model->save();

                    $this->deleteCache();

                    SystemLogModel::create('create','新增“'.$model->title.'”碎片');

                    $this->success([Yii::t('common','Operation successful'),'updateUrl'=>UrlHelper::toRoute(['update','id'=>$model->primaryKey])]);
                }
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrorString()]);
        }

        return $this->render('create', [
            'model' => $model,
            'categoryInfo'=>$this->findCategoryInfo($category_id)
        ]);
    }

	/**
	 * 修改碎片
	 *
	 * @param int $id
	 *
	 * @return mixed
	 * @throws NotFoundHttpException
	 * @throws \yii\base\Exception
	 */
    public function actionUpdate($id){
        $model = $this->findModel($id);
        if(Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post())) {
                $model->site_id = $this->siteInfo->id;
                if($model->setting && strpos($model->setting,'{') === 0){
                    $model->setting = serialize(json_decode(trim($model->setting),true));
                }else{
                    $model->setting = null;
                }
                if($model->save()){
                    $this->deleteCache();

                    SystemLogModel::create('update','修改“'.$model->title.'”碎片内容');

                    $this->success([Yii::t('common','Operation successful')]);
                }
            }
            $this->error([Yii::t('common','Operation failed'),'message'=>$model->getErrorString()]);
        }

        if($model->setting){
            $model->setting = json_encode(unserialize($model->setting),JSON_UNESCAPED_UNICODE);
        }

        return $this->render('update', [
            'model' => $model,
            'categoryInfo'=>$model->category
        ]);
    }

	/**
	 * 删除碎片
	 *
	 * @param int|string $id
	 *
	 * @return mixed|void
	 * @throws \yii\base\Exception
	 * @throws \Throwable
	 */
    public function actionDelete($id){
        $model = $this->findModel($id);

        if($model->delete()){
            $this->deleteCache();

            SystemLogModel::create('delete','删除“'.$model->title.'”碎片');

            $this->success([Yii::t('common','Operation successful')]);
        }
        $this->error([Yii::t('common','Operation failed')]);
    }


	/**
	 * 数据排序
	 * @return mixed|void
	 * @throws \yii\base\Exception
	 */
    public function actionSort(){
        $model = $this->findModel();

        // 批量排序
        if(Yii::$app->getRequest()->getIsPost()){
            $postData = json_decode(Yii::$app->getRequest()->post('data'));
            $db = Yii::$app->db;
            $sql = '';
            foreach ($postData as $item){
                $sql .= $db->createCommand()->update($model->tableName(),['sort'=>$item->sort],['id'=>$item->id])->rawSql.';';
            }
            if($sql){
                $db->createCommand($sql)->execute();
                $this->deleteCache();

                SystemLogModel::create('update','对碎片进行了排序');

                $this->success([Yii::t('common','Operation successful')]);
            }
        }
        $this->error([Yii::t('common','Operation failed')]);
    }


    /**
     * 查找模型
     * @param null $id
     * @return FragmentModel|null
     * @throws NotFoundHttpException
     */
    protected function findModel($id = null)
    {
        $model = empty($id)? new FragmentModel():FragmentModel::findOne(['id'=>$id,'site_id'=>$this->siteInfo->id]);
        if($model !== null){
            return $model;
        }else{
            throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }
    }

    /**
     * 获取幻灯片栏目信息
     * @param $id
     * @return FragmentCategoryModel
     */
    protected function findCategoryInfo($id){
        return FragmentCategoryModel::findOne($id);
    }

	/**
	 * 删除缓存
	 * @throws \yii\base\Exception
	 */
    public function deleteCache(){
	    DelCacheHelper::deleteCache('fragment');
    }
}