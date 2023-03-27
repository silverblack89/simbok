<?php

namespace app\controllers;

use Yii;
use app\models\Deptverification;
use app\models\DeptverificationSearch;
use app\models\Deptprogram;
use app\models\Deptstatus;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;
use yii\data\SqlDataProvider;

/**
 * DeptverificationController implements the CRUD actions for Deptverification model.
 */
class DeptverificationController extends Controller
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
     * Lists all Deptverification models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DeptverificationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Deptverification model.
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
     * Creates a new Deptverification model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id, $revisi, $revised)
    {
        $session = Yii::$app->session;
        $session['deptProgramId'] = $id;

        if($session['poa'] == 'def'){
            $p = 'P';
            $query = 'SELECT g.nama_program, s.nama_kegiatan, v.nama_sub_kegiatan,  IFNULL(a.bentuk_kegiatan, v.nama_sub_kegiatan) bentuk_kegiatan, 
            a.indikator_hasil, a.target_hasil, a.indikator_keluaran, a.target_keluaran, 
            c.nama_rekening, e.vol_1, e.satuan_1, e.vol_2 vol_2, IFNULL(e.satuan_2,"") satuan_2, vol_1*IFNULL(vol_2,1) vol, e.unit_cost, e.jumlah
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_program g ON g.id=s.dept_program_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN account c ON c.id=e.account_id
            WHERE p.unit_id="'.$session['unitId'].'" AND p.tahun='.$session['deptPeriodValue'].' AND g.id='.$id.'
            ORDER BY g.id, s.id, v.id, a.id';
        }

        if($session['poa'] == 'perubahan'){
            $p = 'R';
            $query = 'SELECT g.nama_program, s.nama_kegiatan, v.nama_sub_kegiatan,  IFNULL(a.bentuk_kegiatan, v.nama_sub_kegiatan) bentuk_kegiatan, 
            a.indikator_hasil, a.target_hasil, a.indikator_keluaran, a.target_keluaran, 
            c.nama_rekening, e.vol_1, e.satuan_1, e.vol_2 vol_2, IFNULL(e.satuan_2,"") satuan_2, vol_1*IFNULL(vol_2,1) vol, e.unit_cost, e.jumlah
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_program g ON g.id=s.dept_program_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN account c ON c.id=e.account_id
            WHERE p.unit_id="'.$session['unitId'].'" AND p.tahun='.$session['deptPeriodValue'].' AND g.id='.$id.'
            ORDER BY g.id, s.id, v.id, a.id';
        }

        $program = Deptprogram::findOne($id);

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model2 = $dataProvider->getModels();

        // return $query;
        // exit;

        $status = Deptstatus::find()->where([
            'tahun' => $session['deptPeriodValue'],
            'unit_id' => $session['unitId'],
        ])->one();

        if(!empty($status)){
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
        }else{
            $kunci = false;
        }

        $verifikasi = Deptverification::find()
        ->where(['unit_id' => $session['unitId'], 'dept_program_id' => $id, 'modul' => $p])
        ->one();

        if(isset($verifikasi)){
            $session['verifId'] = $verifikasi->id;
        }
        
        if ($verifikasi == null) {
            $model = new Deptverification();
            $model->unit_id = $session['unitId'];
            $model->dept_program_id = $id;
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
            return $this->redirect(['deptprogram/verif', 'id' => $session['unitId'], 'p' => $session['poa']]);
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
     * Updates an existing Deptverification model.
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
     * Deletes an existing Deptverification model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Deptverification model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Deptverification the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Deptverification::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
