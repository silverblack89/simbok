<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "verification".
 *
 * @property int $id
 * @property string $unit_id
 * @property int $program_id
 * @property string $modul
 * @property string $catatan
 * @property string $perbaikan
 */
class Verification extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'verification';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['program_id', 'revisi', 'perbaikan'], 'integer'],
            [['catatan'], 'string'],
            [['unit_id'], 'string', 'max' => 15],
            [['modul'], 'string', 'max' => 1],
            [['unit_id', 'program_id', 'modul'], 'unique', 'targetAttribute' => ['unit_id', 'program_id', 'modul']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'unit_id' => 'Unit ID',
            'program_id' => 'Program ID',
            'modul' => 'Modul',
            'revisi' => 'Revisi',
            'catatan' => 'Catatan',
            'perbaikan' => 'Perbaikan',
        ];
    }
}
