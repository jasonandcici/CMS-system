<?php

namespace common\entity\searches;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\entity\models\CommentModel;

/**
 * CommentSearch represents the model behind the search form of `common\entity\models\CommentModel`.
 */
class CommentSearch extends CommentModel
{
	public $searchStartTime;
	public $searchEndTime;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'pid', 'category_id', 'data_id', 'user_id', 'is_enable', 'create_time','count_bad','count_like','searchStartTime','searchEndTime'], 'integer'],
            [['content', 'atlas'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
        $query = CommentModel::find();

        // add conditions that should always apply here

	    $dataProvider = new ActiveDataProvider([
		    'query' => $query,
		    'pagination' => [
			    'pageSize' => array_key_exists('page_size',Yii::$app->params)?Yii::$app->params['page_size']:15,
		    ],
		    'sort' => [
			    'defaultOrder' => [
				    'create_time' => SORT_DESC,
			    ]
		    ]
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
            'pid' => $this->pid,
            'category_id' => $this->category_id,
            'data_id' => $this->data_id,
            'user_id' => $this->user_id,
            'is_enable' => $this->is_enable,
            'create_time' => $this->create_time,
            'count_like' => $this->count_like,
            'count_bad' => $this->count_bad,
        ]);

        $query->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'atlas', $this->atlas])
	        ->andFilterCompare('create_time',$this->searchStartTime,'>')
	        ->andFilterCompare('create_time',$this->searchEndTime?$this->searchEndTime+3600*24:null,'<');

        return $dataProvider;
    }
}
