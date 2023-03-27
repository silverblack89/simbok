<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "uk_label".
 *
 * @property int $id
 * @property string $uk_nama
 * @property string $uk_desk
 * @property string $tahun
 * @property int $bd_id
 * @property int $co_1
 * @property int $co_2
 * @property int $co_3
 * @property int $co_4
 *
 * @property BdLabel $bd
 * @property UkPagu[] $ukPagus
 */
class Uklabel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'uk_label';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uk_nama', 'uk_desk', 'tahun', 'bd_id'], 'required'],
            [['tahun'], 'safe'],
            [['bd_id', 'co_1', 'co_2', 'co_3', 'co_4'], 'integer'],
            [['uk_nama'], 'string', 'max' => 5],
            [['uk_desk'], 'string', 'max' => 100],
            [['bd_id'], 'exist', 'skipOnError' => true, 'targetClass' => BdLabel::className(), 'targetAttribute' => ['bd_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uk_nama' => 'Uk Nama',
            'uk_desk' => 'Uk Desk',
            'tahun' => 'Tahun',
            'bd_id' => 'Bd ID',
            'co_1' => 'Co 1',
            'co_2' => 'Co 2',
            'co_3' => 'Co 3',
            'co_4' => 'Co 4',
        ];
    }

    /**
     * Gets query for [[Bd]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBd()
    {
        return $this->hasOne(BdLabel::className(), ['id' => 'bd_id']);
    }

    /**
     * Gets query for [[UkPagus]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUkPagus()
    {
        return $this->hasMany(UkPagu::className(), ['uk_id' => 'id']);
    }
}
