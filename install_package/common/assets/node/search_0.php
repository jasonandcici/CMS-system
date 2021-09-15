<?php
/**
 * This is the template for generating the model class of a specified table.
 */
use common\helpers\ArrayHelper;

/* @var $model  */
/* @var $fields  */

echo "<?php\n";
$modelName = ucwords($model->name);
$int = $number = $string = [];
foreach ($fields as $item){
    if($item->type == 'relation_data' && $item->setting['relationType']) continue;
    if($item->type == 'captcha') continue;
    if($item->field_type == 'int'){
        $int[] = "'".$item->name."'";
    }elseif($item->field_type == 'decimal'){
        $number[] = "'".$item->name."'";
    }else{
        $string[] = "'".$item->name."'";
    }
}
?>

namespace common\entity\nodes;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
* <?=$modelName?>Search represents the model behind the search form about `common\entity\nodes\<?=$modelName?>Model`.
*/
class <?=$modelName?>Search extends <?=$modelName?>Model
{

    public $searchStartTime;
    public $searchEndTime;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','searchStartTime','searchEndTime', 'model_id', 'category_id', 'sort', 'status', 'is_push', 'is_comment', 'views','jump_link', 'update_time', 'create_time','site_id','is_login',<?=implode(',',$int)?>], 'integer'],
            [['title', 'template_content', 'seo_title', 'seo_keywords', 'seo_description','layouts','count_user_relations',<?=implode(',',$string)?>], 'safe'],
            <?php
            if(!empty($number)){
                echo "[[".implode(',',$number)."], 'number'],";
            }?>

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
        $query = <?=$modelName?>Model::find();

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
            <?php foreach ($int as $item){
                echo $item." => \$this->".str_replace("'",'',$item).",\n        ";
            }?>
            <?php foreach ($number as $item){
                echo $item." => \$this->".str_replace("'",'',$item).",\n        ";
            }?>

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
        <?php foreach ($string as $item){
            echo "->andFilterWhere(['like', ".$item.", \$this->".str_replace("'",'',$item)."])\n        ";
        }?>

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