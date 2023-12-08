<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dpa".
 *
 * @property int $id
 * @property int $dept_sub_activity_id
 * @property string $tahun
 * @property string $nama
 * @property string $keterangan
 *
 * @property DeptSubActivityData[] $deptSubActivityDatas
 */
class Dpa extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dpa';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tahun', 'nama'], 'required'],
            [['dept_sub_activity_id'], 'integer'],
            [['tahun'], 'safe'],
            [['nama'], 'string', 'max' => 125],
            [['keterangan'], 'string'],
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
            'tahun' => 'Tahun',
            'nama' => 'Nama',
            'keterangan' => 'Keterangan',
        ];
    }

    /**
     * Gets query for [[DeptSubActivityDatas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeptSubActivityDatas()
    {
        return $this->hasMany(DeptSubActivityData::className(), ['dpa_id' => 'id']);
    }
}
