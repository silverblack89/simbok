<?php

namespace app\controllers;

use Yii;
use app\models\Deptsp2d;
use app\models\Deptsp2dSearch;
use app\models\Deptgroupsp2d;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;
use yii\helpers\ArrayHelper;

/**
 * Deptsp2dController implements the CRUD actions for Deptsp2d model.
 */
class Deptsp2dController extends Controller
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
     * Lists all Deptsp2d models.
     * @return mixed
     */
    public function actionIndex($id)
    {
        $session = Yii::$app->session;
        $session['deptGroupSp2d'] = $id;

        $searchModel = new Deptsp2dSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        if($id !== ''){
            $dataProvider->query->andWhere(['dept_group_sp2d_id' => $id]);
        }
        $dataProvider->query->orderBy(['tanggal'=>SORT_DESC]);

        // $grup = Deptgroupsp2d::find()->all();
        // $data = ArrayHelper::toArray($grup, [
        //     'app\models\deptgroupsp2d' => [
        //         'id',
        //         'nama'
        //     ],
        // ]);
        // foreach($data as $dt);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            // 'data' => $dt
        ]);
    }

    /**
     * Displays a single Deptsp2d model.
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
     * Creates a new Deptsp2d model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $session = Yii::$app->session;
        $model = new Deptsp2d();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' =>$session['deptGroupSp2d']]);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }else{
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Deptsp2d model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $session = Yii::$app->session;
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' =>$session['deptGroupSp2d']]);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }else{
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Deptsp2d model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $session = Yii::$app->session;
        $this->findModel($id)->delete();

        return $this->redirect(['index', 'id' =>$session['deptGroupSp2d']]);
    }

    /**
     * Finds the Deptsp2d model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Deptsp2d the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Deptsp2d::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
