<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "activity_detail_ubah".
 *
 * @property int $id
 * @property int $activity_detail_id
 * @property int $activity_data_id
 * @property int $account_id
 * @property string $rincian
 * @property int $vol_1
 * @property string $satuan_1
 * @property int $vol_2
 * @property string $satuan_2
 * @property int $unit_cost
 * @property double $jumlah
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
 */
class Activitydetailubah extends \yii\db\ActiveRecord
{
    public $total_pagu;
    public $total_poa;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'activity_detail_ubah';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['activity_detail_id', 'activity_data_id', 'account_id'], 'required'],
            [['activity_detail_id', 'activity_data_id', 'account_id', 'jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'], 'integer'],
            [['jumlah', 'vol_1', 'vol_2'], 'number'],
            [['rincian', 'satuan_1', 'satuan_2', 'unit_cost'], 'string', 'max' => 50],
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
            'activity_data_id' => 'Activity Data ID',
            'account_id' => 'Account ID',
            'rincian' => 'Rincian',
            'vol_1' => 'Vol 1',
            'satuan_1' => 'Satuan 1',
            'vol_2' => 'Vol 2',
            'satuan_2' => 'Satuan 2',
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
        ];
    }

    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'account_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Place your custom code here
            $this->unit_cost = str_replace(".", "", $this->unit_cost);
            $this->vol_1 = str_replace(".", "", $this->vol_1);

            if ($this->vol_2 == null){
                $this->jumlah = $this->vol_1 * str_replace(".", ",", $this->unit_cost);
                $this->satuan_2 = null;
            }else{
                $this->vol_2 = str_replace(".", "", $this->vol_2);
                $this->jumlah = $this->vol_1 * $this->vol_2 * str_replace(".", "", $this->unit_cost);
            }

            return true;
        } else {
            return false;
        }
    }
}
