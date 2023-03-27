<?php

namespace app\controllers;

use Yii;
use app\models\Sp2d;
use app\models\Sp2dSearch;
use app\models\Unit;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;

/**
 * Sp2dController implements the CRUD actions for Sp2d model.
 */
class Sp2dController extends Controller
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
     * Lists all Sp2d models.
     * @return mixed
     */
    public function actionIndex($unit,$tahun)
    {
        $session = Yii::$app->session;
        $searchModel = new Sp2dSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['unit_id' => $unit, 'YEAR(tanggal)' => $tahun]);
        $dataProvider->query->orderBy(['tanggal'=>SORT_DESC]);

        $session['unit'] = $unit;
        $unit = Unit::find()->where(['id' => $unit])->all();
        foreach($unit as $unt);

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'tahun' => $tahun,
                'puskesmas' => $unt->puskesmas,
                'unit' => $unt->id
            ]);
        }else{
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'tahun' => $tahun,
                'puskesmas' => $unt->puskesmas,
                'unit' => $unt->id
            ]);
        }
    }

    /**
     * Displays a single Sp2d model.
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
     * Creates a new Sp2d model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($unit)
    {
        $session = Yii::$app->session;
        $model = new Sp2d();

        $model->unit_id = $unit;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'unit' => $unit, 'tahun' => $session['periodValue']]);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                'model' => $model,
                'unit' => $unit
            ]);
        }else{
            return $this->render('create', [
                'model' => $model,
                'unit' => $unit
            ]);
        }
    }

    /**
     * Updates an existing Sp2d model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $session = Yii::$app->session;
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'unit' => $model->unit_id, 'tahun' => $session['periodValue']]);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }else{
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Sp2d model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id,$unit)
    {
        $session = Yii::$app->session;
        $this->findModel($id)->delete();

        return $this->redirect(['index', 'unit' => $unit, 'tahun' => $session['periodValue']]);
    }

    /**
     * Finds the Sp2d model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Sp2d the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Sp2d::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
