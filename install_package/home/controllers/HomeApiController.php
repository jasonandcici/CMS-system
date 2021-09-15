<?php
/**
 * @copyright
 * @link
 * @create Created on 2016/12/15
 */

namespace home\controllers;
use common\entity\models\SystemConfigModel;
use common\helpers\TimerHelper;
use Yii;
use yii\base\Controller;


/**
 * 接口控制器(核心方法不可删除)
 *
 * @author
 * @since 1.0
 */
class HomeApiController extends Controller
{
    /**
     * 清空缓存
     * /home-api/del-cache.html
     * type=['all'，'config','model','category','site','fragment','sensitivewords']
     * token={type+date('Ymdh')}
     */
    public function actionDelCache(){
        $request = Yii::$app->getRequest();
        $cache = Yii::$app->getCache();

        if($request->getIsPost()){
            $type = $request->post('type');
            $token = $request->post('token');
            if(Yii::$app->getSecurity()->validatePassword($type.date('Ymdh',time()),$token)){
            	if($type == 'all'){
		            if($cache->exists('config')) $cache->delete('config');
		            if($cache->exists('model')) $cache->delete('model');
		            if($cache->exists('category')) $cache->delete('category');
		            if($cache->exists('site')) $cache->delete('site');
		            if($cache->exists('fragment')) $cache->delete('fragment');
		            if($cache->exists('sensitivewords')) $cache->delete('sensitivewords');
                }else{
            		foreach (explode(',',$type) as $item){
			            if($cache->exists($item)){
				            $cache->delete($item);
			            }
		            }
                }

                return json_encode([
                    'status'=>1
                ]);
            }
        }

        return json_encode([
            'status'=>0
        ]);
    }
}