<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Ukm;

/**
 * UkmSearch represents the model behind the search form of `app\models\Ukm`.
 */
class UkmSearch extends Ukm
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'bulan', 're_1', 're_2', 're_3', 're_4', 're_5', 're_6', 're_7', 're_8', 're_9', 're_10', 're_11', 're_12', 're_13', 're_14', 're_15', 're_16', 're_17', 're_18', 're_19', 're_20', 're_21', 're_22', 're_23', 're_24', 're_25', 're_26', 're_27', 're_28', 're_29', 're_30'], 'integer'],
            [['tahun', 'unit_id'], 'safe'],
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
        $query = Ukm::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false
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
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
            're_1' => $this->re_1,
            're_2' => $this->re_2,
            're_3' => $this->re_3,
            're_4' => $this->re_4,
            're_5' => $this->re_5,
            're_6' => $this->re_6,
            're_7' => $this->re_7,
            're_8' => $this->re_8,
            're_9' => $this->re_9,
            're_10' => $this->re_10,
            're_11' => $this->re_11,
            're_12' => $this->re_12,
            're_13' => $this->re_13,
            're_14' => $this->re_14,
            're_15' => $this->re_15,
            're_16' => $this->re_16,
            're_17' => $this->re_17,
            're_18' => $this->re_18,
            're_19' => $this->re_19,
            're_20' => $this->re_20,
            're_21' => $this->re_21,
            're_22' => $this->re_22,
            're_23' => $this->re_23,
            're_24' => $this->re_24,
            're_25' => $this->re_25,
            're_26' => $this->re_26,
            're_27' => $this->re_27,
            're_28' => $this->re_28,
            're_29' => $this->re_29,
            're_30' => $this->re_30,
        ]);

        $query->andFilterWhere(['like', 'unit_id', $this->unit_id]);

        return $dataProvider;
    }
}
