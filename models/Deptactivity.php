<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dept_activity".
 *
 * @property int $id
 * @property int $dept_program_id
 * @property string $kode_rekening
 * @property string $nama_kegiatan
 * @property int $pagu
 * @property int $aktif
 *
 * @property DeptProgram $deptProgram
 * @property DeptSubActivity[] $deptSubActivities
 */
class Deptactivity extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dept_activity';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dept_program_id', 'pagu'], 'required'],
            [['dept_program_id', 'pagu', 'aktif'], 'integer'],
            [['kode_rekening'], 'string', 'max' => 100],
            [['nama_kegiatan'], 'string'],
            [['dept_program_id'], 'exist', 'skipOnError' => true, 'targetClass' => Deptprogram::className(), 'targetAttribute' => ['dept_program_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dept_program_id' => 'Dept Program ID',
            'kode_rekening' => 'Kode Rekening',
            'nama_kegiatan' => 'Nama Kegiatan',
            'pagu' => 'Pagu',
            'aktif' => 'Aktif',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeptProgram()
    {
        return $this->hasOne(DeptProgram::className(), ['id' => 'dept_program_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeptSubActivities()
    {
        return $this->hasMany(DeptSubActivity::className(), ['dept_activity_id' => 'id']);
    }
}
