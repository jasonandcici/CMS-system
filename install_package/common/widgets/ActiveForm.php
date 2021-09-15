<?php
/**
 * @copyright
 * @link 
 * @create Created on 2017/9/6
 */

namespace common\widgets;


use Yii;
use yii\helpers\Json;

/**
 * ActiveFormWidget
 *
 * @author 
 * @since 1.0
 */
class ActiveForm extends \yii\widgets\ActiveForm{

    /**
     * This registers the necessary JavaScript code.
     * @since 2.0.12
     */
    public function registerClientScript()
    {
        $id = $this->options['id'];
        $options = Json::htmlEncode($this->getClientOptions());
        $attributes = Json::htmlEncode($this->attributes);
        $view = $this->getView();
        ActiveFormAsset::register($view);
        $view->registerJs("jQuery('#$id').yiiActiveForm($attributes, $options);");
    }

}