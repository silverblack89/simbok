<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Deptperfomance;

/**
 * DeptperfomanceSearch represents the model behind the search form of `app\models\Deptperfomance`.
 */
class DeptperfomanceSearch extends Deptperfomance
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'triwulan', 'dept_sub_activity_data_id', 'target_awal', 'target_real'], 'integer'],
            [['tahun', 'satuan_awal', 'satuan_real'], 'safe'],
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
        $query = Deptperfomance::find();

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
            'triwulan' => $this->triwulan,
            'tahun' => $this->tahun,
            'dept_sub_activity_data_id' => $this->dept_sub_activity_data_id,
            'target_awal' => $this->target_awal,
            'target_real' => $this->target_real,
        ]);

        $query->andFilterWhere(['like', 'satuan_awal', $this->satuan_awal])
            ->andFilterWhere(['like', 'satuan_real', $this->satuan_real]);

        return $dataProvider;
    }
}
