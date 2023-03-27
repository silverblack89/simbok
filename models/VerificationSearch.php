<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Verification;

/**
 * VerificationSearch represents the model behind the search form of `app\models\Verification`.
 */
class VerificationSearch extends Verification
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'program_id'], 'integer'],
            [['unit_id', 'modul'], 'safe'],
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
        $query = Verification::find();

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
            'program_id' => $this->program_id,
        ]);

        $query->andFilterWhere(['like', 'unit_id', $this->unit_id])
            ->andFilterWhere(['like', 'modul', $this->modul]);

        return $dataProvider;
    }
}
