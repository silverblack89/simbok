<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dept_program".
 *
 * @property int $id
 * @property int $bok_id
 * @property string|null $kode_rekening
 * @property string|null $nama_program
 * @property string|null $tahun
 * @property int $pagu
 * @property int $aktif
 *
 * @property DeptActivity[] $deptActivities
 * @property Bok $bok
 */
class Deptprogram extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dept_program';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['bok_id'], 'required'], //, 'dept_group_sp2d_id_1', 'dept_group_sp2d_id_2'
            [['bok_id', 'pagu', 'aktif', 'dept_group_sp2d_id_1', 'dept_group_sp2d_id_2'], 'integer'],
            [['tahun'], 'safe'],
            [['kode_rekening'], 'string', 'max' => 100],
            [['nama_program'], 'string', 'max' => 255],
            [['bok_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bok::className(), 'targetAttribute' => ['bok_id' => 'id']],
            [['dept_group_sp2d_id_1'], 'exist', 'skipOnError' => true, 'targetClass' => Deptgroupsp2d::className(), 'targetAttribute' => ['dept_group_sp2d_id_1' => 'id']],
            [['dept_group_sp2d_id_2'], 'exist', 'skipOnError' => true, 'targetClass' => Deptgroupsp2d::className(), 'targetAttribute' => ['dept_group_sp2d_id_2' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bok_id' => 'Bok ID',
            'kode_rekening' => 'Kode Rekening',
            'nama_program' => 'Nama Program',
            'tahun' => 'Tahun',
            'pagu' => 'Pagu',
            'aktif' => 'Aktif',
        ];
    }

    /**
     * Gets query for [[DeptActivities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeptActivities()
    {
        return $this->hasMany(DeptActivity::className(), ['dept_program_id' => 'id']);
    }

    /**
     * Gets query for [[Bok]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBok()
    {
        return $this->hasOne(Bok::className(), ['id' => 'bok_id']);
    }

    public function getDeptgroupsp2d1()
    {
        return $this->hasOne(Deptgroupsp2d::className(), ['id' => 'dept_group_sp2d_id_1']);
    }

    public function getDeptgroupsp2d2()
    {
        return $this->hasOne(Deptgroupsp2d::className(), ['id' => 'dept_group_sp2d_id_2']);
    }
}
