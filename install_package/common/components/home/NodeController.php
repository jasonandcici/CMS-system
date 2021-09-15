<?php
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Copyright (c) 2015-+ .
// +----------------------------------------------------------------------
// | Author:
// +----------------------------------------------------------------------
// | Created on 2016/5/25.
// +----------------------------------------------------------------------

/**
 * 前台node控制器基类
 */

namespace common\components\home;

use common\entity\models\PrototypePageModel;
use common\helpers\ArrayHelper;
use common\helpers\SecurityHelper;
use common\helpers\UrlHelper;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;


class NodeController extends HomeController
{

    /**
     * @var object 当前栏目信息
     */
    public $categoryInfo;

    /**
     * @var array 当前栏目子栏目
     */
    public $subCategoryList = [];

    /**
     * @var array 当前栏目同类型的子栏目id
     */
    public $sameSubCategoryIds = [];

    /**
     * @var array 当前栏目父栏目
     */
    public $parentCategoryList = [];

    /**
     * @var array 无需授权的action
     */
    protected $accessExceptAction = [];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'denyCallback' => [$this,'accessDenyCallback'],
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => [$this,'accessMatchCallback'],
                    ],
                ],
            ]
        ];
    }

    /**
     * 访问授权失败回调
     * @param $rule
     * @param $action
     */
    public function accessDenyCallback($rule, $action){
        if($action->getUniqueId() != 'u/passport/logout'){
            $jumpUrl = ['jumpLink'=>Yii::$app->getRequest()->getAbsoluteUrl()];
            $this->redirect($this->generateUserUrl('login',['params'=>$jumpUrl]));
        }else{
            $this->redirect($this->generateUserUrl('login'));
        }
    }

    /**
     * 访问权限检查逻辑
     * @param $rule
     * @param $action
     * @return bool
     */
    public function accessMatchCallback($rule, $action){
        if(Yii::$app->getUser()->getIsGuest()){
            if($action->getUniqueId() === 'node/detail'){
                // 内容详情在详情页进行验证。
                return true;
            }elseif ($action->getUniqueId() === 'node/download'){
                if(intval($this->categoryInfo->model->is_login_download)) return false;
            }else{
                if(intval($this->categoryInfo->is_login)) return false;
            }
        }
        return true;
    }

    /**
     * @var array 系统操作
     */
    private $systemAction = [
        'site/index',
        'site/error',
        'site/captcha',
    ];

    /**
     * 检测是否测试网站并给予提示
     * @throws NotFoundHttpException
     */
    public function init()
    {
        parent::init();

        if(YII_ENV === 'dev' && !Yii::$app->getSession()->has('DEVTIP')){
            Yii::$app->getSession()->set('DEVTIP',true);
            echo $this->renderPartial('@common/assets/dev_tip.php');
            die;
        }
    }

    /**
     * 设置页面栏目信息
     * @param \yii\base\Action $action
     * @return bool
     * @throws NotFoundHttpException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        // 根据栏目id查找栏目
        $categoryId = Yii::$app->request->get('category_id');
        if($categoryId && array_key_exists($categoryId,$this->categoryList)){
            $this->categoryInfo = $this->categoryList[$categoryId];
        }
        // 查找系统内置模块是否存在栏目，不存在给出默认栏目
        elseif($this->module->id === 'u' || in_array($action->getUniqueId(),$this->systemAction) || $action->controller->id === 'comment'){
            foreach($this->categoryList?:[] as $item){
                if($item['slug_rules'] == $action->getUniqueId()){
                    $this->categoryInfo = $item;
                    break;
                }
            }
            if(empty($this->categoryInfo)){
                $layout = 'main';
                $template = $action->id;
                $title = Yii::t('system-action-name',$action->getUniqueId());
                $isLogin = 0;
                $isLoginContent = 0;
                if(Yii::$app->controller->module->id ==='u'){
                    $isLogin = 1;
                    $isLoginContent = 1;
                    if(Yii::$app->controller->id === 'passport'){
                        $layout = $this->config->member->layoutPassport?:'main';
                    }else{
                        $layout = $this->config->member->layout?:'main';
                    }
                    if($action->controller->id === 'relation' || $action->controller->id === 'publish'){
                        $slug = Yii::$app->getRequest()->get('slug');
                        $template = ArrayHelper::getValue($this->config->member->relationContent,$slug.'.template')?:$slug;
                        $title = ArrayHelper::getValue($this->config->member->relationContent,$slug.'.title');
                    }
                }
                $this->categoryInfo = [
                    "id" => 0,
                    "site_id" => $this->siteInfo->id,
                    "pid" => 0,
                    "model_id" => 0,
                    "type" => 2,
                    "title" => $title,
                    "sub_title" => "",
                    "slug" => $action->id,
                    "slug_rules" => $action->getUniqueId(),
                    "slug_rules_detail" => "",
                    "sort" => 0,
                    "status" => 0,
                    "link" => "",
                    "target" => "",
                    "thumb" => null,
                    "content" => null,
                    "template" => $template,
                    "template_content" => NULL,
                    "expand" => NULL,
                    "layouts" => $layout,
                    "layouts_content" => NULL,
                    "is_login" => $isLogin,
                    "is_login_content" => $isLoginContent,
                    "enable_tag" => 0,
                    "enable_push" => 0,
                    "system_mark"=>null,
                    "seo_title" => "",
                    "seo_keywords" => "",
                    "seo_description" => "",
                    "model" => NULL
                ];
                unset($layout,$title,$template,$isLogin,$isLoginContent);
                $this->categoryList[] = $this->categoryInfo;
                $this->allCategoryList[] = $this->categoryInfo;
            }
        }

        // 没有栏目id查找栏目
        else{
            // slug处理
            $slugs = [];
            foreach(Yii::$app->getRequest()->get() as $i=>$item){
                $temp = explode('_',$i);
                if($temp[0] === 'slug') $slugs[$temp[1]] = $item;
            }
            ksort($slugs);
            $slug = implode('/',$slugs);

            // 查找栏目
            foreach($this->categoryList as $item){
                if($item['type'] == 2){
                    $slugRules = UrlHelper::convertSlugRules($item['slug_rules']);
                    $slugRulesDetail = UrlHelper::convertSlugRules($item['slug_rules_detail']);

                    if(($slugRules['route'] == $action->getUniqueId() && $item['slug'] == $slug) || ($slugRulesDetail['route'] == $action->getUniqueId() && $item['slug'] == $slug)){
                        $this->categoryInfo = $item;
                        foreach($slugRules['params'] as $k=>$v){
                            $_GET[$k] = $v;
                        }
                        break;
                    }
                }
                elseif(!empty($slug) && $item['slug'] == $slug){
                    $this->categoryInfo = $item;
                    break;
                }
            }

            // 下载
            if($action->getUniqueId() == 'node/download'){
                $token = Yii::$app->getRequest()->get('token');
                if($token){
                    $this->categoryInfo = $this->allCategoryList[intval(SecurityHelper::decrypt($token,date('dYm')))];
                }
            }
        }

        if(empty($this->categoryInfo)){
            Yii::$app->getErrorHandler()->errorAction = null;
            throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));
        }

        // 当前页子栏目列表
        if($this->categoryInfo['id'] !== 0){
            $this->subCategoryList = ArrayHelper::getChildes($this->categoryList,$this->categoryInfo['id']);
            array_unshift($this->subCategoryList,$this->categoryList[$this->categoryInfo['id']]);

            // 当前栏目同类型子栏目id
            $this->sameSubCategoryIds = ArrayHelper::getColumn($this->findSameCategory($this->subCategoryList,$this->categoryInfo),'id');

            // 当前页父栏目列表
            $this->parentCategoryList = ArrayHelper::getParents($this->categoryList,$this->categoryInfo['id']);
        }else{
            $this->parentCategoryList[] = $this->categoryInfo;
            $this->subCategoryList[] = $this->categoryInfo;
            $this->sameSubCategoryIds = $this->categoryInfo['id'];
        }



        if(in_array($action->id,$this->accessExceptAction)){
            $this->categoryInfo['is_login'] = 0;
            $this->categoryInfo['is_login_content'] = 0;
        }
        $this->categoryInfo = ArrayHelper::convertToObject($this->categoryInfo);

        return parent::beforeAction($action);
    }

    /**
     * 获取node 列表类容视图
     * @param array $params
     * @return string
     */
    public function findNodeDetailView($params=[]){
        $params = ArrayHelper::merge([
            "default" => 'detail', // 默认视图
            "detail" => null, // 内容
        ],$params);

        if(empty($params['detail']->template_content)){
            $view = $this->findNodeViewPropagation(1,$params['default']);
        }else{
            $view = $params['detail']->template_content;
        }

        return '/'.($this->categoryInfo->type == 2?Yii::$app->controller->id:$this->categoryInfo->model->name).'/'.$view;
    }

    /**
     * 获取node List视图
     * @param string|null $defaultView
     * @return string
     */
    public function findNodeListView($defaultView = 'index'){

        switch($this->categoryInfo->type){
            case 1:
                $view = '/'.'page/'.$this->findNodeViewPropagation(0,$defaultView);
                break;
            case 2:
                $view = $this->findNodeViewPropagation(0,$defaultView);
                break;
            default:
                $view = '/'.$this->categoryInfo->model->name.'/'.$this->findNodeViewPropagation(0,$defaultView);
                break;
        }
        return $view;
    }

    /**
     * 以向上冒泡的方式获取视图文件
     * @param $type 0:获取template字段，1：获取template_content字段
     * @param string $default 默认视图
     * @return array|string
     */
    private function findNodeViewPropagation($type = 0,$default = null){
        $default = empty($default)?'index':$default;
        $viewName = '';
        foreach(array_reverse($this->parentCategoryList,false) as $item){
            if($item['model_id'] == $this->categoryInfo->model_id && $item['type'] == $this->categoryInfo->type){
                if($type === 0 && !empty($item['template'])){
                    $viewName = $item['template'];
                    break;
                }elseif($type === 1 && !empty($item['template_content'])){
                    $viewName = $item['template_content'];
	                break;
                }
            }
        }
        return empty($viewName)?$default:$viewName;
    }

    /**
     * 列表页
     * @param bool $isRender
     * @return array|string
     */
    protected function nodeList($isRender = true){
        $searchModel = ($this->categoryInfo->model->type == 2)?$this->findSearchModel($this->categoryInfo->model->name,false):$this->findSearchModel($this->categoryInfo->model->name);
        $tableName = $searchModel::tableName();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query
            ->andFilterWhere([$tableName.'.status'=>1,$tableName.'.site_id'=>$this->siteInfo->id])
            ->andFilterWhere(['in','category_id',$this->sameSubCategoryIds]);

        $dataProvider->sort = [
            'defaultOrder' => [
                'sort'=> SORT_DESC,
            ]
        ];

        if($pageSize = Yii::$app->getRequest()->get('per-page')){
            $dataProvider->pagination = ['pageSize'=>intval($pageSize)];
        }else{
            $dataProvider->pagination = ['pageSize'=>array_key_exists('page_size',Yii::$app->params)?Yii::$app->params['page_size']:10];
        }

        $assign = [
            'searchModel'=>$searchModel,
            'dataProvider'=>$dataProvider
        ];
        return $isRender?$this->render($this->findNodeListView(),$assign):$assign;
    }

    /**
     * 单页
     * @param bool $isRender
     * @return array|string
     */
    protected function nodePage($isRender = true){
        $model = PrototypePageModel::findOne($this->categoryInfo->id);

        $assign = [
            'dataDetail'=>$model
        ];
        return $isRender?$this->render($this->findNodeListView(),$assign):$assign;
    }

    /**
     * 内容详情页
     * @param bool $isRender
     * @return string
     * @throws NotFoundHttpException
     */
    protected function nodeDetail($isRender = true){
        $id = intval(Yii::$app->request->get('id'));
        // 是否需要登录
        $previewToken = Yii::$app->getRequest()->get('preview-token');

        if($previewToken && $previewToken == md5(SecurityHelper::encrypt($id,date('dYm')))){
            $status = [0,1,2,3,4,5];
        }else{
            $status = 1;
        }
        unset($previewToken);

        $model = ($this->categoryInfo->model->type == 2)?$this->findModel($this->categoryInfo->model->name,null,false):$this->findModel($this->categoryInfo->model->name);

        // 内容
        $assign['dataDetail'] = $data = $model->find()->where(['id'=>$id,'status'=>$status,'site_id'=>$this->siteInfo->id])->one();

        if(!$data) throw new NotFoundHttpException(Yii::t('common','The requested page does not exist.'));

        if(!empty($data->jump_link)) $this->redirect($data->jump_link);

        if($status === 1 && $data->is_login && Yii::$app->getUser()->getIsGuest()){
            $this->accessDenyCallback(null,$this->action);
        }
        unset($status);

        // 更新浏览量
        if(isset($data->views)){
            $data->updateCounters(['views'=>1]);
        }

        // 前后翻页按钮
        $assign['prevLink'] = $this->findPageQuery($model,$data,'>',true);
        $assign['nextLink'] = $this->findPageQuery($model,$data,'<');

        $this->categoryInfo->layouts_content = $data->layouts;

        return $isRender?$this->render($this->findNodeDetailView(['detail'=>$data]),$assign):$assign;
    }

    /**
     * 获取内容上一页和下一页
     * @param object $model 模型
     * @param object $data node内容
     * @param string $sign 符号“<” 或 “>”
     * @param bool $reverse 反转
     * @return mixed
     */
    protected function findPageQuery($model,$data,$sign,$reverse = false){
        // 排序
        $requestSort = Yii::$app->request->get('order');
        $sort = [];
        if($requestSort){
            foreach(array_keys($requestSort) as $item){
                $sort[$item] = ($requestSort[$item] == 'desc')?($reverse?SORT_ASC:SORT_DESC):($reverse?SORT_DESC:SORT_ASC);
            }
        }else{
            $sort = ['sort'=>($reverse?SORT_ASC:SORT_DESC)];
        }

        return $model->find()->where(['status'=>1,'site_id'=>$this->siteInfo->id,'category_id'=>$data->category_id])
            //->andWhere([$sign,'id',$data->id])
            ->andWhere([$sign,'sort',$data->sort])
            ->orderBy($sort)->one();
    }

    /**
     * 重置视图渲染
     * @param string $view
     * @param array $params
     * @return string
     */
    public function render($view, $params = [])
    {
        if($this->route === 'node/detail'){
            if(empty($this->categoryInfo->layouts_content)){
                return $this->renderPartial($view, $params);
            }
            $this->layout = strpos($this->categoryInfo->layouts_content,'/')===0?$this->categoryInfo->layouts_content:'/'.$this->categoryInfo->layouts_content;
        }else{
            if(empty($this->categoryInfo->layouts)){
                return $this->renderPartial($view, $params);
            }
            $this->layout = strpos($this->categoryInfo->layouts,'/')===0?$this->categoryInfo->layouts:'/'.$this->categoryInfo->layouts;
        }
        return parent::render($view, $params);
    }

}