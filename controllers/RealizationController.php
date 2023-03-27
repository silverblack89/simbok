<?php

namespace app\controllers;

use Yii;
use app\models\Realization;
use app\models\RealizationSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;

/**
 * RealizationController implements the CRUD actions for Realization model.
 */
class RealizationController extends Controller
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
     * Lists all Realization models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new RealizationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Realization model.
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
     * Creates a new Realization model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id,$poa,$prev)
    {
        $session = Yii::$app->session;
        $model = new Realization();

        $model->jml_poa = $poa;
        $model->realisasi_lalu = $prev;
        
        $model->triwulan = $session['triwulan'];
        $model->tahun = $session['periodValue'];
        $model->activity_detail_id = $id;

        $model->sp2d = $session['sp2dBln'];
        $cekreal = Yii::$app->db->createCommand('SELECT IFNULL(SUM(r.jumlah),0) tot_real FROM realization r
        LEFT JOIN activity_detail de ON de.id=r.activity_detail_id
        LEFT JOIN activity_data da ON da.id=de.activity_data_id
        LEFT JOIN period p ON p.id=da.period_id
        WHERE p.unit_id=:unit AND r.tahun=:tahun AND r.triwulan=:triwulan')
        ->bindValue(':unit', Yii::$app->user->identity->unit_id)
        ->bindValue(':tahun', $session['periodValue'])
        ->bindValue(':triwulan', $session['triwulan'])
        ->queryAll();

        foreach($cekreal as $cr){
            $model->total_realisasi = $cr['tot_real'];
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // return $this->redirect(['period/detailpoa', 'p' => $session['triwulan']]);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }else{
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Realization model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id,$poa,$prev)
    {
        $session = Yii::$app->session;
        $model = $this->findModel($id);

        $model->jml_poa = $poa;
        $model->realisasi_lalu = $prev;

        $model->sp2d = $session['sp2dBln'];
        $cekreal = Yii::$app->db->createCommand('SELECT IFNULL(SUM(r.jumlah),0) tot_real FROM realization r
        LEFT JOIN activity_detail de ON de.id=r.activity_detail_id
        LEFT JOIN activity_data da ON da.id=de.activity_data_id
        LEFT JOIN period p ON p.id=da.period_id
        WHERE p.unit_id=:unit AND r.tahun=:tahun AND r.triwulan=:triwulan')
        ->bindValue(':unit', Yii::$app->user->identity->unit_id)
        ->bindValue(':tahun', $session['periodValue'])
        ->bindValue(':triwulan', $session['triwulan'])
        ->queryAll();

        foreach($cekreal as $cr){
            $model->total_realisasi = $cr['tot_real']-$model->jumlah;
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // return $this->redirect(['period/detailpoa', 'p' => $session['triwulan']]);
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
     * Deletes an existing Realization model.
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
     * Finds the Realization model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Realization the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Realization::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
