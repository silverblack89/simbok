<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dept_real".
 *
 * @property int $id
 * @property int $dept_program_id
 * @property int $dept_period_id
 * @property int $bulan
 * @property int $jumlah
 * @property string $modified_at
 *
 * @property DeptPeriod $deptPeriod
 * @property DeptProgram $deptProgram
 */
class Deptreal extends \yii\db\ActiveRecord
{
    public $sisa_sp2d;
    public $poa;
    public $realisasi;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dept_real';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dept_sub_activity_id', 'dept_period_id', 'bulan', 'dept_group_sp2d_id'], 'required'],
            [['dept_sub_activity_id', 'dept_period_id', 'bulan', 'dept_group_sp2d_id'], 'integer'],
            [['modified_at', 'jumlah'], 'safe'],
            [['dept_period_id'], 'exist', 'skipOnError' => true, 'targetClass' => Deptperiod::className(), 'targetAttribute' => ['dept_period_id' => 'id']],
            [['dept_sub_activity_id'], 'exist', 'skipOnError' => true, 'targetClass' => Deptsubactivity::className(), 'targetAttribute' => ['dept_sub_activity_id' => 'id']],
            [['dept_group_sp2d_id'], 'exist', 'skipOnError' => true, 'targetClass' => Deptgroupsp2d::className(), 'targetAttribute' => ['dept_group_sp2d_id' => 'id']],
            ['jumlah', 'compare','compareValue'=>str_replace(".", "", $this->sisa_sp2d),'operator'=>'<', 'message'=>Yii::t('app','Total realisasi tidak boleh melebihi Total SP2D.')],
            // ['jumlah', 'compare','compareValue'=>str_replace(".", "", $this->poa),'operator'=>'<', 'message'=>Yii::t('app','Total realisasi tidak boleh melebihi Total POA yang dientri ('.$this->jumlah.').')],
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
            'bulan' => 'Bulan',
            'jumlah' => 'Jumlah',
            'modified_at' => 'Modified At',
        ];
    }

    /**
     * Gets query for [[DeptPeriod]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeptperiod()
    {
        return $this->hasOne(Deptperiod::className(), ['id' => 'dept_period_id']);
    }

    /**
     * Gets query for [[DeptProgram]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeptsubactivity()
    {
        return $this->hasOne(Deptsubactivity::className(), ['id' => 'dept_sub_activity_id']);
    }

    public function getDeptgroupsp2d()
    {
        return $this->hasOne(Deptgroupsp2d::className(), ['id' => 'dept_group_sp2d_id']);
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
