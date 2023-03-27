<?php

namespace app\controllers;

use Yii;
use app\models\Verification;
use app\models\VerificationSearch;
use app\models\Program;
use app\models\Status;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;
use yii\data\SqlDataProvider;

/**
 * VerificationController implements the CRUD actions for Verification model.
 */
class VerificationController extends Controller
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
     * Lists all Verification models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new VerificationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Verification model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        // return $this->render('view', [
        //     'model' => $this->findModel($id),
        // ]);

        $session = Yii::$app->session;
        $session['programId'] = $id;

        if($session['poa'] == 'def'){
            $query = 'SELECT g.nama_program, s.nama_pelayanan, v.nama_kegiatan,  IFNULL(a.bentuk_kegiatan, v.nama_kegiatan) bentuk_kegiatan, 
            a.sasaran, a.target, a.lokasi, a.pelaksana, 
            c.nama_rekening, f.realisasi_vol_1, f.realisasi_satuan_1, realisasi_vol_2, IFNULL(f.realisasi_satuan_2,"") realisasi_satuan_2, f.realisasi_vol_1*IFNULL(f.realisasi_vol_2,1) realisasi_vol, f.realisasi_unit_cost, f.realisasi_jumlah
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN program g ON g.id=s.program_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN account c ON c.id=e.account_id
            LEFT JOIN financial_realization f ON f.activity_detail_id=e.id
            WHERE p.unit_id="'.$session['unitId'].'" AND p.tahun='.$session['periodValue'].' AND g.id='.$id.'
            ORDER BY g.id, s.id, v.id, a.id';
        }else{
            $query = 'SELECT g.nama_program, s.nama_pelayanan, v.nama_kegiatan,  IFNULL(a.bentuk_kegiatan, v.nama_kegiatan) bentuk_kegiatan, 
            a.sasaran, a.target, a.lokasi, a.pelaksana, 
            c.nama_rekening, f.realisasi_vol_1, f.realisasi_satuan_1, realisasi_vol_2, IFNULL(f.realisasi_satuan_2,"") realisasi_satuan_2, f.realisasi_vol_1*IFNULL(f.realisasi_vol_2,1) realisasi_vol, f.realisasi_unit_cost, f.realisasi_jumlah
            FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN program g ON g.id=s.program_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN account c ON c.id=e.account_id
            LEFT JOIN financial_realization f ON f.activity_detail_id=e.id
            WHERE p.unit_id="'.$session['unitId'].'" AND p.tahun='.$session['periodValue'].' AND g.id='.$id.'
            ORDER BY g.id, s.id, v.id, a.id'; 
        }

        // return $query;

        $program = Program::findOne($id);
        $programName = $program->nama_program;

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model2 = $dataProvider->getModels();

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('list', [
                'dataProvider' => $dataProvider,
                'programName' => $programName,
                ]);
        }else{
            return $this->render('list', [
                'dataProvider' => $dataProvider,
                'programName' => $programName,
                ]);
        }
    }

    /**
     * Creates a new Verification model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id, $revisi, $revised)
    {
        $session = Yii::$app->session;
        $session['programId'] = $id;

        if($session['poa'] == 'def'){
            $p = 'P';
            $query = 'SELECT g.nama_program, s.nama_pelayanan, v.nama_kegiatan,  IFNULL(a.bentuk_kegiatan, v.nama_kegiatan) bentuk_kegiatan, 
            a.sasaran, a.target, a.lokasi, a.pelaksana, 
            c.nama_rekening, e.vol_1, e.satuan_1, e.vol_2 vol_2, IFNULL(e.satuan_2,"") satuan_2, vol_1*IFNULL(vol_2,1) vol, e.unit_cost, e.jumlah
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN program g ON g.id=s.program_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN account c ON c.id=e.account_id
            WHERE p.unit_id="'.$session['unitId'].'" AND p.tahun='.$session['periodValue'].' AND g.id='.$id.'
            ORDER BY g.id, s.id, v.id, a.id';
        }

        if($session['poa'] == 'perubahan'){
            $p = 'R';
            $query = 'SELECT g.nama_program, s.nama_pelayanan, v.nama_kegiatan,  IFNULL(a.bentuk_kegiatan, v.nama_kegiatan) bentuk_kegiatan, 
            a.sasaran, a.target, a.lokasi, a.pelaksana, 
            c.nama_rekening, e.vol_1, e.satuan_1, e.vol_2 vol_2, IFNULL(e.satuan_2,"") satuan_2, vol_1*IFNULL(vol_2,1) vol, e.unit_cost, e.jumlah
            FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN program g ON g.id=s.program_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN account c ON c.id=e.account_id
            WHERE p.unit_id="'.$session['unitId'].'" AND p.tahun='.$session['periodValue'].' AND g.id='.$id.'
            ORDER BY g.id, s.id, v.id, a.id';
        }

        $program = Program::findOne($id);

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model2 = $dataProvider->getModels();

        $status = Status::find()->where([
            'tahun' => $session['periodValue'],
            'unit_id' => $session['unitId'],
        ])->one();

        if($session['poa'] == 'def'){
            if($status->modul_1 == $p){
                $kunci = true;
            }else{
                $kunci = false;
            }
        }else{
            if($status->modul_3 == $p){
                $kunci = true;
            }else{
                $kunci = false;
            }
        }

        $verifikasi = Verification::find()
        ->where(['unit_id' => $session['unitId'], 'program_id' => $id, 'modul' => $p])
        ->one();

        if(isset($verifikasi)){
            $session['verifId'] = $verifikasi->id;
        }
        
        if ($verifikasi == null) {
            $model = new Verification();
            $model->unit_id = $session['unitId'];
            $model->program_id = $id;
            $model->modul = $p;
            $model->revisi = $revisi;
            $status = 'unverified';
        }else{
            if($verifikasi->revisi == 1){
                $status = 'revision';
            }else{
                $status = 'verified';
            }
            $model = $this->findModel($verifikasi->id);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['program/verif', 'id' => $session['unitId'], 'p' => $session['poa']]);
        }elseif (Yii::$app->request->isAjax) {
            if($status=='revision'){
                return $this->renderAjax('update', [
                    'model' => $model,
                    'model2' => $model2,
                    'dataProvider' => $dataProvider,
                    'programName' => $program->nama_program,
                    'status' => $status,
                    'revisi' => $revisi,
                    'kunci' => $kunci
                ]);
            }else{
                return $this->renderAjax('create', [
                    'model' => $model,
                    'model2' => $model2,
                    'dataProvider' => $dataProvider,
                    'programName' => $program->nama_program,
                    'status' => $status,
                    'kunci' => $kunci
                ]);
            }

        } else {
            if($status=='revision'){
                return $this->render('update', [
                    'model' => $model,
                    'model2' => $model2,
                    'dataProvider' => $dataProvider,
                    'programName' => $program->nama_program,
                    'status' => $status,
                    'revisi' => $revisi,
                    'kunci' => $kunci
                ]);
            }else{
                return $this->render('create', [
                    'model' => $model,
                    'model2' => $model2,
                    'dataProvider' => $dataProvider,
                    'programName' => $program->nama_program,
                    'status' => $status,
                    'kunci' => $kunci
                ]);
            }
        }
    }

    /**
     * Updates an existing Verification model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id, $revisi, $revised)
    {
        $session = Yii::$app->session;
        $verifikasi = Verification::find()
        ->where(['unit_id' => $session['unitId'], 'program_id' => $id, 'modul' => 'P'])
        ->one();
        $session['verifId'] = $verifikasi->id;

        $model = $this->findModel($id);
        $model->revisi = $revisi;
        $model->perbaikan = $revised;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if (Yii::$app->user->identity->unit_id == 'DINKES'){
                return $this->redirect(['program/verif', 'id' => $session['unitId'], 'p' => $session['poa']]);
            }else{
                return $this->redirect(['program/list']);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionRevisi($id, $revised)
    {
        $session = Yii::$app->session;
        $revisi = Yii::$app->db->createCommand('SELECT p.*, v.id verif_id, CASE WHEN v.revisi=0 THEN "Sudah" WHEN v.revisi=1 THEN "Revisi" ELSE "Belum" END verifikasi,
        CASE WHEN v.revisi=0 THEN "btn btn-xs btn-success" WHEN v.revisi=1 THEN "btn btn-xs btn-warning" ELSE "btn btn-xs btn-danger" END buttonColor, v.catatan FROM program p
        LEFT JOIN verification v ON v.program_id=p.id AND v.unit_id=:unitId
        WHERE p.tahun=:periodValue AND p.id=:programId ORDER BY p.id')
        ->bindValue(':periodValue', $session['periodValue'])
        ->bindValue(':unitId', Yii::$app->user->identity->unit_id)
        ->bindValue(':programId', $id)
        ->queryAll();

        foreach($revisi as $row);
        // return $row['verif_id'];

        $verifikasi = Verification::find()
        ->where(['unit_id' => $session['unitId'], 'program_id' => $id, 'modul' => 'P'])
        ->one();
        $session['verifId'] = $verifikasi->id;

        $model = $this->findModel($row['verif_id']);
        // $model->perbaikan = $revised;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['program/list']);
            // return 'saved';
        }elseif (Yii::$app->request->isAjax) {
            return $this->renderAjax('revision', [
                        'model' => $model,
                        'id' => $id,
                        'kunci' => true,
                        'status' => 'revision',
                        'revised' => true
            ]);
        } else {
            return $this->render('revision', [
                        'model' => $model,
                        'id' => $id,
                        'kunci' => true,
                        'status' => 'revision',
                        'revised' => true
            ]);
        }
    }

    /**
     * Deletes an existing Verification model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $session = Yii::$app->session;
        $this->findModel($id)->delete();

        return $this->redirect(['program/verif', 'id' => $session['unitId'], 'p' => $session['poa']]);
    }

    /**
     * Finds the Verification model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Verification the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Verification::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
