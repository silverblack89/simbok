<?php

namespace app\controllers;

use Yii;
use app\models\Deptreal;
use app\models\DeptrealSearch;
use app\models\Deptsubactivity;
use app\models\Deptgroupsp2d;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;

/**
 * DeptrealController implements the CRUD actions for Deptreal model.
 */
class DeptrealController extends Controller
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
     * Lists all Deptreal models.
     * @return mixed
     */
    public function actionIndex($id,$st)
    {
        $session = Yii::$app->session;
        $sub = Deptsubactivity::findOne($id);
        $session['deptSubActivityId'] = $id;
        $session['st'] = $st;

        $searchModel = new DeptrealSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->leftJoin('dept_period', '`dept_period`.`id` = `dept_real`.`dept_period_id`');
        $dataProvider->query->andWhere(['dept_sub_activity_id' => $id]);
        $dataProvider->query->andWhere(['dept_period.unit_id' => Yii::$app->user->identity->username]);
        $dataProvider->query->orderBy(['bulan' => SORT_DESC, 'dept_period_id' => SORT_DESC]);

        // $real = Yii::$app->db->createCommand('SELECT v.id, v.nama_sub_kegiatan, IFNULL(SUM(e.jumlah),0) poa, IFNULL(SUM(realisasi.jumlah),0) realisasi, 
        // SUBSTRING(IFNULL(cast(SUM(IFNULL(realisasi.jumlah,0))/SUM(IFNULL(e.jumlah,0))*100 as char),0),1,5) prosentase
        // FROM dept_sub_activity_detail e
        // LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
        // LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
        // LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
        // LEFT JOIN dept_program g ON g.id=s.dept_program_id
        // LEFT JOIN dept_period p ON p.id=a.dept_period_id
        // LEFT JOIN 
        // (
        //     SELECT r.dept_sub_activity_id, dept_period_id, SUM(r.jumlah) jumlah FROM dept_real r
        //     group BY r.dept_sub_activity_id
        // ) realisasi ON realisasi.dept_sub_activity_id=v.id AND realisasi.dept_period_id=p.id
        // WHERE p.tahun=:tahun AND v.id=:id AND p.unit_id=:unit')

        $real = Yii::$app->db->createCommand('SELECT po.id, po.nama_sub_kegiatan, po.jumlah poa, IFNULL(SUM(realisasi.jumlah),0) realisasi, 
        SUBSTRING(IFNULL(cast(SUM(IFNULL(realisasi.jumlah,0))/SUM(IFNULL(po.jumlah,0))*100 as char),0),1,5) prosentase 
        FROM 
        ( 
            SELECT v.id, v.nama_sub_kegiatan, IFNULL(SUM(e.jumlah),0) jumlah 
            FROM dept_sub_activity_detail e 
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id 
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id 
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id 
            LEFT JOIN dept_program g ON g.id=s.dept_program_id 
            LEFT JOIN dept_period p ON p.id=a.dept_period_id 
            WHERE p.tahun=:tahun AND v.id=:id AND p.unit_id=:unit 
            group BY v.id
        ) AS po
        LEFT JOIN
        (
            SELECT r.dept_sub_activity_id, dept_period_id, SUM(r.jumlah) jumlah 
            FROM dept_real r 
            group BY r.dept_sub_activity_id 
        ) realisasi ON realisasi.dept_sub_activity_id=po.id')
        ->bindValue(':tahun', $session['deptPeriodValue'])
        ->bindValue(':id', $id)
        ->bindValue(':unit', Yii::$app->user->identity->username)
        ->queryAll();

        // return $real->getRawSql();

        foreach($real as $real);

        $session['poa'] = $real['poa'];

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'id' => $id,
            'title' => $sub->nama_sub_kegiatan,
            'real' => $real,
            'st' => $st,
        ]);
    }

    /**
     * Displays a single Deptreal model.
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
     * Creates a new Deptreal model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id,$st)
    {
        $session = Yii::$app->session;
        $session['st'] = $st;

        $cek_sp2d = Yii::$app->db->createCommand('SELECT IFNULL(SUM(s.jumlah),0) total_sp, g.nama,
        SUM(CASE WHEN MONTH(s.tanggal)=1 THEN TRUE ELSE FALSE END) jan,
        SUM(CASE WHEN MONTH(s.tanggal)=2 THEN TRUE ELSE FALSE END) feb,
        SUM(CASE WHEN MONTH(s.tanggal)=3 THEN TRUE ELSE FALSE END) mar,
        SUM(CASE WHEN MONTH(s.tanggal)=4 THEN TRUE ELSE FALSE END) apr,
        SUM(CASE WHEN MONTH(s.tanggal)=5 THEN TRUE ELSE FALSE END) mei,
        SUM(CASE WHEN MONTH(s.tanggal)=6 THEN TRUE ELSE FALSE END) jun,
        SUM(CASE WHEN MONTH(s.tanggal)=7 THEN TRUE ELSE FALSE END) jul,
        SUM(CASE WHEN MONTH(s.tanggal)=8 THEN TRUE ELSE FALSE END) agu,
        SUM(CASE WHEN MONTH(s.tanggal)=9 THEN TRUE ELSE FALSE END) sep,
        SUM(CASE WHEN MONTH(s.tanggal)=10 THEN TRUE ELSE FALSE END) okt,
        SUM(CASE WHEN MONTH(s.tanggal)=11 THEN TRUE ELSE FALSE END) nov,
        SUM(CASE WHEN MONTH(s.tanggal)=12 THEN TRUE ELSE FALSE END) des
        FROM dept_sp2d s
        LEFT JOIN dept_group_sp2d g ON g.id=s.dept_group_sp2d_id
        WHERE YEAR(s.tanggal)=:tahun AND g.id=:sp2dId group BY s.dept_group_sp2d_id')
        ->bindValue(':sp2dId', substr($st,2))
        ->bindValue(':tahun', $session['deptPeriodValue'])
        ->queryAll();

        if(empty($cek_sp2d)){
            $jan = false;$feb = false;$mar = false;$apr = false;$mei = false;$jun = false;$jul = false;$agu = false;$sep = false;$okt = false;$nov = false;$des = false;
        }else{
            foreach($cek_sp2d as $ceksp2d){
                $jan = $ceksp2d['jan'];
                $feb = $ceksp2d['feb'];
                $mar = $ceksp2d['mar'];
                $apr = $ceksp2d['apr'];
                $mei = $ceksp2d['mei'];
                $jun = $ceksp2d['jun'];
                $jul = $ceksp2d['jul'];
                $agu = $ceksp2d['agu'];
                $sep = $ceksp2d['sep'];
                $okt = $ceksp2d['okt'];
                $nov = $ceksp2d['nov'];
                $des = $ceksp2d['des'];
            }
        }

        if(substr($st,0,1) == 1){
            $cek_realisasi = Yii::$app->db->createCommand('SELECT IFNULL(sum(d.jumlah),0) total_realisasi
            FROM dept_real d
            LEFT JOIN dept_sub_activity v ON v.id=d.dept_sub_activity_id
            LEFT JOIN dept_activity a ON a.id=v.dept_activity_id
            LEFT JOIN dept_program p ON p.id=a.dept_program_id
            LEFT JOIN dept_period r ON r.id=d.dept_period_id
            WHERE v.id=:subId AND r.tahun=:tahun AND r.unit_id=:unit AND p.dept_group_sp2d_id_1=:sp2dId')
            ->bindValue(':tahun', $session['deptPeriodValue'])
            ->bindValue(':subId', $id)
            ->bindValue(':sp2dId', substr($st,2))
            ->bindValue(':unit', Yii::$app->user->identity->username)
            ->queryAll();
        }else{
            $cek_realisasi = Yii::$app->db->createCommand('SELECT IFNULL(sum(d.jumlah),0) total_realisasi
            FROM dept_real d
            LEFT JOIN dept_sub_activity v ON v.id=d.dept_sub_activity_id
            LEFT JOIN dept_activity a ON a.id=v.dept_activity_id
            LEFT JOIN dept_program p ON p.id=a.dept_program_id
            LEFT JOIN dept_period r ON r.id=d.dept_period_id
            WHERE v.id=:subId AND r.tahun=:tahun AND r.unit_id=:unit AND p.dept_group_sp2d_id_2=:sp2dId')
            ->bindValue(':tahun', $session['deptPeriodValue'])
            ->bindValue(':subId', $id)
            ->bindValue(':sp2dId', substr($st,2))
            ->bindValue(':unit', Yii::$app->user->identity->username)
            ->queryAll();
        }

        foreach($cek_realisasi as $cek_real);

        $model = new Deptreal();
        $model->dept_sub_activity_id = $id;
        $model->dept_period_id = $session['deptPeriodId']; 
        $model->dept_group_sp2d_id = substr($st,2); 

        $grup_sp2d = Deptgroupsp2d::findOne(substr($st,2));

        if(empty($cek_sp2d)){$sp2d = 0;}else{$sp2d = $ceksp2d['total_sp'];}
        if(empty($cek_realisasi)){$real = 0;}else{$real = $cek_real['total_realisasi'];}

        $model->sisa_sp2d = $sp2d-$real;
        $model->poa = $session['poa'];
        $model->realisasi = $real;

        $POST_VARIABLE=Yii::$app->request->post('Deptreal');
        $jumlah = str_replace(".", "", $POST_VARIABLE['jumlah']);

        if ($model->load(Yii::$app->request->post())) {
            // return $jumlah;
            if($jumlah + $model->realisasi <= $model->poa){
                $model->save();
                return $this->redirect(['index', 'id' => $id, 'st' => $session['st']]);
            }else{
                Yii::$app->session->setFlash('error', 'Entri realisasi melebihi total POA');
                return $this->redirect(['index', 'id' => $id, 'st' => $session['st']]);
            }    
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                'model' => $model,
                'jan' => $jan,
                'feb' => $feb,
                'mar' => $mar,
                'apr' => $apr,
                'mei' => $mei,
                'jun' => $jun,
                'jul' => $jul,
                'agu' => $agu,
                'sep' => $sep,
                'okt' => $okt,
                'nov' => $nov,
                'des' => $des,
                'grupLabel' => $grup_sp2d->nama
            ]);
        }else{
            return $this->render('create', [
                'model' => $model,
                'jan' => $jan,
                'feb' => $feb,
                'mar' => $mar,
                'apr' => $apr,
                'mei' => $mei,
                'jun' => $jun,
                'jul' => $jul,
                'agu' => $agu,
                'sep' => $sep,
                'okt' => $okt,
                'nov' => $nov,
                'des' => $des,
                'grupLabel' => $grup_sp2d->nama
            ]);
        }
    }

    /**
     * Updates an existing Deptreal model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $session = Yii::$app->session;
        $model = $this->findModel($id);

        $dept_program = Deptprogram::find()->where(['id' => $model->dept_program_id])->one();

        $cek_sp2d = Yii::$app->db->createCommand('SELECT IFNULL(SUM(s.jumlah),0) total_sp, g.nama,
        SUM(CASE WHEN MONTH(s.tanggal)=1 THEN TRUE ELSE FALSE END) jan,
        SUM(CASE WHEN MONTH(s.tanggal)=2 THEN TRUE ELSE FALSE END) feb,
        SUM(CASE WHEN MONTH(s.tanggal)=3 THEN TRUE ELSE FALSE END) mar,
        SUM(CASE WHEN MONTH(s.tanggal)=4 THEN TRUE ELSE FALSE END) apr,
        SUM(CASE WHEN MONTH(s.tanggal)=5 THEN TRUE ELSE FALSE END) mei,
        SUM(CASE WHEN MONTH(s.tanggal)=6 THEN TRUE ELSE FALSE END) jun,
        SUM(CASE WHEN MONTH(s.tanggal)=7 THEN TRUE ELSE FALSE END) jul,
        SUM(CASE WHEN MONTH(s.tanggal)=8 THEN TRUE ELSE FALSE END) agu,
        SUM(CASE WHEN MONTH(s.tanggal)=9 THEN TRUE ELSE FALSE END) sep,
        SUM(CASE WHEN MONTH(s.tanggal)=10 THEN TRUE ELSE FALSE END) okt,
        SUM(CASE WHEN MONTH(s.tanggal)=11 THEN TRUE ELSE FALSE END) nov,
        SUM(CASE WHEN MONTH(s.tanggal)=12 THEN TRUE ELSE FALSE END) des
        FROM dept_sp2d s
        LEFT JOIN dept_group_sp2d g ON g.id=s.dept_group_sp2d_id
        WHERE YEAR(s.tanggal)=:tahun AND g.id=:sp2dId group BY s.dept_group_sp2d_id')
        ->bindValue(':sp2dId', $dept_program->dept_group_sp2d_id)
        ->bindValue(':tahun', $session['deptPeriodValue'])
        ->queryAll();

        if(empty($cek_sp2d)){
            $jan = false;$feb = false;$mar = false;$apr = false;$mei = false;$jun = false;$jul = false;$agu = false;$sep = false;$okt = false;$nov = false;$des = false;
        }else{
            foreach($cek_sp2d as $ceksp2d){
                $jan = $ceksp2d['jan'];
                $feb = $ceksp2d['feb'];
                $mar = $ceksp2d['mar'];
                $apr = $ceksp2d['apr'];
                $mei = $ceksp2d['mei'];
                $jun = $ceksp2d['jun'];
                $jul = $ceksp2d['jul'];
                $agu = $ceksp2d['agu'];
                $sep = $ceksp2d['sep'];
                $okt = $ceksp2d['okt'];
                $nov = $ceksp2d['nov'];
                $des = $ceksp2d['des'];
            }
        }

        $cek_realisasi = Yii::$app->db->createCommand('SELECT IFNULL(sum(d.jumlah),0) total_realisasi
        FROM dept_real d
        LEFT JOIN dept_program p ON p.id=d.dept_program_id
        LEFT JOIN dept_period r ON r.id=d.dept_period_id
        WHERE p.id=:progId AND r.tahun=:tahun AND r.unit_id=:unit AND p.dept_group_sp2d_id=:sp2dId')
        ->bindValue(':tahun', $session['deptPeriodValue'])
        ->bindValue(':progId', $dept_program->id)
        ->bindValue(':sp2dId', $dept_program->dept_group_sp2d_id)
        ->bindValue(':unit', Yii::$app->user->identity->username)
        ->queryAll();

        foreach($cek_realisasi as $cek_real);

        if(empty($sp2d) && empty($cek_real)){
            $model->sisa_sp2d = 0;
        }else{
            $model->sisa_sp2d = $sp2d['total_sp']-$cek_real['total_realisasi'];
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' => $session['deptProgramId']]);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('update', [
                'model' => $model,
                'jan' => $jan,
                'feb' => $feb,
                'mar' => $mar,
                'apr' => $apr,
                'mei' => $mei,
                'jun' => $jun,
                'jul' => $jul,
                'agu' => $agu,
                'sep' => $sep,
                'okt' => $okt,
                'nov' => $nov,
                'des' => $des,
            ]);
        }else{
            return $this->render('update', [
                'model' => $model,
                'jan' => $jan,
                'feb' => $feb,
                'mar' => $mar,
                'apr' => $apr,
                'mei' => $mei,
                'jun' => $jun,
                'jul' => $jul,
                'agu' => $agu,
                'sep' => $sep,
                'okt' => $okt,
                'nov' => $nov,
                'des' => $des,
            ]);
        }
    }

    /**
     * Deletes an existing Deptreal model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $session = Yii::$app->session;
        $this->findModel($id)->delete();
        
        return $this->redirect(['index', 'id' => $session['deptSubActivityId'], 'st' => $session['st']]);
    }

    /**
     * Finds the Deptreal model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Deptreal the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Deptreal::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
