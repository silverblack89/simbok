<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dept_verification".
 *
 * @property int $id
 * @property string|null $unit_id
 * @property int|null $dept_program_id
 * @property string|null $modul
 * @property int $revisi
 * @property string $catatan
 * @property int $perbaikan
 */
class Deptverification extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dept_verification';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dept_program_id', 'revisi', 'perbaikan'], 'integer'],
            [['catatan'], 'required'],
            [['catatan'], 'string'],
            [['unit_id'], 'string', 'max' => 15],
            [['modul'], 'string', 'max' => 1],
            [['unit_id', 'dept_program_id', 'modul'], 'unique', 'targetAttribute' => ['unit_id', 'dept_program_id', 'modul']],
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
            'dept_program_id' => 'Dept Program ID',
            'modul' => 'Modul',
            'revisi' => 'Revisi',
            'catatan' => 'Catatan',
            'perbaikan' => 'Perbaikan',
        ];
    }
}
