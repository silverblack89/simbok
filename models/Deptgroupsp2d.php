<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dept_group_sp2d".
 *
 * @property int $id
 * @property string $nama
 *
 * @property DeptSp2d[] $deptSp2ds
 */
class Deptgroupsp2d extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dept_group_sp2d';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nama'], 'required'],
            [['nama'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nama' => 'Nama',
        ];
    }

    /**
     * Gets query for [[DeptSp2ds]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeptSp2ds()
    {
        return $this->hasMany(DeptSp2d::className(), ['dept_group_sp2d_id' => 'id']);
    }
}
