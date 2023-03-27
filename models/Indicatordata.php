<?php

namespace app\models;

use Yii;
use yii\web\Session;

/**
 * This is the model class for table "indicator_data".
 *
 * @property int $id
 * @property int $indicator_id
 * @property int $bulan
 * @property string $kinerja
 *
 * @property Indicator $indicator
 */
class Indicatordata extends \yii\db\ActiveRecord
{
    public $kinerjaMax;
    public function replaceStr($value) {
        return str_replace(',', '.', $value);
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'indicator_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        // $session = Yii::$app->session;
        // $session->open();
        return [
            [['indicator_id', 'bulan', 'period_id', 'kinerja'], 'required'],
            [['indicator_id', 'bulan', 'period_id'], 'integer'],
            [['kinerja', 'kinerjaMax'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.]?[0-9]+([eE][-+]?[0-9]+)?\s*$/', 'message'=>Yii::t('app','Harus angka dan decimal titik(.)')],
            [['kinerja'], 'compare', 'compareValue'=> $this->kinerjaMax, 'operator' => '>=', 'message'=>Yii::t('app','Nilai tidak boleh < '. $this->kinerjaMax. '%'), 'on'=>'kMax'],
            [['kinerja'], 'compare', 'compareValue' => 100, 'operator' => '<=' ,'message'=>Yii::t('app','Nilai tidak boleh > 100.')],
            [['indicator_id'], 'exist', 'skipOnError' => true, 'targetClass' => Indicator::className(), 'targetAttribute' => ['indicator_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'indicator_id' => 'Indicator ID',
            'period_id' => 'Period ID',
            'bulan' => 'Bulan',
            'kinerja' => 'Kinerja',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Place your custom code here

            if($this->kinerja == null) {
                $this->kinerja = 0;
            }else{
                $this->kinerja = str_replace(",", ".", $this->kinerja);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIndicator()
    {
        return $this->hasOne(Indicator::className(), ['id' => 'indicator_id']);
    }
}
