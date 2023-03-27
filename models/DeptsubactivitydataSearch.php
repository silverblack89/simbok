<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Deptsubactivitydata;

/**
 * DeptsubactivitydataSearch represents the model behind the search form of `app\models\Deptsubactivitydata`.
 */
class DeptsubactivitydataSearch extends Deptsubactivitydata
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'dept_sub_activity_id', 'dept_period_id'], 'integer'],
            [['bentuk_kegiatan', 'indikator_hasil', 'target_hasil', 'indikator_keluaran', 'target_keluaran'], 'safe'],
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
        $query = Deptsubactivitydata::find();

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
            'dept_sub_activity_id' => $this->dept_sub_activity_id,
            'dept_period_id' => $this->dept_period_id,
        ]);

        $query->andFilterWhere(['like', 'bentuk_kegiatan', $this->bentuk_kegiatan])
            ->andFilterWhere(['like', 'indikator_hasil', $this->indikator_hasil])
            ->andFilterWhere(['like', 'target_hasil', $this->target_hasil])
            ->andFilterWhere(['like', 'indikator_keluaran', $this->indikator_keluaran])
            ->andFilterWhere(['like', 'target_keluaran', $this->target_keluaran]);

        return $dataProvider;
    }
}
