<?php

/**
 * 控制器基类
 * request请求get传入参数：render=0 表示不渲染视图文件
 */

namespace common\components;

use common\entity\models\SystemConfigModel;
use common\helpers\FileHelper;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use ZipArchive;

class BaseController extends Controller
{
    /**
     * @var array 系统配置数据
     */
    public $config;

    /**
     * @var object 站点信息
     */
    public $siteInfo;

    /**
     * 初始化
     */
    public function init()
    {
        parent::init();

        // 获取网站配置
        $this->config = SystemConfigModel::findConfig();

        // 配置邮件
        Yii::$app->getMailer()->htmlLayout = false;
        Yii::$app->getMailer()->transport = [
            'class' => 'Swift_SmtpTransport',
            'host' => $this->config['email']['host'],
            'username' => $this->config['email']['username'],
            'password' => $this->config['email']['password'],
            'port' => $this->config['email']['port'],
            'encryption' => $this->config['email']['encryption']?:'tls',
        ];
    }

    /**
     * 系统消息跳转
     *
     * Yii::$app->params["message_success_tpl"] 成功信息模板
     * Yii::$app->params["message_error_tpl"] 错误信息模板
     * Yii::$app->params["message_reader_method"] 渲染视图方法模板
     * Yii::$app->params["message_reader_layout"] 渲染视图方法布局
     *
     * @param array $params 键名有：
     *  0 string 消息标题
     *  message mixed 信息内容 默认为null
     *  jumpLink string 跳转链接，默认为“javascript:history.back(-1);”
     *  waitTime int 跳转链接等待跳转时间，success：1，error：3
     * @param bool $isReturn 是否返回数据
     * @return mixed
     */

    public function success($params = array(),$isReturn = false)
    {
        if($isReturn){
            return $this->messageHandle(1, $params,$isReturn);
        }

        $this->messageHandle(1, $params,$isReturn);
    }

    public function error($params = array(),$isReturn = false)
    {
        if($isReturn){
            return $this->messageHandle(0, $params,$isReturn);
        }
        $this->messageHandle(0, $params,$isReturn);
    }

    private function messageHandle($status = 1, $params = array(),$isReturn = false)
    {
        if (array_key_exists('status', $params)) unset($params['status']);
        if (array_key_exists('title', $params)) unset($params['title']);

        $paramsDefault = array_merge(array(
            'title' => array_key_exists(0, $params) && is_string($params[0]) ? $params[0] : ($status ? Yii::t('common', 'Operation successful') : Yii::t('common', 'Operation failed')),
            'message' => null,
            'status' => $status,
            'waitTime' => $status ? 2 : 3,
            'jumpLink' => 'javascript:history.back(-1);'
        ), $params);

        unset($paramsDefault[0]);

        if(!$isReturn){
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $render = json_encode($paramsDefault);
            } else {
                $params = Yii::$app->params;
                $successTpl = array_key_exists('message_success_tpl', $params) ? $params['message_success_tpl'] : '//site/message';
                $errorTpl = array_key_exists('message_error_tpl', $params) ? $params['message_error_tpl'] : '//site/message';

                if(array_key_exists('message_reader_layout', $params)) $this->layout = Yii::$app->params["message_reader_layout"];
                $readerMethod = array_key_exists('message_reader_method', $params) ? $params['message_reader_method'] : 'render';

                $readerView = $status ? $successTpl : $errorTpl;
                $render = $this->$readerMethod($readerView, $paramsDefault);
            }
            exit($render);
        }else{
            return $params;
        }
    }

    /**
     * 根据对象返回一个类名（不包含命名空间）
     * @param $object
     * @return mixed
     */
    public function getClassName($object){
        $tem = explode('\\',get_class($object));
        return $tem[count($tem)-1];
    }
}