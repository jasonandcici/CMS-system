<?php

namespace common\entity\searches;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\entity\models\SiteModel;

/**
 * SiteSearch represents the model behind the search form about `common\entity\models\SiteModel`.
 */
class SiteSearch extends SiteModel
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'is_enable', 'is_default', 'enable_mobile'], 'integer'],
            [['title', 'slug', 'domain', 'theme', 'logo', 'language','devices_width'], 'safe'],
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
        $query = SiteModel::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'is_enable' => $this->is_enable,
            'is_default' => $this->is_default,
            'enable_mobile' => $this->enable_mobile,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'slug', $this->slug])
            ->andFilterWhere(['like', 'domain', $this->domain])
            ->andFilterWhere(['like', 'theme', $this->theme])
            ->andFilterWhere(['like', 'logo', $this->logo])
            ->andFilterWhere(['like', 'devices_width', $this->devices_width])
            ->andFilterWhere(['like', 'language', $this->language]);

        return $dataProvider;
    }
}
