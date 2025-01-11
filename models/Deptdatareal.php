<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dept_datareal".
 *
 * @property int $id
 * @property int $dept_sub_activity_detail_id
 * @property string $tanggal
 * @property string $nomor
 * @property float $jumlah
 *
 * @property DeptSubActivityDetail $deptSubActivityDetail
 */
class Deptdatareal extends \yii\db\ActiveRecord
{
    public $jumlah_pagu;
    public $jumlah_realisasi;
    public $sisa_pagu;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dept_datareal';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dept_sub_activity_detail_id', 'tanggal', 'nomor'], 'required'],
            [['dept_sub_activity_detail_id'], 'integer'],
            [['tanggal', 'jumlah'], 'safe'],
            [['nomor'], 'string', 'max' => 100],
            [['dept_sub_activity_detail_id'], 'exist', 'skipOnError' => true, 'targetClass' => DeptSubActivityDetail::className(), 'targetAttribute' => ['dept_sub_activity_detail_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dept_sub_activity_detail_id' => 'Dept Sub Activity Detail ID',
            'tanggal' => 'Tanggal',
            'nomor' => 'Nomor',
            'jumlah' => 'Jumlah',
        ];
    }

    /**
     * Gets query for [[DeptSubActivityDetail]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeptSubActivityDetail()
    {
        return $this->hasOne(DeptSubActivityDetail::className(), ['id' => 'dept_sub_activity_detail_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Place your custom code here
            $this->jumlah = str_replace(".", "", $this->jumlah);

            if($this->tanggal !== null){ 
                $this->tanggal = Yii::$app->formatter->asDate($this->tanggal, 'yyyy-MM-dd'); 
            }else{
                $this->tanggal = null;
            }

            return true;
        } else {
            return false;
        }
    }

    public function afterFind()
    {
        if($this->tanggal == '1970-01-01'){ 
            $this->tanggal = '';
        }elseif($this->tanggal == '0000-00-00'){ 
            $this->tanggal = '';
        }else{
            $this->tanggal = Yii::$app->formatter->asDate($this->tanggal, 'dd-MM-yyyy');   
        }

        parent::afterFind();
        return true;
    }
}
