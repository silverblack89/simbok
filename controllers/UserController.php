<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\UserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();

        if($model->unit_id == 'DINKES'){
            $model->setScenario('unitDinkes');
        }

        // if ($model->unit_id == 'DINKES') {
        //     $model->validatorList->add(
        //         CValidator::createValidator('required', $model, 'email')
        //     );
        // }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionChangepassword()
    {		
        $model = new User;

        $model = User::findOne(Yii::$app->user->identity->id);
        $model->setScenario('changePwd');

        if(isset($_POST['User'])){
            
            $POST_VARIABLE=Yii::$app->request->post('User');
            $model->attributes = $_POST['User'];
            $valid = $model->validate();
                    
            if($valid){        
                $model->password_hash = $POST_VARIABLE['repeat_password']; 
                // $model->auth_key = Yii::$app->security->generateRandomString(); 
                if($model->save()) {
                    // $this->redirect(array('changepassword', 'msg'=>'successfully changed password'));
                    Yii::$app->session->setFlash('success', 'Penggantian password berhasil');
                }else{
                    // $this->redirect(array('changepassword','msg'=>'password not changed'));
                    Yii::$app->session->setFlash('danger', 'Penggantian password gagal!');
                }
            }
            // else{
            //     $this->redirect(array('changepassword','msg'=>'failed'));
            // }
        }

        return $this->render('changepassword', [
            'model' => $model,
        ]);
    }
}
