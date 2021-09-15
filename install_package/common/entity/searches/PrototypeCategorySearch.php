<?php

namespace common\entity\searches;

use common\entity\models\PrototypeCategoryModel;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * PrototypeCategorySearch represents the model behind the search form about `common\entity\domains\PrototypeCategoryModel`.
 */
class PrototypeCategorySearch extends PrototypeCategoryModel
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'pid', 'model_id', 'type', 'sort', 'status','site_id','enable_tag','enable_push','is_login','is_login_content','is_comment'], 'integer'],
            [['title','sub_title', 'slug_rules', 'slug','slug_rules_detail','layouts', 'layouts_content','link', 'thumb', 'content', 'template', 'template_content', 'seo_title', 'seo_keywords', 'seo_description','system_mark'], 'safe'],
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
        $query = PrototypeCategoryModel::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 0,
            ],
            'sort' => [
                'defaultOrder' => [
                    'sort'=>SORT_ASC,
                    'id' => SORT_ASC,
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
            'pid' => $this->pid,
            'model_id' => $this->model_id,
            'type' => $this->type,
            'sort' => $this->sort,
            'site_id'=>$this->site_id,
            'status' => $this->status,
            'enable_tag' => $this->enable_tag,
            'enable_push' => $this->enable_push,
            'is_login_content' => $this->is_login_content,
            'is_login' => $this->is_login,
            'is_comment' => $this->is_comment,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like','sub_title',$this->sub_title])
            ->andFilterWhere(['like', 'slug_rules', $this->slug_rules])
            ->andFilterWhere(['like', 'slug_rules_detail', $this->slug_rules_detail])
            ->andFilterWhere(['like', 'slug', $this->slug])
            ->andFilterWhere(['like', 'link', $this->link])
            ->andFilterWhere(['like', 'thumb', $this->thumb])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'template', $this->template])
            ->andFilterWhere(['like', 'template_content', $this->template_content])
            ->andFilterWhere(['like', 'seo_title', $this->seo_title])
            ->andFilterWhere(['like', 'seo_keywords', $this->seo_keywords])
            ->andFilterWhere(['like', 'layouts', $this->layouts])
            ->andFilterWhere(['like', 'layouts_content', $this->layouts_content])
            ->andFilterWhere(['like', 'system_mark', $this->system_mark])
            ->andFilterWhere(['like', 'seo_description', $this->seo_description]);

        return $dataProvider;
    }
}
