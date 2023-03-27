<?php

namespace app\controllers;

use Yii;
use app\models\Program;
use app\models\Service;
use app\models\ServiceSearch;
use app\models\Allocation;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;

/**
 * ServiceController implements the CRUD actions for Service model.
 */
class ServiceController extends Controller
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
     * Lists all Service models.
     * @return mixed
     */
    public function actionIndex($id)
    {
        $searchModel = new ServiceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['program_id'=>$id]);

        $session = Yii::$app->session;
        $session['programId'] = $id;

        $program = Program::findOne($id);
        $session['programYear'] = $program->tahun;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'id' => $id,
            'name' => $program->nama_program,
        ]);
    }

    public function actionList($id)
    {
        $searchModel = new ServiceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['program_id'=>$id]);
        // $dataProvider->query->andWhere(['unit_id'=>Yii::$app->user->identity->unit_id]);
        $program = Program::findOne($id);
        $session = Yii::$app->session;
        $session['programId'] = $id;
        $session['cvd'] = $program->covid;

        $alloc = Allocation::find()->where(['tahun' => $session['periodValue']])->one();

        if($session['poa'] == 'def'){
            $cekPoa = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as char),0),1,5) prosentase
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN unit u ON u.id=p.unit_id
            WHERE p.tahun=:periodValue AND p.unit_id=:unitId
            GROUP BY p.unit_id, p.pagu
            ORDER BY u.puskesmas')
            ->bindValue(':periodValue', $session['periodValue'])
            ->bindValue(':unitId', Yii::$app->user->identity->unit_id)
            ->queryAll();

            foreach($cekPoa as $cekPoa){
                if($cekPoa['jumlah'] > $cekPoa['pagu'] && $cekPoa['pagu'] > 0){
                    // Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA melebihi Pagu BOK.');
                }
            }

            if($session['cvd'] == 0){
                if($alloc->ukm <> 0){
                    $cekUkm = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, p.pagu_covid, p.pagu_ukm, sum(IFNULL(e.jumlah,0)) jumlah
                    FROM activity_detail e
                    LEFT JOIN activity_data a ON a.id=e.activity_data_id
                    LEFT JOIN activity v ON v.id=a.activity_id
                    LEFT JOIN service s ON s.id=v.service_id
                    LEFT JOIN period p ON p.id=a.period_id
                    LEFT JOIN unit u ON u.id=p.unit_id
                    LEFT JOIN program g ON g.id=s.program_id
                    WHERE p.tahun=:periodValue AND p.unit_id=:unitId AND g.covid=0
                    GROUP BY p.unit_id, p.pagu
                    ORDER BY u.puskesmas')
                    ->bindValue(':periodValue', $session['periodValue'])
                    ->bindValue(':unitId', Yii::$app->user->identity->unit_id)
                    ->queryAll();

                    foreach($cekUkm as $ukm){
                        if($ukm['jumlah'] < $alloc->ukm/100*$ukm['pagu'] && $ukm['jumlah'] <> 0){
                            Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA UKM Kurang Dari '.number_format($alloc->ukm,1,",",".").'%. ( Kurang '.number_format($alloc->ukm/100*$ukm['pagu']-$ukm['jumlah'],0,",",".").' )');
                        }
            
                        // if($ukm['jumlah'] > $ukm['pagu_ukm']){
                        //     Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA UKM melebihi '.$alloc->ukm.'%.');
                        // }
                    }
                }
            }elseif ($session['cvd'] == 1){
                if($alloc->covid <> 0){
                    $cekCovid = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, p.pagu_covid, p.pagu_ukm, sum(IFNULL(e.jumlah,0)) jumlah
                    FROM activity_detail e
                    LEFT JOIN activity_data a ON a.id=e.activity_data_id
                    LEFT JOIN activity v ON v.id=a.activity_id
                    LEFT JOIN service s ON s.id=v.service_id
                    LEFT JOIN period p ON p.id=a.period_id
                    LEFT JOIN unit u ON u.id=p.unit_id
                    LEFT JOIN program g ON g.id=s.program_id
                    WHERE p.tahun=:periodValue AND p.unit_id=:unitId AND g.covid=1
                    GROUP BY p.unit_id, p.pagu
                    ORDER BY u.puskesmas')
                    ->bindValue(':periodValue', $session['periodValue'])
                    ->bindValue(':unitId', Yii::$app->user->identity->unit_id)
                    ->queryAll();

                    foreach($cekCovid as $cvd){
                        // if($cvd['jumlah'] < $cvd['pagu_covid'] && $cvd['jumlah'] <> 0){
                        //     Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA COVID kurang dari '.$alloc->covid.'%.');
                        // }
            
                        if($cvd['jumlah'] > $alloc->covid/100*$cvd['pagu']){
                            Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA COVID melebihi '.number_format($alloc->covid,1,",",".").'%. ( Kelebihan '.number_format($cvd['jumlah']-$alloc->covid/100*$cvd['pagu'],0,",",".").' )');
                        }
                    }
                }
            }else{
                if($alloc->insentif <> 0){
                    $cekInsentif = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, p.pagu_covid, p.pagu_ukm, sum(IFNULL(e.jumlah,0)) jumlah
                    FROM activity_detail e
                    LEFT JOIN activity_data a ON a.id=e.activity_data_id
                    LEFT JOIN activity v ON v.id=a.activity_id
                    LEFT JOIN service s ON s.id=v.service_id
                    LEFT JOIN period p ON p.id=a.period_id
                    LEFT JOIN unit u ON u.id=p.unit_id
                    LEFT JOIN program g ON g.id=s.program_id
                    WHERE p.tahun=:periodValue AND p.unit_id=:unitId AND g.covid=2
                    GROUP BY p.unit_id, p.pagu
                    ORDER BY u.puskesmas')
                    ->bindValue(':periodValue', $session['periodValue'])
                    ->bindValue(':unitId', Yii::$app->user->identity->unit_id)
                    ->queryAll();

                    foreach($cekInsentif as $cekin){
                        // if($cekin['jumlah'] < $cekin['pagu_covid'] && $cekin['jumlah'] <> 0){
                        //     Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA COVID kurang dari '.$alloc->insentif.'%.');
                        // }
            
                        if($cekin['jumlah'] > $alloc->insentif/100*$cekin['pagu']){
                            Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA Insentif UKM melebihi '.number_format($alloc->insentif,1,",",".").'%. ( Kelebihan '.number_format($cekin['jumlah']-$alloc->insentif/100*$cekin['pagu'],0,",",".").' )');
                        }
                    }
                }
            }
        }else{
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
        }

        if($session['poa'] == 'def'){
            $progress = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as char),0),1,5) prosentase
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN unit u ON u.id=p.unit_id
            WHERE p.tahun=:periode AND p.unit_id=:unit
            GROUP BY p.unit_id, p.pagu
            ORDER BY u.puskesmas')
            ->bindValue(':periode', $session['periodValue'])
            ->bindValue(':unit', Yii::$app->user->identity->unit_id)
            ->queryAll();

            foreach($progress as $progress);

            $session = Yii::$app->session;

            if(!empty($progress)){
                $session['pagu'] = $progress['pagu'];
            }
        }else{
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
            }else{
                $session['pagu_ubah'] = 0;
                $progress['prosentase'] = null;
            }
        }

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'id' => $program->id,
            'name' => $program->nama_program,
        ]);
    }

    /**
     * Displays a single Service model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $service = Service::findOne($id);
        $session = Yii::$app->session;
        $session['serviceId'] = $id;
        $session['serviceNama'] = $service->nama_pelayanan;

        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Service model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $session = Yii::$app->session;
        $model = new Service();
        $model->program_id = $session['programId'];

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Service model.
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
     * Deletes an existing Service model.
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
     * Finds the Service model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Service the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Service::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
