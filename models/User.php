<?php

namespace app\models;
use Yii;
use yii\web\IdentityInterface;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    public $old_password;
	public $new_password;
	public $repeat_password;

    public static function tableName()
    {
        // return '{{%user}}';
        return 'user';
    }

    public function behavior()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],

            // Define the rules for old_password, new_password and repeat_password with changePwd Scenario.
            [['old_password', 'new_password', 'repeat_password'], 'required', 'on' => 'changePwd', 'message'=>Yii::t('app','Tidak boleh kosong!')],
            [['old_password'], 'findPasswords', 'on' => 'changePwd'],
            [['repeat_password'], 'compare', 'compareAttribute'=>'new_password', 'on'=>'changePwd', 'message'=>Yii::t('app','Pengulangan password baru harus sama')],

            [['username', 'password_hash', 'status', 'unit_id'], 'required'],
            [['create_at', 'update_at'], 'safe'],
            [['status'], 'integer'],
            [['username', 'alias', 'auth_key'], 'string', 'max' => 100],
            [['password_hash', 'password_reset_token'], 'string', 'max' => 255],
            [['email'], 'string', 'max' => 50],
            [['unit_id', 'group_id'], 'string', 'max' => 15],
            [['unit_id'], 'exist', 'skipOnError' => true, 'targetClass' => Unit::className(), 'targetAttribute' => ['unit_id' => 'id']],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Group::className(), 'targetAttribute' => ['group_id' => 'id']],

            [['email'], 'required', 'on' => 'unitDinkes'] ,

            // ['email', 'required', 'when' => function ($model) {
            //     return $model->unit_id == 'DINKES';
            // }, 'whenClient' => "function (attribute, value) {
            //     return $('#unit_id').val() == 'DINKES';
            // }"]
        ];
    }

   //matching the old password with your existing password.
	public function findPasswords($attribute, $params)
	{
		$user = User::findOne(Yii::$app->user->identity->id);
		if (Yii::$app->getSecurity()->validatePassword($this->old_password, $user->password_hash)){
            // Correct password
        }else{
            return $this->addError($attribute, 'Password lama tidak benar.'); //Old password is incorrect
        }
			
	}

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'alias' => 'Nama',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'status' => 'Status',
            'unit_id' => 'Unit',
            'group_id' => 'Group',
            'create_at' => 'Create At',
            'update_at' => 'Update At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUnit()
    {
        return $this->hasOne(Unit::className(), ['id' => 'unit_id']);
    }

    public function getGroup()
    {
        return $this->hasOne(Group::className(), ['id' => 'group_id']);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        //throw new NotSupportedException('"findIdentityByAccessToken" is not implemented');
        return static::findOne(['auth_key' => $token, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Place your custom code here

            if (isset($this->repeat_password)) {
                $this->password_hash = Yii::$app->security->generatePasswordhash($this->repeat_password);
            }else{
                $this->password_hash = Yii::$app->security->generatePasswordhash($this->password_hash);
            }
            return true;
        } else {
            return false;
        }
    }
}
