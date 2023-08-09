<?php

namespace app\controllers;

use Yii;
use app\models\Activity;
use app\models\Activitydata;
use app\models\ActivitydataSearch;
use app\models\Activitydetail;
use app\models\ActivitydetailSearch;
use app\models\Status;
use app\models\Program;
use app\models\Service;
use app\models\Verification;
use app\models\Allocation;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;
use yii\data\SqlDataProvider;

/**
 * ActivitydataController implements the CRUD actions for Activitydata model.
 */
class ActivitydataController extends Controller
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
     * Lists all Activitydata models.
     * @return mixed
     */
    public function actionIndex($id)
    {
        $session = Yii::$app->session;
        $session['activityId'] = $id;
        $searchModel = new ActivitydataSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['activity_id'=>$id, 'period_id'=>$session['periodId']]);
        $activity = Activity::findOne($id);

        foreach( $dataProvider->models as $activityData){
            $dataid = $activityData->id;
         } 
 

        if ($dataProvider->totalCount > 0) {
            $session = Yii::$app->session;
            $session['activityDataId'] = $activityData->id;
            return $this->redirect(array('/activitydata/view', 'id'=>$session['activityDataId']));    
        }else{
            $session['activityId'] = $activity->id;
            return $this->redirect(array('/activitydata/create', 'id'=>$session['activityId']));     
        }
    }

    public function actionList($id)
    {
        $searchModel = new ActivitydataSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $session = Yii::$app->session;
        $dataProvider->query->andWhere(['activity_id'=>$id, 'period_id'=>$session['periodId']]);

        $activity = Activity::findOne($id);
        $session = Yii::$app->session;
        $session['activityId'] = $id;
        $session['activityName'] = $activity->nama_kegiatan;
        $session['activityStatus'] = $activity->status;

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

        if(isset($verification)){
            if($verification->revisi == 1){
                $session['revisi_poa'] = true;
            }else{
                $session['revisi_poa'] = false;
            }
        }

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
                    Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA melebihi Pagu BOK.');
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
                // $session['pagu'] = $progress['pagu'];
                $session['pagu'] = $progress['pagu']-$progress['jumlah'];
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
            }else{
                $session['pagu_ubah'] = 0;
                $progress['prosentase'] = null;
            }
        }

        if (isset($progress['prosentase'])){
            $session['prosentase'] = $progress['prosentase'];
            
            if($progress['prosentase'] == 100){
                $session['barStatus'] = 'bar';
                $session['barColor'] = 'progress-bar-success';
            }else{
                $session['barStatus'] = 'active progress-striped';

                if($progress['prosentase'] < 33.33){
                    $session['barColor'] = 'progress-bar-info';
                }
                elseif($progress['prosentase'] >= 33.33 && $progress['prosentase']<=66.66){
                    $session['barColor'] = 'progress-bar-default';
                }
                elseif($progress['prosentase'] > 66.66 && $progress['prosentase']<100){
                    $session['barColor'] = 'progress-bar-warning';
                }else{
                    $session['barColor'] = 'progress-bar-danger';
                }
            }
        }else{
            $session['prosentase'] = 0;
        }

        return $this->render('list', [
            'searchModel' => $searchModel,  
            'dataProvider' => $dataProvider,
            'id' => $id,
        ]);
    }

    /**
     * Displays a single Activitydata model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $session = Yii::$app->session;
        // $session['ActivityDataId'] = $id;
        $searchModel = new ActivitydetailSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['activity_data_id'=>$id]);
        $activity = Activity::findOne($session['activityId']);
        $session['activityDataId'] = $id;
        $session['activityName'] = $activity->nama_kegiatan;

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
            if($status->modul_4!=="L") {
                $session['status_real'] = '';
            }else{
                $session['status_real'] = 'disabled';
            }
        }

        $verification = Verification::find()->where([
            'program_id' => $session['programId'],
            'unit_id' => Yii::$app->user->identity->unit_id,
            'modul' => 'P',
        ])->one();

        if(isset($verification)){
            if($verification->revisi == 1){
                $session['revisi_poa'] = true;
            }else{
                $session['revisi_poa'] = false;
            }
        }

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
                    Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA melebihi Pagu BOK.');
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
                // $session['pagu'] = $progress['pagu'];
                $session['pagu'] = $progress['pagu']-$progress['jumlah'];
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
            }else{
                $session['pagu_ubah'] = 0;
                $progress['prosentase'] = null;
            }
        }

        if (isset($progress['prosentase'])){
            $session['prosentase'] = $progress['prosentase'];
            
            if($progress['prosentase'] == 100){
                $session['barStatus'] = 'bar';
                $session['barColor'] = 'progress-bar-success';
            }else{
                $session['barStatus'] = 'active progress-striped';

                if($progress['prosentase'] < 33.33){
                    $session['barColor'] = 'progress-bar-info';
                }
                elseif($progress['prosentase'] >= 33.33 && $progress['prosentase']<=66.66){
                    $session['barColor'] = 'progress-bar-default';
                }
                elseif($progress['prosentase'] > 66.66 && $progress['prosentase']<100){
                    $session['barColor'] = 'progress-bar-warning';
                }else{
                    $session['barColor'] = 'progress-bar-danger';
                }
            }
        }else{
            $session['prosentase'] = 0;
        }
        
        return $this->render('view', [
            'model' => $this->findModel($id),
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'id' => $id,
        ]);
    }

    /**
     * Creates a new Activitydata model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($id, $modul)
    {
        $session = Yii::$app->session;
        $session->open();
        $model = new Activitydata();
        $model->activity_id=$id;
        $model->period_id=$session['periodId'];
        $activity = Activity::findOne($id);
        $session['activityName'] = $activity->nama_kegiatan;
        $session['activityId'] = $activity->id;

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


        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['activitydata/list', 'id' => $session['activityId']]);
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

        foreach($cekPoa as $cekPoa){
            if($cekPoa['jumlah'] > $cekPoa['pagu'] && $cekPoa['pagu'] > 0){
                Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA melebihi Pagu BOK.');
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
        
        foreach($progress as $progress);

        $session = Yii::$app->session;

        if(!empty($progress)){
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
        }else{
            $session['pagu_ubah'] = 0;
            $progress['prosentase'] = null;
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
     * Updates an existing Activitydata model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id, $modul, $mid)
    {
        $model = $this->findModel($id);
        $session = Yii::$app->session;
        $session['activityDataId'] = $id;

        $alloc = Allocation::find()->where(['tahun' => $session['periodValue']])->one();

        if($modul !== 'new'){
            if($modul == 'program'){
                $query='SELECT p.id, p.nama_program nama FROM program p
                WHERE p.tahun='.$session['periodValue'].' ORDER BY p.id';
                $session['modul'] = 'service';
            }
            if($modul == 'service'){
                $query='SELECT s.id, s.nama_pelayanan nama FROM service s
                WHERE s.program_id='.$mid.' ORDER BY s.id';
                $session['modul'] = 'activity';
                $session['programIdNew'] = $mid;

            }
            if($modul == 'activity'){
                $query='SELECT a.id, a.nama_kegiatan nama FROM activity a
                WHERE a.service_id='.$mid.' ORDER BY a.id';
                $session['modul'] = 'select';
                $session['serviceIdNew'] = $mid;
            }
            if($modul == 'select'){
                $query='SELECT a.id, a.nama_kegiatan nama FROM activity a
                WHERE a.service_id='.$mid.' ORDER BY a.id';
                $session['activityIdNew'] = $mid;
            }

            $dataProvider = new SqlDataProvider([
                'sql' => $query,
                'pagination' => false
            ]);

            $model2 = $dataProvider->getModels();
            
            $program = Program::findOne($session['programIdNew']);
            if(!empty($program)){ $model->programId = $program->nama_program; }
            $service = Service::findOne($session['serviceIdNew']);
            if(!empty($service)){ $model->serviceId = $service->nama_pelayanan; }
            $activity = Activity::findOne($session['activityIdNew']);
            if(!empty($activity)){
                $model->activityId = $activity->nama_kegiatan;
                $model->activity_id = $activity->id;
            }
        }else{
            $dataProvider = null;
            $model2 = null;
        }

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

        // $cekPoa = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)),0) prosentase
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

        if (isset($progress['prosentase'])){
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
        }else{
            $session['prosentase'] = 0;
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['activitydata/list', 'id' => $session['activityId']]);
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
     * Deletes an existing Activitydata model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $session = Yii::$app->session;
        $data = Activitydata::find()->where(['id' => $id])->one();
        $detail = Activitydetail::find()->where(['activity_data_id' => $id])->one();

        if(empty($detail)){
            $this->findModel($id)->delete();
            Yii::$app->session->setFlash('Berhasil', 'Data telah dihapus!');
            return $this->redirect(['activitydata/list', 'id' => $session['activityId']]);
            // return "Berhasil dihapus";
        }else{
            Yii::$app->session->setFlash('error', 'Data tidak bisa dihapus! ada detail kegiatan didalamnya.');
            return $this->redirect(['activitydata/list', 'id' => $session['activityId']]);
            // return "Gagal dihapus";
        }

        // $this->findModel($id)->delete();
        // $session = Yii::$app->session;
        // $session->open();
        // return $this->redirect(['activitydata/list', 'id' => $session['activityId']]);
    }

    /**
     * Finds the Activitydata model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Activitydata the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Activitydata::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
