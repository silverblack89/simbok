<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dept_account_access".
 *
 * @property int $id
 * @property int $dept_sub_activity_id
 * @property int $account_id
 *
 * @property Account $account
 * @property DeptSubActivity $deptSubActivity
 */
class Deptaccountaccess extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dept_account_access';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['dept_sub_activity_id', 'account_id'], 'required'],
            [['dept_sub_activity_id', 'account_id'], 'integer'],
            [['dept_sub_activity_id', 'account_id'], 'unique', 'targetAttribute' => ['dept_sub_activity_id', 'account_id']],
            [['account_id'], 'exist', 'skipOnError' => true, 'targetClass' => Account::className(), 'targetAttribute' => ['account_id' => 'id']],
            [['dept_sub_activity_id'], 'exist', 'skipOnError' => true, 'targetClass' => Deptsubactivity::className(), 'targetAttribute' => ['dept_sub_activity_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dept_sub_activity_id' => 'Dept Sub Activity ID',
            'account_id' => 'Account ID',
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

    /**
     * Gets query for [[DeptSubActivity]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeptSubActivity()
    {
        return $this->hasOne(Deptsubactivity::className(), ['id' => 'dept_sub_activity_id']);
    }
}
