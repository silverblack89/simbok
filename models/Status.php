<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "status".
 *
 * @property int $id
 * @property string $modul_1
 * @property string $modul_2
 * @property string $modul_3
 * @property string $modul_4
 * @property string $tahun
 * @property string $unit_id
 */
class Status extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tahun'], 'safe'],
            [['modul_1', 'modul_2', 'modul_3', 'modul_4'], 'string', 'max' => 1],
            [['unit_id'], 'string', 'max' => 15],
            [['modul_1', 'modul_2', 'modul_3', 'modul_4', 'tahun', 'unit_id'], 'unique', 'targetAttribute' => ['modul_1', 'modul_2', 'modul_3', 'modul_4', 'tahun', 'unit_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'modul_1' => 'Modul 1',
            'modul_2' => 'Modul 2',
            'modul_3' => 'Modul 3',
            'modul_4' => 'Modul 4',
            'tahun' => 'Tahun',
            'unit_id' => 'Unit ID',
        ];
    }
}
