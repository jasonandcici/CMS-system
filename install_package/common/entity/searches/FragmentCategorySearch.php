<?php

namespace common\entity\searches;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\entity\models\FragmentCategoryModel;

/**
 * FragmentCategorySearch represents the model behind the search form about `common\entity\models\FragmentCategoryModel`.
 */
class FragmentCategorySearch extends FragmentCategoryModel
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'site_id', 'type', 'sort', 'enable_sub_title', 'enable_thumb', 'multiple_thumb', 'enable_attachment', 'multiple_attachment', 'enable_ueditor', 'enable_link', 'is_disabled_opt','is_global'], 'integer'],
            [['title','slug'], 'safe'],
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
        $query = FragmentCategoryModel::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => array_key_exists('page_size',Yii::$app->params)?Yii::$app->params['page_size']:15,
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

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'site_id' => $this->site_id,
            'type' => $this->type,
            'sort' => $this->sort,
            'enable_sub_title' => $this->enable_sub_title,
            'enable_thumb' => $this->enable_thumb,
            'multiple_thumb' => $this->multiple_thumb,
            'enable_attachment' => $this->enable_attachment,
            'multiple_attachment' => $this->multiple_attachment,
            'enable_ueditor' => $this->enable_ueditor,
            'enable_link' => $this->enable_link,
            'is_disabled_opt' => $this->is_disabled_opt,
            'is_global' => $this->is_global,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'slug', $this->slug]);

        return $dataProvider;
    }
}
