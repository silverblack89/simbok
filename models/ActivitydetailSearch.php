<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Activitydetail;

/**
 * ActivitydetailSearch represents the model behind the search form of `app\models\Activitydetail`.
 */
class ActivitydetailSearch extends Activitydetail
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'activity_data_id', 'account_id'], 'integer'],
            [['rincian', 'jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'], 'safe'],
            [['vol_1', 'vol_2', 'vol_3', 'vol_4', 'unit_cost', 'jumlah'], 'number'],
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
        $query = Activitydetail::find();

        // add conditions that should always apply here
        $query->joinWith(['account']); // join with user table...
        $query->joinWith(['activityData']);

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
            'activity_data_id' => $this->activity_data_id,
            'account_id' => $this->account_id,
            'vol_1' => $this->vol_1,
            'vol_2' => $this->vol_2,
            'unit_cost' => $this->unit_cost,
            'jumlah' => $this->jumlah,
        ]);

        $query->andFilterWhere(['like', 'rincian', $this->rincian])
            ->andFilterWhere(['like', 'jan', $this->jan])
            ->andFilterWhere(['like', 'feb', $this->feb])
            ->andFilterWhere(['like', 'mar', $this->mar])
            ->andFilterWhere(['like', 'apr', $this->apr])
            ->andFilterWhere(['like', 'mei', $this->mei])
            ->andFilterWhere(['like', 'jun', $this->jun])
            ->andFilterWhere(['like', 'jul', $this->jul])
            ->andFilterWhere(['like', 'agu', $this->agu])
            ->andFilterWhere(['like', 'sep', $this->sep])
            ->andFilterWhere(['like', 'okt', $this->okt])
            ->andFilterWhere(['like', 'nov', $this->nov])
            ->andFilterWhere(['like', 'des', $this->des]);

        return $dataProvider;
    }
}
