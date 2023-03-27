<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Deptactivity;

/**
 * DeptactivitySearch represents the model behind the search form of `app\models\Deptactivity`.
 */
class DeptactivitySearch extends Deptactivity
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'dept_program_id', 'pagu', 'aktif'], 'integer'],
            [['kode_rekening', 'nama_kegiatan'], 'safe'],
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
        $query = Deptactivity::find();

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
            'dept_program_id' => $this->dept_program_id,
            'pagu' => $this->pagu,
            'aktif' => $this->aktif,
        ]);

        $query->andFilterWhere(['like', 'kode_rekening', $this->kode_rekening])
            ->andFilterWhere(['like', 'nama_kegiatan', $this->nama_kegiatan]);

        return $dataProvider;
    }
}
