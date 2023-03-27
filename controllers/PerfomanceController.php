<?php

namespace app\controllers;

use Yii;
use app\models\Perfomance;
use app\models\PerfomanceSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;

/**
 * PerfomanceController implements the CRUD actions for Perfomance model.
 */
class PerfomanceController extends Controller
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
     * Lists all Perfomance models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PerfomanceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Perfomance model.
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
     * Creates a new Perfomance model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id, $target)
    {
        $session = Yii::$app->session;
        $model = new Perfomance();

        $model->triwulan = $session['triwulan'];
        $model->tahun = $session['periodValue'];
        $model->activity_data_id = $id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // return $this->redirect(['period/detailpoa', 'p' => $session['triwulan']]);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                'model' => $model,
                'target' => $target
            ]);
        }else{
            return $this->render('create', [
                'model' => $model,
                'target' => $target
            ]);
        }
    }

    /**
     * Updates an existing Perfomance model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id, $target)
    {
        $session = Yii::$app->session;
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // return $this->redirect(['period/detailpoa', 'p' => $session['triwulan']]);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('update', [
                'model' => $model,
                'target' => $target
            ]);
        }else{
            return $this->render('update', [
                'model' => $model,
                'target' => $target
            ]);
        }
    }

    /**
     * Deletes an existing Perfomance model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        // return $this->redirect(['index']);
    }

    /**
     * Finds the Perfomance model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Perfomance the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Perfomance::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
