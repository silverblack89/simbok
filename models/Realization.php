<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "realization".
 *
 * @property int $id
 * @property int $triwulan
 * @property string $tahun
 * @property int $activity_detail_id
 * @property float $jumlah
 */
class Realization extends \yii\db\ActiveRecord
{
    public $sp2d;
    public $realisasi_lalu;
    public $total_realisasi;
    public $jml_poa;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'realization';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['triwulan', 'tahun', 'activity_detail_id', 'jumlah'], 'required'],
            [['triwulan', 'activity_detail_id'], 'integer'],
            [['tahun'], 'safe'],
            [['jumlah', 'sp2d', 'total_realisasi'], 'safe'],
            // [['total_realisasi'], 'compare', 'compareValue' => str_replace(".", "", $this->sp2d), 'operator' => '<=' ,'message'=>Yii::t('app','Total realisasi tidak boleh melebihi Total SP2D (Rp. '.number_format($this->jumlah, 0, ',', '.').'). ')],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'triwulan' => 'Triwulan',
            'tahun' => 'Tahun',
            'activity_detail_id' => 'Activity Detail ID',
            'jumlah' => 'Jumlah',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Place your custom code here
            $this->jumlah = str_replace(".", "", $this->jumlah);
            return true;
        } else {
            return false;
        }
    }
}
