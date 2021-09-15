<?php

namespace common\entity\nodes;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
* NewsSearch represents the model behind the search form about `common\entity\nodes\NewsModel`.
*/
class NewsSearch extends NewsModel
{

    public $searchStartTime;
    public $searchEndTime;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','searchStartTime','searchEndTime', 'model_id', 'category_id', 'sort', 'status', 'is_push', 'is_comment', 'views','jump_link', 'update_time', 'create_time','site_id','is_login',], 'integer'],
            [['title', 'template_content', 'seo_title', 'seo_keywords', 'seo_description','layouts','count_user_relations','thumb','atlas','content','description','attachment'], 'safe'],
            
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
        $query = NewsModel::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => array_key_exists('page_size',Yii::$app->params)?Yii::$app->params['page_size']:15,
            ],
            'sort' => [
                'defaultOrder' => [
                    'sort' => SORT_DESC,
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
            'site_id'=>$this->site_id,
            'model_id' => $this->model_id,
            'category_id' => $this->category_id,
                        
            'sort' => $this->sort,
            'is_login' => $this->is_login,
            'status' => $this->status,
            'is_push' => $this->is_push,
            'is_comment' => $this->is_comment,
            'views' => $this->views,
            'update_time' => $this->update_time,
            'create_time' => $this->create_time,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
        ->andFilterWhere(['like', 'thumb', $this->thumb])
        ->andFilterWhere(['like', 'atlas', $this->atlas])
        ->andFilterWhere(['like', 'content', $this->content])
        ->andFilterWhere(['like', 'description', $this->description])
        ->andFilterWhere(['like', 'attachment', $this->attachment])
        
        ->andFilterWhere(['like', 'jump_link', $this->jump_link])
        ->andFilterWhere(['like', 'layouts', $this->layouts])
        ->andFilterWhere(['like', 'template_content', $this->template_content])
        ->andFilterWhere(['like', 'seo_title', $this->seo_title])
        ->andFilterWhere(['like', 'seo_keywords', $this->seo_keywords])
        ->andFilterWhere(['like', 'seo_description', $this->seo_description])
        ->andFilterCompare('create_time',$this->searchStartTime,'>')
        ->andFilterCompare('create_time',$this->searchEndTime?$this->searchEndTime+3600*24:null,'<');

        return $dataProvider;
    }
}