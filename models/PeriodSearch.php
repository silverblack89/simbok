<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Period;

/**
 * PeriodSearch represents the model behind the search form of `app\models\Period`.
 */
class PeriodSearch extends Period
{
    public $year;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'pagu', 'pagu_geser'], 'integer'],
            [['unit_id', 'tahun', 'year'], 'safe'],
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
        $query = Period::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['unit_id'=>SORT_ASC]]
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
            'tahun' => $this->tahun,
            'pagu' => $this->pagu,
            'pagu_geser' => $this->pagu_geser,
        ]);

        $query->andFilterWhere(['like', 'unit_id', $this->unit_id])
            ->andFilterWhere(['like', 'tahun', $this->year]);

        return $dataProvider;
    }
}
