<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Deptsubactivitydetail;

/**
 * DeptsubactivitydetailSearch represents the model behind the search form of `app\models\Deptsubactivitydetail`.
 */
class DeptsubactivitydetailSearch extends Deptsubactivitydetail
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'dept_sub_activity_data_id', 'account_id', 'vol_1', 'vol_2', 'vol_3', 'vol_4', 'unit_cost', 'tw1', 'tw2', 'tw3'], 'integer'],
            [['satuan_1', 'satuan_2', 'satuan_3', 'satuan_4'], 'safe'],
            [['jumlah'], 'number'],
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
        $query = Deptsubactivitydetail::find();

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
            'dept_sub_activity_data_id' => $this->dept_sub_activity_data_id,
            'account_id' => $this->account_id,
            'vol_1' => $this->vol_1,
            'vol_2' => $this->vol_2,
            'unit_cost' => $this->unit_cost,
            'jumlah' => $this->jumlah,
            'tw1' => $this->tw1,
            'tw2' => $this->tw2,
            'tw3' => $this->tw3,
        ]);

        $query->andFilterWhere(['like', 'satuan_1', $this->satuan_1])
            ->andFilterWhere(['like', 'satuan_2', $this->satuan_2]);

        return $dataProvider;
    }
}
