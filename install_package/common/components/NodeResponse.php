<?php
/**
 * @copyright
 * @link 
 * @create Created on 2018/4/4
 */

namespace common\components;


/**
 * Response
 *
 * @author 
 * @since 1.0
 */
class NodeResponse extends \yii\httpclient\Response
{
    /**
     * Detects response format from raw content.
     * @param string $content raw response content.
     * @return null|string format name, 'null' - if detection failed.
     */
    protected function detectFormatByContent($content)
    {
        if (preg_match('/^\\{.*\\}$/is', $content)) {
            return Client::FORMAT_JSON;
        }
        if (preg_match('/^([^=&])+=[^=&]+(&[^=&]+=[^=&]+)*$/', $content)) {
            return Client::FORMAT_URLENCODED;
        }
        if (preg_match('/^<.*>$/s', $content)) {
            return Client::FORMAT_XML;
        }
        // new
        if (strpos($content, "callback(") === 0) {
            $count = 0;
            $jsonData = preg_replace('/^callback\(\s*(\\{.*\\})\s*\);$/is', '\1', $content, 1, $count);
            if ($count === 1) {
                return Client::FORMAT_JSON;
            }
        }

        return null;
    }

}