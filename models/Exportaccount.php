<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "export_account".
 *
 * @property int $no
 * @property string $program
 * @property int $premi_bpjs
 * @property int $premi_ketenagakerjaan
 * @property int $transportasi_akomodasi
 * @property int $hadiah
 * @property int $tenaga_kontrak
 * @property int $honorar_narsum
 * @property int $sewa_mobil_darat
 * @property int $sspd_luar_daerah
 * @property int $sspd_dalam_daerah
 * @property int $perangko_materai
 * @property int $jasa_transaksi_keuangan
 * @property int $penggandaan
 * @property int $cetak
 * @property int $atk
 * @property int $bahan_habis_pakai
 * @property int $makan_minum_kegiatan
 * @property int $jpk
 * @property int $bbm
 * @property int $internet_pulsa
 * @property double $jumlah
 * @property string $username
 * @property string $login_time
 */
class Exportaccount extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'export_account';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['no', 'program_id', 'premi_bpjs', 'premi_ketenagakerjaan', 'transportasi_akomodasi', 'hadiah', 'tenaga_kontrak', 'honorar_narsum', 'sewa_mobil_darat', 'sspd_luar_daerah', 'sspd_dalam_daerah', 'perangko_materai', 'jasa_transaksi_keuangan', 'penggandaan', 'cetak', 'atk', 'bahan_habis_pakai', 'makan_minum_kegiatan', 'jpk', 'bbm', 'internet_pulsa'], 'integer'],
            [['jumlah'], 'number'],
            [['period'], 'safe'],
            [['program'], 'string'],
            [['username'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'no' => 'No',
            'program_id' => 'Program ID',
            'program' => 'Program',
            'premi_bpjs' => 'Premi Bpjs',
            'premi_ketenagakerjaan' => 'Premi Ketenagakerjaan',
            'transportasi_akomodasi' => 'Transportasi Akomodasi',
            'hadiah' => 'Hadiah',
            'tenaga_kontrak' => 'Tenaga Kontrak',
            'honorar_narsum' => 'Honorar Narsum',
            'sewa_mobil_darat' => 'Sewa Mobil Darat',
            'sspd_luar_daerah' => 'Sspd Luar Daerah',
            'sspd_dalam_daerah' => 'Sspd Dalam Daerah',
            'perangko_materai' => 'Perangko Materai',
            'jasa_transaksi_keuangan' => 'Jasa Transaksi Keuangan',
            'penggandaan' => 'Penggandaan',
            'cetak' => 'Cetak',
            'atk' => 'Atk',
            'bahan_habis_pakai' => 'Bahan Habis Pakai',
            'makan_minum_kegiatan' => 'Makan Minum Kegiatan',
            'jpk' => 'Jpk',
            'bbm' => 'Bbm',
            'internet_pulsa' => 'Internet/ Pulsa',
            'jumlah' => 'Jumlah',
        ];
    }
}
