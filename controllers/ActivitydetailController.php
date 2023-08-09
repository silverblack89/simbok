<?php

namespace app\controllers;

use Yii;
use app\models\Activitydetail;
use app\models\ActivitydetailSearch;
use app\models\Status;
use app\models\Financialrealization;
use app\models\FinancialrealizationSearch;
use app\models\Account;
use app\models\Verification;
use app\models\Allocation;
use app\models\Accountaccess;
use app\models\Activitydata;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;

/**
 * ActivitydetailController implements the CRUD actions for Activitydetail model.
 */
class ActivitydetailController extends Controller
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
     * Lists all Activitydetail models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ActivitydetailSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Activitydetail model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $session = Yii::$app->session;
        $searchModel = new FinancialrealizationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['activity_detail_id'=>$id]);
        $detail = Activitydetail::findOne($id);
        $session['activityDetailId'] = $detail->id;
        $rekening = Account::findOne($detail->account_id);
        $session['activityDetailAccount'] = $rekening->nama_rekening;

        return $this->render('view', [
            'model' => $this->findModel($id),
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Activitydetail model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id)
    {
        $POST_VARIABLE=Yii::$app->request->post('Activitydetail');
        if(isset($POST_VARIABLE['vol_1'])){
            $poa_vol_1 = $POST_VARIABLE['vol_1'];
        }else{
            $poa_vol_1 = 1;
        }
        
        if(isset($POST_VARIABLE['vol_2'])){
            $poa_vol_2 = $POST_VARIABLE['vol_2'];
        }else{
            $poa_vol_2 = 1;
        }

        if(isset($POST_VARIABLE['vol_3'])){
            $poa_vol_3 = $POST_VARIABLE['vol_3'];
        }else{
            $poa_vol_3 = 1;
        }

        if(isset($POST_VARIABLE['vol_4'])){
            $poa_vol_4 = $POST_VARIABLE['vol_4'];
        }else{
            $poa_vol_4 = 1;
        }
        
        if(isset($POST_VARIABLE['unit_cost'])){
            $poa_unit_cost = $POST_VARIABLE['unit_cost'];
        }else{
            $poa_unit_cost = 1;
        }

        $activitydata = ActivityData::findOne($id);
        $accountaccess = Accountaccess::find()->where(['activity_id' => $activitydata->activity_id])->all();

        if(!empty($accountaccess)){
            // foreach($accountaccess as $data){
            //     echo $data['account_id'];
            // }

            foreach($accountaccess as $aa)
            {
                $arrAkun[] = "'".$aa['account_id']."'"; 
            } 
            $akun = implode(", ", $arrAkun);
        }else{
            $akun = null;
        }

        $model = new Activitydetail();
        $model->activity_data_id = $id;

        $session = Yii::$app->session;

        $alloc = Allocation::find()->where(['tahun' => $session['periodValue']])->one();

        $status = Status::find()->where([
            'tahun' => $session['periodValue'],
            'unit_id' => Yii::$app->user->identity->unit_id,
        ])->one();

        if(!empty($status)){
            if($status->modul_1!=="P") {
                $session['status_poa'] = '';
            }else{
                $session['status_poa'] = 'disabled';
            }
        }

        $verification = Verification::find()->where([
            'program_id' => $session['programId'],
            'unit_id' => Yii::$app->user->identity->unit_id,
            'modul' => 'P',
        ])->one();

        if(!empty($verification)){
            if($verification->revisi == 1){
                $session['revisi_poa'] = true;
            }else{
                $session['revisi_poa'] = false;
            }
        }else{
            $session['revisi_poa'] = false;
        }

        $cekPoa = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, 
        SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as char),0),1,5) prosentase
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

        if(!empty($cekpoa)){
        foreach($cekPoa as $cekPoa){
            $model->total_pagu = $cekPoa['pagu'];
            
            if(str_replace(".", "", $poa_vol_1) * str_replace(".", "", $poa_vol_2) * str_replace(".", "", $poa_vol_3) * str_replace(".", "", $poa_vol_4) * str_replace(".", "", $poa_unit_cost) == 1){
                $jumlah = 0;
            }else{
                $jumlah = str_replace(".", "", $poa_vol_1) * str_replace(".", "", $poa_vol_2) * str_replace(".", "", $poa_vol_3) * str_replace(".", "", $poa_vol_4) * str_replace(".", "", $poa_unit_cost);
            }

            $model->total_poa = $cekPoa['jumlah']+$jumlah;
            $session['jumlah_poa'] = $cekPoa['jumlah'];

            if($cekPoa['jumlah'] > $cekPoa['pagu'] && $cekPoa['pagu'] > 0){
                Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA melebihi Pagu BOK.');
            }
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
                    if($ukm['jumlah'] < $ukm['pagu_ukm'] && $ukm['jumlah'] <> 0){
                        Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA UKM kurang dari '.$alloc->ukm.'%.');
                    }
        
                    if($ukm['jumlah'] > $ukm['pagu_ukm']){
                        Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA UKM melebihi '.$alloc->ukm.'%.');
                    }
                }
            }
        }else{
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
                    if($cvd['jumlah'] < $cvd['pagu_covid'] && $cvd['jumlah'] <> 0){
                        Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA COVID kurang dari '.$alloc->covid.'%.');
                    }
        
                    if($cvd['jumlah'] > $cvd['pagu_covid']){
                        Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA COVID melebihi '.$alloc->covid.'%.');
                    }
                }
            }
        }

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
        
        if(!empty($progress)){
            foreach($progress as $progress);

            $session = Yii::$app->session;
            $session['pagu'] = $progress['pagu'];
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
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $session = Yii::$app->session;
            $progress = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, 
            SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as char),0),1,5) prosentase
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

            if (isset($progress['prosentase'])){
                $session['prosentase'] = $progress['prosentase'];
                if($progress['prosentase'] < 33.33){
                    $session['barColor'] = 'progress-bar-success';
                }
                if($progress['prosentase'] >= 33.33 && $progress['prosentase']<=66.66){
                    $session['barColor'] = 'progress-bar-warning';
                }
                if($progress['prosentase'] > 66.66){
                    $session['barColor'] = 'progress-bar-danger';
                }
            }else{
                $session['prosentase'] = 0;
            }
            // Yii::$app->session->setFlash('info', $cekPoa['jumlah']);
            return $this->redirect(['activitydata/view', 'id' => $session['activityDataId']]);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                'model' => $model,
                'real' => 0,
                'akun' => $akun,
            ]);
        }else{
            return $this->render('create', [
                'model' => $model,
                'real' => 0,
                'akun' => $akun,
            ]);
        }
    }

    /**
     * Updates an existing Activitydetail model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $POST_VARIABLE=Yii::$app->request->post('Activitydetail');
        if(isset($POST_VARIABLE['vol_1'])){
            $poa_vol_1 = $POST_VARIABLE['vol_1'];
        }else{
            $poa_vol_1 = 1;
        }
        
        if(isset($POST_VARIABLE['vol_2'])){
            $poa_vol_2 = $POST_VARIABLE['vol_2'];
        }else{
            $poa_vol_2 = 1;
        }

        if(isset($POST_VARIABLE['vol_3'])){
            $poa_vol_3 = $POST_VARIABLE['vol_3'];
        }else{
            $poa_vol_3 = 1;
        }

        if(isset($POST_VARIABLE['vol_4'])){
            $poa_vol_4 = $POST_VARIABLE['vol_4'];
        }else{
            $poa_vol_4 = 1;
        }
        
        if(isset($POST_VARIABLE['unit_cost'])){
            $poa_unit_cost = $POST_VARIABLE['unit_cost'];
        }else{
            $poa_unit_cost = 1;
        }

        $model = $this->findModel($id);

        $activityData = ActivityData::findOne($model->activity_data_id);
        $accountaccess = Accountaccess::find()->where(['activity_id' => $activityData->activity_id])->all();

        if(!empty($accountaccess)){
            foreach($accountaccess as $aa)
            {
                $arrAkun[] = "'".$aa['account_id']."'"; 
            } 
            $akun = implode(", ", $arrAkun);
        }else{
            $akun = null;
        }

        if(empty($model->vol_1)){$vol1 = 1;}else{$vol1 = $model->vol_1;}
        if(empty($model->vol_2)){$vol2 = 1;}else{$vol2 = $model->vol_2;}
        if(empty($model->vol_3)){$vol3 = 1;}else{$vol3 = $model->vol_3;}
        if(empty($model->vol_4)){$vol4 = 1;}else{$vol4 = $model->vol_4;}
        if(empty($model->unit_cost)){$harga = 1;}else{$harga = $model->unit_cost;}

        $model->total =  $vol1*$vol2*$vol3*$vol4*$harga;

        $session = Yii::$app->session;

        $alloc = Allocation::find()->where(['tahun' => $session['periodValue']])->one();

        $session['activityDetailId'] = $id;
        $status = Status::find()->where([
            'tahun' => $session['periodValue'],
            'unit_id' => Yii::$app->user->identity->unit_id,
        ])->one();

        if(!empty($status)){
            if($status->modul_1!=="P") {
                $session['status_poa'] = '';
            }else{
                $session['status_poa'] = 'disabled';
            }
        }

        $verification = Verification::find()->where([
            'program_id' => $session['programId'],
            'unit_id' => Yii::$app->user->identity->unit_id,
            'modul' => 'P',
        ])->one();

        if(!empty($verification)){
            if($verification->revisi == 1){
                $session['revisi_poa'] = true;
            }else{
                $session['revisi_poa'] = false;
            }
        }else{
            $session['revisi_poa'] = false; 
        }

        // $cekPoa = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, 
        // SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as char),0),1,5) prosentase
        // FROM activity_detail e
        // LEFT JOIN activity_data a ON a.id=e.activity_data_id
        // LEFT JOIN activity v ON v.id=a.activity_id
        // LEFT JOIN service s ON s.id=v.service_id
        // LEFT JOIN period p ON p.id=a.period_id
        // LEFT JOIN unit u ON u.id=p.unit_id
        // WHERE p.tahun=:periodValue AND p.unit_id=:unitId AND NOT e.id=:activityDetailId
        // GROUP BY p.unit_id, p.pagu
        // ORDER BY u.puskesmas')
        // ->bindValue(':periodValue', $session['periodValue'])
        // ->bindValue(':unitId', Yii::$app->user->identity->unit_id)
        // ->bindValue(':activityDetailId', $id)
        // ->queryAll();

        // foreach($cekPoa as $cekPoa){
        //     $model->total_pagu = $cekPoa['pagu'];
        //     if(str_replace(".", "", $poa_vol_1) * str_replace(".", "", $poa_vol_2) * str_replace(".", "", $poa_vol_3) * str_replace(".", "", $poa_vol_4) * str_replace(".", "", $poa_unit_cost) == 1){
        //         $jumlah = 0;
        //     }else{
        //         $jumlah = str_replace(".", "", $poa_vol_1) * str_replace(".", "", $poa_vol_2) * str_replace(".", "", $poa_vol_3) * str_replace(".", "", $poa_vol_4) * str_replace(".", "", $poa_unit_cost);
        //     }

        //     $model->total_poa = $cekPoa['jumlah']+$jumlah;
        //     $session['jumlah_poa'] = $cekPoa['jumlah'];
        // }

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
                    if($ukm['jumlah'] < $ukm['pagu_ukm'] && $ukm['jumlah'] <> 0){
                        Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA UKM kurang dari '.$alloc->ukm.'%.');
                    }
        
                    if($ukm['jumlah'] > $ukm['pagu_ukm']){
                        Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA UKM melebihi '.$alloc->ukm.'%.');
                    }
                }
            }
        }else{
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
                    if($cvd['jumlah'] < $cvd['pagu_covid'] && $cvd['jumlah'] <> 0){
                        Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA COVID kurang dari '.$alloc->covid.'%.');
                    }
        
                    if($cvd['jumlah'] > $cvd['pagu_covid']){
                        Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA COVID melebihi '.$alloc->covid.'%.');
                    }
                }
            }
        }

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
        $session['pagu'] = $progress['pagu'];
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $session = Yii::$app->session;
            $progress = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, 
            SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as char),0),1,5) prosentase
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

            $session['prosentase'] = $progress['prosentase'];
            if($progress['prosentase'] < 33.33){
                $session['barColor'] = 'progress-bar-success';
            }
            if($progress['prosentase'] >= 33.33 && $progress['prosentase']<=66.66){
                $session['barColor'] = 'progress-bar-warning';
            }
            if($progress['prosentase'] > 66.66){
                $session['barColor'] = 'progress-bar-danger';
            }

            // Yii::$app->session->setFlash('info', $jumlah);
            return $this->redirect(['/activitydata/view', 'id' => $session['activityDataId']]);
        }

        // $cekPoa = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, 
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

        $real = Financialrealization::find()->where([
            'id' => $model->id,
        ])->count();

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('update', [
                'model' => $model,
                'real' => $real,
                'akun' => $akun,
            ]);
        }else{
            return $this->render('update', [
                'model' => $model,
                'real' => $real,
                'akun' => $akun,
            ]);
        }
    }

    /**
     * Deletes an existing Activitydetail model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        $session = Yii::$app->session;
        $progress = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, 
        SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as char),0),1,5) prosentase
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

        if(!empty($progress)){
            $session['prosentase'] = $progress['prosentase'];
            if($progress['prosentase'] < 33.33){
                $session['barColor'] = 'progress-bar-success';
            }
            if($progress['prosentase'] >= 33.33 && $progress['prosentase']<=66.66){
                $session['barColor'] = 'progress-bar-warning';
            }
            if($progress['prosentase'] > 66.66){
                $session['barColor'] = 'progress-bar-danger';
            }
        }else{
            $session['prosentase'] = 0;
        }
        return $this->redirect(['/activitydata/view', 'id' => $session['activityDataId']]);
    }

    /**
     * Finds the Activitydetail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Activitydetail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Activitydetail::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionGetdata($ids)
    {
        return $this->redirect(['/program/list']);
    }

    public function actionGetPoa()
    {
        $session = Yii::$app->session;
        $poa = $session['jumlah_poa'];

        if(empty($_COOKIE['vol1'])){$vol1 = 1;}else{$vol1 = $_COOKIE['vol1'];}
        if(empty($_COOKIE['vol2'])){$vol2 = 1;}else{$vol2 = $_COOKIE['vol2'];}
        if(empty($_COOKIE['vol3'])){$vol3 = 1;}else{$vol3 = $_COOKIE['vol3'];}
        if(empty($_COOKIE['vol4'])){$vol4 = 1;}else{$vol4 = $_COOKIE['vol4'];}
        if(empty($_COOKIE['harga'])){$harga = 1;}else{$harga = $_COOKIE['harga'];}

        $total =  str_replace('.','',$vol1)*str_replace('.','',$vol2)*str_replace('.','',$vol3)*str_replace('.','',$vol4)*str_replace('.','',$harga);
        return $total+$poa;
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

    public function actionRak($id)
    {
        $POST_VARIABLE=Yii::$app->request->post('Activitydetail');
        if(isset($POST_VARIABLE['vol_1'])){
            $poa_vol_1 = $POST_VARIABLE['vol_1'];
        }else{
            $poa_vol_1 = 1;
        }
        
        if(isset($POST_VARIABLE['vol_2'])){
            $poa_vol_2 = $POST_VARIABLE['vol_2'];
        }else{
            $poa_vol_2 = 1;
        }

        if(isset($POST_VARIABLE['vol_3'])){
            $poa_vol_3 = $POST_VARIABLE['vol_3'];
        }else{
            $poa_vol_3 = 1;
        }

        if(isset($POST_VARIABLE['vol_4'])){
            $poa_vol_4 = $POST_VARIABLE['vol_4'];
        }else{
            $poa_vol_4 = 1;
        }
        
        if(isset($POST_VARIABLE['unit_cost'])){
            $poa_unit_cost = $POST_VARIABLE['unit_cost'];
        }else{
            $poa_unit_cost = 1;
        }

        $model = $this->findModel($id);

        $activityData = ActivityData::findOne($model->activity_data_id);
        $accountaccess = Accountaccess::find()->where(['activity_id' => $activityData->activity_id])->all();

        if(!empty($accountaccess)){
            foreach($accountaccess as $aa)
            {
                $arrAkun[] = "'".$aa['account_id']."'"; 
            } 
            $akun = implode(", ", $arrAkun);
        }else{
            $akun = null;
        }

        if(empty($model->vol_1)){$vol1 = 1;}else{$vol1 = $model->vol_1;}
        if(empty($model->vol_2)){$vol2 = 1;}else{$vol2 = $model->vol_2;}
        if(empty($model->vol_3)){$vol3 = 1;}else{$vol3 = $model->vol_3;}
        if(empty($model->vol_4)){$vol4 = 1;}else{$vol4 = $model->vol_4;}
        if(empty($model->unit_cost)){$harga = 1;}else{$harga = $model->unit_cost;}

        $model->total =  $vol1*$vol2*$vol3*$vol4*$harga;

        $session = Yii::$app->session;

        $alloc = Allocation::find()->where(['tahun' => $session['periodValue']])->one();

        $session['activityDetailId'] = $id;
        $status = Status::find()->where([
            'tahun' => $session['periodValue'],
            'unit_id' => Yii::$app->user->identity->unit_id,
        ])->one();

        if($status->modul_1!=="P") {
            $session['status_poa'] = '';
        }else{
            $session['status_poa'] = 'disabled';
        }

        $verification = Verification::find()->where([
            'program_id' => $session['programId'],
            'unit_id' => Yii::$app->user->identity->unit_id,
            'modul' => 'P',
        ])->one();

        if(!empty($verification)){
            if($verification->revisi == 1){
                $session['revisi_poa'] = true;
            }else{
                $session['revisi_poa'] = false;
            }
        }else{
            $session['revisi_poa'] = false; 
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
                    if($ukm['jumlah'] < $ukm['pagu_ukm'] && $ukm['jumlah'] <> 0){
                        Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA UKM kurang dari '.$alloc->ukm.'%.');
                    }
        
                    if($ukm['jumlah'] > $ukm['pagu_ukm']){
                        Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA UKM melebihi '.$alloc->ukm.'%.');
                    }
                }
            }
        }else{
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
                    if($cvd['jumlah'] < $cvd['pagu_covid'] && $cvd['jumlah'] <> 0){
                        Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA COVID kurang dari '.$alloc->covid.'%.');
                    }
        
                    if($cvd['jumlah'] > $cvd['pagu_covid']){
                        Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA COVID melebihi '.$alloc->covid.'%.');
                    }
                }
            }
        }

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
        $session['pagu'] = $progress['pagu'];
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $session = Yii::$app->session;
            $progress = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, 
            SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as char),0),1,5) prosentase
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

            $session['prosentase'] = $progress['prosentase'];
            if($progress['prosentase'] < 33.33){
                $session['barColor'] = 'progress-bar-success';
            }
            if($progress['prosentase'] >= 33.33 && $progress['prosentase']<=66.66){
                $session['barColor'] = 'progress-bar-warning';
            }
            if($progress['prosentase'] > 66.66){
                $session['barColor'] = 'progress-bar-danger';
            }
        }

        $real = Financialrealization::find()->where([
            'id' => $model->id,
        ])->count();

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('update', [
                'model' => $model,
                'real' => $real,
                'akun' => $akun,
            ]);
        }else{
            return $this->render('update', [
                'model' => $model,
                'real' => $real,
                'akun' => $akun,
            ]);
        }
    }
}
