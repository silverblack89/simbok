<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Activitydetailubah;

/**
 * ActivitydetailubahSearch represents the model behind the search form of `app\models\Activitydetailubah`.
 */
class ActivitydetailubahSearch extends Activitydetailubah
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'activity_detail_id', 'activity_data_id', 'account_id', 'vol_1', 'vol_2', 'unit_cost', 'jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'], 'integer'],
            [['rincian', 'satuan_1', 'satuan_2'], 'safe'],
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
        $query = Activitydetailubah::find();

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
            'activity_detail_id' => $this->activity_detail_id,
            'activity_data_id' => $this->activity_data_id,
            'account_id' => $this->account_id,
            'vol_1' => $this->vol_1,
            'vol_2' => $this->vol_2,
            'unit_cost' => $this->unit_cost,
            'jumlah' => $this->jumlah,
            'jan' => $this->jan,
            'feb' => $this->feb,
            'mar' => $this->mar,
            'apr' => $this->apr,
            'mei' => $this->mei,
            'jun' => $this->jun,
            'jul' => $this->jul,
            'agu' => $this->agu,
            'sep' => $this->sep,
            'okt' => $this->okt,
            'nov' => $this->nov,
            'des' => $this->des,
        ]);

        $query->andFilterWhere(['like', 'rincian', $this->rincian])
            ->andFilterWhere(['like', 'satuan_1', $this->satuan_1])
            ->andFilterWhere(['like', 'satuan_2', $this->satuan_2]);

        return $dataProvider;
    }
}
