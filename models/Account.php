<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "account".
 *
 * @property int $id
 * @property string $nama_rekening
 * @property string $aktif
 *
 * @property ActivityDetail[] $activityDetails
 */
class Account extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'account';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['aktif'], 'integer'],
            [['kode'], 'string', 'max' => 25],
            [['nama_rekening'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'kode' => 'Kode',
            'nama_rekening' => 'Nama Rekening',
            'aktif' => 'Aktif',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActivityDetails()
    {
        return $this->hasMany(ActivityDetail::className(), ['account_id' => 'id']);
    }
}
