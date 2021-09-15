<?php
namespace manage\controllers;

use common\components\manage\ManageController;
use common\entity\models\AuthItemModel;
use common\entity\models\PrototypeCategoryModel;
use common\entity\models\SiteModel;
use common\entity\models\SystemMenuModel;
use common\helpers\ArrayHelper;
use manage\models\DelCacheHelper;
use Yii;
use yii\helpers\Url;

/**
 * 后台管理
 */
class SiteController extends ManageController
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'minLength' => 4,
                'maxLength' => 4,
                'backColor'=>0xfafafa,
                'foreColor'=>0x000000,
                'height'=>32,
                'width' => 90,
                'offset'=>4,
            ],
        ];
    }

    /**
     * 后台首页
     * @param null $sid
     * @param bool $getSiteList
     * @return string
     */
    public function actionIndex($sid = null,$getSiteList = false)
    {
        // 获取站点列表
        $assign['siteList'] = SiteModel::findSite();

        if($getSiteList){
            $html = '';
            foreach ($assign['siteList'] as $item){
                $html .='<a id="site-list-'.$item['id'].'" href="'.Url::to(['index','sid'=>$item['id']]).'" class="list-group-item'.($item['id']==$this->siteInfo->id?' active':'').'"><span class="iconfont pull-right">&#xe60c;</span>'.$item['title'].'</a>';
            }
            return $html;
        }

        // 切换站点
        $session = Yii::$app->session;
        if(Yii::$app->getRequest()->getIsPost() && $sid){
            if(array_key_exists($sid,$assign['siteList'])){
                $session->set('siteInfo',$assign['siteList'][$sid]);
                $this->success(['操作成功']);
            }else{
                $this->error(['操作失败','message'=>'站点不存在。']);
            }
        }

        // 设置布局
        $this->layout = 'base';

        // 生成和权限相匹配导航
        $userPermissionList = $this->isSuperAdmin?[]:Yii::$app->getAuthManager()->getPermissionsByUser(Yii::$app->getUser()->getId());

        $menuList = SystemMenuModel::find()->where(['status'=>1])->orderBy(['sort' => SORT_ASC])->asArray()->all();
        foreach ($menuList as $i=>$item){
            switch ($item['link']){
                case 'import/prototype':
                case 'prototype/category/index':
                    $menuList[$i]['slug'] = $item['link'].'?site_id='.$this->siteInfo->id;
                    break;
                case 'config/index':
                    $menuList[$i]['slug'] = $item['link'].'?'.$item['param'];
                    break;
                default:
                    $menuList[$i]['slug'] = $item['link'];
                    break;
            }
        }

        $navList = [];
        foreach($menuList as $value){
        	if($value['link'] == 'comment/index' && !$this->isSuperAdmin && !$this->config['site']['enableComment']) continue;
            if($this->isSuperAdmin || array_key_exists($value['slug'],$userPermissionList) || $value['type'] == 1 || $value['type'] == 2) {
                switch ($value['type']) {
                    case 0:
                    case 2:
                        $str_url = [$value['link']];
                        if ($value['param'] && str_replace(' ', '', $value['param']) != '') {
                            $params_tmp = [];
                            foreach (explode('&', $value['param']) as $p) {
                                $tmp = explode('=', $p);
                                $params_tmp[$tmp[0]] = $tmp[1];
                            }
                            $str_url = array_merge($str_url, $params_tmp);
                        }
                        $value['url'] = Url::to($str_url);
                        break;
                    default:
                        $value['url'] = $value['link'];
                        break;
                }
                $navList[] = $value;
            }
        }
        unset($userPermissionList);

        $assign['navList'] = $this->navHandle(ArrayHelper::tree($navList));

        return $this->render('index',$assign);
    }

    /**
     * 菜单处理
     * @param $data
     * @return array
     */
    protected function navHandle($data){
        $arr = [];
        foreach ($data as $item){
            if($item['type'] == 1){
                if(empty($item['child'])){
                    continue;
                }else{
                    $res = $this->navHandle($item['child']);
                    if(empty($res)){
                        continue;
                    }else{
                        $item['child'] = $res;
                        $arr[] = $item;
                    }
                }
            }else{
                $arr[] = $item;
            }
        }
        return $arr;
    }

    /**
     * 欢迎页
     * @return string
     */
    public function actionWelcome()
    {
        /*$newData = [];
        $actionList = ['prototype/node/create','prototype/node/delete','prototype/node/index','prototype/node/move','prototype/node/sort','prototype/node/status','prototype/node/update'];
        $time = time();
        foreach (PrototypeCategoryModel::findCategory() as $item){
            if($item['type'] == 0){
                foreach ($actionList as $a){
                    $newData[] = [
                        "name"=>$a.'?category_id='.$item['id'],
                        "type"=>2,
                        "created_at"=>$time,
                        "updated_at"=>$time,
                    ];
                }
            }elseif($item['type'] == 1){
                $newData[] = [
                    "name"=>'prototype/node/page?category_id='.$item['id'],
                    "type"=>2,
                    "created_at"=>$time,
                    "updated_at"=>$time,
                ];
            }
        }

        Yii::$app->getDb()->createCommand()->batchInsert(AuthItemModel::tableName(),['name','type','created_at','updated_at'],$newData)->execute();*/

        // 系统信息
        $server['serverSoft'] = $_SERVER['SERVER_SOFTWARE'];
        $server['serverOs'] = PHP_OS;

        $server['phpVersion'] = PHP_VERSION;
        $server['fileUpload'] = ini_get('file_uploads') ? ini_get('upload_max_filesize') : '禁止上传';

        // 数据库信息
        $dbSize = 0;
        $connection = Yii::$app->db;
        $command = $connection->createCommand('SHOW TABLE STATUS')->queryAll();
        foreach ($command as $table)
            $dbSize += $table['Data_length'] + $table['Index_length'];
        $mysqlVersion = $connection->createCommand("SELECT version() AS version")->queryAll();
        $server['mysqlVersion'] = $mysqlVersion[0]['version'];
        $server['dbSize'] = Yii::$app->formatter->asSize($dbSize);

        return $this->render($this->action->id,['server'=>$server]);
    }

	/**
	 * 清除缓存
	 *
	 * @param bool $isReturn
	 *
	 * @throws \yii\base\Exception
	 */
    public function actionClearCache($isReturn = true){
        foreach (SiteModel::findSite() as $item){
            Yii::$app->cache->delete('category'.$item['id']);
        }
        Yii::$app->getCache()->delete('model');
        Yii::$app->getCache()->delete('config');
        Yii::$app->getCache()->delete('category');
        Yii::$app->getCache()->delete('site');
        Yii::$app->getCache()->delete('fragment');
        Yii::$app->getCache()->delete('sensitivewords');

	    $res = DelCacheHelper::deleteCache('all');
        if($res){
	        if($isReturn) $this->success([Yii::t('common','Operation successful')]);
        }else{
            $this->error([Yii::t('common','Operation failed'),'message'=>'执行curl失败。']);
        }
    }
}
