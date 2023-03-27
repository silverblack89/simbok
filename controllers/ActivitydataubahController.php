<?php

namespace app\controllers;

use Yii;
use app\models\Activity;
use app\models\Activitydataubah;
use app\models\ActivitydataubahSearch;
use app\models\Activitydetailubah;
use app\models\ActivitydetailubahSearch;
use app\models\Status;
use app\models\Verification;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;
use yii\data\SqlDataProvider;
use app\models\Program;
use app\models\Service;

/**
 * ActivitydataubahController implements the CRUD actions for Activitydataubah model.
 */
class ActivitydataubahController extends Controller
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

    public function actionList($id)
    {
        $session = Yii::$app->session;
        $session['activityId'] = $id;

        $activity = Activity::findOne($id);
        $session['activityName'] = $activity->nama_kegiatan;

        $searchModel = new ActivitydataubahSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $dataProvider->query->andWhere(['activity_id'=>$id, 'period_id'=>$session['periodId']]);

        $status = Status::find()->where([
            'tahun' => $session['periodValue'],
            'unit_id' => Yii::$app->user->identity->unit_id,
        ])->one();

        if(!empty($status)){
            if($status->modul_3!=="R") {
                $session['status_ubah'] = '';
            }else{
                $session['status_ubah'] = 'disabled';
            }
        }

        $verification = Verification::find()->where([
            'program_id' => $session['programId'],
            'unit_id' => Yii::$app->user->identity->unit_id,
            'modul' => 'R',
        ])->one();

        if(isset($verification)){
            if($verification->revisi == 1){
                $session['revisi_ubah'] = true;
            }else{
                $session['revisi_ubah'] = false;
            }
        }else{
            $session['revisi_ubah'] = false;
        }

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

        return $this->render('list', [
            'searchModel' => $searchModel,  
            'dataProvider' => $dataProvider,
            'id' => $id,
        ]);
    }

    /**
     * Lists all Activitydataubah models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ActivitydataubahSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Activitydataubah model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $session = Yii::$app->session;

        // $activityDataUbah = Activitydataubah::findOne($id);

        $searchModel = new ActivitydetailubahSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['activity_data_id'=>$id]);
        $activity = Activity::findOne($session['activityId']);
        $session['activityDataUbahId'] = $id;
        $session['activityName'] = $activity->nama_kegiatan;

        // $model = $dataProvider->getModels();
        // foreach ($model as $model);

        // return var_dump($model);

        $status = Status::find()->where([
            'tahun' => $session['periodValue'],
            'unit_id' => Yii::$app->user->identity->unit_id,
        ])->one();

        if($status->modul_3!=="R") {
            $session['status_ubah'] = '';
        }else{
            $session['status_ubah'] = 'disabled';
        }
        if($status->modul_4!=="L") {
            $session['status_real'] = '';
        }else{
            $session['status_real'] = 'disabled';
        }

        $verification = Verification::find()->where([
            'program_id' => $session['programId'],
            'unit_id' => Yii::$app->user->identity->unit_id,
            'modul' => 'R',
        ])->one();

        if($verification !== NULL){
            if($verification->revisi == 1){
                $session['revisi_ubah'] = true;
            }else{
                $session['revisi_ubah'] = false;
            }
        }else{
            $session['revisi_ubah'] = false;
        }

        // return $session['status_real'];

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
        
        return $this->render('view', [
            'model' => $this->findModel($id),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            // 'id' => $id,
        ]);
    }

    /**
     * Creates a new Activitydataubah model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id, $modul)
    {
        $session = Yii::$app->session;
        $model = new Activitydataubah();

        $model->activity_data_id = 0;
        $model->activity_id=$id;
        $model->period_id=$session['periodId'];
        $activity = Activity::findOne($id);
        $session['activityName'] = $activity->nama_kegiatan;
        $session['activityId'] = $activity->id;

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

        $progress = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu_ubah,0) pagu_ubah, sum(IFNULL(e.jumlah,0)) jumlah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as char),0),1,5) prosentase
        FROM activity_detail_ubah e
        LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN unit u ON u.id=p.unit_id
        WHERE p.tahun=:periode AND p.unit_id=:unit
        GROUP BY p.unit_id, p.pagu_ubah
        UNION SELECT t.id, t.puskesmas, "0", "0", "0" FROM unit t WHERE t.id="P3309020101"
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
            return $this->redirect(['activitydataubah/list', 'id' => $session['activityId']]);
        }

        return $this->render('create', [
            'model' => $model,
            'modul' => $modul,
        ]);
    }

    /**
     * Updates an existing Activitydataubah model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id, $modul, $mid)
    {
        $session = Yii::$app->session;
        $model = $this->findModel($id);

        if($modul !== 'new'){
            if($modul == 'program'){
                $query='SELECT p.id, p.nama_program nama FROM program p
                WHERE p.tahun='.$session['periodValue'].' 
                AND p.id="'.$session['programId'].'" ORDER BY p.id';
                $session['modul'] = 'service';
            }
            if($modul == 'service'){
                $query='SELECT s.id, s.nama_pelayanan nama FROM service s
                WHERE s.program_id='.$mid.' ORDER BY s.id';
                $session['modul'] = 'activity';
                $session['programIdNew'] = $mid;

            }
            if($modul == 'activity'){
                $query='SELECT a.id, a.nama_kegiatan nama FROM activity a
                WHERE a.service_id='.$mid.' ORDER BY a.id';
                $session['modul'] = 'select';
                $session['serviceIdNew'] = $mid;
            }
            if($modul == 'select'){
                $query='SELECT a.id, a.nama_kegiatan nama FROM activity a
                WHERE a.service_id='.$mid.' ORDER BY a.id';
                $session['activityIdNew'] = $mid;
            }

            $dataProvider = new SqlDataProvider([
                'sql' => $query,
                'pagination' => false
            ]);

            $model2 = $dataProvider->getModels();
            
            $program = Program::findOne($session['programIdNew']);
            $model->programId = $program->nama_program;
            $service = Service::findOne($session['serviceIdNew']);
            $model->serviceId = $service->nama_pelayanan;
            $activity = Activity::findOne($session['activityIdNew']);
            $model->activityId = $activity->nama_kegiatan;
            $model->activity_id = $activity->id;
        }else{
            $dataProvider = null;
            $model2 = null;
        }

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
            return $this->redirect(['activitydataubah/list', 'id' => $session['activityId']]);
        }elseif (Yii::$app->request->isAjax) {
            return $this->renderAjax('update', [
                'model' => $model,
                'model2' => $model2,
                'modul' => $modul,
                'dataProvider' => $dataProvider,
            ]);
        }else{
            return $this->render('update', [
                'model' => $model,
                'model2' => $model2,
                'modul' => $modul,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    /**
     * Deletes an existing Activitydataubah model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $session = Yii::$app->session;
        $data = Activitydataubah::find()->where(['id' => $id])->one();
        $detail = Activitydetailubah::find()->where(['activity_data_id' => $id])->one();

        if(empty($detail)){
            $this->findModel($id)->delete();
            Yii::$app->session->setFlash('Berhasil', 'Data telah dihapus!');
            return $this->redirect(['activitydataubah/list', 'id' => $session['activityId']]);
            // return "Berhasil dihapus";
        }else{
            Yii::$app->session->setFlash('error', 'Data tidak bisa dihapus! ada detail kegiatan didalamnya.');
            return $this->redirect(['activitydataubah/list', 'id' => $session['activityId']]);
            // return "Gagal dihapus";
        }
    }

    /**
     * Finds the Activitydatageser model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Activitydatageser the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Activitydataubah::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
