<?php
/**
 * @copyright
 * @link
 * @create Created on 2018/11/12
 */

namespace manage\modules\doc\controllers;

use common\components\BaseController;
use common\components\manage\ManageController;
use yii\web\NotFoundHttpException;


/**
 * BaseDocController
 *
 * @author
 * @since 1.0
 */
class BaseDocController extends ManageController {
	/**
	 * @throws NotFoundHttpException
	 */
	public function init() {
		parent::init();

		if(!$this->isSuperAdmin){
			throw new NotFoundHttpException();
		}
	}

	/**
	 * 获取api响应返回的数据
	 * @param $model
	 * @param bool $isList
	 *
	 * @return string
	 */
	public function apiResponseGet($model,$isList = true){
		$fields = $model->attributeLabels();
		if($isList){
			$fields = [
				"items"=>[$fields],
				"_links"=>[
					"self"=>["href"=>"当前页链接"],
					"first"=>["href"=>"第一页链接"],
					"prev"=>["href"=>"上一页链接"],
					"next"=>["href"=>"下一页链接"],
					"last"=>["href"=>"最后一页链接"],
				],
				"_meta"=>[
					"totalCount"=>"数据总数",
			        "pageCount"=> "页面总数",
			        "currentPage"=>"当前页码",
			        "perPage"=>"分页大小"
				]
			];
		}
		return json_encode($fields, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
	}
}