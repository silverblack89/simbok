<?php
namespace app\components;
use Yii;
class AccessControl extends \yii\base\ActionFilter
{
    public function beforeAction($action)
    {
        $moduleID = $action->controller->module->id;
        $controllerID = $action->controller->id;
        $actionID = $action->id;
        $user = \Yii::$app->user;
        // $userID = $user->id;
        $group = \app\models\User::find()->where([
            'id' => $user->id,
            ])->one();
        
        if(isset($group)){
            $groupID = $group->group_id;
        }else{
            $groupID = null;
        }

        if (!in_array($controllerID, ['default', 'site'])) {
            $auth = \app\models\Auth::find()->where([
                'module' => $moduleID,
                'controller' => $controllerID,
                'action' => $actionID,
                'group_id' => $groupID,
            ])->count();
            if ($auth==0) {
                if (!$action instanceof \yii\web\ErrorAction) {
                    if ($user->getIsGuest()) {
                        $user->loginRequired();
                    }else{
                        throw new \yii\web\ForbiddenHttpException('Anda tidak diizinkan untuk mengakses halaman ini!');
                    }
                }
            }
        }
        return true;
    }
}