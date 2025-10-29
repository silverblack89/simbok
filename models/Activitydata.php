<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "activity_data".
 *
 * @property int $id
 * @property int $activity_id
 * @property int $period_id
 * @property string $sasaran
 * @property string $target
 * @property string $lokasi
 * @property string $pelaksana
 */
class Activitydata extends \yii\db\ActiveRecord
{
    public $programId;
    public $serviceId;
    public $activityId;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'activity_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['activity_id', 'period_id', 'bentuk_kegiatan', 'activity_data_sub_id'], 'required'],
            [['activity_id', 'period_id', 'activity_data_sub_id'], 'integer'],
            [['bentuk_kegiatan'], 'string', 'max' => 100],
            [['sasaran', 'target', 'satuan', 'lokasi', 'pelaksana'], 'string', 'max' => 50],
            [['programId', 'serviceId', 'activityId'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'activity_id' => 'Activity ID',
            'period_id' => 'Period ID',
            'activity_data_sub_id' => 'Sub Bentuk Kegiatan',
            'bentuk_kegiatan' => 'Bentuk Kegiatan',
            'sasaran' => 'Sasaran',
            'target' => 'Target',
            'satuan' => 'Satuan',
            'lokasi' => 'Lokasi',
            'pelaksana' => 'Pelaksana',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Place your custom code here
    
            if ($this->bentuk_kegiatan == ""){
                $this->bentuk_kegiatan = null;
            }

            return true;
        } else {
            return false;
        }
    }
}
