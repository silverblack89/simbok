<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Deptprogram;

/**
 * DeptprogramSearch represents the model behind the search form of `app\models\Deptprogram`.
 */
class DeptprogramSearch extends Deptprogram
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'bok_id', 'pagu', 'aktif'], 'integer'],
            [['kode_rekening', 'nama_program', 'tahun'], 'safe'],
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
        $query = Deptprogram::find();

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
            'bok_id' => $this->bok_id,
            'tahun' => $this->tahun,
            'pagu' => $this->pagu,
            'aktif' => $this->aktif,
        ]);

        $query->andFilterWhere(['like', 'kode_rekening', $this->kode_rekening])
            ->andFilterWhere(['like', 'nama_program', $this->nama_program]);

        return $dataProvider;
    }
}
