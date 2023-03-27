<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "export_perfomance".
 *
 * @property int $id
 * @property string $nama_program
 * @property string $indikator
 * @property int $target
 * @property int $anggaran
 * @property string $kinerja_1
 * @property int $keuangan_nilai_1
 * @property string $keuangan_persen_1
 * @property string $kinerja_2
 * @property int $keuangan_nilai_2
 * @property string $keuangan_persen_2
 * @property string $kinerja_3
 * @property int $keuangan_nilai_3
 * @property string $keuangan_persen_3
 * @property string $kinerja_4
 * @property int $keuangan_nilai_4
 * @property string $keuangan_persen_4
 * @property string $total_kinerja
 * @property int $total_keuangan_nilai
 * @property string $total_keuangan_persen
 */
class Exportperfomance extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'export_perfomance';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['target', 'anggaran', 'keuangan_nilai_1', 'keuangan_nilai_2', 'keuangan_nilai_3', 'keuangan_nilai_4', 'total_keuangan_nilai'], 'integer'],
            [['kinerja_1', 'keuangan_persen_1', 'kinerja_2', 'keuangan_persen_2', 'kinerja_3', 'keuangan_persen_3', 'kinerja_4', 'keuangan_persen_4', 'total_kinerja', 'total_keuangan_persen'], 'number'],
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
            'kinerja_1' => 'Kinerja 1',
            'keuangan_nilai_1' => 'Keuangan Nilai 1',
            'keuangan_persen_1' => 'Keuangan Persen 1',
            'kinerja_2' => 'Kinerja 2',
            'keuangan_nilai_2' => 'Keuangan Nilai 2',
            'keuangan_persen_2' => 'Keuangan Persen 2',
            'kinerja_3' => 'Kinerja 3',
            'keuangan_nilai_3' => 'Keuangan Nilai 3',
            'keuangan_persen_3' => 'Keuangan Persen 3',
            'kinerja_4' => 'Kinerja 4',
            'keuangan_nilai_4' => 'Keuangan Nilai 4',
            'keuangan_persen_4' => 'Keuangan Persen 4',
            'total_kinerja' => 'Total Kinerja',
            'total_keuangan_nilai' => 'Total Keuangan Nilai',
            'total_keuangan_persen' => 'Total Keuangan Persen',
        ];
    }
}
