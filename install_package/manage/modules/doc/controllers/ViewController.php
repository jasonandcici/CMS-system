<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/11/12
 */

namespace manage\modules\doc\controllers;
use common\entity\models\CommentModel;
use common\entity\models\FragmentCategoryModel;
use common\entity\models\FragmentListModel;
use common\entity\models\FragmentModel;
use common\entity\models\PrototypeCategoryModel;
use common\entity\models\PrototypeFieldModel;
use common\entity\models\PrototypeModelModel;
use common\entity\models\SiteModel;
use common\entity\models\SmsVerificationCodeForm;
use common\entity\models\SystemConfigModel;
use common\helpers\ArrayHelper;
use Yii;
use yii\helpers\Inflector;


/**
 * ViewController
 *
 * @author 
 * @since 1.0
 */
class ViewController extends BaseDocController {

	public $layout = false;

	public $enableCsrfValidation = false;

	/**
	 * node数据
	 *
	 * @param $action
	 *
	 * @return string
	 */
	public function actionNode($action){
		$expandList = [
			'categoryInfo'=>'栏目信息',
			'modelInfo'=>'模型信息',
			'siteInfo'=>'站点信息',
			'tagRelations'=>'相关标签',
			'commentInfo'=>'评论信息',
			'commentCount'=>'评论统计'
		];

		// 获取字段
		if($action == 'getFieldsList' || $action == 'getFieldsView'){
			$postData = Yii::$app->getRequest()->post($this->action->id,[]);
			$cid = ArrayHelper::getValue($postData,'cid');
			if($cid){
				$categoryInfo = PrototypeCategoryModel::find()->where(['id'=>$cid])->with(['model'])->one();
				$modelName = ucfirst($categoryInfo->model->name);
				$mid = $categoryInfo->model_id;
				unset($categoryInfo);
			}else{
				$mid = ArrayHelper::getValue($postData,'mid');
				if($mid){
					$modelInfo = PrototypeModelModel::findModel($mid);
					$modelName = ucfirst($modelInfo->name);
					unset($modelInfo);
				}
			}

			if(empty($modelName)){
				$this->error(['操作失败','message'=>'mid和cid必须选择一个。']);
			}else{
				$relationFields = PrototypeFieldModel::find()->where(['type'=>'relation_data','model_id'=>$mid])->asArray()->all();
				foreach ($relationFields as $fieldInfo){
					if(empty($fieldInfo['setting'])) continue;
					$fieldInfo['setting'] = json_decode($fieldInfo['setting'],true);

					if($fieldInfo['setting']['relationType']){
						$relationFieldName = Inflector::pluralize($fieldInfo['setting']['modelName']).'List';
					}else{
						$relationFieldName = $fieldInfo['setting']['modelName'].'Info';
					}

					$expandList[$relationFieldName] = $fieldInfo['title'];
				}

				$modelName = 'common\entity\nodes\\'.$modelName.'Model';
				$this->success(['操作成功','message'=>$this->apiResponseGet(new $modelName(),$action == 'getFieldsList'?true:false),'expand'=>['expand'=>$expandList]]);
			}
		}

		$siteList = SiteModel::findSite();
		$modelList = [];
		foreach (PrototypeModelModel::findModel() as $item){
			if($item['type']) continue;
			$modelList[$item['id']] = $item['title'];
		}
		$siteList = ArrayHelper::map($siteList,'id','title');

		$relationList = [];
		foreach ($this->config['member']['relationContent'] as $item){
			if(array_key_exists($item['model_id'],$modelList)){
				$relationList[$item['slug']] = $item['title'];
			}
		}

		$categoryList =  ArrayHelper::linear(PrototypeCategoryModel::findCategory(),' ├ ');
		$categoryDisable = [];
		foreach ($categoryList as $i=>$item){
			$item['str_title'] = $item['str'].$item['title'];
			$categoryList[$i] = $item;
			if($item['type']) $categoryDisable[$item['id']] = ['disabled' => true];
		}
		$mapCategoryList = [];
		foreach (ArrayHelper::map($categoryList,'id','str_title','site_id') as $s=>$item){
			$mapCategoryList[$siteList[$s]] = $item;
		}
		unset($categoryList);

		$getParameters = [
			["name"=>"sid",'alias'=>'SiteId','isRequired'=>true,"title"=>'站点Id',"type"=>'int',"remark"=>'必填','value'=>$siteList],
			["name"=>"cid",'alias'=>'CategoryId','isRequired'=>true,"title"=>'栏目Id',"type"=>'int',"remark"=>'必填（mid参数为空时）','value'=>$mapCategoryList,'disabled'=>$categoryDisable],
			["name"=>"mid",'alias'=>'ModelId','isRequired'=>false,"title"=>'模型Id',"type"=>'int',"remark"=>'cid参数为空时必填，表示查询当前模型下所有数据','value'=>$modelList],
			["name"=>"user-relations",'alias'=>'UserRelations','isRequired'=>false,"title"=>'用户关联关系',"type"=>'string',"remark"=>'多个用英文逗号分隔，只有传递access-token参数时有效','value'=>$relationList,'multipleSelect'=>true],
			["name"=>"expand",'alias'=>'Expand','isRequired'=>false,"title"=>'数据关联',"type"=>'string',"remark"=>'多个用英文逗号分隔，获取更多关联请点击“获取字段”按钮','value'=>$expandList,'multipleSelect'=>true],
			["name"=>"access-token",'alias'=>null,'isRequired'=>false,"title"=>'授权认证码',"type"=>'string',"remark"=>'需要登录访问的页面需传递此参数才能正常访问'],
		];

		if($action == 'view'){
			array_unshift($getParameters,["name"=>"NodeId",'alias'=>'NodeId','isRequired'=>true,"title"=>'数据Id',"type"=>'int',"remark"=>'必填']);
		}

		return $this->render('index',[
			"parameters"=>[
				"get"=>$getParameters,
				"getFields"=>'getFields'.ucfirst($action),
			]
		]);
	}

	/**
	 * 站点
	 * @param $action
	 *
	 * @return string
	 */
	public function actionSite($action){
		$parameters = [];
		if($action == 'view'){
			$siteList = SiteModel::findSite();
			$parameters['get'] = [
				["name"=>"SiteId",'alias'=>'SiteId','isRequired'=>true,"title"=>'站点Id',"type"=>'int',"remark"=>'必填',"value"=>ArrayHelper::map($siteList,'id','title')],
			];
		}

		return $this->render('index',[
			"parameters"=>$parameters,
			"fields"=>$this->apiResponseGet(new SiteModel(),$action == 'list'?true:false),
		]);
	}

	/**
	 * 表单
	 *
	 * @param $action
	 *
	 * @return string
	 */
	public function actionForm($action) {
		$parameters = [];
		// 获取post参数
		if ($action == 'postFieldsCreate'){
			$postData = Yii::$app->getRequest()->post($this->action->id,[]);
			$mid = ArrayHelper::getValue($postData,'mid');
			if($mid){
				$modelInfo = PrototypeModelModel::findModel($mid);
				foreach ($modelInfo->fields as $item){
					if($item->type == 'captcha') continue;

					$options = [];
					$remark = '';
					$type = 'string';
					switch ($item->field_type){
						case 'int':
							$type = 'int';
							break;
						case 'decimal':
							$type = 'int';
							$options['decimalPlace'] = $item->field_decimal_place;
							$remark = '精确到小数点'.$item->field_decimal_place.'位';
							break;
						case 'date':
							$remark = '格式如：'.date('Y-m-d');
							break;
						case 'datetime':
							$remark = '格式如：'.date('Y-m-d H:i:s');
							break;
					}

					if(in_array($item->type,['radio','radio_inline','checkbox','checkbox_inline','select','select_multiple'])){
						$opts = PrototypeModelModel::optionResolve($item->options);
						if($opts){
							$vv = [];
							foreach ($opts['list'] as $l){
								$vv[] = $l['value'];
							}
							$remark = '允许的值“'.implode(',',$vv).'”';
						}
						if(in_array($item->type,['checkbox','checkbox_inline','select_multiple'])){
							$remark .= '，多个用英文“,”分隔';
						}
					}elseif ($item->type == 'tag'){
						$remark = '多个用英文“,”分隔';
					}elseif (in_array($item->type,['image','attachment',])){
						$remark = '上传接口返回的文件路径';
					}elseif (in_array($item->type,['image_multiple','attachment_multiple'])){
						$remark = '上传接口返回的文件路径，多个用英文“,”分隔';
					}

					if($item->is_required){
						if(empty($remark)){
							$remark = '必填';
						}else{
							$remark = '必填，'.$remark;
						}
					}

					$parameters[] = ArrayHelper::merge([
						"name"=>$item->name,
						'isRequired'=>$item->is_required?true:false,
						"title"=>$item->title,
						"type"=>$type,
						"remark"=>$remark,
					],$options);
				}

				$this->success(['操作成功','message'=>$this->renderPartial('index',['parameters'=>['post'=>$parameters]])]);
			}else{
				$this->error(['操作失败','message'=>'mid必须选择。']);
			}
		}elseif ($action == 'send-sms'){
			$parameters = [
				"get"=>[
					["name"=>"mode",'alias'=>'Mode','isRequired'=>true,"title"=>'Sms类型',"type"=>'string',"remark"=>'必填','value'=>["cellphone"=>"手机","email"=>"邮箱"]],
				],
				"post"=>[
					["name"=>"type",'isRequired'=>true,"title"=>'Sms类型',"type"=>'string',"remark"=>"必填，允许的值“register 注册验证,reset 重置密码或找回密码验证,login 登录”"],
					["name"=>"cellphone_code",'isRequired'=>false,"title"=>'国际区号',"type"=>'string',"remark"=>'默认为“0086”中国'],
					["name"=>"account",'isRequired'=>true,"title"=>'手机或邮箱',"type"=>'string',"remark"=>'必填，由get参数mode决定'],
				]
			];
		}elseif ($action == 'upload'){
			$parameters = [
				"get"=>[
					["name"=>"type",'alias'=>'Type','isRequired'=>false,"title"=>'上传类型',"type"=>'string',"remark"=>"默认image",'value'=>["image"=>"图片","attachment"=>"附件","media"=>"媒体"]],
					["name"=>"mode",'alias'=>'Mode','isRequired'=>false,"title"=>'上传模式',"type"=>'string',"remark"=>'默认为file','value'=>["file"=>"文件流","base64"=>"Base64","remote"=>"网络文件"]],
					["name"=>"access-token",'alias'=>null,'isRequired'=>false,"title"=>'授权认证码',"type"=>'string',"remark"=>'如果“管理后台-上传设置”中未开放前台上传功能，需传递此参数才能正常使用'],
				],
				"post"=>[
					["name"=>"UploadForm[file]",'isRequired'=>true,"title"=>'文件',"type"=>'string',"remark"=>"必填"],
					["name"=>"folderName",'isRequired'=>false,"title"=>'文件夹',"type"=>'string',"remark"=>'上传到服务器所在的文件夹'],
				]
			];
		}else{
			$siteList = SiteModel::findSite();
			$modelList = [];
			foreach (PrototypeModelModel::findModel() as $item){
				if(!$item['type']) continue;
				$modelList[$item['id']] = $item['title'];
			}
			$siteList = ArrayHelper::map($siteList,'id','title');

			$parameters = [
				"get"=>[
					["name"=>"sid",'alias'=>'SiteId','isRequired'=>true,"title"=>'站点Id',"type"=>'int',"remark"=>'必填','value'=>$siteList],
					["name"=>"mid",'alias'=>'ModelId','isRequired'=>true,"title"=>'模型Id',"type"=>'int',"remark"=>'必填','value'=>$modelList],
				],
				"postFields"=>'postFields'.ucfirst($action)
			];
		}

		return $this->render('index',[
			"parameters"=>$parameters
		]);
	}

	/**
	 * 碎片
	 * @param $action
	 *
	 * @return string
	 */
	public function actionFragment($action){
		if($action == 'getFieldsList' || $action == 'getFieldsView'){
			$postData = Yii::$app->getRequest()->post($this->action->id,[]);
			$slug = ArrayHelper::getValue($postData,'slug');
			$sid = ArrayHelper::getValue($postData,'sid');
			if(!$slug || !$sid){
				$this->error(['操作失败','message'=>'slug和sid必须选择。']);
			}

			$categoryInfo = FragmentCategoryModel::find()->where(['site_id'=>$sid,'slug'=>$slug])->one();
			if($categoryInfo->type){
				$list = FragmentModel::find()->where(['category_id'=>$categoryInfo->id])
				                     ->orderBy(['sort'=>SORT_ASC])
				                     ->select(['id','name','title'])
				                     ->asArray()
				                     ->all();
				$dataList = [];
				foreach ($list as $item){
					$dataList[$item['name']] = $item['title'];
				}
				$msg = json_encode($dataList, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
			}else{
				$msg = $this->apiResponseGet(new FragmentListModel());
			}

			$this->success(['操作成功','message'=>$msg]);
		}


		$siteList = ArrayHelper::map(SiteModel::findSite(),'id','title');
		$categoryList = FragmentCategoryModel::find()->select(['id','title','slug','site_id'])->orderBy(['sort'=>SORT_DESC])->asArray()->all();

		$slugs = [];
		foreach (ArrayHelper::map($categoryList,'slug','title','site_id') as $k=>$v){
			$slugs[$siteList[$k]] = $v;
		}

		$getParameters = [
			["name"=>"sid",'alias'=>'SiteId','isRequired'=>true,"title"=>'站点Id',"type"=>'int',"remark"=>'必填','value'=>$siteList],
			["name"=>"slug",'alias'=>'Slug','isRequired'=>true,"title"=>'碎片标识',"type"=>'string',"remark"=>'必填','value'=>$slugs],
		];

		if($action == 'view'){
			array_unshift($getParameters,["name"=>"FragmentId",'alias'=>'FragmentId','isRequired'=>true,"title"=>'数据Id',"type"=>'int',"remark"=>'必填',"value"=>'int']);
		}

		return $this->render('index',[
			"parameters"=>[
				"get"=>$getParameters,
				"getFields"=>'getFields'.ucfirst($action),
			]
		]);
	}

	/**
	 * 评论
	 * @param $action
	 *
	 * @return string
	 */
	public function actionComment($action){
		$fields = $getParameters = $postParameters = [];

		if($action == 'relation'){

			$getParameters = [
				["name"=>"slug",'alias'=>'Slug','isRequired'=>true,"title"=>'关联标识',"type"=>'string',"remark"=>'必填',"value"=>["like"=>'Like',"bad"=>"Bad"]],
				["name"=>"access-token",'alias'=>null,'isRequired'=>true,"title"=>'授权认证码',"type"=>'string',"remark"=>'必填']
			];
			$postParameters = [
				["name"=>"id",'isRequired'=>true,"title"=>'评论数据Id',"type"=>'int',"remark"=>'必填','value'=>'int'],
				["name"=>"action",'isRequired'=>true,"title"=>'操作',"type"=>'string',"remark"=>'必填，允许的值“relation,unRelation”','value'=>'string'],
			];

		}elseif ($action == 'create'){
			$getParameters[] = ["name"=>"access-token",'alias'=>null,'isRequired'=>true,"title"=>'授权认证码',"type"=>'string',"remark"=>'必填'];
			$postParameters = [
				["name"=>"category_id",'isRequired'=>true,"title"=>'栏目Id',"type"=>'int',"remark"=>'必填','value'=>'int'],
				["name"=>"data_id",'isRequired'=>true,"title"=>'被评论数据Id',"type"=>'string',"remark"=>'必填','value'=>'int'],
				["name"=>"content",'isRequired'=>true,"title"=>'评论内容',"type"=>'string',"remark"=>'必填','value'=>'string'],
				["name"=>"atlas",'isRequired'=>false,"title"=>'评论图片',"type"=>'string',"remark"=>'上传接口返回的文件路径，多个用英文“,”分隔','value'=>'string'],
			];
		}

		if($action == 'view' || $action == 'list'){
			$getParameters = [
				["name"=>"expand",'alias'=>'Expand','isRequired'=>false,"title"=>'扩展字段',"type"=>'string',"remark"=>'多个用英文逗号分隔','value'=>['userProfile'=>'用户资料','category'=>'栏目详情','dataDetail'=>'评论数据详情'],'multipleSelect'=>true],
				["name"=>"access-token",'alias'=>null,'isRequired'=>false,"title"=>'授权认证码',"type"=>'string',"remark"=>'判断用户是否点赞等操作需传递此参数。'],
			];

			$fields = $this->apiResponseGet(new CommentModel(),$action=='list'?true:false);
		}

		if($action == 'delete'){
			$getParameters[] = ["name"=>"access-token",'alias'=>null,'isRequired'=>true,"title"=>'授权认证码',"type"=>'string',"remark"=>'必填'];
		}

		if($action == 'view' || $action == 'delete'){
			array_unshift($getParameters,["name"=>"CommentId",'alias'=>'CommentId','isRequired'=>true,"title"=>'评论Id',"type"=>'int',"remark"=>'必填','value'=>'int']);
		}

		return $this->render('index',[
			"parameters"=>[
				"get"=>$getParameters,
				"post"=>$postParameters
			],
			'fields'=>$fields
		]);
	}


	/**
	 * 栏目
	 * @param $action
	 *
	 * @return string
	 */
	public function actionCategory($action){

		return $this->render('index',[
			"parameters"=>[
				"get"=>[
					["name"=>"sid",'alias'=>'SiteId','isRequired'=>true,"title"=>'站点Id',"type"=>'int',"remark"=>'必填',"value"=>ArrayHelper::map(SiteModel::findSite(),'id','title')]
				],
			],
			"fields"=>$this->apiResponseGet(new PrototypeCategoryModel(),false),
		]);
	}

	/**
	 * 搜索
	 * @param $action
	 *
	 * @return string
	 */
	public function actionSearch($action){

		if($action == 'getFieldsList'){
			$postData = Yii::$app->getRequest()->post($this->action->id,[]);
			$mid = ArrayHelper::getValue($postData,'mid');
			if($mid){
				$modelInfo = PrototypeModelModel::findModel($mid);
				$modelName = ucfirst($modelInfo->name);
				unset($modelInfo);

				$modelName = 'common\entity\nodes\\'.$modelName.'Model';
				$this->success(['操作成功','message'=>$this->apiResponseGet(new $modelName())]);
			}else{
				$this->error(['操作失败','message'=>'mid选择一个。']);
			}
		}


		$modelList = [];
		foreach (PrototypeModelModel::findModel() as $item){
			if($item['type']) continue;
			$modelList[$item['id']] = $item['title'];
		}

		return $this->render('index',[
			"parameters"=>[
				"get"=>[
					["name"=>"mid",'alias'=>'ModelId','isRequired'=>true,"title"=>'模型Id',"type"=>'int',"remark"=>'必填',"value"=>$modelList]
				],
				"getFields"=>'getFields'.ucfirst($action),
			]
		]);
	}

	/**
	 * 标签搜索
	 * @param $action
	 *
	 * @return string
	 */
	public function actionTagSearch($action){

		if($action == 'getFieldsList'){
			$postData = Yii::$app->getRequest()->post(str_replace('-','_',$this->action->id),[]);
			$mid = ArrayHelper::getValue($postData,'mid');
			if($mid){
				$modelInfo = PrototypeModelModel::findModel($mid);
				$modelName = ucfirst($modelInfo->name);
				unset($modelInfo);

				$modelName = 'common\entity\nodes\\'.$modelName.'Model';
				$this->success(['操作成功','message'=>$this->apiResponseGet(new $modelName())]);
			}else{
				$this->error(['操作失败','message'=>'mid选择一个。']);
			}
		}


		$modelList = [];
		foreach (PrototypeModelModel::findModel() as $item){
			if($item['type']) continue;
			$modelList[$item['id']] = $item['title'];
		}

		return $this->render('index',[
			"parameters"=>[
				"get"=>[
					["name"=>"mid",'alias'=>'ModelId','isRequired'=>true,"title"=>'模型Id',"type"=>'int',"remark"=>'必填',"value"=>$modelList],
					["name"=>"tag",'alias'=>'Tag','isRequired'=>true,"title"=>'标签名',"type"=>'string',"remark"=>'必填',"value"=>'string'],
				],
				"getFields"=>'getFields'.ucfirst($action),
			]
		]);
	}

	/**
	 * 配置
	 * @param $action
	 *
	 * @return string
	 */
	public function actionConfig($action){
		$configTitle = array(
			'site'=>'系统设置',
			'email'=>'邮件设置',
			'sms'=>'短信设置',
			'third'=>'第三方账号的设置',
			'upload'=>'上传设置',
			'custom'=>'全局碎片',
			'member'=>'用户配置',
		);

		if($action == 'getFieldsList'){
			$postData = Yii::$app->getRequest()->post(str_replace('-','_',$this->action->id),[]);
			$slug = ArrayHelper::getValue($postData,'slug');
			if($slug == "wxConfig"){
				$this->success(['操作成功','message'=>json_encode([
					"debug"=>"调试",
				    "appId"=>"AppID",
				    "timestamp"=>"时间戳",
				    "nonceStr"=>"NonceStr",
				    "signature"=>"签名",
				    "jsApiList"=>"Api列表"
				],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)]);
			}else{
				$model = SystemConfigModel::find();
				if(!empty($slug)) $model->where(['scope'=>$slug]);
				$list = $model->select(['scope','title','name'])->asArray()->all();

				$fields = [];
				if(!empty($slug)){
					$fields = ArrayHelper::map($list,'name','title');
				}else{
					foreach ($list as $item){
						$fields[ArrayHelper::getValue($configTitle,$item['scope'],$item['scope'])][$item['name']] = $item['title'];
					}
				}

				$this->success(['操作成功','message'=>json_encode($fields,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)]);
			}
		}

		$slugs = [];
		foreach (ArrayHelper::getColumn(SystemConfigModel::find()->groupBy(['scope'])->asArray()->all(),'scope') as $item){
			$slugs[$item] = ArrayHelper::getValue($configTitle,$item,$item);
		}

		return $this->render('index',[
			"parameters"=>[
				"get"=>[
					["name"=>"slug",'alias'=>'Slug','isRequired'=>false,"title"=>'标识',"type"=>'string',"remark"=>'为空时获取全部',"value"=>$slugs],
					["name"=>"url",'isRequired'=>false,"title"=>'网页链接',"type"=>'string',"remark"=>'slug值为wxConfig时必传'],
				],
				"getFields"=>'getFields'.ucfirst($action),
			]
		]);
	}

	/**
	 * 通行证
	 * @param $action
	 *
	 * @return string
	 */
	public function actionPassport($action){
		$getParameters = $postParameters = [];

		switch ($action){
			case 'login':
				$getParameters = [
					["name"=>"mode",'alias'=>'Mode','isRequired'=>false,"title"=>'登录类型',"type"=>'string',"remark"=>'默认为password',"value"=>["password"=>'密码登录',"cellphone"=>"手机验证码","email"=>"邮箱验证码"]],
				];
				$postParameters = [
					["name"=>"cellphone_code",'isRequired'=>false,"title"=>'国际区号',"type"=>'string',"remark"=>'get参数mode=cellphone时选填，默认为中国0086'],
					["name"=>"account",'isRequired'=>true,"title"=>'用户名、手机或邮箱',"type"=>'string',"remark"=>'必填，由get参数mode确定'],
					["name"=>"captcha",'isRequired'=>true,"title"=>'验证码',"type"=>'string',"remark"=>'get参数mode=cellphone|email时必填'],
					["name"=>"password",'isRequired'=>true,"title"=>'密码',"type"=>'string',"remark"=>'get参数mode=password时必填'],
				];
				break;
			case 'third-login':
				$postParameters = [
					["name"=>"timestamp",'isRequired'=>true,"title"=>'时间戳',"type"=>'int',"remark"=>'必填'],
					["name"=>"token",'isRequired'=>true,"title"=>'签名',"type"=>'string',"remark"=>'必填，值为md5(密钥+open_id+时间戳)，当前项目的密钥为：'.Yii::$app->params['third.loginKey']],
					["name"=>"open_id",'isRequired'=>true,"title"=>'第三方账号unid',"type"=>'string',"remark"=>'必填，ShareSDk或其他方式获取'],
					["name"=>"client_id",'isRequired'=>true,"title"=>'客户端',"type"=>'string',"remark"=>'必填，允许的值“'.implode(',',Yii::$app->params['third.thirdAllowList']).'”'],

					["name"=>"nickname",'isRequired'=>false,"title"=>'昵称',"type"=>'string',"remark"=>''],
					["name"=>"avatar",'isRequired'=>false,"title"=>'头像路径',"type"=>'string',"remark"=>''],
					["name"=>"gender",'isRequired'=>false,"title"=>'性别',"type"=>'string',"remark"=>"允许的值“male,female,secrecy”"],
					["name"=>"birthday",'isRequired'=>false,"title"=>'生日',"type"=>'string',"remark"=>''],
					["name"=>"blood",'isRequired'=>false,"title"=>'血型',"type"=>'string',"remark"=>''],
					["name"=>"country",'isRequired'=>false,"title"=>'国家',"type"=>'string',"remark"=>'如：中国、其他'],
					["name"=>"province",'isRequired'=>false,"title"=>'省',"type"=>'string',"remark"=>'如：上海市、安徽省'],
					["name"=>"city",'isRequired'=>false,"title"=>'市',"type"=>'string',"remark"=>'杨浦区、合肥市'],
					["name"=>"area",'isRequired'=>false,"title"=>'区',"type"=>'string',"remark"=>'杨浦区、蜀山区'],
					["name"=>"street",'isRequired'=>false,"title"=>'街道',"type"=>'string',"remark"=>''],
					["name"=>"signature",'isRequired'=>false,"title"=>'个人签名',"type"=>'string',"remark"=>''],
				];
				break;
			case 'register':
				$getParameters = [
					["name"=>"mode",'alias'=>'Mode','isRequired'=>false,"title"=>'注册类型',"type"=>'string',"remark"=>'默认为username',"value"=>["username"=>'密码登录',"cellphone"=>"手机验证码","email"=>"邮箱验证码","fast"=>"快速登录"]],
				];
				$postParameters = [
					["name"=>"cellphone_code",'isRequired'=>false,"title"=>'国际区号',"type"=>'string',"remark"=>'get参数mode=cellphone时选填，默认为中国0086'],
					["name"=>"account",'isRequired'=>true,"title"=>'用户名、手机或邮箱',"type"=>'string',"remark"=>'必填，由get参数mode确定'],
					["name"=>"captcha",'isRequired'=>true,"title"=>'验证码',"type"=>'string',"remark"=>'get参数mode=cellphone|email|fast时必填'],
					["name"=>"password",'isRequired'=>true,"title"=>'密码',"type"=>'string',"remark"=>'get参数mode=password|cellphone|email时必填'],
					["name"=>"password_repeat",'isRequired'=>true,"title"=>'确认密码',"type"=>'string',"remark"=>'get参数mode=password|cellphone|email时必填'],
				];
				break;
			case 'find-password':
				$getParameters = [
					["name"=>"mode",'alias'=>'Mode','isRequired'=>false,"title"=>'找回类型',"type"=>'string',"remark"=>'默认为cellphone',"value"=>["cellphone"=>"手机验证码","email"=>"邮箱验证码"]],
				];
				$postParameters = [
					["name"=>"cellphone_code",'isRequired'=>false,"title"=>'国际区号',"type"=>'string',"remark"=>'get参数mode=cellphone时选填，默认为中国0086'],
					["name"=>"account",'isRequired'=>true,"title"=>'手机或邮箱',"type"=>'string',"remark"=>'必填，由get参数mode确定'],
					["name"=>"captcha",'isRequired'=>true,"title"=>'验证码',"type"=>'string',"remark"=>'必填'],
					["name"=>"password",'isRequired'=>true,"title"=>'密码',"type"=>'string',"remark"=>'必填'],
					["name"=>"password_repeat",'isRequired'=>true,"title"=>'密码',"type"=>'string',"remark"=>'必填'],
				];
				break;
			case 'is-logged':
			case 'logout':
				$getParameters = [
					["name"=>"access-token",'alias'=>null,'isRequired'=>true,"title"=>'授权认证码',"type"=>'string',"remark"=>'必填']
				];
				break;
		}

		return $this->render('index',[
			"parameters"=>[
				"get"=>$getParameters,
				"post"=>$postParameters
			]
		]);
	}

	/**
	 * 用户
	 * @param $action
	 *
	 * @return string
	 */
	public function actionUser($action){
		$fields = $getParameters = $postParameters = [];

		switch ($action){
			case 'profile_post':
			case 'profile_patchput':
				$postParameters = [
					["name"=>"nickname",'isRequired'=>false,"title"=>'昵称',"type"=>'string',"remark"=>''],
					["name"=>"avatar",'isRequired'=>false,"title"=>'头像路径',"type"=>'string',"remark"=>''],
					["name"=>"gender",'isRequired'=>false,"title"=>'性别',"type"=>'string',"remark"=>"允许的值“male,female,secrecy”"],
					["name"=>"birthday",'isRequired'=>false,"title"=>'生日',"type"=>'string',"remark"=>''],
					["name"=>"blood",'isRequired'=>false,"title"=>'血型',"type"=>'string',"remark"=>''],
					["name"=>"country",'isRequired'=>false,"title"=>'国家',"type"=>'string',"remark"=>'如：中国、其他'],
					["name"=>"province",'isRequired'=>false,"title"=>'省',"type"=>'string',"remark"=>'如：上海市、安徽省'],
					["name"=>"city",'isRequired'=>false,"title"=>'市',"type"=>'string',"remark"=>'杨浦区、合肥市'],
					["name"=>"area",'isRequired'=>false,"title"=>'区',"type"=>'string',"remark"=>'杨浦区、蜀山区'],
					["name"=>"street",'isRequired'=>false,"title"=>'街道',"type"=>'string',"remark"=>''],
					["name"=>"signature",'isRequired'=>false,"title"=>'个人签名',"type"=>'string',"remark"=>''],
				];

				break;
			case 'reset-password':
				$getParameters = [
					["name"=>"mode",'alias'=>'Mode','isRequired'=>false,"title"=>'类型',"type"=>'string',"remark"=>'默认为password',"value"=>["password"=>"密码","cellphone"=>"手机验证码","email"=>"邮箱验证码"]],
				];
				$postParameters = [
					["name"=>"password_old",'isRequired'=>true,"title"=>'旧密码',"type"=>'string',"remark"=>'get参数mode=password时必填'],
					["name"=>"password",'isRequired'=>true,"title"=>'密码',"type"=>'string',"remark"=>'必填'],
					["name"=>"password_repeat",'isRequired'=>true,"title"=>'密码',"type"=>'string',"remark"=>'必填'],
					["name"=>"captcha",'isRequired'=>true,"title"=>'验证码',"type"=>'string',"remark"=>'get参数mode=cellphone|email必填'],
				];
				break;
			case 'reset-username':

				$postParameters = [
					["name"=>"username",'isRequired'=>true,"title"=>'新用户名',"type"=>'string',"remark"=>'必填'],
				];

				break;
			case 'bind':

				$getParameters = [
					["name"=>"mode",'alias'=>'Mode','isRequired'=>true,"title"=>'绑定类型',"type"=>'string',"remark"=>'必填',"value"=>["cellphone"=>"手机验证码","email"=>"邮箱验证码"]],
				];
				$postParameters = [
					["name"=>"action",'isRequired'=>true,"title"=>'操作',"type"=>'string',"remark"=>'必填，允许的值“bind,unbind”'],
					["name"=>"cellphone_code",'isRequired'=>false,"title"=>'国际区号',"type"=>'string',"remark"=>'get参数mode=cellphone时选填，默认为中国0086'],
					["name"=>"account",'isRequired'=>true,"title"=>'手机或邮箱',"type"=>'string',"remark"=>'必填，由get参数mode确定'],
					["name"=>"captcha",'isRequired'=>true,"title"=>'验证码',"type"=>'string',"remark"=>'必填，Sms接口获取，其中Sms接口的post参数type随着当前接口的post参数action改变而改变，1、action=bind时type值为register，2、action=unbind时值为reset'],
				];

				break;
			case 'third-account_post':
				$postParameters = [
					["name"=>"action",'isRequired'=>true,"title"=>'操作',"type"=>'string',"remark"=>'必填，允许的值“bind,unbind”'],
					["name"=>"client_id",'isRequired'=>true,"title"=>'客户端',"type"=>'string',"remark"=>'必填，允许的值“'.implode(',',Yii::$app->params['third.thirdAllowList']).'”'],
					["name"=>"open_id",'isRequired'=>true,"title"=>'第三方账号unid',"type"=>'string',"remark"=>'当action=bind时必填'],
				];
				break;
			case 'comment':
				$getParameters = [
					["name"=>"expand",'alias'=>'Expand','isRequired'=>false,"title"=>'扩展字段',"type"=>'string',"remark"=>'多个用英文逗号分隔','value'=>['userProfile'=>'用户资料','category'=>'栏目详情','dataDetail'=>'评论数据详情'],'multipleSelect'=>true]
				];
				$fields = $this->apiResponseGet(new CommentModel());
				break;

		}

		$getParameters[] = ["name"=>"access-token",'alias'=>null,'isRequired'=>true,"title"=>'授权认证码',"type"=>'string',"remark"=>'必填'];

		return $this->render('index',[
			"parameters"=>[
				"get"=>$getParameters,
				"post"=>$postParameters
			],
			"fields"=>$fields
		]);
	}

	/**
	 * 关联
	 * @param $action
	 *
	 * @return string
	 */
	public function actionRelation($action){
		$getParameters = $postParameters = [];
		$getFields = false;
		switch ($action){
			case 'getFieldsList':

				$postData = Yii::$app->getRequest()->post($this->action->id,[]);
				$slug = ArrayHelper::getValue($postData,'slug');
				if($slug){
					$modelInfo = PrototypeModelModel::findModel($this->config['member']['relationContent'][$slug]['model_id']);
					$modelName = ucfirst($modelInfo->name);
					unset($categoryInfo);

					$modelName = 'common\entity\nodes\\'.$modelName.'Model';
					$this->success(['操作成功','message'=>$this->apiResponseGet(new $modelName(),$action == 'getFieldsList'?true:false)]);
				}else{
					$this->error(['操作失败','message'=>'mid和cid必须选择一个。']);
				}

				break;
			case 'list':
				$getFields = 'getFieldsList';
				break;
			case 'operation':
				$postParameters = [
					["name"=>"action",'isRequired'=>true,"title"=>'操作',"type"=>'string',"remark"=>'必填，允许的值“relation,unRelation,check”'],
					["name"=>"ids",'isRequired'=>true,"title"=>'关联数据Id',"type"=>'string',"remark"=>'必填，多个用英文“,”分隔'],
				];
				break;
		}

		$relationList = [];
		foreach ($this->config['member']['relationContent'] as $item){
			$relationList[$item['slug']] = $item['title'];
		}

		$getParameters[] = ["name"=>"slug",'alias'=>'Slug','isRequired'=>true,"title"=>'标识',"type"=>'string',"remark"=>'必填',"value"=>$relationList];
		$getParameters[] = ["name"=>"access-token",'alias'=>null,'isRequired'=>true,"title"=>'授权认证码',"type"=>'string',"remark"=>'必填'];

		return $this->render('index',[
			"parameters"=>[
				"get"=>$getParameters,
				"post"=>$postParameters,
				"getFields"=>$getFields,
			]
		]);
	}

	/**
	 * H5页面
	 * @param $action
	 *
	 * @return string
	 */
	public function actionHtml5($action){
		$siteList = ArrayHelper::map(SiteModel::findSite(),'id','title');

		$categoryList =  ArrayHelper::linear(PrototypeCategoryModel::findCategory(),' ├ ');
		$categoryDisable = [];
		foreach ($categoryList as $i=>$item){
			$item['str_title'] = $item['str'].$item['title'];
			$categoryList[$i] = $item;

			if($action == 'view' && $item['type']){
				$categoryDisable[$item['id']] = ['disabled' => true];
			}elseif ($action == 'page' && $item['type'] != 1){
				$categoryDisable[$item['id']] = ['disabled' => true];
			}

		}
		$mapCategoryList = [];
		foreach (ArrayHelper::map($categoryList,'id','str_title','site_id') as $s=>$item){
			$mapCategoryList[$siteList[$s]] = $item;
		}
		unset($categoryList);

		$getParameters = [
			["name"=>"sid",'alias'=>'SiteId','isRequired'=>true,"title"=>'站点Id',"type"=>'int',"remark"=>'必填','value'=>$siteList],
			["name"=>"category_id",'alias'=>'CategoryId','isRequired'=>true,"title"=>'栏目Id',"type"=>'int',"remark"=>'必填','value'=>$mapCategoryList,'disabled'=>$categoryDisable],
		];

		$postParameters = [];

		if($action == 'view'){
			$getParameters[] = ["name"=>"id",'alias'=>'Id','isRequired'=>true,"title"=>'数据Id',"type"=>'int',"remark"=>'必填','value'=>'int'];
		}

		$getParameters[] = ["name"=>"slug",'alias'=>null,'isRequired'=>false,"title"=>'base64处理过的access-token',"type"=>'string',"remark"=>'需要登录访问的页面需传递此参数才能正常访问'];

		return $this->render('index',[
			"parameters"=>[
				"get"=>$getParameters,
				"post"=>$postParameters
			]
		]);
	}

	/**
	 * 上传的资源文件访问文件
	 * @param $action
	 *
	 * @return string
	 */
	public function actionUploads($action){
		return '此功能待开发token验证，thumb参数使用，如：thumb=w/100/h/100，表示生成一个100*100大小的图片。';
	}
}