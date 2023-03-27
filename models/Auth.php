<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "auth".
 *
 * @property string $module
 * @property string $controller
 * @property string $action
 * @property int $group_id
 */
class Auth extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auth';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['module', 'controller', 'action', 'group_id'], 'required'],
            [['group_id'], 'string', 'max' => 15],
            [['module', 'controller', 'action'], 'string', 'max' => 60],
            [['module', 'controller', 'action', 'group_id'], 'unique', 'targetAttribute' => ['module', 'controller', 'action', 'group_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'module' => 'Module',
            'controller' => 'Controller',
            'action' => 'Action',
            'group_id' => 'Group ID',
        ];
    }
}
