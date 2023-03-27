<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "unit".
 *
 * @property string $id
 * @property string $puskesmas
 * @property string $kecamatan
 * @property string $kepala
 * @property string $jabatan_kepala
 * @property string $nip_kepala
 * @property string $petugas
 * @property string $jabatan_petugas
 * @property string $nip_petugas
 * @property string $jenis_puskesmas
 * @property string $telepon_puskesmas
 *
 * @property User[] $users
 */
class Unit extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'unit';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'string', 'max' => 15],
            [['puskesmas', 'kecamatan'], 'string', 'max' => 200],
            [['kepala', 'jabatan_kepala', 'petugas', 'jabatan_petugas', 'jenis_puskesmas'], 'string', 'max' => 100],
            [['nip_kepala', 'nip_petugas'], 'string', 'max' => 50],
            [['telepon_puskesmas'], 'string', 'max' => 20],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Kode',
            'puskesmas' => 'Instansi',
            'kecamatan' => 'Kecamatan',
            'kepala' => 'Kepala',
            'jabatan_kepala' => 'Jabatan Kepala',
            'nip_kepala' => 'Nip Kepala',
            'petugas' => 'Petugas',
            'jabatan_petugas' => 'Jabatan Petugas',
            'nip_petugas' => 'Nip Petugas',
            'jenis_puskesmas' => 'Jenis',
            'telepon_puskesmas' => 'Telepon',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['unit_id' => 'id']);
    }
}
