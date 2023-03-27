<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "period".
 *
 * @property int $id
 * @property string $unit_id
 * @property string $tahun
 * @property int $pagu
 *
 * @property ActivityData[] $activityDatas
 * @property IndicatorData[] $indicatorDatas
 * @property Unit $unit
 */
class Period extends \yii\db\ActiveRecord
{
    public $bulan;
    public $periode;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'period';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['unit_id', 'tahun'], 'required'],
            [['pagu'], 'number', 'on' => 'listprogram'],
            [['pagu', 'pagu_geser', 'pagu_ubah'], 'string', 'max' => 50, 'on' => 'entripagu'],
            [['bulan'], 'required', 'on'=>'pilihbulan'],
            [['tahun', 'periode'], 'safe'],
            [['unit_id'], 'string', 'max' => 15],
            [['unit_id', 'tahun'], 'unique', 'targetAttribute' => ['unit_id', 'tahun']],
            [['unit_id'], 'exist', 'skipOnError' => true, 'targetClass' => Unit::className(), 'targetAttribute' => ['unit_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'unit_id' => 'Unit ID',
            'tahun' => '',
            'pagu' => 'Pagu Awal',
            'pagu_geser' => 'Pagu Pergeseran',
            'pagu_ubah' => 'Pagu Perubahan',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActivityDatas()
    {
        return $this->hasMany(ActivityData::className(), ['period_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIndicatorDatas()
    {
        return $this->hasMany(IndicatorData::className(), ['period_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUnit()
    {
        return $this->hasOne(Unit::className(), ['id' => 'unit_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Place your custom code here
            $this->pagu = str_replace(".", "", $this->pagu);
            $this->pagu_geser = str_replace(".", "", $this->pagu_geser);
            $this->pagu_ubah = str_replace(".", "", $this->pagu_ubah);

            return true;
        } else {
            return false;
        }
    }
}
