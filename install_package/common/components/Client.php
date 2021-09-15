<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/4/4
 */

namespace common\components;
use Yii;
use yii\base\InvalidParamException;
use yii\httpclient\ParserInterface;


/**
 * Client
 *
 * @author 
 * @since 1.0
 */
class Client extends \yii\httpclient\Client
{

    /**
     * Returns HTTP message parser instance for the specified format.
     * @param string $format format name
     * @return ParserInterface parser instance.
     * @throws InvalidParamException on invalid format name.
     * @throws \yii\base\InvalidConfigException
     */
    public function getParser($format)
    {
        static $defaultParsers = [
            self::FORMAT_JSON => 'common\components\JsonParser',
            self::FORMAT_URLENCODED => 'yii\httpclient\UrlEncodedParser',
            self::FORMAT_RAW_URLENCODED => 'yii\httpclient\UrlEncodedParser',
            self::FORMAT_XML => 'yii\httpclient\XmlParser',
        ];

        if (!isset($this->parsers[$format])) {
            if (!isset($defaultParsers[$format])) {
                throw new InvalidParamException("Unrecognized format '{$format}'");
            }
            $this->parsers[$format] = $defaultParsers[$format];
        }

        if (!is_object($this->parsers[$format])) {
            $this->parsers[$format] = Yii::createObject($this->parsers[$format]);
        }

        return $this->parsers[$format];
    }

    /**
     * Creates a response instance.
     * @param string $content raw content
     * @param array $headers headers list.
     * @return object
     * @throws \yii\base\InvalidConfigException
     */
    public function createResponse($content = null, array $headers = [])
    {
        $config = $this->responseConfig;
        if (!isset($config['class'])) {
            $config['class'] = NodeResponse::className();
        }
        $config['client'] = $this;
        $response = Yii::createObject($config);
        $response->setContent($content);
        $response->setHeaders($headers);
        return $response;
    }

}