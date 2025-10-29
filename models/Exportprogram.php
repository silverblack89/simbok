<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "export_program".
 *
 * @property int $id
 * @property string $nama_program
 * @property string $nama_pelayanan
 * @property string $nama_kegiatan
 * @property string $bentuk_kegiatan
 * @property string $sasaran
 * @property string $target
 * @property string $lokasi
 * @property string $pelaksana
 * @property string $nama_rekening
 * @property int $vol_1
 * @property string $satuan_1
 * @property int $vol_2
 * @property string $satuan_2
 * @property double $vol
 * @property int $unit_cost
 * @property double $jumlah
 * @property string $jan
 * @property string $feb
 * @property string $mar
 * @property string $apr
 * @property string $mei
 * @property string $jun
 * @property string $jul
 * @property string $agu
 * @property string $sep
 * @property string $okt
 * @property string $nov
 * @property string $des
 */
class Exportprogram extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'export_program';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vol_1', 'vol_2', 'unit_cost', 'vol_1_awal', 'vol_2_awal', 'unit_cost_awal'], 'integer'],
            [['vol', 'jumlah', 'vol_awal', 'jumlah_awal'], 'number'],
            [['unit', 'nama_rekening', 'sasaran', 'satuan', 'target', 'lokasi', 'pelaksana'], 'string', 'max' => 255],
            [['nama_program', 'nama_pelayanan', 'nama_kegiatan', 'bentuk_kegiatan'], 'string'],
            [['satuan_1', 'satuan_2', 'satuan_1_awal', 'satuan_2_awal'], 'string', 'max' => 50],
            [['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'], 'string', 'max' => 1],
            [['period'], 'safe'],
            [['username'], 'string', 'max' => 100],
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
            'nama_pelayanan' => 'Nama Pelayanan',
            'nama_kegiatan' => 'Nama Kegiatan',
            'bentuk_kegiatan' => 'Bentuk Kegiatan',
            'sasaran' => 'Sasaran',
            'target' => 'Target',
            'satuan' => 'Satuan',
            'lokasi' => 'Lokasi',
            'pelaksana' => 'Pelaksana',
            'nama_rekening' => 'Nama Rekening',
            'vol_1' => 'Vol 1',
            'satuan_1' => 'Satuan 1',
            'vol_2' => 'Vol 2',
            'satuan_2' => 'Satuan 2',
            'vol' => 'Vol',
            'unit_cost' => 'Unit Cost',
            'jumlah' => 'Jumlah',
            'jan' => 'Jan',
            'feb' => 'Feb',
            'mar' => 'Mar',
            'apr' => 'Apr',
            'mei' => 'Mei',
            'jun' => 'Jun',
            'jul' => 'Jul',
            'agu' => 'Agu',
            'sep' => 'Sep',
            'okt' => 'Okt',
            'nov' => 'Nov',
            'des' => 'Des',
            'vol_1_awal' => 'Vol 1 Awal',
            'satuan_1_awal' => 'Satuan 1 Awal',
            'vol_2_awal' => 'Vol 2 Awal',
            'satuan_2_awal' => 'Satuan 2 Awal',
            'vol_awal' => 'Vol Awal',
            'unit_cost_awal' => 'Unit Cost Awal',
            'jumlah_awal' => 'Jumlah Awal',
            'username' => 'Username',
            'period' => 'Period',
            'unit' => 'Unit',
        ];
    }
}
