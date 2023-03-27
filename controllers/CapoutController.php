<?php

namespace app\controllers;

use Yii;
use app\models\Capout;
use app\models\CapoutSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;

/**
 * CapoutController implements the CRUD actions for Capout model.
 */
class CapoutController extends Controller
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
     * Lists all Capout models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CapoutSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Capout model.
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
     * Creates a new Capout model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Capout();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Capout model.
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
     * Deletes an existing Capout model.
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
     * Finds the Capout model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Capout the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Capout::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionConfirm($no,$co1,$co2,$co3,$co4)
    {
        $session = Yii::$app->session;
        $nomor = $session['tahun'] .$no;

        $data = Capout::find()->where(['nomor' => $nomor, 'unit_id' => Yii::$app->user->identity->unit_id, 'bulan' => $session['bulan']])->all();
        foreach($data as $capout);

        if(isset($capout['id'])){
            // echo "<script>alert('update')</script>";
            $findcapout = Capout::find()->where(['id' => $capout['id']])->all();
        }else{
            // $newcapout =  new Capout();
            // $newcapout->nomor = $nomor;
            // $newcapout->unit_id = Yii::$app->user->identity->unit_id;
            // $newcapout->bulan = $session['bulan'];
            // $newcapout->save();

            $insert = Yii::$app->db->createCommand()->insert('capout', [
                'nomor' => $nomor,
                'unit_id' => Yii::$app->user->identity->unit_id,
                'bulan' => $session['bulan'],
            ])->execute();

            $findcapout = Capout::find()->where(['nomor' => $nomor, 'unit_id' => Yii::$app->user->identity->unit_id, 'bulan' => $session['bulan']])->all();
        }

        // return $findcapout->createCommand()->getRawSql();

        foreach($findcapout as $capout);
        // return var_dump($findcapout);
        $model = $this->findModel($capout['id']);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(Yii::$app->request->referrer);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('update', [
                'model' => $model,
                'co1' => $co1,
                'co2' => $co2,
                'co3' => $co3,
                'co4' => $co4,
            ]);
        }else{
            return $this->render('update', [
                'model' => $model,
                'co1' => $co1,
                'co2' => $co2,
                'co3' => $co3,
                'co4' => $co4,
            ]);
        }
    }

    public function actionTemp()
    {
        $session = Yii::$app->session;

        $session['re_1'] = $_COOKIE['re1'];
        $session['re_2'] = $_COOKIE['re2'];
        $session['re_3'] = $_COOKIE['re3'];
        $session['re_4'] = $_COOKIE['re4'];
        $session['re_5'] = $_COOKIE['re5'];
        $session['re_6'] = $_COOKIE['re6'];
        $session['re_7'] = $_COOKIE['re7'];
        $session['re_8'] = $_COOKIE['re8'];
        $session['re_9'] = $_COOKIE['re9'];
        $session['re_10'] = $_COOKIE['re10'];
        $session['re_11'] = $_COOKIE['re11'];
        $session['re_12'] = $_COOKIE['re12'];
        $session['re_13'] = $_COOKIE['re13'];
        $session['re_14'] = $_COOKIE['re14'];
        $session['re_15'] = $_COOKIE['re15'];
        $session['re_16'] = $_COOKIE['re16'];
        $session['re_17'] = $_COOKIE['re17'];
        $session['re_18'] = $_COOKIE['re18'];
        $session['re_19'] = $_COOKIE['re19'];
        $session['re_20'] = $_COOKIE['re20'];
        $session['re_21'] = $_COOKIE['re21'];
        $session['re_22'] = $_COOKIE['re22'];
        $session['re_23'] = $_COOKIE['re23'];
        $session['re_24'] = $_COOKIE['re24'];
        $session['re_25'] = $_COOKIE['re25'];
        $session['re_26'] = $_COOKIE['re26'];
        $session['re_27'] = $_COOKIE['re27'];
        $session['re_28'] = $_COOKIE['re28'];
        $session['re_29'] = $_COOKIE['re29'];
        $session['re_30'] = $_COOKIE['re30'];
    }
}
