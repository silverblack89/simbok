<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Deptreal;

/**
 * DeptrealSearch represents the model behind the search form of `app\models\Deptreal`.
 */
class DeptrealSearch extends Deptreal
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'dept_sub_activity_id', 'dept_period_id', 'bulan', 'jumlah'], 'integer'],
            [['modified_at'], 'safe'],
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
        $query = Deptreal::find();

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
            'dept_sub_activity_id' => $this->dept_sub_activity_id,
            'dept_period_id' => $this->dept_period_id,
            'bulan' => $this->bulan,
            'jumlah' => $this->jumlah,
            'modified_at' => $this->modified_at,
        ]);

        return $dataProvider;
    }
}
