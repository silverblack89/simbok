<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Deptsubactivity;

/**
 * DeptsubactivitySearch represents the model behind the search form of `app\models\Deptsubactivity`.
 */
class DeptsubactivitySearch extends Deptsubactivity
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'dept_activity_id', 'pagu', 'aktif'], 'integer'],
            [['nama_sub_kegiatan', 'kode_rekening', 'status'], 'safe'],
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
        $query = Deptsubactivity::find();

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
            'dept_activity_id' => $this->dept_activity_id,
            'aktif' => $this->aktif,
        ]);

        $query->andFilterWhere(['like', 'kode_rekening', $this->kode_rekening])
        ->andFilterWhere(['like', 'nama_sub_kegiatan', $this->nama_sub_kegiatan])
        ->andFilterWhere(['like', 'status', $this->aktif]);

        return $dataProvider;
    }
}
