<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dept_sp2d".
 *
 * @property int $id
 * @property string $tanggal
 * @property int $group_sp2d_id
 * @property string $no_sp2d
 * @property string $jenis_spm
 * @property string $uraian
 * @property int|null $jumlah
 *
 * @property DeptKelompokSp2d $kelompokSp2d
 */
class Deptsp2d extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dept_sp2d';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tanggal', 'no_sp2d', 'jenis_spm', 'uraian'], 'required'],
            [['tanggal', 'jumlah'], 'safe'],
            [['dept_group_sp2d_id', 'dpa_id'], 'integer'],
            [['no_sp2d', 'jenis_spm'], 'string', 'max' => 50],
            [['uraian'], 'string', 'max' => 255],
            [['dept_group_sp2d_id'], 'exist', 'skipOnError' => true, 'targetClass' => Deptgroupsp2d::className(), 'targetAttribute' => ['dept_group_sp2d_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tanggal' => 'Tanggal',
            'dept_group_sp2d_id' => 'Kelompok SP2D',
            'no_sp2d' => 'No Sp2d',
            'jenis_spm' => 'Jenis Spm',
            'uraian' => 'Uraian',
            'jumlah' => 'Jumlah',
        ];
    }

    /**
     * Gets query for [[KelompokSp2d]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGroupsp2d()
    {
        return $this->hasOne(Deptgroupsp2d::className(), ['id' => 'dept_group_sp2d_id']);
    }

    public function beforeSave($insert)
    {
        $this->jumlah = str_replace(".", "", $this->jumlah);
        if($this->tanggal !== null){ 
            $this->tanggal = Yii::$app->formatter->asDate($this->tanggal, 'yyyy-MM-dd'); 
        }else{
            $this->tanggal = null;
        }
        parent::beforeSave($insert);

        return true;
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
    }
}
