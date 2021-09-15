<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2016/7/31
 * Time: 20:01
 */

namespace common\components;


use yii\base\Arrayable;
use yii\data\DataProviderInterface;
use yii\rest\Serializer;

class ApiSerializerBase extends Serializer
{
    /**
     * @var string
     */
    public $collectionEnvelope = 'items';

    /**
     * @var string DataProvider数据处理前
     */
    public $afterSerializeModel;

    /**
     * @var string DataProvider数据处理后
     */
    public $afterSerializeDataProvider;


    /**
     * Serializes a data provider.
     * @param DataProviderInterface $dataProvider
     * @return array the array representation of the data provider.
     */
    protected function serializeDataProvider($dataProvider)
    {

        $models = $this->serializeModels($dataProvider->getModels());

        if($this->afterSerializeDataProvider !== null){
            $models = call_user_func($this->afterSerializeDataProvider, $models);
        }

        if (($pagination = $dataProvider->getPagination()) !== false) {
            $this->addPaginationHeaders($pagination);
        }

        if ($this->request->getIsHead()) {
            return null;
        } elseif ($this->collectionEnvelope === null) {
            return $models;
        } else {
            $result = [
                $this->collectionEnvelope => $models,
            ];
            if ($pagination !== false) {
                return array_merge($result, $this->serializePagination($pagination));
            } else {
                return $result;
            }
        }
    }

    /**
     * Serializes a model object.
     * @param Arrayable $model
     * @return array the array representation of the model
     */
    protected function serializeModel($model)
    {
        if ($this->request->getIsHead()) {
            return null;
        } else {
            list ($fields, $expand) = $this->getRequestedFields();
            $data = $model->toArray($fields, $expand);

            if($this->afterSerializeModel !== null){
                $data = call_user_func($this->afterSerializeModel, $data);
            }

            return $data;
        }
    }
}