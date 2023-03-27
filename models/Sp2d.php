<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sp2d".
 *
 * @property int $id
 * @property string $tanggal
 * @property string $unit_id
 * @property string|null $no_sp2d
 * @property string|null $jenis_spm
 * @property string|null $uraian
 * @property int|null $jumlah
 */
class Sp2d extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sp2d';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tanggal', 'unit_id', 'no_sp2d'], 'required'],
            [['tanggal'], 'safe'],
            [['jumlah'], 'string'],
            [['unit_id'], 'string', 'max' => 15],
            [['no_sp2d', 'jenis_spm'], 'string', 'max' => 50],
            [['uraian'], 'string', 'max' => 255],
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
            'unit_id' => 'Unit ID',
            'no_sp2d' => 'No Sp2d',
            'jenis_spm' => 'Jenis Spm',
            'uraian' => 'Uraian',
            'jumlah' => 'Jumlah',
        ];
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
