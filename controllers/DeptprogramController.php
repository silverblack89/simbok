<?php

namespace app\controllers;

use Yii;
use app\models\Deptprogram;
use app\models\DeptprogramSearch;
use app\models\Deptstatus;
use app\models\Unit;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;
use yii\data\SqlDataProvider;

/**
 * DeptprogramController implements the CRUD actions for Deptprogram model.
 */
class DeptprogramController extends Controller
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
     * Lists all Deptprogram models.
     * @return mixed
     */
    public function actionIndex($tahun)
    {
        $session = Yii::$app->session;
        $session['programYear']=$tahun;
        $searchModel = new DeptprogramSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['tahun'=>$session['programYear']]);
        $dataProvider->pagination = false;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Deptprogram model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $program = Deptprogram::findOne($id);
        $session = Yii::$app->session;
        $session['programId'] = $id;
        $session['programNama'] = $program->nama_program;

        return $this->render('view', [
            'model' => $this->findModel($id),
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
            CASE WHEN (v.revisi=0 AND v.perbaikan=0) THEN "progress-bar-success" WHEN (v.revisi=1 AND v.perbaikan=0) THEN "progress-bar-warning" WHEN (v.revisi=1 AND v.perbaikan=1) THEN "progress-bar" ELSE "progress-bar-danger" END barColor FROM dept_program p 
            LEFT JOIN verification v ON v.program_id=p.id AND v.unit_id="'.$id.'" AND v.modul = "P"
            WHERE p.tahun='.$session['deptPeriodValue'].' ORDER BY p.id';
        }elseif($p == 'pergeseran'){
            $session['poa'] = 'pergeseran';
            $session['poaLabel'] = ' Pergeseran';
        }elseif($p == 'perubahan'){
            $session['poa'] = 'perubahan';
            $session['poaLabel'] = ' Perubahan';
            $query = 'SELECT p.*, CASE WHEN (v.revisi=0 AND v.perbaikan=0) THEN "Sudah" WHEN (v.revisi=1 AND v.perbaikan=0) THEN "Revisi" WHEN (v.revisi=1 AND v.perbaikan=1) THEN "Direvisi" ELSE "Belum" END status,
            CASE WHEN (v.revisi=0 AND v.perbaikan=0) THEN "progress-bar-success" WHEN (v.revisi=1 AND v.perbaikan=0) THEN "progress-bar-warning" WHEN (v.revisi=1 AND v.perbaikan=1) THEN "progress-bar" ELSE "progress-bar-danger" END barColor FROM dept_program p 
            LEFT JOIN verification v ON v.program_id=p.id AND v.unit_id="'.$id.'" AND v.modul = "R"
            WHERE p.tahun='.$session['deptPeriodValue'].' ORDER BY p.id';
        }

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model = $dataProvider->getModels();

        // $progress = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)),0) prosentase
        // FROM dept_sub_activity_detail e
        // LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
        // LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
        // LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
        // LEFT JOIN dept_period p ON p.id=a.dept_period_id
        // LEFT JOIN unit u ON u.id=p.unit_id
        // WHERE p.tahun=:periode AND p.unit_id=:unit
        // GROUP BY p.unit_id, p.pagu
        // ORDER BY u.puskesmas')
        // ->bindValue(':periode', $session['deptPeriodValue'])
        // ->bindValue(':unit', $id)
        // ->queryAll();

        // foreach($progress as $progress);

        // if ($progress['prosentase'] !== null){
        //     $session['prosentase'] = $progress['prosentase'];
        // }else{
        //     $session['prosentase'] = 0;
        // }
        
        // if($progress['prosentase'] < 33.33){
        //     $session['barColor'] = 'progress-bar-success';
        // }
        // if($progress['prosentase'] >= 33.33 && $progress['prosentase']<=66.66){
        //     $session['barColor'] = 'progress-bar-warning';
        // }
        // if($progress['prosentase'] > 66.66){
        //     $session['barColor'] = 'progress-bar-danger';
        // }

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

        if (Yii::$app->user->identity->group_id == 'ADM'){
            $searchModel = new deptProgramSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            $dataProvider->query->andWhere(['tahun'=>$session['deptPeriodValue']]);
        }else{
            $query='SELECT p.*, sp1.nama sp2d1, sp2.nama sp2d2, CASE WHEN (v.revisi=0 AND v.perbaikan=0) THEN "Sudah" WHEN (v.revisi=1 AND v.perbaikan=0) THEN "Revisi" WHEN (v.revisi=1 AND v.perbaikan=1) THEN "Direvisi" ELSE "Belum" END verifikasi,
            CASE WHEN (v.revisi=0 AND v.perbaikan=0) THEN "showModalButton btn btn-xs btn-success" WHEN (v.revisi=1 AND v.perbaikan=0) THEN "showModalButton btn btn-xs btn-warning" WHEN (v.revisi=1 AND v.perbaikan=1) THEN "showModalButton btn btn-xs btn-primary" ELSE "showModalButton btn btn-xs btn-danger" END buttonColor, v.catatan FROM dept_program p
            LEFT JOIN verification v ON v.program_id=p.id AND v.unit_id="'.Yii::$app->user->identity->unit_id.'"
            LEFT JOIN dept_group_sp2d sp1 ON sp1.id=p.dept_group_sp2d_id_1
            LEFT JOIN dept_group_sp2d sp2 ON sp2.id=p.dept_group_sp2d_id_2
            WHERE p.tahun='.$session['deptPeriodValue'].' ORDER BY p.id';

            $dataProvider = new SqlDataProvider([
                'sql' => $query,
                'pagination' => false
            ]);

            $model = $dataProvider->getModels();
        }

        $status = Deptstatus::find()->where([
            'tahun' => $session['deptPeriodValue'],
            'unit_id' => Yii::$app->user->identity->unit_id,
        ])->one();
        
        if($status == null){
            $session['status_real'] = 'NULL';
        }elseif($status->modul_4=="L") {
            $session['status_real'] = 'disabled';
        }else{
            $session['status_real'] = '';
        }

        // $cekPoa = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as char),0),1,5) prosentase
        // FROM activity_detail e
        // LEFT JOIN activity_data a ON a.id=e.activity_data_id
        // LEFT JOIN activity v ON v.id=a.activity_id
        // LEFT JOIN service s ON s.id=v.service_id
        // LEFT JOIN period p ON p.id=a.period_id
        // LEFT JOIN unit u ON u.id=p.unit_id
        // WHERE p.tahun=:periodValue AND p.unit_id=:unitId
        // GROUP BY p.unit_id, p.pagu
        // ORDER BY u.puskesmas')
        // ->bindValue(':periodValue', $session['deptPeriodValue'])
        // ->bindValue(':unitId', Yii::$app->user->identity->unit_id)
        // ->queryAll();

        // foreach($cekPoa as $cekPoa){
        //     if($cekPoa['jumlah'] > $cekPoa['pagu'] && $cekPoa['pagu'] > 0){
        //         Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA melebihi Pagu BOK.');
        //     }
        // }

        // $progress = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as char),0),1,5) prosentase
        // FROM activity_detail e
        // LEFT JOIN activity_data a ON a.id=e.activity_data_id
        // LEFT JOIN activity v ON v.id=a.activity_id
        // LEFT JOIN service s ON s.id=v.service_id
        // LEFT JOIN period p ON p.id=a.period_id
        // LEFT JOIN unit u ON u.id=p.unit_id
        // WHERE p.tahun=:periode AND p.unit_id=:unit
        // GROUP BY p.unit_id, p.pagu
        // ORDER BY u.puskesmas')
        // ->bindValue(':periode', $session['periodValue'])
        // ->bindValue(':unit', Yii::$app->user->identity->unit_id)
        // ->queryAll();
        
        // foreach($progress as $progress);

        // $session = Yii::$app->session;
        // $session['pagu'] = $progress['pagu'];
        // if ($progress['prosentase'] !== null){
        //     $session['prosentase'] = $progress['prosentase'];
        // }else{
        //     $session['prosentase'] = 0;
        // }
        
        // if($progress['prosentase'] < 33.33){
        //     $session['barColor'] = 'progress-bar-success';
        // }
        // if($progress['prosentase'] >= 33.33 && $progress['prosentase']<=66.66){
        //     $session['barColor'] = 'progress-bar-warning';
        // }
        // if($progress['prosentase'] > 66.66){
        //     $session['barColor'] = 'progress-bar-danger';
        // }

        // if($progress['prosentase'] == 100){
        //     $session['barStatus'] = 'bar';
        // }else{
        //     $session['barStatus'] = 'active progress-striped';
        // }
        
        if (Yii::$app->user->identity->group_id == 'ADM'){
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
     * Creates a new Deptprogram model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $session = Yii::$app->session;
        $model = new Deptprogram();
        $model->tahun = $session['programYear'];
        $model->aktif = 1;
        $model->pagu = 0;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Deptprogram model.
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
     * Deletes an existing Deptprogram model.
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
    }

    /**
     * Finds the Deptprogram model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Deptprogram the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Deptprogram::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
