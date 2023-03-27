<?php

namespace app\controllers;

use Yii;
use app\models\Program;
use app\models\ProgramSearch;
use app\models\Indicator;
use app\models\IndicatorSearch;
use app\models\Status;
use app\models\Unit;
use app\models\Period;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Session;
use yii\data\SqlDataProvider;

/**
 * ProgramController implements the CRUD actions for Program model.
 */
class ProgramController extends Controller
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
     * Lists all Program models.
     * @return mixed
     */
    public function actionIndex($tahun)
    {
        $session = Yii::$app->session;
        $session['programYear']=$tahun;
        $searchModel = new ProgramSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['tahun'=>$session['programYear']]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            // 'id' => $model->id
        ]);
    }

    public function actionProsesVerif($action)
    {
        if ($action=='verif_ok'){
            return 'OK';
        }else{
            return 'CANCEL';
        }
    }

    public function actionDetail($id)
    {
        $session = Yii::$app->session;
        $query = 'SELECT g.nama_program, s.nama_pelayanan, v.nama_kegiatan,  IFNULL(a.bentuk_kegiatan, v.nama_kegiatan) bentuk_kegiatan, 
        a.sasaran, a.target, a.lokasi, a.pelaksana, 
        c.nama_rekening, e.vol_1, e.satuan_1, IFNULL(e.vol_2,1) vol_2, IFNULL(e.satuan_2,"") satuan_2, vol_1*IFNULL(vol_2,1) vol, e.unit_cost, e.jumlah
        FROM activity_detail e
        LEFT JOIN activity_data a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN program g ON g.id=s.program_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN account c ON c.id=e.account_id
        WHERE p.unit_id="'.$session['unitId'].'" AND p.tahun='.$session['periodValue'].' AND g.id='.$id.'
        ORDER BY g.id, s.id, v.id, a.id';

        $program = Program::findOne($id);

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model = $dataProvider->getModels();

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('detail', [
                        'model' => $model,
                        'dataProvider' => $dataProvider,
                        'programName' => $program->nama_program
            ]);
        } else {
            return $this->render('detail', [
                        'model' => $model,
                        'dataProvider' => $dataProvider,
                        'programName' => $program->nama_program
            ]);
        }
    }

    public function actionReal($id, $p)
    {
        $session = Yii::$app->session;
        $session['view'] = 'real';

        if($p == 'def'){
            $session['poa'] = 'def';
            $session['poaLabel'] = '';
        }elseif($p == 'pergeseran'){
            $session['poa'] = 'pergeseran';
            $session['poaLabel'] = ' Pergeseran';
        }elseif($p == 'perubahan'){
            $session['poa'] = 'perubahan';
            $session['poaLabel'] = ' Perubahan';
        }

        $query = 'SELECT p.*, CASE WHEN (v.revisi=0 AND v.perbaikan=0) THEN "Sudah" WHEN (v.revisi=1 AND v.perbaikan=0) THEN "Revisi" WHEN (v.revisi=1 AND v.perbaikan=1) THEN "Direvisi" ELSE "Belum" END status,
        CASE WHEN (v.revisi=0 AND v.perbaikan=0) THEN "progress-bar-success" WHEN (v.revisi=1 AND v.perbaikan=0) THEN "progress-bar-warning" WHEN (v.revisi=1 AND v.perbaikan=1) THEN "progress-bar" ELSE "progress-bar-danger" END barColor FROM program p 
        LEFT JOIN verification v ON v.program_id=p.id AND v.unit_id="'.$id.'"
        WHERE p.tahun='.$session['periodValue'].' ORDER BY p.id';

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model = $dataProvider->getModels();

        $unit = Unit::findOne($id);
        $session['unitId'] = $id;

        return $this->render('list', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'namaUnit' => $unit->puskesmas,
            'title' => 'Data Realisasi',
        ]);
    }

    public function actionVerif($id, $p)
    {
        $session = Yii::$app->session;
        $session['view'] = 'verif';

        if($p == 'def'){
            $session['poa'] = 'def';
            $session['poaLabel'] = '';
            $query = 'SELECT p.*, CASE WHEN (v.revisi=0 AND v.perbaikan=0) THEN "Sudah" WHEN (v.revisi=1 AND v.perbaikan=0) THEN "Revisi" WHEN (v.revisi=1 AND v.perbaikan=1) THEN "Direvisi" ELSE "Belum" END status,
            CASE WHEN (v.revisi=0 AND v.perbaikan=0) THEN "progress-bar-success" WHEN (v.revisi=1 AND v.perbaikan=0) THEN "progress-bar-warning" WHEN (v.revisi=1 AND v.perbaikan=1) THEN "progress-bar" ELSE "progress-bar-danger" END barColor FROM program p 
            LEFT JOIN verification v ON v.program_id=p.id AND v.unit_id="'.$id.'" AND v.modul = "P"
            WHERE p.tahun='.$session['periodValue'].' ORDER BY p.id';
        }elseif($p == 'pergeseran'){
            $session['poa'] = 'pergeseran';
            $session['poaLabel'] = ' Pergeseran';
        }elseif($p == 'perubahan'){
            $session['poa'] = 'perubahan';
            $session['poaLabel'] = ' Perubahan';
            $query = 'SELECT p.*, CASE WHEN (v.revisi=0 AND v.perbaikan=0) THEN "Sudah" WHEN (v.revisi=1 AND v.perbaikan=0) THEN "Revisi" WHEN (v.revisi=1 AND v.perbaikan=1) THEN "Direvisi" ELSE "Belum" END status,
            CASE WHEN (v.revisi=0 AND v.perbaikan=0) THEN "progress-bar-success" WHEN (v.revisi=1 AND v.perbaikan=0) THEN "progress-bar-warning" WHEN (v.revisi=1 AND v.perbaikan=1) THEN "progress-bar" ELSE "progress-bar-danger" END barColor FROM program p 
            LEFT JOIN verification v ON v.program_id=p.id AND v.unit_id="'.$id.'" AND v.modul = "R"
            WHERE p.tahun='.$session['periodValue'].' ORDER BY p.id';
        }


        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model = $dataProvider->getModels();

        $progress = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)),0) prosentase
        FROM activity_detail e
        LEFT JOIN activity_data a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN unit u ON u.id=p.unit_id
        WHERE p.tahun=:periode AND p.unit_id=:unit
        GROUP BY p.unit_id, p.pagu
        ORDER BY u.puskesmas')
        ->bindValue(':periode', $session['periodValue'])
        ->bindValue(':unit', $id)
        ->queryAll();

        foreach($progress as $progress);

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

        $unit = Unit::findOne($id);
        $session['unitId'] = $id;

        if (Yii::$app->user->identity->username == 'admin'){
            $title = 'Data POA '.$session['poaLabel'];
        }else{
            $title = 'Data Verifikasi'.$session['poaLabel'];
        }

        return $this->render('list', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'namaUnit' => $unit->puskesmas,
            'title' => $title,
        ]);
    }

    public function actionList()
    {
        $session = Yii::$app->session;
        $session['unitId'] = Yii::$app->user->identity->unit_id;

        if (Yii::$app->user->identity->id == 'admin'){
            $searchModel = new ProgramSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            $dataProvider->query->andWhere(['tahun'=>$session['periodValue']]);
        }else{
            // CASE WHEN v.revisi=0 THEN "showModalButton btn btn-xs btn-success" WHEN v.revisi=1 THEN "showModalButton btn btn-xs btn-warning" ELSE "showModalButton btn btn-xs btn-danger" END buttonColor, v.catatan FROM program p
            $query='SELECT p.*, CASE WHEN (v.revisi=0 AND v.perbaikan=0) THEN "Sudah" WHEN (v.revisi=1 AND v.perbaikan=0) THEN "Revisi" WHEN (v.revisi=1 AND v.perbaikan=1) THEN "Direvisi" ELSE "Belum" END verifikasi,
            CASE WHEN (v.revisi=0 AND v.perbaikan=0) THEN "showModalButton btn btn-xs btn-success" WHEN (v.revisi=1 AND v.perbaikan=0) THEN "showModalButton btn btn-xs btn-warning" WHEN (v.revisi=1 AND v.perbaikan=1) THEN "showModalButton btn btn-xs btn-primary" ELSE "showModalButton btn btn-xs btn-danger" END buttonColor, v.catatan FROM program p
            
            LEFT JOIN verification v ON v.program_id=p.id AND v.unit_id="'.Yii::$app->user->identity->unit_id.'"
            WHERE p.tahun='.$session['periodValue'].' ORDER BY p.id';

            $dataProvider = new SqlDataProvider([
                'sql' => $query,
                'pagination' => false
            ]);

            $model = $dataProvider->getModels();

            foreach($model as $model){
                $session['stscovid'] = $model['covid'];
            }
        }

        $status = Status::find()->where([
            'tahun' => $session['periodValue'],
            'unit_id' => Yii::$app->user->identity->unit_id,
        ])->one();
        
        if($status == null){
            $session['status_real'] = 'NULL';
        }elseif($status->modul_4=="L") {
            $session['status_real'] = 'disabled';
        }else{
            $session['status_real'] = '';
        }

        if($session['poa'] == 'def'){
            $cekPoa = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as char),0),1,5) prosentase
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN unit u ON u.id=p.unit_id
            WHERE p.tahun=:periodValue AND p.unit_id=:unitId
            GROUP BY p.unit_id, p.pagu
            ORDER BY u.puskesmas')
            ->bindValue(':periodValue', $session['periodValue'])
            ->bindValue(':unitId', Yii::$app->user->identity->unit_id)
            ->queryAll();

            foreach($cekPoa as $cekPoa){
                if($cekPoa['jumlah'] > $cekPoa['pagu'] && $cekPoa['pagu'] > 0){
                    Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA melebihi Pagu BOK.');
                }
            }

            // $cekUkm = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as char),0),1,5) prosentase
            // FROM activity_detail e
            // LEFT JOIN activity_data a ON a.id=e.activity_data_id
            // LEFT JOIN activity v ON v.id=a.activity_id
            // LEFT JOIN service s ON s.id=v.service_id
            // LEFT JOIN period p ON p.id=a.period_id
            // LEFT JOIN unit u ON u.id=p.unit_id
            // LEFT JOIN program g ON g.id=s.program_id
            // WHERE p.tahun=:periodValue AND p.unit_id=:unitId AND g.covid=0
            // GROUP BY p.unit_id, p.pagu
            // ORDER BY u.puskesmas')
            // ->bindValue(':periodValue', $session['periodValue'])
            // ->bindValue(':unitId', Yii::$app->user->identity->unit_id)
            // ->queryAll();

            // foreach($cekUkm as $ukm){
            //     if($ukm['prosentase'] <= 60){
            //         Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA UKM kurang dari 60%.');
            //     }
    
            //     if($ukm['prosentase'] >= 65){
            //         Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA UKM melebihi 65%.');
            //     }
            // }

            // $cekCovid = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as char),0),1,5) prosentase
            // FROM activity_detail e
            // LEFT JOIN activity_data a ON a.id=e.activity_data_id
            // LEFT JOIN activity v ON v.id=a.activity_id
            // LEFT JOIN service s ON s.id=v.service_id
            // LEFT JOIN period p ON p.id=a.period_id
            // LEFT JOIN unit u ON u.id=p.unit_id
            // LEFT JOIN program g ON g.id=s.program_id
            // WHERE p.tahun=:periodValue AND p.unit_id=:unitId AND g.covid=1
            // GROUP BY p.unit_id, p.pagu
            // ORDER BY u.puskesmas')
            // ->bindValue(':periodValue', $session['periodValue'])
            // ->bindValue(':unitId', Yii::$app->user->identity->unit_id)
            // ->queryAll();

            // foreach($cekCovid as $cvd){
            //     if($cvd['prosentase'] <= 35){
            //         Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA COVID kurang dari 35%.');
            //     }
    
            //     if($cvd['prosentase'] >= 40){
            //         Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA COVID melebihi 40%.');
            //     }
            // }
        }else{
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
                if($cekPoa['jumlah'] > $cekPoa['pagu_ubah'] && $cekPoa['pagu_ubah'] > 0){
                    Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA Perubahan melebihi Pagu Perubahan.');
                }
            }
        }

        if($session['poa'] == 'def'){
            $progress = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as char),0),1,5) prosentase
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN unit u ON u.id=p.unit_id
            WHERE p.tahun=:periode AND p.unit_id=:unit
            GROUP BY p.unit_id, p.pagu
            ORDER BY u.puskesmas')
            ->bindValue(':periode', $session['periodValue'])
            ->bindValue(':unit', Yii::$app->user->identity->unit_id)
            ->queryAll();

            foreach($progress as $progress);

            $session = Yii::$app->session;

            if(!empty($progress)){
                $session['pagu'] = $progress['pagu'];
            }
        }else{
            $progress = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu_ubah,0) pagu_ubah, sum(IFNULL(e.jumlah,0)) jumlah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as char),0),1,5) prosentase
            FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN unit u ON u.id=p.unit_id
            WHERE p.tahun=:periode AND p.unit_id=:unit
            GROUP BY p.unit_id, p.pagu_ubah
            ORDER BY puskesmas')
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
        }
        
        
        if (isset($progress['prosentase'])){
            $session['prosentase'] = $progress['prosentase'];
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
        }else{
            $session['prosentase'] = 0;
        }
        
        
        if (Yii::$app->user->identity->id == 'admin'){
            return $this->render('list', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }else{
            return $this->render('list', [
                'dataProvider' => $dataProvider,
            ]);   
        }
    }

    /**
     * Displays a single Program model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $searchModel = new IndicatorSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['program_id'=>$id]);
        
        $program = Program::findOne($id);
        $session = Yii::$app->session;
        $session['programId'] = $id;
        $session['programNama'] = $program->nama_program;
        
        return $this->render('view', [
            'model' => $this->findModel($id),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'id' => $id,
        ]);
    }

    /**
     * Creates a new Program model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $session = Yii::$app->session;
        $model = new Program();
        $model->tahun = $session['programYear'];
        $model->aktif = 1;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Program model.
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
     * Deletes an existing Program model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $session = Yii::$app->session;
        $this->findModel($id)->delete();

        return $this->redirect(['index', 'tahun' => $session['programYear']]);

        // try {
        //     if($this->findModel($id)->delete()){
        //         Yii::$app->getSession()->setFlash(
        //             'success','Data deleted!'
        //         );
        //         return $this->redirect(['index']);
        //     }
        //  } catch (\Exception $e) {
        //     Yii::$app->getSession()->setFlash(
        //         'error',"{$e->getMessage()}"
        //     );
        //     return $this->redirect(['index']);
        //  }
    }

    /**
     * Finds the Program model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Program the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Program::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionGetAccess()
    {
        if($_COOKIE['checked'] == 'false'){
            $value = 0;
        }else{
            $value = 1;
        }
        // return $value;

        $program = Program::findOne($_COOKIE['id']);
        $program->akses = $value;
        $program->save();

        if ($_COOKIE['checked'] == 'true'){
            return "Hak akses berhasil diaktifkan";
        }else{
            return "Hak akses berhasil dinonaktifkan";
        }

        Program::refresh();
    }
}
