<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Ukpagu;

/**
 * UkpaguSearch represents the model behind the search form of `app\models\Ukpagu`.
 */
class UkpaguSearch extends Ukpagu
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'uk_id', 'jumlah'], 'integer'],
            [['unit_id'], 'safe'],
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
        $query = Ukpagu::find();

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
            'uk_id' => $this->uk_id,
            'jumlah' => $this->jumlah,
        ]);

        $query->andFilterWhere(['like', 'unit_id', $this->unit_id]);

        return $dataProvider;
    }
}
