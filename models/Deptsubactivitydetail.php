<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dept_sub_activity_detail".
 *
 * @property int $id
 * @property int $dept_sub_activity_data_id
 * @property int $account_id
 * @property int|null $vol_1
 * @property string|null $satuan_1
 * @property int|null $vol_2
 * @property string|null $satuan_2
 * @property int|null $unit_cost
 * @property float|null $jumlah
 * @property int $tw1
 * @property int $tw2
 * @property int $tw3
 *
 * @property Account $account
 * @property DeptSubActivityData $deptSubActivityData
 */
class Deptsubactivitydetail extends \yii\db\ActiveRecord
{
    public $total_poa;
    public $total;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dept_sub_activity_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dept_sub_activity_data_id', 'account_id', 'sumber_dana_id', 'rincian'], 'required'],
            [['dept_sub_activity_data_id', 'account_id', 'sumber_dana_id', 'tw1', 'tw2', 'tw3', 'tw4'], 'integer'],
            [['jumlah', 'vol_1', 'vol_2', 'vol_3', 'vol_4'], 'number'],
            [['satuan_1', 'satuan_2', 'satuan_3', 'satuan_4', 'unit_cost', 'rincian'], 'string', 'max' => 50],
            [['account_id'], 'exist', 'skipOnError' => true, 'targetClass' => Account::className(), 'targetAttribute' => ['account_id' => 'id']],
            [['sumber_dana_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sumberdana::className(), 'targetAttribute' => ['sumber_dana_id' => 'id']],
            [['dept_sub_activity_data_id'], 'exist', 'skipOnError' => true, 'targetClass' => Deptsubactivitydata::className(), 'targetAttribute' => ['dept_sub_activity_data_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dept_sub_activity_data_id' => 'Dept Sub Activity Data ID',
            'account_id' => 'Account ID',
            'rincian' => 'Rincian',
            'vol_1' => 'Vol 1',
            'satuan_1' => 'Satuan 1',
            'vol_2' => 'Vol 2',
            'satuan_2' => 'Satuan 2',

            'vol_3' => 'Volume 3',
            'satuan_3' => 'Satuan 3',
            'vol_4' => 'Volume 4',
            'satuan_4' => 'Satuan 4',

            'unit_cost' => 'Unit Cost',
            'jumlah' => 'Jumlah',
            'tw1' => 'Tw1',
            'tw2' => 'Tw2',
            'tw3' => 'Tw3',
            'tw4' => 'Tw4',
        ];
    }

    /**
     * Gets query for [[Account]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'account_id']);
    }

    public function getSumberdana()
    {
        return $this->hasOne(Sumberdana::className(), ['id' => 'sumber_dana_id']);
    }

    /**
     * Gets query for [[DeptSubActivityData]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeptSubActivityData()
    {
        return $this->hasOne(DeptSubActivityData::className(), ['id' => 'dept_sub_activity_data_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Place your custom code here
            $this->unit_cost = str_replace(".", "", $this->unit_cost);
            $this->vol_1 = str_replace(".", "", $this->vol_1);

            if ($this->vol_2 == null && $this->vol_3 == null && $this->vol_4 == null){
                $this->jumlah = $this->vol_1 * str_replace(".", ",", $this->unit_cost);
                $this->satuan_2 = null;
                $this->satuan_3 = null;
                $this->satuan_4 = null;

            }elseif ($this->vol_3 == null && $this->vol_4 == null){
                $this->vol_2 = str_replace(".", "", $this->vol_2);
                $this->jumlah = $this->vol_1 * $this->vol_2 * str_replace(".", "", $this->unit_cost);
                $this->satuan_3 = null;
                $this->satuan_4 = null;

            }elseif ($this->vol_2 == null && $this->vol_4 == null){
                $this->vol_3 = str_replace(".", "", $this->vol_3);
                $this->jumlah = $this->vol_1 * $this->vol_3 * str_replace(".", "", $this->unit_cost);
                $this->satuan_2 = null;
                $this->satuan_4 = null;

            }elseif ($this->vol_2 == null && $this->vol_3 == null){
                $this->vol_4 = str_replace(".", "", $this->vol_4);
                $this->jumlah = $this->vol_1 * $this->vol_4 * str_replace(".", "", $this->unit_cost);  
                $this->satuan_2 = null;
                $this->satuan_3 = null;  

            }elseif ($this->vol_2 == null){
                $this->vol_3 = str_replace(".", "", $this->vol_3);
                $this->vol_4 = str_replace(".", "", $this->vol_4);
                $this->jumlah = $this->vol_1 * $this->vol_3 * $this->vol_4 * str_replace(".", "", $this->unit_cost);
                $this->satuan_2 = null;

            }elseif ($this->vol_3 == null){
                $this->vol_2 = str_replace(".", "", $this->vol_2);
                $this->vol_4 = str_replace(".", "", $this->vol_4);
                $this->jumlah = $this->vol_1 * $this->vol_2 * $this->vol_4 * str_replace(".", "", $this->unit_cost);
                $this->satuan_3 = null;

            }elseif ($this->vol_4 == null){
                $this->vol_2 = str_replace(".", "", $this->vol_2);
                $this->vol_3 = str_replace(".", "", $this->vol_3);
                $this->jumlah = $this->vol_1 * $this->vol_2 * $this->vol_3 * str_replace(".", "", $this->unit_cost);
                $this->satuan_4 = null;

            }else{
                $this->vol_2 = str_replace(".", "", $this->vol_2);
                $this->vol_3 = str_replace(".", "", $this->vol_3);
                $this->vol_4 = str_replace(".", "", $this->vol_4);
                $this->jumlah = $this->vol_1 * $this->vol_2 * $this->vol_3 * $this->vol_4 * str_replace(".", "", $this->unit_cost);
            }

            return true;
        } else {
            return false;
        }
    }

    public function getStatus($id)
    {
        $data = Yii::$app->db->createCommand('SELECT b.id bok_id, b.keterangan, b.sumber_dana_id dana_id, sd.nama sumber_dana, d.nama, v.id, v.nama_sub_kegiatan FROM dept_sub_activity v
        LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
        LEFT JOIN dept_program p ON p.id=s.dept_program_id
        LEFT JOIN bok b ON b.id=p.bok_id
        LEFT JOIN sumber_dana sd ON sd.id=b.sumber_dana_id
        LEFT JOIN dpa d ON d.dept_sub_activity_id=v.id
        WHERE v.id=:id')
        ->bindValue(':id', $id)
        ->queryAll();

        foreach($data as $dt);

        return $dt['bok_id'];
    }
}
