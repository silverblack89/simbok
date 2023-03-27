<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "perfomance".
 *
 * @property int $id
 * @property int $triwulan
 * @property string $tahun
 * @property int $activity_data_id
 * @property int $target_awal
 * @property string $satuan_awal
 * @property int $target_real
 * @property string $satuan_real
 */
class Perfomance extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'perfomance';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['triwulan', 'tahun', 'activity_data_id', 'target_awal', 'satuan_awal', 'target_real'], 'required'],
            [['triwulan', 'activity_data_id'], 'integer'],
            [['tahun', 'target_awal', 'target_real'], 'safe'],
            [['satuan_awal', 'satuan_real'], 'string', 'max' => 50],
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
            'activity_data_id' => 'Activity Data ID',
            'target_awal' => 'Target Awal',
            'satuan_awal' => 'Satuan Awal',
            'target_real' => 'Target Real',
            'satuan_real' => 'Satuan Real',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Place your custom code here
            $this->target_awal = str_replace(".", "", $this->target_awal);
            $this->target_real = str_replace(".", "", $this->target_real);
            return true;
        } else {
            return false;
        }
    }
}
