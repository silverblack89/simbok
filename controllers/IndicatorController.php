<?php

namespace app\controllers;

use Yii;
use app\models\Indicator;
use app\models\IndicatorSearch;
use app\models\Program;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;

/**
 * IndicatorController implements the CRUD actions for Indicator model.
 */
class IndicatorController extends Controller
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
     * Lists all Indicator models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new IndicatorSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionList($id)
    {
        $searchModel = new IndicatorSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['program_id'=>$id]);
        $program = Program::findOne($id);
        $session = Yii::$app->session;
        $session['programId'] = $id;

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'id' => $program->id,
            'name' => $program->nama_program,
        ]);
    }

    /**
     * Displays a single Indicator model.
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
     * Creates a new Indicator model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id)
    {
        $model = new Indicator();
        $model->program_id = $id;
        $model->data_komulatif = '1';
        $session = Yii::$app->session;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['program/view', 'id' => $session['programId']]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Indicator model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $session = Yii::$app->session;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['program/view', 'id' => $session['programId']]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Indicator model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        $session = Yii::$app->session;
        return $this->redirect(['/program/view', 'id' => $session['programId']]);
    }

    /**
     * Finds the Indicator model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Indicator the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Indicator::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
