<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/9/20
 */

namespace common\helpers;

use common\components\BaseModel;
use Yii;


/**
 * 系统帮助类
 *
 * @author 
 * @since 1.0
 */
class SystemHelper extends BaseModel
{

	/**
	 * 判断是否启用redis
	 */
	static public function isEnableRedis(){
		return Yii::$app->components['cache']['class'] == 'yii\redis\Cache';
	}

    /**
     * 获取数据库名称
     * @return string
     */
    static public function getDbName(){
        $dbname = '';
        foreach (explode(';',Yii::$app->getDb()->dsn) as $item){
            $tmp = explode('=',$item);
            if($tmp[0] == 'dbname'){
                $dbname = $tmp[1];
                break;
            }
        }
        return $dbname;
    }

	/**
	 * 获取表的 auto_increment 自增长值
	 *
	 * @param $domain object
	 *
	 * @return mixed
	 * @throws \yii\db\Exception
	 */
    static public function getTableAutoIncrement($domain){
        $sql = "SELECT auto_increment FROM information_schema.tables WHERE table_schema='".self::getDbName()."' and table_name='".$domain::getTableSchema()->fullName."';";
        return intval(ArrayHelper::getValue(Yii::$app->getDb()->createCommand($sql)->queryOne(),'auto_increment',1));
    }
}