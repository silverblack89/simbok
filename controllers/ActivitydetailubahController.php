<?php

namespace app\controllers;

use Yii;
use app\models\Activitydetailubah;
use app\models\ActivitydetailubahSearch;
use app\models\Financialrealization;
use app\models\FinancialrealizationSearch;
use app\models\Account;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ActivitydetailubahController implements the CRUD actions for Activitydetailubah model.
 */
class ActivitydetailubahController extends Controller
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
     * Lists all Activitydetailubah models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ActivitydetailubahSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Activitydetailrubah model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $session = Yii::$app->session;
        $searchModel = new FinancialrealizationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['activity_detail_ubah_id'=>$id]);
        $detail = Activitydetailubah::findOne($id);
        $session['activityDetailUbahId'] = $id;
        $rekening = Account::findOne($detail->account_id);
        $session['activityDetailAccount'] = $rekening->nama_rekening;

        return $this->render('view', [
            'model' => $this->findModel($id),
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Activitydetailrubah model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id)
    {
        $POST_VARIABLE=Yii::$app->request->post('Activitydetailubah');
        $poa_vol_1 = $POST_VARIABLE['vol_1'];
        $poa_vol_2 = $POST_VARIABLE['vol_2'];
        $poa_unit_cost = $POST_VARIABLE['unit_cost'];

        $session = Yii::$app->session;
        $model = new Activitydetailubah();
        $model->activity_data_id = $id;
        $model->activity_detail_id = 0;

        $cekPoa = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu_ubah,0) pagu_ubah, sum(IFNULL(e.jumlah,0)) jumlah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as char),0),1,5) prosentase
        FROM activity_detail_ubah e
        LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN unit u ON u.id=p.unit_id
        WHERE p.tahun=:periodValue AND p.unit_id=:unitId
        GROUP BY p.unit_id, p.pagu_ubah
        ORDER BY u.puskesmas')
        ->bindValue(':periodValue', $session['periodValue'])
        ->bindValue(':unitId', Yii::$app->user->identity->unit_id)
        ->queryAll();

        foreach($cekPoa as $cekPoa){
            $model->total_pagu = $cekPoa['pagu_ubah'];
            if ($poa_vol_2 == null){
                $jumlah = $poa_vol_1 * str_replace(".", "", $poa_unit_cost);
            }else{
                $poa_vol_2 = str_replace(".", "", $poa_vol_2);
                $jumlah = $poa_vol_1 * $poa_vol_2 * str_replace(".", "", $poa_unit_cost);
            }

            $model->total_poa = $cekPoa['jumlah']+$jumlah;

            if($cekPoa['jumlah'] > $cekPoa['pagu_ubah'] && $cekPoa['pagu_ubah'] > 0){
                Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA Perubahan melebihi Pagu Perubahan.');
            }
        }

        $progress = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu_ubah,0) pagu_ubah, sum(IFNULL(e.jumlah,0)) jumlah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as char),0),1,5) prosentase
        FROM activity_detail_ubah e
        LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN unit u ON u.id=p.unit_id
        WHERE p.tahun=:periode AND p.unit_id=:unit
        GROUP BY p.unit_id, p.pagu_ubah
        ORDER BY u.puskesmas')
        ->bindValue(':periode', $session['periodValue'])
        ->bindValue(':unit', Yii::$app->user->identity->unit_id)
        ->queryAll();

        foreach($progress as $progress);

        if(!empty($progress)){
            $session['pagu_ubah'] = $progress['pagu_ubah'];
        }else{
            $session['pagu_ubah'] = 0;
            $progress['prosentase'] = null;
        }
        
        
        if ($progress['prosentase'] !== null){
            $session['prosentase'] = $progress['prosentase'];
        }else{
            $session['prosentase'] = 0;
        }
        
        if($progress['prosentase'] < 33.33){
            $session['barColor'] = 'progress-bar-success';
        }
        if($progress['prosentase'] >= 33.33 && $progress['prosentase']<=66.66){
            $session['barColor'] = 'progress-bar-warning';
        }
        if($progress['prosentase'] > 66.66){
            $session['barColor'] = 'progress-bar-danger';
        }

        if($progress['prosentase'] == 100){
            $session['barStatus'] = 'bar';
        }else{
            $session['barStatus'] = 'active progress-striped';
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['activitydataubah/view', 'id' => $session['activityDataId']]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Activitydetailubah model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $POST_VARIABLE=Yii::$app->request->post('Activitydetailubah');
        $poa_vol_1 = $POST_VARIABLE['vol_1'];
        $poa_vol_2 = $POST_VARIABLE['vol_2'];
        $poa_unit_cost = $POST_VARIABLE['unit_cost'];

        $session = Yii::$app->session;
        $model = $this->findModel($id);

        $cekPoa = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu_ubah,0) pagu_ubah, sum(IFNULL(e.jumlah,0)) jumlah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as char),0),1,5) prosentase
        FROM activity_detail_ubah e
        LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN unit u ON u.id=p.unit_id
        WHERE p.tahun=:periodValue AND p.unit_id=:unitId
        GROUP BY p.unit_id, p.pagu_ubah
        ORDER BY u.puskesmas')
        ->bindValue(':periodValue', $session['periodValue'])
        ->bindValue(':unitId', Yii::$app->user->identity->unit_id)
        ->queryAll();

        foreach($cekPoa as $cekPoa){
            $model->total_pagu = $cekPoa['pagu_ubah'];
            if ($poa_vol_2 == null){
                $jumlah = $poa_vol_1 * str_replace(".", "", $poa_unit_cost);
            }else{
                $poa_vol_2 = str_replace(".", "", $poa_vol_2);
                $jumlah = $poa_vol_1 * $poa_vol_2 * str_replace(".", "", $poa_unit_cost);
            }

            $model->total_poa = $cekPoa['jumlah']+$jumlah;
            
            if($cekPoa['jumlah'] > $cekPoa['pagu_ubah'] && $cekPoa['pagu_ubah'] > 0){
                Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA Perubahan melebihi Pagu Perubahan.');
            }
        }

        $progress = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu_ubah,0) pagu_ubah, sum(IFNULL(e.jumlah,0)) jumlah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as char),0),1,5) prosentase
        FROM activity_detail_ubah e
        LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN unit u ON u.id=p.unit_id
        WHERE p.tahun=:periode AND p.unit_id=:unit
        GROUP BY p.unit_id, p.pagu_ubah
        ORDER BY u.puskesmas')
        ->bindValue(':periode', $session['periodValue'])
        ->bindValue(':unit', Yii::$app->user->identity->unit_id)
        ->queryAll();

        foreach($progress as $progress);

        if(!empty($progress)){
            $session['pagu_ubah'] = $progress['pagu_ubah'];
        }else{
            $session['pagu_ubah'] = 0;
            $progress['prosentase'] = null;
        }
        
        if ($progress['prosentase'] !== null){
            $session['prosentase'] = $progress['prosentase'];
        }else{
            $session['prosentase'] = 0;
        }
        
        if($progress['prosentase'] < 33.33){
            $session['barColor'] = 'progress-bar-success';
        }
        if($progress['prosentase'] >= 33.33 && $progress['prosentase']<=66.66){
            $session['barColor'] = 'progress-bar-warning';
        }
        if($progress['prosentase'] > 66.66){
            $session['barColor'] = 'progress-bar-danger';
        }

        if($progress['prosentase'] == 100){
            $session['barStatus'] = 'bar';
        }else{
            $session['barStatus'] = 'active progress-striped';
        }


        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/activitydataubah/view', 'id' => $session['activityDataId']]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Activitydetailubah model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $session = Yii::$app->session;
        $this->findModel($id)->delete();

        return $this->redirect(['/activitydataubah/view', 'id' => $session['activityDataId']]);
    }

    /**
     * Finds the Activitydetailubah model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Activitydetailubah the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Activitydetailubah::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
