<?php

namespace app\controllers;

use Yii;
use app\models\Deptsubactivitydetail;
use app\models\DeptsubactivitydetailSearch;
use app\models\Deptaccountaccess;
use app\models\Deptsubactivitydata;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;

/**
 * DeptsubactivitydetailController implements the CRUD actions for Deptsubactivitydetail model.
 */
class DeptsubactivitydetailController extends Controller
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
     * Lists all Deptsubactivitydetail models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DeptsubactivitydetailSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Deptsubactivitydetail model.
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
     * Creates a new Deptsubactivitydetail model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id)
    {
        $session = Yii::$app->session;
        $POST_VARIABLE=Yii::$app->request->post('Deptubactivitydetail');
        $poa_vol_1 = $POST_VARIABLE['vol_1'];
        $poa_vol_2 = $POST_VARIABLE['vol_2'];
        $poa_unit_cost = $POST_VARIABLE['unit_cost'];

        $deptsubactivitydata = Deptsubactivitydata::findOne($id);
        $deptaccountaccess = Deptaccountaccess::find()->where(['dept_sub_activity_id' => $deptsubactivitydata->dept_sub_activity_id])->all();

        if(!empty($deptaccountaccess)){
            foreach($deptaccountaccess as $daa)
            {
                $arrAkun[] = "'".$daa['account_id']."'"; 
            } 
            $akun = implode(", ", $arrAkun);
        }else{
            $akun = null;
        }

        $model = new Deptsubactivitydetail();
        $model->dept_sub_activity_data_id = $id;

        $bok = $model->getStatus($session['deptSubActivityId']);
        if ($bok <= 5){
            $sd = ['<=','id', 2];
        }elseif ($bok = 6){
            $sd = ['<=','id', 2];
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $session = Yii::$app->session;
            return $this->redirect(['deptsubactivitydata/view', 'id' => $session['deptSubActivityDataId']]);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                'model' => $model,
                'sd' => $sd,
                'akun' => $akun,
            ]);
        }else{
            return $this->render('create', [
                'model' => $model,
                'sd' => $sd,
                'akun' => $akun,
            ]);
        }
    }

    /**
     * Updates an existing Deptsubactivitydetail model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $session = Yii::$app->session;
        $model = $this->findModel($id);

        if(empty($model->vol_1)){$vol1 = 1;}else{$vol1 = $model->vol_1;}
        if(empty($model->vol_2)){$vol2 = 1;}else{$vol2 = $model->vol_2;}
        if(empty($model->vol_3)){$vol3 = 1;}else{$vol3 = $model->vol_3;}
        if(empty($model->vol_4)){$vol4 = 1;}else{$vol4 = $model->vol_4;}
        if(empty($model->unit_cost)){$harga = 1;}else{$harga = $model->unit_cost;}

        $model->total =  $vol1*$vol2*$vol3*$vol4*$harga;

        $bok = $model->getStatus($session['deptSubActivityId']);
        if ($bok <= 5){
            $sd = ['<=','id', 2];
        }elseif ($bok = 6){
            $sd = ['<=','id', 2];
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $session = Yii::$app->session;
            return $this->redirect(['deptsubactivitydata/view', 'id' => $session['deptSubActivityDataId']]);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('update', [
                'model' => $model,
                'sd' => $sd
            ]);
        }else{
            return $this->render('update', [
                'model' => $model,
                'sd' => $sd
            ]);
        }
    }

    /**
     * Deletes an existing Deptsubactivitydetail model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        $session = Yii::$app->session;
        return $this->redirect(['deptsubactivitydata/view', 'id' => $session['deptSubActivityDataId']]);
    }

    /**
     * Finds the Deptsubactivitydetail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Deptsubactivitydetail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Deptsubactivitydetail::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionGetTotal()
    {
        if(empty($_COOKIE['vol1'])){$vol1 = 1;}else{$vol1 = $_COOKIE['vol1'];}
        if(empty($_COOKIE['vol2'])){$vol2 = 1;}else{$vol2 = $_COOKIE['vol2'];}
        if(empty($_COOKIE['vol3'])){$vol3 = 1;}else{$vol3 = $_COOKIE['vol3'];}
        if(empty($_COOKIE['vol4'])){$vol4 = 1;}else{$vol4 = $_COOKIE['vol4'];}
        if(empty($_COOKIE['harga'])){$harga = 1;}else{$harga = $_COOKIE['harga'];}

        $total =  str_replace('.','',$vol1)*str_replace('.','',$vol2)*str_replace('.','',$vol3)*str_replace('.','',$vol4)*str_replace('.','',$harga);
        return $total;
    }
}
