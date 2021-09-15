<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/10/23
 */

namespace common\entity\models;

use yii\redis\ActiveRecord;


/**
 * 在redis中存储用户授权信息
 * @property string $token
 * @property string $type
 * @property string $value
 * @property string $create_time
 * @property string $data
 *
 * @author 
 * @since 1.0
 */
class RedisAuthTokenModel extends ActiveRecord{

	/**
	 * 主键
	 *
	 * @return array|string[]
	 */
	public static function primaryKey()
	{
		return ['token'];
	}

	/**
	 * 属性
	 *
	 * @return array
	 */
	public function attributes()
	{
		return [
			"token",
			"type",
			"value",
			"create_time",
			"data"
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['token','type','value'], 'required'],
			[['create_time'], 'integer'],
			[['type'], 'in','range'=>['login','register','reset','loginApi']],
			[['token'], 'string', 'max' => 70],
			[['data'], 'string'],
			[['create_time'],'default','value'=>time()],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			"token"=>'Token',
			"type"=>'类型',
			"value"=>'值',
			"create_time"=>'创建时间',
			"data"=>'扩展数据',
		];
	}

}