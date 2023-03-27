<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "activity_data_ubah".
 *
 * @property int $id
 * @property int $activity_data_id
 * @property int $activity_id
 * @property int $period_id
 * @property string $bentuk_kegiatan
 * @property string $sasaran
 * @property string $target
 * @property string $lokasi
 * @property string $pelaksana
 */
class Activitydataubah extends \yii\db\ActiveRecord
{
    public $programId;
    public $serviceId;
    public $activityId;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'activity_data_ubah';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['activity_data_id', 'activity_id', 'period_id'], 'required'],
            [['activity_data_id', 'activity_id', 'period_id'], 'integer'],
            [['bentuk_kegiatan'], 'string', 'max' => 100],
            [['sasaran', 'target', 'lokasi', 'pelaksana'], 'string', 'max' => 50],
            [['programId', 'serviceId', 'activityId'], 'string', 'max' => 255],
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
            'activity_id' => 'Activity ID',
            'period_id' => 'Period ID',
            'bentuk_kegiatan' => 'Bentuk Kegiatan',
            'sasaran' => 'Sasaran',
            'target' => 'Target',
            'lokasi' => 'Lokasi',
            'pelaksana' => 'Pelaksana',
        ];
    }
}
