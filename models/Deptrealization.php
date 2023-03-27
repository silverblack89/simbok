<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dept_realization".
 *
 * @property int $id
 * @property int $triwulan
 * @property string $tahun
 * @property int $dept_sub_activity_detail_id
 * @property float $jumlah
 */
class Deptrealization extends \yii\db\ActiveRecord
{
    public $sp2d;
    public $realisasi_lalu;
    public $total_realisasi;
    public $jml_poa;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dept_realization';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['triwulan', 'tahun', 'dept_sub_activity_detail_id', 'jumlah'], 'required'],
            [['triwulan', 'dept_sub_activity_detail_id'], 'integer'],
            [['tahun'], 'safe'],
            [['jumlah', 'sp2d', 'total_realisasi'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'triwulan' => 'Triwulan',
            'tahun' => 'Tahun',
            'dept_sub_activity_detail_id' => 'Dept Sub Activity Detail ID',
            'jumlah' => 'Jumlah',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Place your custom code here
            $this->jumlah = str_replace(".", "", $this->jumlah);
            return true;
        } else {
            return false;
        }
    }
}
