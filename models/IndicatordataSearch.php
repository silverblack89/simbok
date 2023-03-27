<?php

namespace app\models;

use yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Indicatordata;
use yii\web\Session;

/**
 * IndicatordataSearch represents the model behind the search form of `app\models\Indicatordata`.
 */
class IndicatordataSearch extends Indicatordata
{
    public $indicator;
    public $program;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'indicator_id', 'bulan'], 'integer'],
            [['kinerja'], 'number'],
            [['indicator', 'program'], 'safe'],
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
        $query = Indicatordata::find();

        // add conditions that should always apply here
        $session = Yii::$app->session;
        $query->joinWith(['indicator']);
        $query->andWhere(['program_id' => $session['programId']]);

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
            'indicator_id' => $this->indicator_id,
            'bulan' => $this->bulan,
            'kinerja' => $this->kinerja,
        ]);

        return $dataProvider;
    }
}
