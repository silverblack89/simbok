<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "uk_pagu".
 *
 * @property int $id
 * @property int $uk_id
 * @property string $unit_id
 * @property int $jumlah
 *
 * @property UkLabel $uk
 */
class Ukpagu extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'uk_pagu';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uk_id', 'unit_id'], 'required'],
            [['uk_id', 'jumlah'], 'string'],
            [['unit_id'], 'string', 'max' => 15],
            [['uk_id'], 'exist', 'skipOnError' => true, 'targetClass' => Uklabel::className(), 'targetAttribute' => ['uk_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uk_id' => 'Uk ID',
            'unit_id' => 'Unit ID',
            'jumlah' => 'Jumlah',
        ];
    }

    public function beforeSave($insert)
    {
        $this->jumlah = str_replace(".", "", $this->jumlah);
        parent::beforeSave($insert);

        return true;
    }

    /**
     * Gets query for [[Uk]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUklabel()
    {
        return $this->hasOne(Uklabel::className(), ['id' => 'uk_id']);
    }
}
