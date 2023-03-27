<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "profile".
 *
 * @property int $id
 * @property string $nama
 * @property string $alamat
 * @property string $kota_kab
 * @property string $provinsi
 * @property string $telepon
 * @property string $kepala
 * @property string $jabatan_kepala
 * @property string $nip_kepala
 * @property string $sekretaris
 * @property string $jabatan_sekretaris
 * @property string $nip_sekretaris
 */
class Profile extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'profile';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nama', 'kota_kab', 'provinsi', 'telepon', 'kepala', 'jabatan_kepala', 'nip_kepala', 'sekretaris', 'jabatan_sekretaris', 'nip_sekretaris'], 'string', 'max' => 50],
            [['alamat'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nama' => 'Nama Instansi',
            'alamat' => 'Alamat',
            'kota_kab' => 'Kota/Kabupaten',
            'provinsi' => 'Provinsi',
            'telepon' => 'Telepon',
            'kepala' => 'Kepala Dinas',
            'jabatan_kepala' => 'Jabatan',
            'nip_kepala' => 'NIP',
            'sekretaris' => 'Sekretaris Dinas',
            'jabatan_sekretaris' => 'Jabatan',
            'nip_sekretaris' => 'NIP',
        ];
    }
}
