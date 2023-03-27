<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "bd_label".
 *
 * @property int $id
 * @property string $bd_desk
 * @property string $tahun
 *
 * @property UkLabel[] $ukLabels
 */
class Bdlabel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bd_label';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['bd_desk', 'tahun'], 'required'],
            [['tahun'], 'safe'],
            [['jenis'], 'string', 'max' => 5],
            [['bd_desk'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bd_desk' => 'Bd Desk',
            'tahun' => 'Tahun',
            'jenis' => 'Jenis',
        ];
    }

    /**
     * Gets query for [[UkLabels]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUkLabels()
    {
        return $this->hasMany(UkLabel::className(), ['bd_id' => 'id']);
    }
}
