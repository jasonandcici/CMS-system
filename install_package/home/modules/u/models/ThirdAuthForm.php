<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/12/20
 */

namespace home\modules\u\models;

use common\components\BaseModel;
use common\entity\models\RedisAuthTokenModel;
use common\entity\models\SystemConfigModel;
use common\entity\models\UserAuthTokenModel;
use common\entity\models\UserModel;
use common\entity\models\UserProfileModel;
use common\entity\models\UserThirdAccountModel;
use common\helpers\ArrayHelper;
use common\helpers\FileHelper;
use common\helpers\SequenceNumberHelper;
use common\helpers\SystemHelper;
use Faker\Provider\Uuid;
use Yii;
use yii\db\Exception;


/**
 * 第三方账号绑定
 *
 * @author 
 * @since 1.0
 */
class ThirdAuthForm extends BaseModel
{

	/**
	 * 第三方登录
	 *
	 * @param $client
	 * @param bool $isApi
	 *
	 * @return array|bool
	 * @throws \yii\base\Exception
	 */
    public function auth($client,$isApi = false){
    	$userId = Yii::$app->getUser()->getId();
    	if($isApi && $slug = Yii::$app->getRequest()->get('slug')){
		    $tokenModel = SystemHelper::isEnableRedis()?new RedisAuthTokenModel():new UserAuthTokenModel();
		    $token = $tokenModel::find()->where(['token'=>base64_decode($slug),'type'=>'loginApi'])->asArray()->one();
    		if($token){
    			$userId = $token['value'];
		    }
	    }

        $data = [];

        $attributes = $client->userAttributes;
        switch ($client->id){
            case 'qq':
                $data = [
                    'user_id'=>$userId,
                    'client_id'=>$client->id,
                    'open_id'=>$attributes['id'],
                    'token'=>null,
                    'raw_data'=>json_encode($attributes),
                    'nickname'=>$attributes['nickname'],
                    'avatar'=>empty($attributes['figureurl_qq_2'])?$attributes['figureurl_qq_1']:$attributes['figureurl_qq_2'],

                    'gender'=>$attributes['gender'] == '女'?'female':'male',
                    'birthday'=>null,
                    'blood'=>null,
                    'country'=>null,
                    'province'=>null,
                    'city'=>null,
                    'area'=>null,
                    'street'=>null,
                    'signature'=>null,
                ];
                break;
            case 'weibo':
                if(!empty($attributes['location'])){
                    $attributes['location'] = explode(' ',$attributes['location']);
                    if(in_array($attributes['location'][0],['北京','天津','上海','重庆'])){
                        $attributes['province'] = $attributes['location'][0];
                        $attributes['city'] = $attributes['location'][0];
                        $attributes['area'] = ArrayHelper::getValue($attributes['location'],1);
                    }else{
                        $attributes['province'] = $attributes['location'][0];
                        $attributes['city'] = ArrayHelper::getValue($attributes['location'],1);
                        $attributes['area'] = null;
                    }
                }else{
                    $attributes['province'] = null;
                    $attributes['city'] = null;
                    $attributes['area'] = null;
                }
                $data = [
                    'user_id'=>$userId,
                    'client_id'=>$client->id,
                    'open_id'=>$attributes['idstr'],
                    'token'=>null,
                    'raw_data'=>json_encode($attributes),
                    'nickname'=>$attributes['name'],
                    'avatar'=>$attributes['avatar_hd'],
                    'gender'=>$attributes['gender']?($attributes['gender']=='m'?'male':'female'):'secrecy',
                    'birthday'=>null,
                    'blood'=>null,
                    'country'=>$attributes['lang']=='zh-cn'?'中国':'其他',
                    'province'=>$attributes['province'],
                    'city'=>$attributes['city'],
                    'area'=>$attributes['area'],
                    'street'=>null,
                    'signature'=>$attributes['description'],
                ];
                break;
            case 'wechat':
                if(in_array($attributes['province'],['北京','天津','上海','重庆'])){
                    $attributes['area'] = $attributes['city'];
                    $attributes['city'] = $attributes['province'];
                }else{
                    $attributes['area'] = null;
                }

                $data = [
                    'user_id'=>$userId,
                    'client_id'=>$client->id,
                    'open_id'=>$attributes['openid'],
                    'token'=>$attributes['unionid'],
                    'raw_data'=>json_encode($attributes),
                    'nickname'=>$attributes['nickname'],
                    'avatar'=>$attributes['headimgurl'],
                    'gender'=>$attributes['sex'] == 2?'female':'male',
                    'birthday'=>null,
                    'blood'=>null,
                    'country'=>$attributes['country'] == 'CN'?'中国':'其他',
                    'province'=>$attributes['province'],
                    'city'=>$attributes['city'],
                    'area'=>$attributes['area'],
                    'street'=>null,
                    'signature'=>null,
                ];
                break;
        }

        if(empty($data)){
            $this->addError($client->id,'系统不支持第三方账户“'.$client->id.'”。');
            return false;
        }

        return Yii::$app->getUser()->getIsGuest()?$this->thirdLogin($data,$isApi):$this->bind($data,$isApi);
    }

	/**
	 * 第三方方登录
	 *
	 * @param $data
	 * @param $isApi
	 *
	 * @return bool
	 * @throws Exception
	 * @throws \yii\base\Exception
	 */
    public function thirdLogin($data,$isApi){
        $thirdInfo = UserThirdAccountModel::find()->where(['client_id'=>$data['client_id'],'open_id'=>$data['open_id']])->one();
        if($thirdInfo){
            $userInfo = UserModel::findOne($thirdInfo->user_id);
            $loginForm = new LoginForm();
            $resLogin = $loginForm->login($userInfo,$isApi);

            return $isApi?$resLogin:true;
        }else{
            $config = SystemConfigModel::findConfig();
            if(in_array('third',$config['member']['registerMode'])){
                // 保存图片
	            if(!empty($data['avatar'])){
		            if(strpos($data['avatar'],'http') === 0 || strpos($data['avatar'],'//') === 0){
			            $avatar = FileHelper::uploadRemote($data['avatar']);
			            $avatar = $avatar[0];
			            if($avatar['status']){
				            $data['avatar'] = json_encode([['file'=>$avatar['file'],'alt'=>$data['nickname']]]);
			            }
			            unset($avatar);
		            }elseif (strpos($data['avatar'],'[{') !== 0){
			            $data['avatar'] = json_encode([['file'=>$data['avatar'],'alt'=>$data['nickname']]]);
		            }
	            }

                // 注册用户
                $res = false;
                $model = new UserModel();
                $transaction= Yii::$app->db->beginTransaction();
                try {
                    $model->username = Uuid::uuid();
                    $model->create_time = time();
                    $model->auth_key = Yii::$app->getSecurity()->generateRandomString();

                    if($model->save()){
                        $db = Yii::$app->getDb();
                        $sql = '';
                        if($this->getScenario() != 'username'){
                            $model->username = 'u_'.SequenceNumberHelper::get($model->primaryKey,8);
                            $sql .= $db->createCommand()->update(UserModel::tableName(),['username'=>$model->username],['id'=>$model->primaryKey])->rawSql.';';
                        }

                        // 用户资料
                        $sql .= $db->createCommand()->insert(UserProfileModel::tableName(),[
                                'user_id'=>$model->primaryKey,
                                'nickname'=>$data['nickname'],
                                'avatar'=>$data['avatar'],
                                'gender'=>in_array($data['gender'],['male','female','secrecy'])?$data['gender']:'secrecy',
                                'birthday'=>$data['birthday'],
                                'blood'=>$data['blood'],
                                'country'=>$data['country'],
                                'province'=>$data['province'],
                                'city'=>$data['city'],
                                'area'=>$data['area'],
                                'street'=>$data['street'],
                                'signature'=>$data['signature'],
                            ])->rawSql.';';

                        // 绑定
                        $sql .= $db->createCommand()->insert(UserThirdAccountModel::tableName(),[
                                'user_id'=>$model->primaryKey,
                                'client_id'=>$data['client_id'],
                                'open_id'=>$data['open_id'],
                                'token'=>$data['token'],
                                'raw_data'=>$data['raw_data'],
                            ])->rawSql.';';

                        $db->createCommand($sql)->execute();
                        $loginForm = new LoginForm();
	                    $resLogin = $loginForm->login($model,$isApi);

	                    $res = $isApi?$resLogin:true;
                    }else{
                        $this->addErrors($model->getErrors());
                        $transaction->rollBack();
                    }
                    $transaction->commit();
                } catch(Exception $e){
                    $transaction->rollBack();
                }
                return $res;
            }else{
                $this->addError('third','第三方注册已关闭。');
                return false;
            }
        }
    }

	/**
	 * 账号绑定
	 *
	 * @param $data
	 * @param $isApi
	 *
	 * @return bool
	 */
    protected function bind($data,$isApi)
    {
        $model = new UserThirdAccountModel();
        $model->user_id = $data['user_id'];
        $model->client_id = $data['client_id'];
        $model->open_id = $data['open_id'];
        $model->token = $data['token'];
        $model->raw_data = $data['raw_data'];
        if($model->validate() && $model->save()){
            return true;
        }else{
            $this->addErrors($model->getErrors());
            return false;
        }
    }

}