<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ukm".
 *
 * @property int $id
 * @property int $bulan
 * @property string $tahun
 * @property string $unit_id
 * @property int $re_1
 * @property int $re_2
 * @property int $re_3
 * @property int $re_4
 * @property int $re_5
 * @property int $re_6
 * @property int $re_7
 * @property int $re_8
 * @property int $re_9
 * @property int $re_10
 * @property int $re_11
 * @property int $re_12
 * @property int $re_13
 * @property int $re_14
 * @property int $re_15
 * @property int $re_16
 * @property int $re_17
 * @property int $re_18
 * @property int $re_19
 * @property int $re_20
 * @property int $re_21
 * @property int $re_22
 * @property int $re_23
 * @property int $re_24
 * @property int $re_25
 * @property int $re_26
 * @property int $re_27
 * @property int $re_28
 * @property int $re_29
 * @property int $re_30
 */
class Ukm extends \yii\db\ActiveRecord
{
    public $total_realisasi_bulanan;
    public $total_sp2d_bulanan;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ukm';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['bulan', 'tahun', 'unit_id'], 'required'],
            [['bulan', 're_1', 're_2', 're_3', 're_4', 're_5', 're_6', 're_7', 're_8', 're_9', 're_10', 're_11', 're_12', 're_13', 're_14', 're_15', 're_16', 're_17', 're_18', 're_19', 're_20', 're_21', 're_22', 're_23', 're_24', 're_25', 're_26', 're_27', 're_28', 're_29', 're_30'], 'safe'],
            [['tahun'], 'safe'],
            [['unit_id'], 'string', 'max' => 15],
            // [['total_realisasi_bulanan'], 'integer'],
            // [['total_sp2d_bulanan'], 'integer'],
            // [['total_realisasi_bulanan'], 'compare', 'compareValue' => $this->total_sp2d_bulanan, 'operator' => '<=' ,'message'=>Yii::t('app','Total realisasi tidak boleh melebihi Total SP2D (Rp. '.number_format($this->total_sp2d_bulanan, 0, ',', '.').'). ')],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bulan' => 'Bulan',
            'tahun' => 'Tahun',
            'unit_id' => 'Unit ID',
            're_1' => 'Re 1',
            're_2' => 'Re 2',
            're_3' => 'Re 3',
            're_4' => 'Re 4',
            're_5' => 'Re 5',
            're_6' => 'Re 6',
            're_7' => 'Re 7',
            're_8' => 'Re 8',
            're_9' => 'Re 9',
            're_10' => 'Re 10',
            're_11' => 'Re 11',
            're_12' => 'Re 12',
            're_13' => 'Re 13',
            're_14' => 'Re 14',
            're_15' => 'Re 15',
            're_16' => 'Re 16',
            're_17' => 'Re 17',
            're_18' => 'Re 18',
            're_19' => 'Re 19',
            're_20' => 'Re 20',
            're_21' => 'Re 21',
            're_22' => 'Re 22',
            're_23' => 'Re 23',
            're_24' => 'Re 24',
            're_25' => 'Re 25',
            're_26' => 'Re 26',
            're_27' => 'Re 27',
            're_28' => 'Re 28',
            're_29' => 'Re 29',
            're_30' => 'Re 30',
        ];
    }

    public function beforeSave($insert)
    {
        $i = 1;
        if (parent::beforeSave($insert)) {
            for ($i=1; $i<=30; $i++){

                $re = 're_' .$i;
                if ($this->$re == null){
                    $this->$re = 0;
                }else{
                    $this->$re = str_replace(".", "", $this->$re);
                }
            }

            // if ($this->total_sp2d_bulanan == null){
            //     $this->total_sp2d_bulanan = 0;
            // }else{
            //     $this->total_sp2d_bulanan = str_replace(".", "", $this->total_sp2d_bulanan);
            // }

            // if ($this->total_realisasi_bulanan == null){
            //     $this->total_realisasi_bulanan = 0;
            // }else{
            //     $this->total_realisasi_bulanan = str_replace(".", "", $this->total_realisasi_bulanan);
            // }

            return true;
        } else {
            return false;
        }
    }

    public function getPagu()
    {
        return $this->hasOne(UkPagu::className(), ['id' => 'uk_id']);
    }
}
