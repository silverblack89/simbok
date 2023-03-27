<?php

namespace app\controllers;

use Yii;
use app\models\Financialrealization;
use app\models\FinancialrealizationSearch;
use app\models\Activitydetail;
use app\models\Activitydetailubah;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;

/**
 * FinancialrealizationController implements the CRUD actions for Financialrealization model.
 */
class FinancialrealizationController extends Controller
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
     * Lists all Financialrealization models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new FinancialrealizationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Financialrealization model.
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
     * Creates a new Financialrealization model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id)
    {
        $POST_VARIABLE=Yii::$app->request->post('Financialrealization');
        $real_vol_1 = $POST_VARIABLE['realisasi_vol_1'];
        $real_vol_2 = $POST_VARIABLE['realisasi_vol_2'];
        $real_unit_cost = $POST_VARIABLE['realisasi_unit_cost'];
        
        $session = Yii::$app->session;

        if($session['poa'] == 'def') {
            $detail = Activitydetail::findOne($session['activityDetailId']);
            $model = new Financialrealization();
            $model->activity_detail_id = $id;
            $model->activity_detail_ubah_id = 0;
            $model->realisasi_satuan_1 = $detail->satuan_1;
            $model->realisasi_satuan_2 = $detail->satuan_2;
            // return $model->realisasi_satuan_1.' '.$model->realisasi_satuan_2 ;

            $cekReal = Yii::$app->db->createCommand('SELECT ad.jumlah total_poa, sum(fr.realisasi_jumlah) total_realisasi, 
            SUM(CASE WHEN fr.bulan=1 THEN TRUE ELSE FALSE END) jan,
            SUM(CASE WHEN fr.bulan=2 THEN TRUE ELSE FALSE END) feb,
            SUM(CASE WHEN fr.bulan=3 THEN TRUE ELSE FALSE END) mar,
            SUM(CASE WHEN fr.bulan=4 THEN TRUE ELSE FALSE END) apr,
            SUM(CASE WHEN fr.bulan=5 THEN TRUE ELSE FALSE END) mei,
            SUM(CASE WHEN fr.bulan=6 THEN TRUE ELSE FALSE END) jun,
            SUM(CASE WHEN fr.bulan=7 THEN TRUE ELSE FALSE END) jul,
            SUM(CASE WHEN fr.bulan=8 THEN TRUE ELSE FALSE END) agu,
            SUM(CASE WHEN fr.bulan=9 THEN TRUE ELSE FALSE END) sep,
            SUM(CASE WHEN fr.bulan=10 THEN TRUE ELSE FALSE END) okt,
            SUM(CASE WHEN fr.bulan=11 THEN TRUE ELSE FALSE END) nov,
            SUM(CASE WHEN fr.bulan=12 THEN TRUE ELSE FALSE END) des
            FROM activity_detail ad 
            LEFT JOIN financial_realization fr ON fr.activity_detail_id=ad.id
            WHERE ad.id=:detailId')
            ->bindValue(':detailId', $detail->id)
            ->queryAll();

            foreach($cekReal as $cekReal){
                $model->total_poa = $cekReal['total_poa'];
                $model->total_realisasi = $cekReal['total_realisasi']+(str_replace(".", "", $real_vol_1)*str_replace(".", "", $real_vol_2)*str_replace(".", "", $real_unit_cost));
                $session['jan'] = $cekReal['jan'];
                $session['feb'] = $cekReal['feb'];
                $session['mar'] = $cekReal['mar'];
                $session['apr'] = $cekReal['apr'];
                $session['mei'] = $cekReal['mei'];
                $session['jun'] = $cekReal['jun'];
                $session['jul'] = $cekReal['jul'];
                $session['agu'] = $cekReal['agu'];
                $session['sep'] = $cekReal['sep'];
                $session['okt'] = $cekReal['okt'];
                $session['nov'] = $cekReal['nov'];
                $session['des'] = $cekReal['des'];
            }

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['activitydetail/view', 'id' => $id]); 
            }

            return $this->render('create', [
                'model' => $model,
            ]);
        }else{
            $detail = Activitydetailubah::findOne($session['activityDetailUbahId']);
            $model = new Financialrealization();
            $model->activity_detail_id = 0;
            $model->activity_detail_ubah_id = $id;
            $model->realisasi_satuan_1 = $detail->satuan_1;
            $model->realisasi_satuan_2 = $detail->satuan_2;
            // return $model->realisasi_satuan_1.' '.$model->realisasi_satuan_2 ;

            $cekReal = Yii::$app->db->createCommand('SELECT ad.jumlah total_poa, sum(fr.realisasi_jumlah) total_realisasi, 
            SUM(CASE WHEN fr.bulan=1 THEN TRUE ELSE FALSE END) jan,
            SUM(CASE WHEN fr.bulan=2 THEN TRUE ELSE FALSE END) feb,
            SUM(CASE WHEN fr.bulan=3 THEN TRUE ELSE FALSE END) mar,
            SUM(CASE WHEN fr.bulan=4 THEN TRUE ELSE FALSE END) apr,
            SUM(CASE WHEN fr.bulan=5 THEN TRUE ELSE FALSE END) mei,
            SUM(CASE WHEN fr.bulan=6 THEN TRUE ELSE FALSE END) jun,
            SUM(CASE WHEN fr.bulan=7 THEN TRUE ELSE FALSE END) jul,
            SUM(CASE WHEN fr.bulan=8 THEN TRUE ELSE FALSE END) agu,
            SUM(CASE WHEN fr.bulan=9 THEN TRUE ELSE FALSE END) sep,
            SUM(CASE WHEN fr.bulan=10 THEN TRUE ELSE FALSE END) okt,
            SUM(CASE WHEN fr.bulan=11 THEN TRUE ELSE FALSE END) nov,
            SUM(CASE WHEN fr.bulan=12 THEN TRUE ELSE FALSE END) des
            FROM activity_detail_ubah ad 
            LEFT JOIN financial_realization fr ON fr.activity_detail_ubah_id=ad.id
            WHERE ad.id=:detailId')
            ->bindValue(':detailId', $detail->id)
            ->queryAll();

            foreach($cekReal as $cekReal){
                $model->total_poa = $cekReal['total_poa'];
                $model->total_realisasi = $cekReal['total_realisasi']+(str_replace(".", "", $real_vol_1)*str_replace(".", "", $real_vol_2)*str_replace(".", "", $real_unit_cost));
                $session['jan'] = $cekReal['jan'];
                $session['feb'] = $cekReal['feb'];
                $session['mar'] = $cekReal['mar'];
                $session['apr'] = $cekReal['apr'];
                $session['mei'] = $cekReal['mei'];
                $session['jun'] = $cekReal['jun'];
                $session['jul'] = $cekReal['jul'];
                $session['agu'] = $cekReal['agu'];
                $session['sep'] = $cekReal['sep'];
                $session['okt'] = $cekReal['okt'];
                $session['nov'] = $cekReal['nov'];
                $session['des'] = $cekReal['des'];
            }

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['activitydetailubah/view', 'id' => $id]); 
            }

            return $this->render('create', [
                'model' => $model,
            ]);
        }

        // if ($model->load(Yii::$app->request->post()) && $model->save()) {
        //     return $this->redirect(['activitydetail/view', 'id' => $id]); 
        // }elseif (Yii::$app->request->isAjax) {
        //     return $this->renderAjax('_form', [
        //                 'model' => $model
        //     ]);
        // } else {
        //     return $this->render('_form', [
        //                 'model' => $model
        //     ]);
        // }
    }

    /**
     * Updates an existing Financialrealization model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $session = Yii::$app->session;
        $model = $this->findModel($id);

        $cekReal = Yii::$app->db->createCommand('SELECT ad.jumlah total_poa, sum(fr.realisasi_jumlah) total_realisasi FROM activity_detail ad 
        LEFT JOIN financial_realization fr ON fr.activity_detail_id=ad.id
        WHERE ad.id=:detailId')
        ->bindValue(':detailId', $session['activityDetailId'])
        ->queryAll();

        foreach($cekReal as $cekReal){
            $model->total_poa = $cekReal['total_poa'];
            $model->total_realisasi = $cekReal['total_realisasi']+(str_replace(".", "",$model->realisasi_vol_1)*str_replace(".", "",$model->realisasi_vol_2)*str_replace(".", "",$model->realisasi_unit_cost));
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['activitydetail/view', 'id' => $session['activityDetailId']]); 
        }elseif (Yii::$app->request->isAjax) {
            return $this->renderAjax('_form', [
                        'model' => $model
            ]);
        } else {
            return $this->render('_form', [
                        'model' => $model
            ]);
        }
    }

    /**
     * Deletes an existing Financialrealization model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $session = Yii::$app->session;
        $this->findModel($id)->delete();

        return $this->redirect(['activitydetail/view', 'id' => $session['activityDetailId']]);
        // return $session['activityDetailId'];
    }

    /**
     * Finds the Financialrealization model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Financialrealization the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Financialrealization::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
