<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "activity".
 *
 * @property int $id
 * @property int $service_id
 * @property string $nama_kegiatan
 * @property string $aktif
 *
 * @property Service $service
 */
class Activity extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'activity';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['service_id'], 'required'],
            [['service_id'], 'integer'],
            [['nama_kegiatan', 'aktif', 'status'], 'string'],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Service::className(), 'targetAttribute' => ['service_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'service_id' => 'Service ID',
            'nama_kegiatan' => 'Nama Kegiatan',
            'aktif' => 'Aktif',
            'status' => 'Status'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Service::className(), ['id' => 'service_id']);
    }

    public function getAccount($id)
    {
        $akun = Account::findOne($id);
        return $akun->nama_rekening;
    }

    public function getAccountAccess($id)
    {
        $accountaccess = Accountaccess::find()->where(['activity_id' => $id])->orderBy('id')->all();
        if(!empty($accountaccess)){
            foreach($accountaccess as $aa)
            {
                $arrAkun[] = $this->getAccount($aa['account_id']); 
            } 
            $akun = implode(", ", $arrAkun);
        }else{
            $akun = null;
        }
        return $akun;
    }
}
