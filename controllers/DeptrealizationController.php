<?php

namespace app\controllers;

use Yii;
use app\models\Deptrealization;
use app\models\DeptrealizationSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;

/**
 * DeptrealizationController implements the CRUD actions for Deptrealization model.
 */
class DeptrealizationController extends Controller
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
     * Lists all Deptrealization models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DeptrealizationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Deptrealization model.
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
     * Creates a new Deptrealization model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id,$poa,$prev)
    {
        $session = Yii::$app->session;
        $model = new Deptrealization();

        $model->jml_poa = $poa;
        $model->realisasi_lalu = $prev;

        $model->triwulan = $session['triwulan'];
        $model->tahun = $session['deptPeriodValue'];
        $model->dept_sub_activity_detail_id = $id;

        $model->sp2d = $session['sp2d'];
        $cekreal = Yii::$app->db->createCommand('SELECT IFNULL(SUM(r.jumlah),0) tot_real FROM dept_realization r
        LEFT JOIN dept_sub_activity_detail de ON de.id=r.dept_sub_activity_detail_id
        LEFT JOIN dept_sub_activity_data da ON da.id=de.dept_sub_activity_data_id
        LEFT JOIN dept_period p ON p.id=da.dept_period_id
        WHERE p.unit_id=:unit AND r.tahun=:tahun AND r.triwulan=:triwulan')
        ->bindValue(':unit', Yii::$app->user->identity->unit_id)
        ->bindValue(':tahun', $session['deptPeriodValue'])
        ->bindValue(':triwulan', $session['triwulan'])
        ->queryAll();

        foreach($cekreal as $cr){
            $model->total_realisasi = $cr['tot_real'];
        }


        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // return $this->redirect(['deptperiod/detailpoa', 'p' => $session['triwulan']]);
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
     * Updates an existing Deptrealization model.
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

        $model->sp2d = $session['sp2d'];
        $cekreal = Yii::$app->db->createCommand('SELECT IFNULL(SUM(r.jumlah),0) tot_real FROM dept_realization r
        LEFT JOIN dept_sub_activity_detail de ON de.id=r.dept_sub_activity_detail_id
        LEFT JOIN dept_sub_activity_data da ON da.id=de.dept_sub_activity_data_id
        LEFT JOIN dept_period p ON p.id=da.dept_period_id
        WHERE p.unit_id=:unit AND r.tahun=:tahun AND r.triwulan=:triwulan')
        ->bindValue(':unit', Yii::$app->user->identity->unit_id)
        ->bindValue(':tahun', $session['deptPeriodValue'])
        ->bindValue(':triwulan', $session['triwulan'])
        ->queryAll();

        foreach($cekreal as $cr){
            $model->total_realisasi = $cr['tot_real']-$model->jumlah;
        }


        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // return $this->redirect(['deptperiod/detailpoa', 'p' => $session['triwulan']]);
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
     * Deletes an existing Deptrealization model.
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
     * Finds the Deptrealization model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Deptrealization the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Deptrealization::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
