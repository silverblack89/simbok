<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "export_perfomance_bln".
 *
 * @property int $id
 * @property string $nama_program
 * @property string $indikator
 * @property int $target
 * @property int $anggaran
 * @property string $kinerja_lalu
 * @property int $keuangan_nilai_lalu
 * @property string $keuangan_persen_lalu
 * @property string $kinerja_ini
 * @property int $keuangan_nilai_ini
 * @property string $keuangan_persen_ini
 * @property string $total_kinerja
 * @property int $total_keuangan_nilai
 * @property string $total_keuangan_persen
 */
class Exportperfomancebln extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'export_perfomance_bln';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['target', 'anggaran', 'keuangan_nilai_lalu', 'keuangan_nilai_ini', 'total_keuangan_nilai'], 'integer'],
            [['kinerja_lalu', 'keuangan_persen_lalu', 'kinerja_ini', 'keuangan_persen_ini', 'total_kinerja', 'total_keuangan_persen'], 'number'],
            [['nama_program', 'indikator'], 'string', 'max' => 255],
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
            'indikator' => 'Indikator',
            'target' => 'Target',
            'anggaran' => 'Anggaran',
            'kinerja_lalu' => 'Kinerja Lalu',
            'keuangan_nilai_lalu' => 'Keuangan Nilai Lalu',
            'keuangan_persen_lalu' => 'Keuangan Persen Lalu',
            'kinerja_ini' => 'Kinerja Ini',
            'keuangan_nilai_ini' => 'Keuangan Nilai Ini',
            'keuangan_persen_ini' => 'Keuangan Persen Ini',
            'total_kinerja' => 'Total Kinerja',
            'total_keuangan_nilai' => 'Total Keuangan Nilai',
            'total_keuangan_persen' => 'Total Keuangan Persen',
        ];
    }
}
