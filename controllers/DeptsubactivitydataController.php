<?php

namespace app\controllers;

use Yii;
use app\models\Deptprogram;
use app\models\Deptactivity;
use app\models\Deptsubactivity;
use app\models\Deptsubactivitydata;
use app\models\DeptsubactivitydataSearch;
use app\models\DeptsubactivitydetailSearch;
use app\models\Deptstatus;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\SqlDataProvider;

/**
 * DeptsubactivitydataController implements the CRUD actions for Deptsubactivitydata model.
 */
class DeptsubactivitydataController extends Controller
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
        $searchModel = new DeptsubactivitydataSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $session = Yii::$app->session;
        $dataProvider->query->andWhere(['dept_sub_activity_id'=>$id, 'dept_period_id'=>$session['deptPeriodId']]);

        $subactivity = Deptsubactivity::findOne($id);
        $session = Yii::$app->session;
        $session['deptSubActivityId'] = $id;
        $session['deptSubActivityName'] = $subactivity->nama_sub_kegiatan;
        $session['deptSubActivityStatus'] = $subactivity->status;

        $status = Deptstatus::find()->where([
            'tahun' => $session['deptPeriodValue'],
            'unit_id' => Yii::$app->user->identity->unit_id,
        ])->one();

        $statusDpa = Yii::$app->db->createCommand('SELECT b.id bok_id, b.keterangan, b.sumber_dana_id dana_id, sd.nama sumber_dana, v.id, v.nama_sub_kegiatan FROM dept_sub_activity v
        LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
        LEFT JOIN dept_program p ON p.id=s.dept_program_id
        LEFT JOIN bok b ON b.id=p.bok_id
        LEFT JOIN sumber_dana sd ON sd.id=b.sumber_dana_id
        LEFT JOIN dpa d ON d.dept_sub_activity_id=v.id
        WHERE v.id=:id')
        ->bindValue(':id', $id)
        ->queryAll();

        foreach($statusDpa as $stsDpa){
            $session['sumberDana'] = $stsDpa['sumber_dana'];
            $session['bokId'] = $stsDpa['bok_id'];
        };
        

        if(!empty($status)){
            if($status->modul_1!=="P") {
                $session['status_poa'] = '';
            }else{
                $session['status_poa'] = 'disabled';
            }
        }

        return $this->render('list', [
            'searchModel' => $searchModel,  
            'dataProvider' => $dataProvider,
            'id' => $id,
        ]);
    }

    /**
     * Lists all Deptsubactivitydata models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DeptsubactivitydataSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Deptsubactivitydata model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $session = Yii::$app->session;
        $searchModel = new DeptsubactivitydetailSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['dept_sub_activity_data_id'=>$id]);

        $dataProvider->pagination  = false;

        $deptsubactivitydata = Deptsubactivitydata::findOne($id);
        $session['deptSubActivityDataId'] = $id;
        $session['deptSubActivityDataName'] = $deptsubactivitydata->bentuk_kegiatan;

        $status = Deptstatus::find()->where([
            'tahun' => $session['deptPeriodValue'],
            'unit_id' => Yii::$app->user->identity->unit_id,
        ])->one();

        if(!empty($status)){
            if($status->modul_1!=="P") {
                $session['status_poa'] = '';
            }else{
                $session['status_poa'] = 'disabled';
            }
            if($status->modul_4!=="L") {
                $session['status_real'] = '';
            }else{
                $session['status_real'] = 'disabled';
            }
        }

        return $this->render('view', [
            'model' => $this->findModel($id),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'id' => $id,
        ]);
    }

    /**
     * Creates a new Deptsubactivitydata model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id, $modul)
    {
        $model = new Deptsubactivitydata();

        $session = Yii::$app->session;
        $model->dept_sub_activity_id=$id;
        $model->dept_period_id=$session['deptPeriodId'];

        if($session['bokId'] == '6'){
            $model->dpa_id = 1;
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['deptsubactivitydata/list', 'id' => $session['deptSubActivityId']]);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                'model' => $model,
                'modul' => $modul,
            ]);
        }else{
            return $this->render('create', [
                'model' => $model,
                'modul' => $modul,
            ]);
        }
    }

    /**
     * Updates an existing Deptsubactivitydata model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id, $modul, $mid)
    {
        $model = $this->findModel($id);
        $session = Yii::$app->session;
        $session['deptSubActivityDataId'] = $id;

        if($modul !== 'new'){
            if($modul == 'program'){
                $query='SELECT p.id, p.nama_program nama FROM dept_program p
                WHERE p.tahun='.$session['deptPeriodValue'].' AND p.bok_id='.$session['bok_id'].' ORDER BY p.id'; 
                $session['modul'] = 'activity';
            }
            if($modul == 'activity'){
                $query='SELECT s.id, s.nama_kegiatan nama FROM dept_activity s
                WHERE s.dept_program_id='.$mid.' ORDER BY s.id';
                $session['modul'] = 'subactivity';
                $session['deptProgramIdNew'] = $mid;

            }
            if($modul == 'subactivity'){
                $query='SELECT a.id, a.nama_sub_kegiatan nama FROM dept_sub_activity a
                WHERE a.dept_activity_id='.$mid.' ORDER BY a.id';
                $session['modul'] = 'select';
                $session['deptActivityIdNew'] = $mid;
            }
            if($modul == 'select'){
                $query='SELECT a.id, a.nama_sub_kegiatan nama FROM dept_sub_activity a
                WHERE a.dept_activity_id='.$mid.' ORDER BY a.id';
                $session['deptSubActivityIdNew'] = $mid;
            }

            $dataProvider = new SqlDataProvider([
                'sql' => $query,
                'pagination' => false
            ]);

            $model2 = $dataProvider->getModels();
            
            $program = Deptprogram::findOne($session['deptProgramIdNew']);
            if(!empty($program)){ $model->deptProgramId = $program->nama_program; }
            $activity = Deptactivity::findOne($session['deptActivityIdNew']);
            if(!empty($activity)){ $model->deptActivityId = $activity->nama_kegiatan; }
            $subactivity = Deptsubactivity::findOne($session['deptSubActivityIdNew']);
            if(!empty($subactivity)){
                $model->deptSubActivityId = $subactivity->nama_sub_kegiatan;
                $model->dept_sub_activity_id = $subactivity->id;
            }
        }else{
            $dataProvider = null;
            $model2 = null;
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['deptsubactivitydata/list', 'id' => $session['deptSubActivityId']]);
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
     * Deletes an existing Deptsubactivitydata model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        $session = Yii::$app->session;
        return $this->redirect(['deptsubactivitydata/list', 'id' => $session['deptSubActivityId']]);
    }

    /**
     * Finds the Deptsubactivitydata model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Deptsubactivitydata the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Deptsubactivitydata::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
