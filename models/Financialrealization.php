<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "financial_realization".
 *
 * @property int $id
 * @property int $activity_detail_id
 * @property int $bulan
 * @property int $realisasi_vol_1
 * @property string $realisasi_satuan_1
 * @property int $realisasi_vol_2
 * @property string $realisasi_satuan_2
 * @property int $realisasi_unit_cost
 * @property double $realisasi_jumlah
 */
class Financialrealization extends \yii\db\ActiveRecord
{
    public $total_poa;
    public $total_realisasi;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'financial_realization';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['activity_detail_id', 'activity_detail_ubah_id', 'bulan'], 'required'],
            [['activity_detail_id', 'activity_detail_ubah_id', 'bulan'], 'integer'],
            [['realisasi_jumlah', 'realisasi_vol_1', 'realisasi_vol_2'], 'number'],
            [['realisasi_satuan_1', 'realisasi_satuan_2', 'realisasi_unit_cost'], 'string', 'max' => 50],
            [['activity_detail_id', 'bulan'], 'unique', 'targetAttribute' => ['activity_detail_id', 'bulan']],
            [['total_realisasi'], 'compare', 'compareValue' => str_replace(".", "", $this->total_poa), 'operator' => '<=' ,'message'=>Yii::t('app','Total realisasi tidak boleh melebihi Total pada POA (Rp. '.number_format($this->total_poa, 0, ',', '.').'). ')],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'activity_detail_id' => 'Activity Detail ID',
            'activity_detail_ubah_id' => 'Activity Detail Ubah ID',
            'bulan' => 'Bulan',
            'realisasi_vol_1' => 'Realisasi Vol 1',
            'realisasi_satuan_1' => 'Realisasi Satuan 1',
            'realisasi_vol_2' => 'Realisasi Vol 2',
            'realisasi_satuan_2' => 'Realisasi Satuan 2',
            'realisasi_unit_cost' => 'Realisasi Unit Cost',
            'realisasi_jumlah' => 'Realisasi Jumlah',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Place your custom code here
            $this->realisasi_unit_cost = str_replace(".", "", $this->realisasi_unit_cost);
            $this->realisasi_vol_1 = str_replace(".", "", $this->realisasi_vol_1);

            if ($this->realisasi_vol_2 == null){
                $this->realisasi_jumlah = $this->realisasi_vol_1 * str_replace(".", ",", $this->realisasi_unit_cost);
                $this->realisasi_satuan_2 = null;
            }else{
                $this->realisasi_vol_2 = str_replace(".", "", $this->realisasi_vol_2);
                $this->realisasi_jumlah = $this->realisasi_vol_1 * $this->realisasi_vol_2 * str_replace(".", "", $this->realisasi_unit_cost);
            }

            return true;
        } else {
            return false;
        }
    }
}
