<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Deptdatareal;

/**
 * DeptdatarealSearch represents the model behind the search form of `app\models\Deptdatareal`.
 */
class DeptdatarealSearch extends Deptdatareal
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'dept_sub_activity_detail_id'], 'integer'],
            [['tanggal', 'nomor'], 'safe'],
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
        $query = Deptdatareal::find();

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
            'dept_sub_activity_detail_id' => $this->dept_sub_activity_detail_id,
            'tanggal' => $this->tanggal,
            'jumlah' => $this->jumlah,
        ]);

        $query->andFilterWhere(['like', 'nomor', $this->nomor]);

        return $dataProvider;
    }
}
