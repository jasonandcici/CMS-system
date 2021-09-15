<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/10/15
 */

namespace common\jobs;

use common\helpers\ArrayHelper;
use common\libs\dysmsapi\Sms;
use Exception;
use Qcloud\Sms\SmsSingleSender;
use Yii;
use yii\base\Component;
use yii\db\StaleObjectException;

/**
 * 邮件发送队列
 *
 * @author 
 * @since 1.0
 */
class SmsJob extends Component implements \yii\queue\JobInterface{

	/**
	 * @var string 发送类型
	 */
	public $sendType;

	/**
	 * @var array 系统配置项
	 */
	public $config;

	/**
	 * @var string 验证码
	 */
	public $code;

	/**
	 * @var int 手机号前缀
	 */
	public $cellphone_code;

	/**
	 * @var string 要发送的地址，即手机号或邮箱
	 */
	public $account;

	/**
	 * 执行队列
	 *
	 * @param \yii\queue\Queue $queue
	 *
	 * @throws StaleObjectException
	 * @throws \Throwable
	 */
	public function execute($queue)
	{
		try {
			if($this->sendType == 'email'){

				Yii::$app->getMailer()->htmlLayout = false;
				Yii::$app->getMailer()->transport = [
					'class' => 'Swift_SmtpTransport',
					'host' => $this->config['host'],
					'username' => $this->config['username'],
					'password' => $this->config['password'],
					'port' => $this->config['port'],
					'encryption' => $this->config['encryption']?:'tls',
				];

				$mail = Yii::$app->getMailer()->compose();
				$mail->setFrom($this->config['username']);
				$mail->setTo($this->account);
				$mail->setSubject('【'.$this->config['site_name']."】验证码邮件");
				$html = '【'.$this->config['site_name'].'】您的验证码是<strong style="color: red;">'.$this->code.'</strong>，有效期为'.(Yii::$app->params['user.verificationCodeTokenExpire']/60).'分钟。';
				$mail->setHtmlBody($html);
				if(!$mail->send()){
					$this->error('发送邮件失败');
				}
			}else{
				if($this->config['enable'] === 1){
					$sms = new Sms($this->config['appid'],$this->config['appkey']);

					$response = $sms->sendSms(
						$this->cellphone_code=='0086'?$this->config['signName']:$this->config['signNameAbroad'],
						$this->cellphone_code=='0086'?$this->config['tplCode']:$this->config['tplCodeAbroad'],
						($this->cellphone_code=='0086'?'':$this->cellphone_code).$this->account,
						["code"=>$this->code]
					);
					if($response->Code !== 'OK'){
						$this->error(ArrayHelper::getValue([
							'isp.RAM_PERMISSION_DENY'=>'RAM权限DENY',
							'isv.OUT_OF_SERVICE'=>'业务停机',
							'isv.PRODUCT_UN_SUBSCRIPT'=>'未开通云通信产品的阿里云客户',
							'isv.PRODUCT_UNSUBSCRIBE'=>'产品未开通',
							'isv.ACCOUNT_NOT_EXISTS'=>'账户不存在',
							'isv.ACCOUNT_ABNORMAL'=>'账户异常',
							'isv.SMS_TEMPLATE_ILLEGAL'=>'短信模板不合法',
							'isv.SMS_SIGNATURE_ILLEGAL'=>'短信签名不合法',
							'isv.INVALID_PARAMETERS'=>'参数异常',
							'isp.SYSTEM_ERROR'=>'系统错误',
							'isv.MOBILE_NUMBER_ILLEGAL'=>'非法手机号',
							'isv.MOBILE_COUNT_OVER_LIMIT'=>'手机号码数量超过限制',
							'isv.TEMPLATE_MISSING_PARAMETERS'=>'模板缺少变量',
							'isv.BUSINESS_LIMIT_CONTROL'=>'业务限流',
							'isv.INVALID_JSON_PARAM'=>'JSON参数不合法，只接受字符串值',
							'isv.BLACK_KEY_CONTROL_LIMIT'=>'黑名单管控',
							'isv.PARAM_LENGTH_LIMIT'=>'参数超出长度限制',
							'isv.PARAM_NOT_SUPPORT_URL'=>'不支持URL',
							'isv.AMOUNT_NOT_ENOUGH'=>'账户余额不足'
						],$response->Code,'短信发送失败'));
					}
				}else{
					$sender = new SmsSingleSender($this->config['appid'],$this->config['appkey']);
					$result = $sender->sendWithParam(
						$this->cellphone_code,
						$this->account,
						$this->cellphone_code=='0086'?$this->config['tplCode']:$this->config['tplCodeAbroad'],
						[$this->code, (Yii::$app->params['user.verificationCodeTokenExpire']/60)]
						, "", "", "");
					$res = json_decode($result);
					if($res->result !== 0){
						$this->error($res->errmsg);
					}
				}
			}
		} catch(Exception $e){
			$this->error('系统发送信息失败。');
		}
	}

	/**
	 * 错误
	 *
	 * @param $message
	 *
	 * @throws StaleObjectException
	 * @throws \Throwable
	 */
	private function error($message){
		//$tokenModel = SystemHelper::isEnableRedis()?new XzshRedisAuthTokenModel():new UserAuthTokenModel();
		//$tokenModel::deleteAll(['token'=>md5(($this->sendType == 'email'?'':$this->cellphone_code.'-').$this->account)]);

		file_put_contents(Yii::$app->getBasePath().'/../home/runtime/logs/sms'.date('Ym',time()).'.log',$message."\r\n",FILE_APPEND);
	}

}