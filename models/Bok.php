<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "bok".
 *
 * @property int $id
 * @property string $keterangan
 *
 * @property DeptProgram[] $deptPrograms
 */
class Bok extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bok';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['keterangan'], 'required'],
            [['keterangan'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'keterangan' => 'Keterangan',
        ];
    }

    /**
     * Gets query for [[DeptPrograms]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeptPrograms()
    {
        return $this->hasMany(DeptProgram::className(), ['bok_id' => 'id']);
    }
}
