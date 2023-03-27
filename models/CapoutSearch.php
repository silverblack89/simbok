<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Capout;

/**
 * CapoutSearch represents the model behind the search form of `app\models\Capout`.
 */
class CapoutSearch extends Capout
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'jml_ke', 'jml_confirm', 'tenaga_tracer', 'tenaga_surveilans', 'bulan'], 'integer'],
            [['nomor', 'unit_id'], 'safe'],
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
        $query = Capout::find();

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
            'jml_ke' => $this->jml_ke,
            'jml_confirm' => $this->jml_confirm,
            'tenaga_tracer' => $this->tenaga_tracer,
            'tenaga_surveilans' => $this->tenaga_surveilans,
            'bulan' => $this->bulan,
        ]);

        $query->andFilterWhere(['like', 'nomor', $this->nomor])
            ->andFilterWhere(['like', 'unit_id', $this->unit_id]);

        return $dataProvider;
    }
}
