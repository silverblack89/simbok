<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "service".
 *
 * @property int $id
 * @property int $program_id
 * @property string $nama_pelayanan
 * @property string $aktif
 *
 * @property Activity[] $activities
 * @property Program $program
 */
class Service extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'service';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['program_id'], 'required'],
            [['program_id'], 'integer'],
            [['aktif'], 'string'],
            [['nama_pelayanan'], 'string'],
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
            'nama_pelayanan' => 'Nama Pelayanan',
            'aktif' => 'Aktif',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActivities()
    {
        return $this->hasMany(Activity::className(), ['service_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProgram()
    {
        return $this->hasOne(Program::className(), ['id' => 'program_id']);
    }
}
