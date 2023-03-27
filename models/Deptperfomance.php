<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dept_perfomance".
 *
 * @property int $id
 * @property int $triwulan
 * @property string $tahun
 * @property int $dept_sub_activity_data_id
 * @property int $target_awal
 * @property string $satuan_awal
 * @property int $target_real
 * @property string|null $satuan_real
 */
class Deptperfomance extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dept_perfomance';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['triwulan', 'tahun', 'dept_sub_activity_data_id', 'target_awal', 'satuan_awal', 'target_real'], 'required'],
            [['triwulan', 'dept_sub_activity_data_id', 'target_awal', 'target_real'], 'integer'],
            [['tahun'], 'safe'],
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
            'dept_sub_activity_data_id' => 'Dept Sub Activity Data ID',
            'target_awal' => 'Target Awal',
            'satuan_awal' => 'Satuan Awal',
            'target_real' => 'Target Real',
            'satuan_real' => 'Satuan Real',
        ];
    }
}
