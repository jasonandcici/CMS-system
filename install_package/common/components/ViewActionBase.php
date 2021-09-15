<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/7/20
 */

namespace common\components;

use yii\rest\ViewAction;


/**
 * ViewActionBase
 *
 * @author 
 * @since 1.0
 */
class ViewActionBase extends ViewAction{
	/**
	 * @var string 显示模型回调方法
	 */
	public $prepareModel;

	/**
	 * @var string 获取model
	 */
	public $getModel;

	/**
	 * Displays a model.
	 *
	 * @param string $id the primary key of the model.
	 *
	 * @return \yii\db\ActiveRecordInterface the model being displayed
	 * @throws \yii\web\NotFoundHttpException
	 */
	public function run($id)
	{
		// 新增回调方法
		if ($this->getModel !== null) {
			$model = call_user_func($this->getModel);
		}else{
			$model = $this->findModel($id);
		}


		if ($this->checkAccess) {
			call_user_func($this->checkAccess, $this->id, $model);
		}

		// 新增回调方法
		if ($this->prepareModel !== null) {
			$callbackData =  call_user_func($this->prepareModel, $model);
			if($callbackData !== null) $model = $callbackData;
			unset($callbackData);
		}

		return $model;
	}
}