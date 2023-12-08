<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dept_sub_activity_data".
 *
 * @property int $id
 * @property int $dept_sub_activity_id
 * @property int $dept_period_id
 * @property string $bentuk_kegiatan
 * @property string|null $indikator_hasil
 * @property string|null $target_hasil
 * @property string|null $indikator_keluaran
 * @property string|null $target_keluaran
 *
 * @property DeptPeriod $deptPeriod
 * @property DeptSubActivity $deptSubActivity
 * @property DeptSubActivityDetail[] $deptSubActivityDetails
 */
class Deptsubactivitydata extends \yii\db\ActiveRecord
{
    public $deptProgramId;
    public $deptActivityId;
    public $deptSubActivityId;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dept_sub_activity_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dept_sub_activity_id', 'dept_period_id', 'bentuk_kegiatan', 'dpa_id'], 'required'],
            [['dept_sub_activity_id', 'dept_period_id'], 'integer'],
            [['target'], 'safe'],
            [['bentuk_kegiatan'], 'string'],
            [['indikator_hasil', 'indikator_keluaran'], 'string', 'max' => 100],
            [['target_hasil', 'target_keluaran', 'satuan'], 'string', 'max' => 50],
            [['dept_period_id'], 'exist', 'skipOnError' => true, 'targetClass' => Deptperiod::className(), 'targetAttribute' => ['dept_period_id' => 'id']],
            [['dept_sub_activity_id'], 'exist', 'skipOnError' => true, 'targetClass' => Deptsubactivity::className(), 'targetAttribute' => ['dept_sub_activity_id' => 'id']],
            [['dpa_id'], 'exist', 'skipOnError' => true, 'targetClass' => Dpa::className(), 'targetAttribute' => ['dpa_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dept_sub_activity_id' => 'Dept Sub Activity ID',
            'dept_period_id' => 'Dept Period ID',
            'dpa_id' => 'Sub Menu DPA',
            'bentuk_kegiatan' => 'Bentuk Kegiatan',
            'indikator_hasil' => 'Indikator Output',
            'target_hasil' => 'Target Output',
            'target' => 'Target',
            'satuan' => 'Satuan',
            'indikator_keluaran' => 'Indikator Keluaran',
            'target_keluaran' => 'Target Keluaran',
        ];
    }

    /**
     * Gets query for [[DeptPeriod]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeptPeriod()
    {
        return $this->hasOne(DeptPeriod::className(), ['id' => 'dept_period_id']);
    }

    /**
     * Gets query for [[DeptSubActivity]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeptSubActivity()
    {
        return $this->hasOne(DeptSubActivity::className(), ['id' => 'dept_sub_activity_id']);
    }

    /**
     * Gets query for [[DeptSubActivityDetails]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeptSubActivityDetails()
    {
        return $this->hasMany(DeptSubActivityDetail::className(), ['dept_activity_data_id' => 'id']);
    }

    /**
     * Gets query for [[Dpa]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDpa()
    {
        return $this->hasOne(Dpa::className(), ['id' => 'dpa_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->target = str_replace(".", "", $this->target);
            return true;
        } else {
            return false;
        }
    }

}
