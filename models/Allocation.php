<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "allocation".
 *
 * @property int $id
 * @property string $tahun
 * @property int $ukm
 * @property int $covid
 */
class Allocation extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'allocation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tahun'], 'required'],
            [['tahun'], 'safe'],
            [['ukm', 'covid', 'insentif'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tahun' => 'Tahun',
            'ukm' => 'Ukm',
            'covid' => 'Covid',
            'insentif' => 'Insentif'
        ];
    }
}
