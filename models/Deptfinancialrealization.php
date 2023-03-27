<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dept_financial_realization".
 *
 * @property int $id
 * @property int $dept_sub_activity_detail_id
 * @property int $dept_sub_activity_detail_ubah_id
 * @property int $bulan
 * @property int|null $realisasi_vol_1
 * @property string|null $realisasi_satuan_1
 * @property int|null $realisasi_vol_2
 * @property string|null $realisasi_satuan_2
 * @property int|null $realisasi_vol_3
 * @property string|null $realisasi_satuan_3
 * @property int|null $realisasi_vol_4
 * @property string|null $realisasi_satuan_4
 * @property int|null $realisasi_unit_cost
 * @property float|null $realisasi_jumlah
 */
class Deptfinancialrealization extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dept_financial_realization';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dept_sub_activity_detail_id', 'dept_sub_activity_detail_ubah_id', 'bulan', 'realisasi_vol_1', 'realisasi_vol_2', 'realisasi_vol_3', 'realisasi_vol_4', 'realisasi_unit_cost'], 'integer'],
            [['realisasi_jumlah'], 'number'],
            [['realisasi_satuan_1', 'realisasi_satuan_2', 'realisasi_satuan_3', 'realisasi_satuan_4'], 'string', 'max' => 50],
            [['dept_sub_activity_detail_id', 'dept_sub_activity_detail_ubah_id', 'bulan'], 'unique', 'targetAttribute' => ['dept_sub_activity_detail_id', 'dept_sub_activity_detail_ubah_id', 'bulan']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dept_sub_activity_detail_id' => 'Dept Sub Activity Detail ID',
            'dept_sub_activity_detail_ubah_id' => 'Dept Sub Activity Detail Ubah ID',
            'bulan' => 'Bulan',
            'realisasi_vol_1' => 'Realisasi Vol 1',
            'realisasi_satuan_1' => 'Realisasi Satuan 1',
            'realisasi_vol_2' => 'Realisasi Vol 2',
            'realisasi_satuan_2' => 'Realisasi Satuan 2',
            'realisasi_vol_3' => 'Realisasi Vol 3',
            'realisasi_satuan_3' => 'Realisasi Satuan 3',
            'realisasi_vol_4' => 'Realisasi Vol 4',
            'realisasi_satuan_4' => 'Realisasi Satuan 4',
            'realisasi_unit_cost' => 'Realisasi Unit Cost',
            'realisasi_jumlah' => 'Realisasi Jumlah',
        ];
    }
}
