<?php

namespace common\entity\searches;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\entity\models\EditorTemplateModel;

/**
 * EditorTemplateSearch represents the model behind the search form about `common\entity\models\EditorTemplateModel`.
 */
class EditorTemplateSearch extends EditorTemplateModel
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'category_id', 'sort','create_time','remote_id'], 'integer'],
            [['title', 'thumb', 'color', 'tags', 'content'], 'safe'],
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
        $query = EditorTemplateModel::find();

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
            'category_id' => $this->category_id,
            'sort' => $this->sort,
            'create_time' => $this->create_time,
            'remote_id' => $this->remote_id,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'thumb', $this->thumb])
            ->andFilterWhere(['like', 'color', $this->color])
            ->andFilterWhere(['like', 'tags', $this->tags])
            ->andFilterWhere(['like', 'content', $this->content]);

        return $dataProvider;
    }
}
