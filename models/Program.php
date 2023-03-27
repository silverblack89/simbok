<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "program".
 *
 * @property int $id
 * @property string $nama_program
 * @property string $tahun
 * @property int $aktif
 *
 * @property Service[] $services
 */
class Program extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'program';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tahun'], 'safe'],
            [['tahun'], 'required'],
            [['aktif', 'covid', 'akses', 'detail'], 'integer'],
            [['nama_program'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nama_program' => 'Nama Program',
            'tahun' => 'Tahun',
            'aktif' => 'Aktif',
            'covid' => 'Covid',
            'akses' => 'Akses',
            'detail' => 'Detail',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServices()
    {
        return $this->hasMany(Service::className(), ['program_id' => 'id']);
    }
}
