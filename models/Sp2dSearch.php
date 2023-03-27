<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Sp2d;

/**
 * Sp2dSearch represents the model behind the search form of `app\models\Sp2d`.
 */
class Sp2dSearch extends Sp2d
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'jumlah'], 'integer'],
            [['tanggal', 'no_sp2d', 'jenis_spm', 'uraian'], 'safe'],
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
        $query = Sp2d::find();

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
            'tanggal' => $this->tanggal,
            'jumlah' => $this->jumlah,
        ]);

        $query->andFilterWhere(['like', 'no_sp2d', $this->no_sp2d])
            ->andFilterWhere(['like', 'jenis_spm', $this->jenis_spm])
            ->andFilterWhere(['like', 'uraian', $this->uraian]);

        return $dataProvider;
    }
}
