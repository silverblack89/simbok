<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Period;
use app\models\Deptperiod;
use yii\web\session;
use yii\db\Expression;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['login', 'logout'],
                'rules' => [
                    [
                        'actions' => ['login'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        // $session = Yii::$app->session;
        // $session->open(); // open a session
        // $session['loginTime'] = date('Y-m-d H:i:s');
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        // if ($model->load(Yii::$app->request->post()) && $model->login()) {
        //     return $this->goBack();
        // }

        // $model->password = '';
        // return $this->render('login', [
        //     'model' => $model,
        // ]);
        
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            // Yii::$app->session->setFlash('success', 'Login berhasil');

            if(substr(Yii::$app->user->identity->unit_id,0,3) == 'P33'){
                $period = Period::find()->where(['unit_id' => Yii::$app->user->identity->unit_id])->all();
                foreach ($period as $prd){
                    $prd->last_seen = new Expression('NOW()');
                    $prd->save(false);
                }
            }else{
                $period = Deptperiod::find()->where(['unit_id' => Yii::$app->user->identity->unit_id])->all();
                foreach ($period as $prd){
                    $prd->last_seen = new Expression('NOW()');
                    $prd->save(false);
                }
            }
            // return $this->goBack();
            return $this->goHome();
        }elseif (Yii::$app->request->isAjax) {
            $model->password = '';$model->password = '';
            return $this->renderAjax('login', [
                        'model' => $model
            ]);
        } else {
            $model->password = '';
            // return $this->render('login', [
            //             'model' => $model
            // ]);
            
            Yii::$app->session->setFlash('error', 'Username atau password salah!');
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        }

        // return $this->goHome();
    }

    // public function actionLogin()
    // {
    //     $model = new LoginForm();
    //     if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post()))
    //     {
    //         Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    //         return ActiveForm::validate($model);
    //     }
    //     if ($model->load(Yii::$app->request->post()) && $model->login())
    //     { 
    //         $session = Yii::$app->session;
    //         $session->set('username', $_POST['LoginForm']['username']);
    //         $session->set('password', $_POST['LoginForm']['password']);
    //         return $this->goHome();
    //     } 
    //     else
    //     {
    //         return $this->renderAjax( 'login', [ 'model' => $model ] );
    //     }
    //     return $this->renderAjax( 'login', [ 'model' => $model ] );
    // }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionUnits()
    {
        return $this->render('units', [ 
            'model' => $model
        ]);
    }

    // public function actionSumberdana()
    // {
    //     $session = Yii::$app->session;
    //     $session['sumberDana'] = $_COOKIE['sumberdana'];

    //     return $session['sumberDana'];
    // }
}
