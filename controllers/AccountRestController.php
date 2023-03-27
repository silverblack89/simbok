<?php
namespace app\controllers;
use yii\rest\Controller;

class AccountRestController extends Controller{   
    public function behaviors(){
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            // 'class' => \yii\filters\auth\QueryParamAuth::className(),
            'class' => \app\components\CustomAuth::className(),
            'tokenParam'=>'key',
        ];
        return $behaviors;
    }
     
    protected function verbs()
    {
        return [
            'index' => ['GET'],
        ];
    }
    
    public function actionIndex()
    {
        $account = \app\models\Account::find()->all();
        return [
            'results'=>$account,
        ];
    }

}