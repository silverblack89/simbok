<?php

namespace app\controllers;

use Yii;
use app\models\Indicatordata;
use app\models\IndicatordataSearch;
use app\models\Indicator;
use app\models\Status;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;

/**
 * IndicatordataController implements the CRUD actions for Indicatordata model.
 */
class IndicatordataController extends Controller
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
     * Lists all Indicatordata models.
     * @return mixed
     */
    public function actionIndex($id)
    {
        $session = Yii::$app->session;
        $session['indicatorId'] = $id;
        $searchModel = new IndicatordataSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['indicator_id'=>$id, 'period_id'=>$session['periodId']]);

        $indicator = Indicator::findOne($id);
        $session['indicatorName'] = $indicator->nama_indikator;

        $status = Status::find()->where([
            'tahun' => $session['periodValue'],
            'unit_id' => Yii::$app->user->identity->unit_id,
        ])->count();

        if($status=="0") {
            $session['status'] = '';
        }else{
            $session['status'] = 'disabled';
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'id' => $session['indicatorId']
        ]);
    }

    /**
     * Displays a single Indicatordata model.
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
     * Creates a new Indicatordata model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id)
    {
        $model = new Indicatordata();
        $model->indicator_id = $id;
        $session = Yii::$app->session;
        // $session['indicatorId'] = $id;
        $model->period_id=$session['periodId'];

        $status = Status::find()->where([
            'tahun' => $session['periodValue'],
            'unit_id' => Yii::$app->user->identity->unit_id,
        ])->count();

        if($status=="0") {
            $session['status'] = '';
        }else{
            $session['status'] = 'disabled';
        }

        $data = Yii::$app->db->createCommand('SELECT ifnull(MAX(id.kinerja),0) kin_max FROM indicator_data id
        LEFT JOIN indicator i ON i.id=id.indicator_id
        LEFT JOIN program p ON p.id=i.program_id
        WHERE id.indicator_id =:indicatorId')
        ->bindValue(':indicatorId', $id)
        ->queryAll();
        foreach($data as $row){
            $model->kinerjaMax = $row['kin_max'];
        }
        $model->setScenario('kMax');

        // if ($model->load(Yii::$app->request->post()) && $model->save()) {
        //     return $this->redirect(['index', 'id' => $session['indicatorId']]);
        // }

        // return $this->render('create', [
        //     'model' => $model,
        // ]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' => $id]); 
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
     * Updates an existing Indicatordata model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $session = Yii::$app->session;

        $data = Yii::$app->db->createCommand('SELECT ifnull(MAX(id.kinerja),0) kin_max FROM indicator_data id
        LEFT JOIN indicator i ON i.id=id.indicator_id
        LEFT JOIN program p ON p.id=i.program_id
        WHERE id.indicator_id =:indicatorId')
        ->bindValue(':indicatorId', $session['indicatorId'])
        ->queryAll();
        foreach($data as $row){
            $model->kinerjaMax = $row['kin_max'];
        }
        $model->setScenario('kMax');

        $status = Status::find()->where([
            'tahun' => $session['periodValue'],
            'unit_id' => Yii::$app->user->identity->unit_id,
        ])->count();

        if($status=="0") {
            $session['status'] = '';
        }else{
            $session['status'] = 'disabled';
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' => $session['indicatorId']]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Indicatordata model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        $session = Yii::$app->session;
        return $this->redirect(['index', 'id' => $session['indicatorId']]);
    }

    /**
     * Finds the Indicatordata model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Indicatordata the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Indicatordata::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
