<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Deptfinancialrealization;

/**
 * DeptfinancialrealizationSearch represents the model behind the search form of `app\models\Deptfinancialrealization`.
 */
class DeptfinancialrealizationSearch extends Deptfinancialrealization
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'dept_sub_activity_detail_id', 'dept_sub_activity_detail_ubah_id', 'bulan', 'realisasi_vol_1', 'realisasi_vol_2', 'realisasi_vol_3', 'realisasi_vol_4', 'realisasi_unit_cost'], 'integer'],
            [['realisasi_satuan_1', 'realisasi_satuan_2', 'realisasi_satuan_3', 'realisasi_satuan_4'], 'safe'],
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
        $query = Deptfinancialrealization::find();

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
            'dept_sub_activity_detail_id' => $this->dept_sub_activity_detail_id,
            'dept_sub_activity_detail_ubah_id' => $this->dept_sub_activity_detail_ubah_id,
            'bulan' => $this->bulan,
            'realisasi_vol_1' => $this->realisasi_vol_1,
            'realisasi_vol_2' => $this->realisasi_vol_2,
            'realisasi_vol_3' => $this->realisasi_vol_3,
            'realisasi_vol_4' => $this->realisasi_vol_4,
            'realisasi_unit_cost' => $this->realisasi_unit_cost,
            'realisasi_jumlah' => $this->realisasi_jumlah,
        ]);

        $query->andFilterWhere(['like', 'realisasi_satuan_1', $this->realisasi_satuan_1])
            ->andFilterWhere(['like', 'realisasi_satuan_2', $this->realisasi_satuan_2])
            ->andFilterWhere(['like', 'realisasi_satuan_3', $this->realisasi_satuan_3])
            ->andFilterWhere(['like', 'realisasi_satuan_4', $this->realisasi_satuan_4]);

        return $dataProvider;
    }
}
