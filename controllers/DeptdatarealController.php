<?php

namespace app\controllers;

use Yii;
use app\models\Deptdatareal;
use app\models\DeptdatarealSearch;
use app\models\Deptsubactivitydetail;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * DeptdatarealController implements the CRUD actions for Deptdatareal model.
 */
class DeptdatarealController extends Controller
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
     * Lists all Deptdatareal models.
     * @return mixed
     */
    public function actionIndex($id)
    {
        $searchModel = new DeptdatarealSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['dept_sub_activity_detail_id' => $id]);
        $dataProvider->query->orderBy(['tanggal' => SORT_ASC]);

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'id' => $id
            ]);
        }else{
            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'id' => $id
            ]);
        }
    }

    /**
     * Displays a single Deptdatareal model.
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
     * Creates a new Deptdatareal model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id)
    {
        $detail = Deptsubactivitydetail::findOne($id);
        $real = Deptdatareal::find()->where(['dept_sub_activity_detail_id' => $id])->sum('jumlah');

        $model = new Deptdatareal();
        $model->dept_sub_activity_detail_id = $id;
        $model->jumlah_pagu = $detail->jumlah;
        $model->jumlah_realisasi = $real;

        if($real > 0){
            $model->sisa_pagu = $detail->jumlah-$real;
        }else{
            $model->sisa_pagu = $detail->jumlah;
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // return $this->redirect(['view', 'id' => $model->id]);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                'model' => $model,
                'id' => $id
            ]);
        }else{
            return $this->render('create', [
                'model' => $model,
                'id' => $id
            ]);
        }
    }

    /**
     * Updates an existing Deptdatareal model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $detail = Deptsubactivitydetail::findOne($model->dept_sub_activity_detail_id);
        $real = Deptdatareal::find()->where(['dept_sub_activity_detail_id' => $model->dept_sub_activity_detail_id])->sum('jumlah');

        $model->jumlah_pagu = $detail->jumlah;
        $model->jumlah_realisasi = $real;

        if($real > 0){
            $model->sisa_pagu = $detail->jumlah-$real;
        }else{
            $model->sisa_pagu = $detail->jumlah;
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // return $this->redirect(['view', 'id' => $model->id]);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('update', [
                'model' => $model,
                'id' => $model->dept_sub_activity_detail_id
            ]);
        }else{
            return $this->render('update', [
                'model' => $model,
                'id' => $model->dept_sub_activity_detail_id
            ]);
        }
    }

    /**
     * Deletes an existing Deptdatareal model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        // return $this->redirect(['index']);
    }

    /**
     * Finds the Deptdatareal model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Deptdatareal the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Deptdatareal::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
