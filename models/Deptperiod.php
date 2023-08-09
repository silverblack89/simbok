<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dept_period".
 *
 * @property int $id
 * @property string $unit_id
 * @property string $tahun
 */
class Deptperiod extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dept_period';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['unit_id', 'tahun'], 'required'],
            [['tahun'], 'safe'],
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
            'tahun' => 'Tahun',
        ];
    }

    public function getUnit()
    {
        return $this->hasOne(Unit::className(), ['id' => 'unit_id']);
    }
}
