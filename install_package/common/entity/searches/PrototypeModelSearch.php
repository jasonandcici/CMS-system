<?php

namespace common\entity\searches;

use common\entity\models\PrototypeModelModel;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * PrototypeModelSearch represents the model behind the search form about `common\entity\domains\PrototypeModelModel`.
 */
class PrototypeModelSearch extends PrototypeModelModel
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type','is_login','is_generate','is_login_download','is_login_category'], 'integer'],
            [['title', 'name', 'description','extend_code','setting','filter_sensitive_words_fields'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = PrototypeModelModel::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => array_key_exists('page_size',Yii::$app->params)?Yii::$app->params['page_size']:15,
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
            'is_login'=>$this->is_login,
            'is_login_download'=>$this->is_login_download,
            'is_login_category'=>$this->is_login_category,
            'is_generate'=>$this->is_generate
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'filter_sensitive_words_fields', $this->filter_sensitive_words_fields])
            ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
