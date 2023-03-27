<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "indicator".
 *
 * @property int $id
 * @property int $program_id
 * @property string $nama_indikator
 *
 * @property Program $program
 */
class Indicator extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'indicator';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['program_id'], 'required'],
            [['program_id', 'data_komulatif'], 'integer'],
            [['nama_indikator'], 'string', 'max' => 100],
            [['program_id'], 'exist', 'skipOnError' => true, 'targetClass' => Program::className(), 'targetAttribute' => ['program_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'program_id' => 'Program ID',
            'nama_indikator' => 'Nama Indikator',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProgram()
    {
        return $this->hasOne(Program::className(), ['id' => 'program_id']);
    }
}
