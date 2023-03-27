<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dept_sub_activity".
 *
 * @property int $id
 * @property int $dept_activity_id
 * @property string $nama_sub_kegiatan
 * @property int $aktif
 *
 * @property DeptActivityData[] $deptActivityDatas
 * @property DeptActivity $deptActivity
 */
class DeptSubActivity extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dept_sub_activity';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dept_activity_id', 'pagu'], 'required'],
            [['dept_activity_id', 'pagu', 'aktif'], 'integer'],
            [['kode_rekening'], 'string', 'max' => 100],
            [['nama_sub_kegiatan', 'status'], 'string'],
            [['dept_activity_id'], 'exist', 'skipOnError' => true, 'targetClass' => Deptactivity::className(), 'targetAttribute' => ['dept_activity_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dept_activity_id' => 'Dept Activity ID',
            'kode_rekening' => 'Kode Rekening',
            'nama_sub_kegiatan' => 'Nama Sub Kegiatan',
            'pagu' => 'Pagu',
            'aktif' => 'Aktif',
            'status' => 'Status'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeptActivityDatas()
    {
        return $this->hasMany(DeptActivityData::className(), ['dept_sub_activity_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeptActivity()
    {
        return $this->hasOne(DeptActivity::className(), ['id' => 'dept_activity_id']);
    }

    public function getTotal($id)
    {
        $data = Yii::$app->db->createCommand('SELECT s.id, s.nama_sub_kegiatan, SUM(e.jumlah) jumlah
        FROM dept_sub_activity_detail e
        LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
        LEFT JOIN dept_sub_activity s ON s.id=a.dept_sub_activity_id
        LEFT JOIN dept_activity v ON v.id=s.dept_activity_id
        LEFT JOIN dept_program g ON g.id=v.dept_program_id
        LEFT JOIN dept_period p ON p.id=a.dept_period_id
        LEFT JOIN account c ON c.id=e.account_id
        WHERE p.unit_id="p2ptm" AND p.tahun="2022" AND g.bok_id="1" AND s.id=:idsub
        group BY s.id ORDER BY g.id, s.id, v.id, a.id')
        ->bindValue(':idsub', $id)
        ->queryAll();

        if(!empty($data)){
            foreach($data as $dt);
            return $dt['jumlah'];
        }else{
            return '';
        }
    }

    public function getGroup($id)
    {
        $group = Deptgroupsp2d::findOne($id);
        return $group->nama;
    }
}
