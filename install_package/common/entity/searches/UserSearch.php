<?php

namespace common\entity\searches;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\entity\models\UserModel;

/**
 * UserSearch represents the model behind the search form about `common\entity\models\UserModel`.
 */
class UserSearch extends UserModel
{
    public $nickname = null;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'is_enable', 'create_time'], 'integer'],
            [['account_type', 'username', 'password', 'email', 'cellphone','cellphone_code','auth_key'], 'safe'],
            [['nickname'], 'safe','on'=>'userProfile'],
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
        $query = UserModel::find();

        // add conditions that should always apply here

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

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'is_enable' => $this->is_enable,
            'create_time' => $this->create_time,
        ]);

        $query->andFilterWhere(['like', 'account_type', $this->account_type])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'cellphone_code', $this->cellphone_code])
            ->andFilterWhere(['like', 'cellphone', $this->cellphone]);

        if($this->getScenario() === 'userProfile'){
            $query->andFilterWhere(['like', 'nickname', $this->nickname]);
        }

        return $dataProvider;
    }
}
