<?php

namespace app\controllers;

use Yii;
use app\models\Deptactivity;
use app\models\DeptactivitySearch;
use app\models\Deptprogram;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * DeptactivityController implements the CRUD actions for Deptactivity model.
 */
class DeptactivityController extends Controller
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
     * Lists all Deptactivity models.
     * @return mixed
     */
    public function actionIndex($id)
    {
        $session = Yii::$app->session;
        $session['deptProgramId'] = $id;

        $searchModel = new DeptactivitySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['dept_program_id'=>$id]);

        $program = DeptProgram::findOne($id);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'name' => $program->nama_program,
        ]);
    }

    /**
     * Displays a single Deptactivity model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $session = Yii::$app->session;
        $program = Deptprogram::findOne($session['deptProgramId']);
        $session = Yii::$app->session;
        $session['deptProgramNama'] = $program->nama_program;

        $activity = Deptactivity::findOne($id);
        $session['deptActivityNama'] = $activity->nama_kegiatan;

        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionList($id)
    {
        $session = Yii::$app->session;
        $searchModel = new DeptactivitySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['dept_program_id'=>$id]);
        // $dataProvider->query->andWhere(['unit_id'=>Yii::$app->user->identity->unit_id]);
        $dataProvider->query->orderBy(['kode_rekening' => SORT_ASC]);
        $program = Deptprogram::findOne($id);
        unset($session['deptProgramId']);
        $session['deptProgramId'] = $id;
        $session['deptProgramName'] = $program->nama_program;
        $session['bok_id'] = $program->bok_id;

        $session['deptGroupSp2dId1'] = $program->dept_group_sp2d_id_1;
        $session['deptGroupSp2dId2'] = $program->dept_group_sp2d_id_2;

        // $cekPoa = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, 
        // SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as char),0),1,5) prosentase
        // FROM activity_detail e
        // LEFT JOIN activity_data a ON a.id=e.activity_data_id
        // LEFT JOIN activity v ON v.id=a.activity_id
        // LEFT JOIN service s ON s.id=v.service_id
        // LEFT JOIN period p ON p.id=a.period_id
        // LEFT JOIN unit u ON u.id=p.unit_id
        // WHERE p.tahun=:periodValue AND p.unit_id=:unitId
        // GROUP BY p.unit_id, p.pagu
        // ORDER BY u.puskesmas')
        // ->bindValue(':periodValue', $session['periodValue'])
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
        
        $session['prosentase'] = 0;

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

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'id' => $program->id,
        ]);
    }

    /**
     * Creates a new Deptactivity model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $session = Yii::$app->session;
        $model = new Deptactivity();
        $model->dept_program_id = $session['deptProgramId'];
        $model->pagu = 0;
        $model->aktif = 1;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Deptactivity model.
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
     * Deletes an existing Deptactivity model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $session = Yii::$app->session;
        $this->findModel($id)->delete();

        return $this->redirect(['index', 'id' => $session['deptProgramId']]);
    }

    /**
     * Finds the Deptactivity model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Deptactivity the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Deptactivity::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
