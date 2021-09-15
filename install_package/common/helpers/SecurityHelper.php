<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/7/24
 */

namespace common\helpers;

use common\entity\models\RedisAuthTokenModel;
use common\entity\models\SensitiveWordsModel;
use common\entity\models\UserAuthTokenModel;
use Yii;
use yii\base\Component;


/**
 * 字符串加密和解密
 *
 * @author 
 * @since 1.0
 */
class SecurityHelper extends Component
{

    /**
     * 加密
     * @param $data
     * @param $key
     * @return string
     */
    static public function encrypt($data, $key)
    {
        if(!is_string($data)) $data = (string)$data;

        $key    =   md5($key);
        $x      =   0;
        $len    =   strlen($data);
        $l      =   strlen($key);

        $char = '';
        $str = '';
        for ($i = 0; $i < $len; $i++)
        {
            if ($x == $l)
            {
                $x = 0;
            }
            $char .= $key{$x};
            $x++;
        }
        for ($i = 0; $i < $len; $i++)
        {
            $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
        }
        return base64_encode($str);
    }

    /**
     * 解密
     * @param $data
     * @param $key
     * @return string
     */
    static public function decrypt($data, $key)
    {
        $key = md5($key);
        $x = 0;
        $data = base64_decode($data);
        $len = strlen($data);
        $l = strlen($key);
        $str = '';
        $char = '';
        for ($i = 0; $i < $len; $i++)
        {
            if ($x == $l)
            {
                $x = 0;
            }
            $char .= substr($key, $x, 1);
            $x++;
        }
        for ($i = 0; $i < $len; $i++)
        {
            if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1)))
            {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            }
            else
            {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        return $str;
    }

    /**
     * 生成认证token
     * @param int $type token类型 'login'登陆,'loginApi','register'注册手机验证,'reset'重置密码手机验证
     * @param array $params type为register或reset必须值['account','verificationCode']，type为loginApi时必须值['user_id']
     * @return array
     * @throws \yii\base\Exception
     */
    static public function generateAuthToken($type,$params = [])
    {
        $result = true;
        $error = null;

	    $tokenModel = SystemHelper::isEnableRedis()?new RedisAuthTokenModel():new UserAuthTokenModel();
        $tokenModel->type = $type;
        $tokenModel->create_time = time();

        // api登陆授权
        if($type === 'loginApi'){
            $tokenModel->value = (string)$params['user_id'];
            $tokenModel->token = Yii::$app->getSecurity()->generateRandomString();

	        $tokenModel::deleteAll(['type'=>'loginApi','value'=>$tokenModel->value]);

            if(!$tokenModel->save()){
                $result = false;
                $error = $tokenModel->getErrors();
            }
        }
        // 验证码授权
        else{
            $tokenModel->token = md5($params['account']);
            $tokenModel->value = (string)$params['verificationCode'];

            // 查找是否已存在token
            $model = $tokenModel::findOne(['token'=>$tokenModel->token]);
            if($model){
                if($model->type == $type && time() - $model->create_time < 60){
                    $result = false;
                    $error = '操作过于频繁。';
                }else{
                    $model->value = $tokenModel->value;
                    $model->create_time = time();
	                $model->type = $type;
                    if(!$model->save()){
                        $result = false;
                        $error = $model->getErrors();
                    }
                }
            }else{
                if(!$tokenModel->save()){
                    $result = false;
                    $error = $tokenModel->getErrors();
                }
            }
        }

        return $result?['status'=>1,'token'=>$tokenModel->token]:['status'=>0,'error'=>$error];
    }

    /**
     * 验证token
     * @param int $type token类型 'login'登陆,'loginApi','register'注册手机验证,'reset'重置密码手机验证
     * @param $token
     * @return null|object
     */
    static public function validateAuthToken($token,$type){
	    $tokenModel = SystemHelper::isEnableRedis()?new RedisAuthTokenModel():new UserAuthTokenModel();
	    return $tokenModel::find()->where(['token'=>$token,'type'=>$type])->one();
    }

	/**
	 * 检测通过返回false
	 * @param string $content
	 * @param array $params
	 * @return array|bool|mixed|string
	 */
	static public function checkSensitiveWords($content = '',$params = []){
		if(empty($content)) return false;

		$params = ArrayHelper::merge([
			'global'=>true, // 是否全局检测
			'isReplace'=>false,
			'replace'=>[],// '敏感词'=>'替换词'
			'replaceStr'=>'*',
		],$params);


		$matches = [];
		foreach(SensitiveWordsModel::findSensitiveWords() as $item){
			if(strpos($content,$item) !== false){
				if($params['isReplace']){
					$content = str_replace($item,(array_key_exists($item,$params['replace'])?$params['replace'][$item]:$params['replaceStr']),$content);
				}else{
					$matches[] = $item;
				}

				if(!$params['global']) break;
			}
		}

		return $params['isReplace']?$content:(empty($matches)?false:$matches);
	}
}