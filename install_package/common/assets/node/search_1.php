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
            [['id','model_id', 'status','create_time','searchStartTime','searchEndTime','site_id',<?=implode(',',$int)?>], 'integer'],
            <?php
            if(!empty($string)){
                echo "[[".implode(',',$string)."], 'safe'],";
            }?>
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
            'model_id'=>$this->model_id,
            'site_id'=>$this->site_id,
            <?php foreach ($int as $item){
                echo $item." => \$this->".str_replace("'",'',$item).",\n        ";
            }?>
            <?php foreach ($number as $item){
                echo $item." => \$this->".str_replace("'",'',$item).",\n        ";
            }?>

            'status' => $this->status,
            'create_time' => $this->create_time,
        ]);

        $query<?php
        foreach ($string as $item){
            echo "->andFilterWhere(['like', ".$item.", \$this->".str_replace("'",'',$item)."])\n        ";
        }
        ?>->andFilterCompare('create_time',$this->searchStartTime,'>')
        ->andFilterCompare('create_time',$this->searchEndTime?$this->searchEndTime+3600*24:null,'<');

        return $dataProvider;
    }
}