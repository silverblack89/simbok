<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Indicator;

/**
 * IndicatorSearch represents the model behind the search form of `app\models\Indicator`.
 */
class IndicatorSearch extends Indicator
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'program_id'], 'integer'],
            [['nama_indikator'], 'safe'],
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
        $query = Indicator::find();

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
            'program_id' => $this->program_id,
        ]);

        $query->andFilterWhere(['like', 'nama_indikator', $this->nama_indikator]);

        return $dataProvider;
    }
}
