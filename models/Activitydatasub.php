<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "activity_data_sub".
 *
 * @property int $id
 * @property string $tahun
 * @property string|null $nama
 * @property string $keterangan
 */
class Activitydatasub extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'activity_data_sub';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tahun', 'keterangan'], 'required'],
            [['tahun'], 'safe'],
            [['keterangan'], 'string'],
            [['nama'], 'string', 'max' => 100],
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
            'nama' => 'Nama',
            'keterangan' => 'Keterangan',
        ];
    }
}
