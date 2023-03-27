<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "capout".
 *
 * @property int $id
 * @property string $nomor
 * @property string $unit_id
 * @property int $bulan
 * @property int|null $jml_ke
 * @property int|null $jml_confirm
 * @property int|null $tenaga_tracer
 * @property int|null $tenaga_surveilans
 */
class Capout extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'capout';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nomor', 'unit_id', 'bulan'], 'required'],
            [['bulan', 'jml_ke', 'jml_confirm', 'tenaga_tracer', 'tenaga_surveilans'], 'integer'],
            [['nomor'], 'string', 'max' => 6],
            [['unit_id'], 'string', 'max' => 15],
            [['nomor', 'unit_id', 'bulan'], 'unique', 'targetAttribute' => ['nomor', 'unit_id', 'bulan']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nomor' => 'Nomor',
            'unit_id' => 'Unit ID',
            'bulan' => 'Bulan',
            'jml_ke' => 'Jml Ke',
            'jml_confirm' => 'Jml Confirm',
            'tenaga_tracer' => 'Tenaga Tracer',
            'tenaga_surveilans' => 'Tenaga Surveilans',
        ];
    }
}
