<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Accountaccess;

/**
 * AccountaccessSearch represents the model behind the search form of `app\models\Accountaccess`.
 */
class AccountaccessSearch extends Accountaccess
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'activity_id', 'account_id'], 'integer'],
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
        $query = Accountaccess::find();

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
            'activity_id' => $this->activity_id,
            'account_id' => $this->account_id,
        ]);

        return $dataProvider;
    }
}
