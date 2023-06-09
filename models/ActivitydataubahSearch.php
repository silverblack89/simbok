<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Activitydataubah;

/**
 * ActivitydataubahSearch represents the model behind the search form of `app\models\Activitydataubah`.
 */
class ActivitydataubahSearch extends Activitydataubah
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'activity_data_id', 'activity_id', 'period_id'], 'integer'],
            [['bentuk_kegiatan', 'sasaran', 'target', 'lokasi', 'pelaksana'], 'safe'],
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
        $query = Activitydataubah::find();

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
            'activity_data_id' => $this->activity_data_id,
            'activity_id' => $this->activity_id,
            'period_id' => $this->period_id,
        ]);

        $query->andFilterWhere(['like', 'bentuk_kegiatan', $this->bentuk_kegiatan])
            ->andFilterWhere(['like', 'sasaran', $this->sasaran])
            ->andFilterWhere(['like', 'target', $this->target])
            ->andFilterWhere(['like', 'lokasi', $this->lokasi])
            ->andFilterWhere(['like', 'pelaksana', $this->pelaksana]);

        return $dataProvider;
    }
}
