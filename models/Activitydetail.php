<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "activity_detail".
 *
 * @property int $id
 * @property int $activity_data_id
 * @property int $account_id
 * @property string $rincian
 * @property string $vol_1
 * @property string $satuan_1
 * @property string $vol_2
 * @property string $satuan_2
 * @property string $unit_cost
 * @property string $jumlah
 * @property int $jan
 * @property int $feb
 * @property int $mar
 * @property int $apr
 * @property int $mei
 * @property int $jun
 * @property int $jul
 * @property int $agu
 * @property int $sep
 * @property int $okt
 * @property int $nov
 * @property int $des
 *
 * @property Account $account
 * @property ActivityData $activityData
 */
class Activitydetail extends \yii\db\ActiveRecord
{
    public $bulan;
    public $total_pagu;
    public $total_poa;
    public $total;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'activity_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['activity_data_id', 'account_id', 'vol_1', 'unit_cost'], 'required'],
            [['activity_data_id', 'account_id', 'jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'], 'integer'],
            [['vol_1', 'vol_2', 'vol_3', 'vol_4', 'jumlah', 'jan_val', 'feb_val', 'mar_val', 'apr_val', 'mei_val', 'jun_val', 'jul_val', 'agu_val', 'sep_val', 'okt_val', 'nov_val', 'des_val'], 'safe'],
            [['rincian', 'satuan_1', 'satuan_2', 'satuan_3', 'satuan_4', 'unit_cost'], 'string', 'max' => 50],
            [['account_id'], 'exist', 'skipOnError' => true, 'targetClass' => Account::className(), 'targetAttribute' => ['account_id' => 'id']],
            [['activity_data_id'], 'exist', 'skipOnError' => true, 'targetClass' => Activitydata::className(), 'targetAttribute' => ['activity_data_id' => 'id']],
            [['total_poa'], 'compare', 'compareValue' => str_replace(".", "", $this->total_pagu), 'operator' => '<=' ,'message'=>Yii::t('app','Total POA tidak boleh > Total PAGU BOK ('.number_format($this->total_pagu, 0, ',', '.').'). ')],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'activity_data_id' => 'Activity Data ID',
            'account_id' => 'Account ID',
            'rincian' => 'Rincian',
            'vol_1' => 'Volume 1',
            'satuan_1' => 'Satuan 1',
            'vol_2' => 'Volume 2',
            'satuan_2' => 'Satuan 2',

            'vol_3' => 'Volume 3',
            'satuan_3' => 'Satuan 3',
            'vol_4' => 'Volume 4',
            'satuan_4' => 'Satuan 4',

            'unit_cost' => 'Biaya',
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

            'jan_val' => 'Jan', 
            'feb_val' => 'Feb', 
            'mar_val' => 'Mar', 
            'apr_val' => 'Apr', 
            'mei_val' => 'Mei', 
            'jun_val' => 'Jun', 
            'jul_val' => 'Jul', 
            'agu_val' => 'Agu', 
            'sep_val' => 'Sep', 
            'okt_val' => 'Okt', 
            'nov_val' => 'Nov', 
            'des_val' => 'Des',

            'realisasi_vol_1' => 'Volume 1',
            'realisasi_vol_2' => 'Volume 2',
            'realisasi_unit_cost' => 'Biaya',
            'realisasi_jumlah' => 'Jumlah',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'account_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActivityData()
    {
        return $this->hasOne(ActivityData::className(), ['id' => 'activity_data_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Place your custom code here
            $this->unit_cost = str_replace(".", "", $this->unit_cost);
            $this->vol_1 = str_replace(".", "", $this->vol_1);

            if ($this->vol_1 == null && $this->vol_2 == null && $this->vol_3 == null && $this->vol_4 == null){
                $this->jumlah = str_replace(".", ",", $this->unit_cost);
                $this->satuan_1 = null;
                $this->satuan_2 = null;
                $this->satuan_3 = null;
                $this->satuan_4 = null;

            }elseif ($this->vol_1 == null && $this->vol_2 == null && $this->vol_3 == null){
                $this->jumlah = $this->vol_4 * str_replace(".", ",", $this->unit_cost);
                $this->satuan_1 = null;
                $this->satuan_2 = null;
                $this->satuan_3 = null;

            }elseif ($this->vol_2 == null && $this->vol_3 == null && $this->vol_4 == null){
                $this->jumlah = $this->vol_1 * str_replace(".", ",", $this->unit_cost);
                $this->satuan_2 = null;
                $this->satuan_3 = null;
                $this->satuan_4 = null;
            
            }elseif ($this->vol_1 == null && $this->vol_3 == null && $this->vol_4 == null){
                $this->jumlah = $this->vol_2 * str_replace(".", ",", $this->unit_cost);
                $this->satuan_1 = null;
                $this->satuan_3 = null;
                $this->satuan_4 = null;

            }elseif ($this->vol_1 == null && $this->vol_2 == null && $this->vol_4 == null){
                $this->jumlah = $this->vol_3 * str_replace(".", ",", $this->unit_cost);
                $this->satuan_1 = null;
                $this->satuan_2 = null;
                $this->satuan_4 = null;

            }elseif ($this->vol_1 == null && $this->vol_2 == null){
                $this->vol_3 = str_replace(".", "", $this->vol_3);
                $this->vol_4 = str_replace(".", "", $this->vol_4);
                $this->jumlah = $this->vol_3 * $this->vol_4 * str_replace(".", "", $this->unit_cost);
                $this->satuan_1 = null;
                $this->satuan_2 = null;

            }elseif ($this->vol_1 == null && $this->vol_3 == null){
                $this->vol_2 = str_replace(".", "", $this->vol_2);
                $this->vol_4 = str_replace(".", "", $this->vol_4);
                $this->jumlah = $this->vol_2 * $this->vol_4 * str_replace(".", "", $this->unit_cost);
                $this->satuan_1 = null;
                $this->satuan_3 = null;

            }elseif ($this->vol_1 == null && $this->vol_4 == null){
                $this->vol_2 = str_replace(".", "", $this->vol_2);
                $this->vol_3 = str_replace(".", "", $this->vol_3);
                $this->jumlah = $this->vol_2 * $this->vol_3 * str_replace(".", "", $this->unit_cost);
                $this->satuan_1 = null;
                $this->satuan_4 = null;

            }elseif ($this->vol_3 == null && $this->vol_4 == null){
                $this->vol_2 = str_replace(".", "", $this->vol_2);
                $this->jumlah = $this->vol_1 * $this->vol_2 * str_replace(".", "", $this->unit_cost);
                $this->satuan_3 = null;
                $this->satuan_4 = null;

            }elseif ($this->vol_2 == null && $this->vol_4 == null){
                $this->vol_3 = str_replace(".", "", $this->vol_3);
                $this->jumlah = $this->vol_1 * $this->vol_3 * str_replace(".", "", $this->unit_cost);
                $this->satuan_2 = null;
                $this->satuan_4 = null;

            }elseif ($this->vol_2 == null && $this->vol_3 == null){
                $this->vol_4 = str_replace(".", "", $this->vol_4);
                $this->jumlah = $this->vol_1 * $this->vol_4 * str_replace(".", "", $this->unit_cost);  
                $this->satuan_2 = null;
                $this->satuan_3 = null;  

            }elseif ($this->vol_1 == null){
                $this->vol_2 = str_replace(".", "", $this->vol_2);
                $this->vol_3 = str_replace(".", "", $this->vol_3);
                $this->vol_4 = str_replace(".", "", $this->vol_4);
                $this->jumlah = $this->vol_2 * $this->vol_3 * $this->vol_4 * str_replace(".", "", $this->unit_cost);
                $this->satuan_1 = null;

            }elseif ($this->vol_2 == null){
                $this->vol_3 = str_replace(".", "", $this->vol_3);
                $this->vol_4 = str_replace(".", "", $this->vol_4);
                $this->jumlah = $this->vol_1 * $this->vol_3 * $this->vol_4 * str_replace(".", "", $this->unit_cost);
                $this->satuan_2 = null;

            }elseif ($this->vol_3 == null){
                $this->vol_2 = str_replace(".", "", $this->vol_2);
                $this->vol_4 = str_replace(".", "", $this->vol_4);
                $this->jumlah = $this->vol_1 * $this->vol_2 * $this->vol_4 * str_replace(".", "", $this->unit_cost);
                $this->satuan_3 = null;

            }elseif ($this->vol_4 == null){
                $this->vol_2 = str_replace(".", "", $this->vol_2);
                $this->vol_3 = str_replace(".", "", $this->vol_3);
                $this->jumlah = $this->vol_1 * $this->vol_2 * $this->vol_3 * str_replace(".", "", $this->unit_cost);
                $this->satuan_4 = null;

            }else{
                $this->vol_1 = str_replace(".", "", $this->vol_1);
                $this->vol_2 = str_replace(".", "", $this->vol_2);
                $this->vol_3 = str_replace(".", "", $this->vol_3);
                $this->vol_4 = str_replace(".", "", $this->vol_4);
                $this->jumlah = $this->vol_1 * $this->vol_2 * $this->vol_3 * $this->vol_4 * str_replace(".", "", $this->unit_cost);
            }

            $this->jan_val = str_replace(".", "", $this->jan_val);
            $this->feb_val = str_replace(".", "", $this->feb_val);
            $this->mar_val = str_replace(".", "", $this->mar_val);
            $this->apr_val = str_replace(".", "", $this->apr_val);
            $this->mei_val = str_replace(".", "", $this->mei_val);
            $this->jun_val = str_replace(".", "", $this->jun_val);
            $this->jul_val = str_replace(".", "", $this->jul_val);
            $this->agu_val = str_replace(".", "", $this->agu_val);
            $this->sep_val = str_replace(".", "", $this->sep_val);
            $this->okt_val = str_replace(".", "", $this->okt_val);
            $this->nov_val = str_replace(".", "", $this->nov_val);
            $this->des_val = str_replace(".", "", $this->des_val);

            return true;
        } else {
            return false;
        }
    }
}
