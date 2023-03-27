<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Financialrealization;

/**
 * FinancialrealizationSearch represents the model behind the search form of `app\models\Financialrealization`.
 */
class FinancialrealizationSearch extends Financialrealization
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'activity_detail_id', 'activity_detail_ubah_id', 'bulan', 'realisasi_vol_1', 'realisasi_vol_2', 'realisasi_unit_cost'], 'integer'],
            [['realisasi_jumlah'], 'number'],
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
        $query = Financialrealization::find();

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
            'activity_detail_id' => $this->activity_detail_id,
            'bulan' => $this->bulan,
            'realisasi_vol_1' => $this->realisasi_vol_1,
            'realisasi_vol_2' => $this->realisasi_vol_2,
            'realisasi_unit_cost' => $this->realisasi_unit_cost,
            'realisasi_jumlah' => $this->realisasi_jumlah,
        ]);

        return $dataProvider;
    }
}
