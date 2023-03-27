<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Unit;

/**
 * UnitSearch represents the model behind the search form of `app\models\Unit`.
 */
class UnitSearch extends Unit
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'puskesmas', 'kecamatan', 'kepala', 'jabatan_kepala', 'nip_kepala', 'petugas', 'jabatan_petugas', 'nip_petugas', 'jenis_puskesmas', 'telepon_puskesmas'], 'safe'],
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
        $query = Unit::find();

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
        $query->andFilterWhere(['like', 'id', $this->id])
            ->andFilterWhere(['like', 'puskesmas', $this->puskesmas])
            ->andFilterWhere(['like', 'kecamatan', $this->kecamatan])
            ->andFilterWhere(['like', 'kepala', $this->kepala])
            ->andFilterWhere(['like', 'jabatan_kepala', $this->jabatan_kepala])
            ->andFilterWhere(['like', 'nip_kepala', $this->nip_kepala])
            ->andFilterWhere(['like', 'petugas', $this->petugas])
            ->andFilterWhere(['like', 'jabatan_petugas', $this->jabatan_petugas])
            ->andFilterWhere(['like', 'nip_petugas', $this->nip_petugas])
            ->andFilterWhere(['like', 'jenis_puskesmas', $this->jenis_puskesmas])
            ->andFilterWhere(['like', 'telepon_puskesmas', $this->telepon_puskesmas]);

        return $dataProvider;
    }
}
