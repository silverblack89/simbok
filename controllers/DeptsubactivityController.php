<?php

namespace app\controllers;

use Yii;
use app\models\Deptsubactivity;
use app\models\DeptsubactivitySearch;
use app\models\Deptactivity;
use app\models\Deptstatus;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;

$session = Yii::$app->session;

/**
 * DeptsubactivityController implements the CRUD actions for Deptsubactivity model.
 */
class DeptsubactivityController extends Controller
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
     * Lists all Deptsubactivity models.
     * @return mixed
     */
    public function actionIndex($id)
    {
        $session = Yii::$app->session;
        $session['deptActivityId'] = $id;

        $searchModel = new DeptsubactivitySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['dept_activity_id'=>$id]);
        $dataProvider->query->orderBy(['kode_rekening' => SORT_ASC]);

        $service = Deptactivity::findOne($id);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'name' => $service->nama_kegiatan,
        ]);
    }

    /**
     * Displays a single Deptsubactivity model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $session = Yii::$app->session;
        $activity = Deptactivity::findOne($session['deptActivityId']);
        $session = Yii::$app->session;
        $session['deptActivityNama'] = $activity->nama_kegiatan;

        $subactivity = Deptsubactivity::findOne($id);
        $session = Yii::$app->session;
        $session['deptSubActivityNama'] = $subactivity->nama_sub_kegiatan;

        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionList($id)
    {
        $searchModel = new DeptsubactivitySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['dept_activity_id'=>$id]);
        // $dataProvider->query->andWhere(['unit_id'=>Yii::$app->user->identity->unit_id]);
        $activity = Deptactivity::findOne($id);
        $session = Yii::$app->session;
        $session['deptActivityId'] = $id;
        $session['deptActivityName'] = $activity->nama_kegiatan;

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
            'id' => $activity->id,
        ]);
    }

    /**
     * Creates a new Deptsubactivity model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $session = Yii::$app->session;
        $model = new Deptsubactivity();
        $model->dept_activity_id = $session['deptActivityId'];
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
     * Updates an existing Deptsubactivity model.
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
     * Deletes an existing Deptsubactivity model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $session = Yii::$app->session;
        $this->findModel($id)->delete();

        return $this->redirect(['index', 'id' => $session['deptActivityId']]);
    }

    /**
     * Finds the Deptsubactivity model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Deptsubactivity the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Deptsubactivity::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
