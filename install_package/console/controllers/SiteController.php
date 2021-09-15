<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/10/23
 */

namespace console\controllers;

use common\entity\models\RedisAuthTokenModel;
use common\entity\models\UserAuthTokenModel;
use common\helpers\ArrayHelper;
use common\helpers\SystemHelper;
use Yii;
use yii\console\Controller;

/**
 * 定时任务
 *
 * @author 
 * @since 1.0
 */
class SiteController extends Controller {

	/**
	 * 清理失效的Token
	 * 定时器每分钟运行一次
	 */
	public function actionClearAuthToken() {
		// */1 * * * * php /data/www/xingzoushanghai/yii timer/clear-auth-token

		$tokenModel = SystemHelper::isEnableRedis() ? new RedisAuthTokenModel() : new UserAuthTokenModel();

		$outList = [];
		// 清除已经过期token
		if ( SystemHelper::isEnableRedis() ) {
			foreach ( $tokenModel::find()->asArray()->all() as $item ) {
				if ( $this->getOutTime( $item['type'] ) > $item['create_time'] ) {
					$outList[] = $item['token'];
				}
			}
		} else {
			$outLoginList = ArrayHelper::getColumn( $tokenModel::find()
               ->where( [ 'type' => 'loginApi' ] )
               ->andWhere( [
                   '<',
                   'create_time',
                   $this->getOutTime( 'loginApi' )
               ] )
               ->select( [ 'token' ] )->asArray()->all(), 'token' );

			$outOtherList = ArrayHelper::getColumn( $tokenModel::find()
               ->where( [ 'type' => [ 'register', 'reset', 'login' ] ] )
               ->andWhere( [
                   '<',
                   'create_time',
                   $this->getOutTime( 'reset' )
               ] )
               ->select( [ 'token' ] )->asArray()->all(), 'token' );

			$outList = ArrayHelper::merge( $outLoginList, $outOtherList );
			unset( $outLoginList, $outOtherList );
		}
		if ( ! empty( $outList ) ) {
			$tokenModel::deleteAll( [ 'token' => $outList ] );
		}
		unset( $outList, $tokenModel );
	}

	protected function getOutTime( $type ) {
		return time() - Yii::$app->params[ ( $type == 'loginApi' ? 'user.identityByAccessTokenExpire' : 'user.verificationCodeTokenExpire' ) ];
	}
}