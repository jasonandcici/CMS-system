<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/4/4
 */

namespace common\components;

use yii\helpers\Json;
use yii\httpclient\ParserInterface;
use yii\httpclient\Response;


/**
 * JsonParser
 *
 * @author 
 * @since 1.0
 */
class JsonParser extends \yii\httpclient\JsonParser implements ParserInterface
{

    /**
     * @inheritdoc
     */
    public function parse(Response $response)
    {
        $content = $response->getContent();

        // 解析腾讯变态的返回, 形如:
        // string(83) "callback( {"client_id":"101457101","openid":"7F5CAC7B7AC84594C506507B460653F7"} );"
        if (strpos($content, 'callback(') !== false) {
            $count = 0;
            $jsonData = preg_replace('/^callback\(\s*(\\{.*\\})\s*\);$/is', '\1', $content, 1, $count);
            if ($count === 1) {
                $content = $jsonData;
            }
        }

        return Json::decode($content);
    }
}