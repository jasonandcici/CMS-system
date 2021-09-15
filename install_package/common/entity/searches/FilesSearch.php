<?php

namespace common\entity\searches;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\entity\models\FilesModel;

/**
 * FilesSearch represents the model behind the search form about `common\entity\models\FilesModel`.
 */
class FilesSearch extends FilesModel
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'sort', 'create_time'], 'integer'],
            [['width', 'height','size'], 'double'],
            [['title', 'username', 'file', 'extension', 'path', 'filename','type', 'category_id'], 'safe'],
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
        $query = FilesModel::find();

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
        if($this->category_id !== null) $query->andWhere(['category_id' => $this->category_id === ''?null:$this->category_id]);

        $query->andFilterWhere([
            'id' => $this->id,
            'sort' => $this->sort,
            'create_time' => $this->create_time,
            'width' => $this->width,
            'height' => $this->height,
            'type' => $this->type,
            'size' => $this->size,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'file', $this->file])
            ->andFilterWhere(['like', 'extension', $this->extension])
            ->andFilterWhere(['like', 'path', $this->path])
            ->andFilterWhere(['like', 'filename', $this->filename]);

        return $dataProvider;
    }
}
