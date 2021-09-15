<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/7/20
 */

namespace common\components;
use yii\rest\IndexAction;


/**
 * IndexActionBase
 *
 * @author 
 * @since 1.0
 */
class IndexActionBase extends IndexAction {

	/**
	 * @var string 重置modelClass回调
	 */
	public $setModelClassOnBeforeRun;

	/**
	 * @return \yii\data\ActiveDataProvider
	 */
	public function run() {
		// 新增回调
		if ($this->setModelClassOnBeforeRun !== null) {
			$callbackData = call_user_func($this->setModelClassOnBeforeRun,$this);
			if($callbackData !== null) $this->modelClass = $callbackData;
			unset($callbackData);
		}

		return parent::run();
	}

}