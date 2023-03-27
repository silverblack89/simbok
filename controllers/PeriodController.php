<?php

namespace app\controllers;

use Yii;
use app\models\Period;
use app\models\PeriodSearch;
use app\models\Exportaccount;
use app\models\Exportprogram;
use app\models\Activity;
use app\models\Activitydata;
use app\models\Activitydataubah;
use app\models\Activitydetail;
use app\models\Activitydetailubah;
use app\models\Exportperfomance;
use app\models\Exportperfomancebln;
use app\models\Unit;
use app\models\ExportaccountSearch;
use app\models\Profile;
use app\models\User;
use app\models\Status;
use app\models\Service;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use yii\filters\AccessControl;
use yii\data\SqlDataProvider;

/**
 * PeriodController implements the CRUD actions for Period model.
 */
class PeriodController extends Controller
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

    public function actionSubKegiatan($id)
    {
        $session = Yii::$app->session;
        $session['subKegiatan'] = $id;
        
        if($id > 0){
            $query = 'SELECT u.id, g.nama_program, s.nama_kegiatan, v.id id_sub, v.nama_pelayanan, IFNULL(a.bentuk_kegiatan, v.nama_pelayanan) bentuk_kegiatan, 
            a.sasaran, a.target, a.lokasi, a.pelaksana, a.activity_data_sub_id,
            c.nama_rekening, e.rincian,
            e.vol_1, e.satuan_1, 
            
            IFNULL(e.vol_2,"") vol_2, IFNULL(e.satuan_2,"") satuan_2, 
            IFNULL(e.vol_3,"") vol_3, IFNULL(e.satuan_3,"") satuan_3,
            IFNULL(e.vol_4,"") vol_4, IFNULL(e.satuan_4,"") satuan_4,
            vol_1*IFNULL(vol_2,1)*IFNULL(vol_3,1)*IFNULL(vol_4,1) vol, 
            e.unit_cost, e.jumlah
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity s ON s.id=a.activity_id
            LEFT JOIN service v ON v.id=s.service_id
            LEFT JOIN program g ON g.id=v.program_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN account c ON c.id=e.account_id
            LEFT JOIN unit u ON u.id=p.unit_id
            WHERE p.tahun="'.$session['periodValue'].'" 
            AND a.activity_data_sub_id = "'.$id.'"
            ORDER BY g.id, s.id, v.id, a.id';
        }else{
            $query = 'SELECT u.id, g.nama_program, s.nama_kegiatan, v.id id_sub, v.nama_pelayanan, IFNULL(a.bentuk_kegiatan, v.nama_pelayanan) bentuk_kegiatan, 
            a.sasaran, a.target, a.lokasi, a.pelaksana, a.activity_data_sub_id,
            c.nama_rekening, e.rincian,
            e.vol_1, e.satuan_1, 
            
            IFNULL(e.vol_2,"") vol_2, IFNULL(e.satuan_2,"") satuan_2, 
            IFNULL(e.vol_3,"") vol_3, IFNULL(e.satuan_3,"") satuan_3,
            IFNULL(e.vol_4,"") vol_4, IFNULL(e.satuan_4,"") satuan_4,
            vol_1*IFNULL(vol_2,1)*IFNULL(vol_3,1)*IFNULL(vol_4,1) vol, 
            e.unit_cost, e.jumlah
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity s ON s.id=a.activity_id
            LEFT JOIN service v ON v.id=s.service_id
            LEFT JOIN program g ON g.id=v.program_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN account c ON c.id=e.account_id
            LEFT JOIN unit u ON u.id=p.unit_id
            WHERE p.tahun IS NULL';
        }

        $session['qrydetail'] = $query;

        // return $query;

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model = $dataProvider->getModels();

        return $this->render('subkegiatan', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'namaUnit' => Yii::$app->user->identity->alias
        ]);
    }

    public function actionExportxlssub()
    {
        $session = Yii::$app->session;
        $period = $session['periodValue'];

        Yii::$app->db->createCommand('DELETE FROM export_program WHERE username=:username AND period=:periodValue ')
        ->bindValue(':username', Yii::$app->user->identity->unit_id)
        ->bindValue(':periodValue', $period)
        ->execute();

        $data = Yii::$app->db->createCommand($session['qrydetail'])
        ->queryAll();

        // return $session['qrydetail'];exit;

        $rinci = '';
        $komponen = '';
        $kegiatan = '';
        $bentuk = '';
        foreach ($data as $row) {
            $exportprogram =  new Exportprogram();
   
            $exportprogram->nama_program=$row['nama_program'];
            
            if ($komponen !== $row['nama_pelayanan']) {
                $exportprogram->nama_pelayanan=$row['nama_pelayanan']; $komponen = $row['nama_pelayanan'];
            }

            if ($kegiatan !== $row['nama_kegiatan']) {
                $exportprogram->nama_kegiatan=$row['nama_kegiatan']; 
                
                $jumlahSub = Yii::$app->db->createCommand('SELECT v.id, IFNULL(SUM(e.jumlah),0) jml_sub FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE s.id=:id AND a.activity_data_sub_id=:subId')
                ->bindValue(':id', $row['id_sub'])
                ->bindValue(':subId', $row['activity_data_sub_id'])
                ->queryAll();

                foreach($jumlahSub as $jmlsub);
                $exportprogram->jumlah_awal = $jmlsub['jml_sub'];

                $kegiatan = $row['nama_kegiatan'];
            }

            if ($bentuk !== $row['bentuk_kegiatan']) {
                $exportprogram->bentuk_kegiatan=$row['bentuk_kegiatan']; $bentuk = $row['bentuk_kegiatan'];
                $exportprogram->sasaran=$row['sasaran'];
                $exportprogram->target=$row['target'];
                $exportprogram->lokasi=$row['lokasi'];
                $exportprogram->pelaksana=$row['pelaksana'];
            }

            $exportprogram->nama_rekening=$row['nama_rekening'];
            $exportprogram->rincian=$row['rincian'];

            $exportprogram->vol_1=$row['vol_1'];
            $exportprogram->satuan_1=$row['satuan_1'];
            $exportprogram->vol_2=$row['vol_2'];
            $exportprogram->satuan_2=$row['satuan_2'];

            $exportprogram->vol_3=$row['vol_3'];
            $exportprogram->satuan_3=$row['satuan_3'];
            $exportprogram->vol_4=$row['vol_4'];
            $exportprogram->satuan_4=$row['satuan_4'];

            $exportprogram->vol=$row['vol'];
            $exportprogram->unit_cost=$row['unit_cost'];
            $exportprogram->jumlah=$row['jumlah'];
            $exportprogram->username=Yii::$app->user->identity->unit_id;
            $exportprogram->period=$period;
            $exportprogram->save();
        }

        $dataprogram = Yii::$app->db->createCommand('SELECT p.* FROM dept_program p
        RIGHT JOIN export_program e ON e.nama_program=p.nama_program
        WHERE tahun=:tahun AND e.username=:username
        GROUP BY p.nama_program
        ORDER BY p.id')
        ->bindValue(':tahun', $period)
        ->bindValue(':username', Yii::$app->user->identity->unit_id)
        ->queryAll();

        $inputFileName = Yii::getAlias('@app/views/exportaccount').'/_export_komponen_dinas.xlsx';

        /** Load $inputFileName to a Spreadsheet Object  **/
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        // $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();

        $baseRowAwal = 0;
        $baseRowProgram = 4;
        $baseRowService = 0;

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $styleArrayHeader = [
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        foreach ($dataprogram as $dataprogram) {
            $dataExcel = Yii::$app->db->createCommand('SELECT e.*, p.id FROM export_program e
            LEFT JOIN dept_program p ON p.nama_program=e.nama_program AND p.tahun=:periodValue
            where e.nama_program=:namaprogram AND username=:username AND period=:periodValue ')
            ->bindValue(':username', Yii::$app->user->identity->unit_id)
            ->bindValue(':periodValue', $period)
            ->bindValue(':namaprogram', $dataprogram['nama_program'])
            ->queryAll();

            $jumlahProgram = Yii::$app->db->createCommand('SELECT SUM(e.jumlah) total FROM export_program e
            LEFT JOIN dept_program p ON p.nama_program=e.nama_program AND p.tahun=:periodValue
            where e.nama_program=:namaprogram AND username=:username AND period=:periodValue ')
            ->bindValue(':username', Yii::$app->user->identity->unit_id)
            ->bindValue(':periodValue', $period)
            ->bindValue(':namaprogram', $dataprogram['nama_program'])
            ->queryAll();

            foreach($jumlahProgram as $jmlprogram);
            // $exportprogram->jumlah_awal = $jmlprogram['total'];

            $count = count($dataExcel);

            $baseRowAwal = $baseRowAwal+1;
            $tabletitle = $baseRowProgram+2;
            if ($baseRowAwal > 1) {
                $baseRowProgram = $baseRowProgram+1;
                $activeSheet->setCellValue('A'.$baseRowProgram, $baseRowAwal) 
                ->setCellValue('C'.$baseRowProgram, $dataprogram['nama_program']);
            }else{
                $activeSheet->setCellValue('A'.$baseRowProgram, $baseRowAwal)
                ->setCellValue('C'.$baseRowProgram, $dataprogram['nama_program'])
                ->setCellValue('X'.$baseRowProgram, $jmlprogram['total']);
                
                $spreadsheet->getActiveSheet()->mergeCells('C'.$baseRowProgram. ':W' .$baseRowProgram);
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowProgram. ':X' .$baseRowProgram)->applyFromArray($styleArrayHeader);
                $activeSheet->getStyle('A'.$baseRowProgram. ':X' .$baseRowProgram)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('9BC2E6');
            }
                 
            $baseRowService = 0;

            $baseRow = $baseRowProgram+1;
            $firstData = $baseRowProgram+1;
            $rowAkhir = '';
            
            if ($count > 0) {
                foreach($dataExcel as $rowExcel) {
                    if ($rowAkhir === $baseRowAwal) {
                        $rowAkhir = '';
                    }else{
                        $rowAkhir = $baseRowAwal;
                    }

                    $activeSheet->setCellValue('A'.$baseRow, '')
                    ->setCellValue('B'.$baseRow, '')
                    ->setCellValue('C'.$baseRow, $rowExcel['nama_kegiatan'])
                    ->setCellValue('D'.$baseRow, $rowExcel['bentuk_kegiatan'])
                    ->setCellValue('E'.$baseRow, '')
                    ->setCellValue('F'.$baseRow, $rowExcel['nama_rekening'])
                    ->setCellValue('G'.$baseRow, $rowExcel['rincian'])
                    ->setCellValue('H'.$baseRow, '')
                    ->setCellValue('I'.$baseRow, $rowExcel['vol_1'])
                    ->setCellValue('J'.$baseRow, $rowExcel['satuan_1'])
                    ->setCellValue('K'.$baseRow, 'x')
                    ->setCellValue('L'.$baseRow, $rowExcel['vol_2'])
                    ->setCellValue('M'.$baseRow, $rowExcel['satuan_2'])
                    ->setCellValue('N'.$baseRow, 'x')
                    ->setCellValue('O'.$baseRow, $rowExcel['vol_3'])
                    ->setCellValue('P'.$baseRow, $rowExcel['satuan_3'])
                    ->setCellValue('Q'.$baseRow, 'x')
                    ->setCellValue('R'.$baseRow, $rowExcel['vol_4'])
                    ->setCellValue('S'.$baseRow, $rowExcel['satuan_4'])
                    ->setCellValue('T'.$baseRow, '=')
                    ->setCellValue('U'.$baseRow, $rowExcel['vol'])
                    ->setCellValue('V'.$baseRow, $rowExcel['unit_cost'])
                    ->setCellValue('W'.$baseRow, $rowExcel['jumlah'])
                    ->setCellValue('X'.$baseRow, $rowExcel['jumlah_awal']);

                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':X' .$baseRow)->applyFromArray($styleArray);
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':X' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setVertical('top'); 
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':X' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setWrapText(true);
                                            
                    $baseRow++;
                    $baseRowService = $baseRowService+1;
                    $rowAkhir = $baseRowAwal;
                }
                
                $baseRowProgram=$baseRowProgram+1;
                $baseRowProgram++;   
            }else{  
                $baseRowProgram++; 
            }
        }

        // $spreadsheet->getSecurity()->setLockWindows(true);
        // $spreadsheet->getSecurity()->setLockStructure(true);
        // $spreadsheet->getSecurity()->setWorkbookPassword("silverblack");

        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setPassword('silverblack');
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setSheet(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setSort(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setInsertRows(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setFormatCells(true);

        // $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
        

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_rekap_sub_kegiatan_'.$period.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * Lists all Period models.
     * @return mixed
     */
    public function actionIndex($tahun)
    {
        $session = Yii::$app->session;
        $session['periodValue'] = $tahun;
        $searchModel = new PeriodSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        // $dataProvider->query->leftJoin('unit', 'unit.id' = 'period.unit_id');
        $dataProvider->query->joinWith(['unit']);
        $dataProvider->query->andWhere(['tahun'=>$session['periodValue']]);
        $dataProvider->query->orderBy('puskesmas');
        $dataProvider->pagination = false;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Period model.
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
     * Creates a new Period model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($p)
    {
        $session = Yii::$app->session;
        $session['poa'] = $p;
        $POST_VARIABLE=Yii::$app->request->post('Period');
        $request = $POST_VARIABLE['tahun'];

        $period = Period::find()
        ->where(['unit_id' => Yii::$app->user->identity->unit_id, 'tahun' => $request])
        ->one();

        if (!isset($period)) {
            $model = new Period();
            $model->setScenario('listprogram');
            $model->unit_id = Yii::$app->user->identity->unit_id;
            $model->pagu = 0;
            $model->pagu_covid = 0;
            $model->pagu_ukm = 0;
            $model->pagu_geser = 0;
            $model->pagu_ubah = 0;
        }else{
            $model = $this->findModel($period->id);
            $model->setScenario('listprogram');
        }

        if($p == 'def'){
            $session['poa'] = 'def';
            $session['poaLabel'] = ' Awal';
        }elseif($p == 'pergeseran'){
            $session['poa'] = 'pergeseran';
            $session['poaLabel'] = ' Pergeseran';
        }elseif($p == 'perubahan'){
            $session['poa'] = 'perubahan';
            $session['poaLabel'] = ' Perubahan';
        }
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $session['periodId'] = $model->id;
            $session['periodValue'] = $model->tahun;

            if ($session['poa'] == 'perubahan'){
                $activitydata = Activitydata::find()
                ->where(['period_id' => $period->id])
                ->all();
                foreach($activitydata as $row){
                    $exists = Activitydataubah::find()->where(['activity_data_id' => $row->id])->exists();
                    if($exists == false){
                        $activitydatageser = new Activitydataubah();

                        $command = Yii::$app->db->createCommand('SHOW TABLE STATUS LIKE "activity_data_ubah"')->queryAll();	
                        foreach($command as $next_id);

                        $activitydatageser->activity_data_id = $row->id;
                        $activitydatageser->activity_id = $row->activity_id;
                        $activitydatageser->period_id = $row->period_id;
                        $activitydatageser->bentuk_kegiatan = $row->bentuk_kegiatan;
                        $activitydatageser->sasaran = $row->sasaran;
                        $activitydatageser->target = $row->target;
                        $activitydatageser->lokasi = $row->lokasi;
                        $activitydatageser->pelaksana = $row->pelaksana;

                            $activitydetail = Activitydetail::find()
                            ->where(['activity_data_id' => $row->id])
                            ->all();
                            foreach($activitydetail as $rowdetail){
                                $activitydetailgeser = new Activitydetailubah();
                                $activitydetailgeser->activity_detail_id = $rowdetail->id;
                                $activitydetailgeser->activity_data_id = $next_id['Auto_increment'];
                                $activitydetailgeser->account_id = $rowdetail->account_id;
                                $activitydetailgeser->rincian = $rowdetail->rincian;
                                $activitydetailgeser->vol_1 = $rowdetail->vol_1;
                                $activitydetailgeser->satuan_1 = $rowdetail->satuan_1;
                                $activitydetailgeser->vol_2 = $rowdetail->vol_2;
                                $activitydetailgeser->satuan_2 = $rowdetail->satuan_2;
                                $activitydetailgeser->unit_cost = strval($rowdetail->unit_cost);
                                $activitydetailgeser->jumlah = $rowdetail->jumlah;
                                $activitydetailgeser->jan = $rowdetail->jan;
                                $activitydetailgeser->feb = $rowdetail->feb;
                                $activitydetailgeser->mar = $rowdetail->mar;
                                $activitydetailgeser->apr = $rowdetail->apr;
                                $activitydetailgeser->mei = $rowdetail->mei;
                                $activitydetailgeser->jun = $rowdetail->jun;
                                $activitydetailgeser->jul = $rowdetail->jul;
                                $activitydetailgeser->agu = $rowdetail->agu;
                                $activitydetailgeser->sep = $rowdetail->sep;
                                $activitydetailgeser->okt = $rowdetail->okt;
                                $activitydetailgeser->nov = $rowdetail->nov;
                                $activitydetailgeser->des = $rowdetail->des;
                                $activitydetailgeser->save();
                            }

                        $activitydatageser->save();
                    }
                }
            }
            return $this->redirect(['program/list']);
        }

        //query chart

        $tahunnya = $session['periodValue']; 

        $query = 'SELECT u.puskesmas POA, CASE WHEN IFNULL(p.pagu,0)=0 THEN 0
        ELSE IFNULL(p.pagu,0)-sum(IFNULL(e.jumlah,0))
        END AS prosentase
        FROM activity_detail e LEFT JOIN activity_data a ON a.id=e.activity_data_id 
        LEFT JOIN activity v ON v.id=a.activity_id 
        LEFT JOIN service s ON s.id=v.service_id 
        LEFT JOIN period p ON p.id=a.period_id 
        LEFT JOIN unit u ON u.id=p.unit_id WHERE p.tahun="'.$tahunnya.'"
        GROUP BY p.unit_id, p.pagu';

        // return $query;

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model2 = $dataProvider->getModels();

        return $this->render('create', [
            'model' => $model,
            'data' => $model2,
            'dataProvider' => $dataProvider,
            'tahun' => $tahunnya,
        ]);

        // echo $request;
    }

    /**
     * Updates an existing Period model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // if ($model->load(Yii::$app->request->post()) && $model->save()) {
        //     return $this->redirect(['view', 'id' => $model->id]);
        // }

        // return $this->render('update', [
        //     'model' => $model,
        // ]);

        $session = Yii::$app->session;
        $model->setScenario('entripagu');
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $searchModel = new PeriodSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            $dataProvider->query->joinWith(['unit']);
            $dataProvider->query->andWhere(['tahun'=>$session['periodValue']]);
            $dataProvider->query->orderBy('puskesmas');
            $dataProvider->pagination = false;

            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }elseif (Yii::$app->request->isAjax) {
            return $this->renderAjax('update', [
                        'model' => $model
            ]);
            // return "ajax";
        } else {
            return $this->render('update', [
                        'model' => $model
            ]);
            // return 'non-ajax';
        }
    }

    /**
     * Deletes an existing Period model.
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
     * Finds the Period model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Period the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Period::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionUnlockall($id)
    {
        $session = Yii::$app->session;
        // $data = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)),0) prosentase,
        // CASE 
        //     WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) < 33.33 THEN "progress-bar-success"
        //     WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) >= 33.33 AND cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) <=66.66 THEN "progress-bar-warning"
        //     ELSE "progress-bar-danger"
        // END AS bar_color,
		// CASE WHEN t.modul_1 = "P" THEN "Buka Kunci" ELSE "Kunci" END AS status_poa,
		// CASE WHEN t.modul_1 = "P" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-check" END AS status_poa_icon,
		  
        // CASE WHEN t.modul_2 = "G" THEN "Buka Kunci" ELSE "Kunci" END AS status_geser,
        // CASE WHEN t.modul_2 = "G" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-transfer" END AS status_geser_icon,
        
        // CASE WHEN t.modul_3 = "R" THEN "Buka Kunci" ELSE "Kunci" END AS status_rubah,
        // CASE WHEN t.modul_3 = "R" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-pencil" END AS status_rubah_icon,

        // CASE WHEN t.modul_4 = "L" THEN "Buka Kunci" ELSE "Kunci" END AS status_real,
        // CASE WHEN t.modul_4 = "L" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-stats" END AS status_real_icon
        // FROM activity_detail e
        // LEFT JOIN activity_data a ON a.id=e.activity_data_id
        // LEFT JOIN activity v ON v.id=a.activity_id
        // LEFT JOIN service s ON s.id=v.service_id
        // LEFT JOIN period p ON p.id=a.period_id
        // LEFT JOIN unit u ON u.id=p.unit_id
        // LEFT JOIN status t ON t.unit_id=p.unit_id AND t.tahun=:periode
        // WHERE p.tahun=:periode
        // GROUP BY p.unit_id, p.pagu
        // ORDER BY u.puskesmas')
        // ->bindValue(':periode', $session['periodValue']) 
        // ->queryAll();

        $data = Yii::$app->db->createCommand('select id unit_id from unit where mid(id,1,3) = "P33"')
        ->queryAll();

        if(!empty($data))
        {
            foreach($data as $row)
            {
                if(substr($id,-1)==='P'){
                    $status = Status::find()->where([
                        'tahun' => $session['periodValue'],
                        'unit_id' => $row['unit_id'],
                    ])->count();

                    if($status=="0") {
                        $model = new Status(); //$params
                        $model->modul_1 = null;
                        $model->tahun = $session['periodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }else{
                        $status = Status::find()->where([
                            'tahun' => $session['periodValue'],
                            'unit_id' => $row['unit_id'],
                        ])->one();
        
                        $model = Status::findOne($status['id']);
                        $model->modul_1 = null;
                        $model->tahun = $session['periodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }
                }

                if(substr($id,-1)==='G'){
                    $status = Status::find()->where([
                        'tahun' => $session['periodValue'],
                        'unit_id' => $row['unit_id'],
                    ])->count();

                    if($status=="0") {
                        $model = new Status(); //$params
                        $model->modul_2 = null;
                        $model->tahun = $session['periodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }else{
                        $status = Status::find()->where([
                            'tahun' => $session['periodValue'],
                            'unit_id' => $row['unit_id'],
                        ])->one();
        
                        $model = Status::findOne($status['id']);
                        $model->modul_2 = null;
                        $model->tahun = $session['periodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }
                }

                if(substr($id,-1)==='R'){
                    $status = Status::find()->where([
                        'tahun' => $session['periodValue'],
                        'unit_id' => $row['unit_id'],
                    ])->count();

                    if($status=="0") {
                        $model = new Status(); //$params
                        $model->modul_3 = null;
                        $model->tahun = $session['periodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }else{
                        $status = Status::find()->where([
                            'tahun' => $session['periodValue'],
                            'unit_id' => $row['unit_id'],
                        ])->one();
        
                        $model = Status::findOne($status['id']);
                        $model->modul_3 = null;
                        $model->tahun = $session['periodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }
                }

                if(substr($id,-1)==='L'){
                    $status = Status::find()->where([
                        'tahun' => $session['periodValue'],
                        'unit_id' => $row['unit_id'],
                    ])->count();

                    if($status=="0") {
                        $model = new Status(); //$params
                        $model->modul_4 = null;
                        $model->tahun = $session['periodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }else{
                        $status = Status::find()->where([
                            'tahun' => $session['periodValue'],
                            'unit_id' => $row['unit_id'],
                        ])->one();
        
                        $model = Status::findOne($status['id']);
                        $model->modul_4 = null;
                        $model->tahun = $session['periodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }
                }
            }
        }
        return $this->redirect(array('list', 'period'=>$session['periodValue']));    
    }

    public function actionLockall($id)
    {
        $session = Yii::$app->session;
        // $data = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)),0) prosentase,
        // CASE 
        //     WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) < 33.33 THEN "progress-bar-success"
        //     WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) >= 33.33 AND cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) <=66.66 THEN "progress-bar-warning"
        //     ELSE "progress-bar-danger"
        // END AS bar_color,
		// CASE WHEN t.modul_1 = "P" THEN "Buka Kunci" ELSE "Kunci" END AS status_poa,
		// CASE WHEN t.modul_1 = "P" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-check" END AS status_poa_icon,
		  
        // CASE WHEN t.modul_2 = "G" THEN "Buka Kunci" ELSE "Kunci" END AS status_geser,
        // CASE WHEN t.modul_2 = "G" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-transfer" END AS status_geser_icon,
        
        // CASE WHEN t.modul_3 = "R" THEN "Buka Kunci" ELSE "Kunci" END AS status_rubah,
        // CASE WHEN t.modul_3 = "R" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-pencil" END AS status_rubah_icon,

        // CASE WHEN t.modul_4 = "L" THEN "Buka Kunci" ELSE "Kunci" END AS status_real,
        // CASE WHEN t.modul_4 = "L" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-stats" END AS status_real_icon
        // FROM activity_detail e
        // LEFT JOIN activity_data a ON a.id=e.activity_data_id
        // LEFT JOIN activity v ON v.id=a.activity_id
        // LEFT JOIN service s ON s.id=v.service_id
        // LEFT JOIN period p ON p.id=a.period_id
        // LEFT JOIN unit u ON u.id=p.unit_id
        // LEFT JOIN status t ON t.unit_id=p.unit_id AND t.tahun=:periode
        // WHERE p.tahun=:periode
        // GROUP BY p.unit_id, p.pagu
        // ORDER BY u.puskesmas')
        // ->bindValue(':periode', $session['periodValue']) 
        // ->queryAll();

        $data = Yii::$app->db->createCommand('select id unit_id from unit where mid(id,1,3) = "P33"')
        ->queryAll();

        if(!empty($data))
        {
            foreach($data as $row)
            {
                if(substr($id,-1)==='P'){
                    $status = Status::find()->where([
                        'tahun' => $session['periodValue'],
                        'unit_id' => $row['unit_id'],
                    ])->count();

                    if($status=="0") {
                        $model = new Status(); //$params
                        $model->modul_1 = substr($id,-1);
                        $model->tahun = $session['periodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }else{
                        $status = Status::find()->where([
                            'tahun' => $session['periodValue'],
                            'unit_id' => $row['unit_id'],
                        ])->one();
        
                        $model = Status::findOne($status['id']);
                        $model->modul_1 = substr($id,-1);
                        $model->tahun = $session['periodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }
                }

                if(substr($id,-1)==='G'){
                    $status = Status::find()->where([
                        'tahun' => $session['periodValue'],
                        'unit_id' => $row['unit_id'],
                    ])->count();

                    if($status=="0") {
                        $model = new Status($params);
                        $model->modul_2 = substr($id,-1);
                        $model->tahun = $session['periodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }else{
                        $status = Status::find()->where([
                            'tahun' => $session['periodValue'],
                            'unit_id' => $row['unit_id'],
                        ])->one();
        
                        $model = Status::findOne($status['id']);
                        $model->modul_2 = substr($id,-1);
                        $model->tahun = $session['periodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }
                }

                if(substr($id,-1)==='R'){
                    $status = Status::find()->where([
                        'tahun' => $session['periodValue'],
                        'unit_id' => $row['unit_id'],
                    ])->count();

                    if($status=="0") {
                        $model = new Status($params);
                        $model->modul_3 = substr($id,-1);
                        $model->tahun = $session['periodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }else{
                        $status = Status::find()->where([
                            'tahun' => $session['periodValue'],
                            'unit_id' => $row['unit_id'],
                        ])->one();
        
                        $model = Status::findOne($status['id']);
                        $model->modul_3 = substr($id,-1);
                        $model->tahun = $session['periodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }
                }

                if(substr($id,-1)==='L'){
                    $status = Status::find()->where([
                        'tahun' => $session['periodValue'],
                        'unit_id' => $row['unit_id'],
                    ])->count();

                    if($status=="0") {
                        $model = new Status($params);
                        $model->modul_4 = substr($id,-1);
                        $model->tahun = $session['periodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }else{
                        $status = Status::find()->where([
                            'tahun' => $session['periodValue'],
                            'unit_id' => $row['unit_id'],
                        ])->one();
        
                        $model = Status::findOne($status['id']);
                        $model->modul_4 = substr($id,-1);
                        $model->tahun = $session['periodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }
                }
            }
        }
        return $this->redirect(array('list', 'period'=>$session['periodValue']));    
    }

    public function actionUnlockallreal($id,$tw)
    {
        $session = Yii::$app->session;
        $data = Yii::$app->db->createCommand('select id unit_id from unit where mid(id,1,3) = "P33"')
        ->queryAll();

        if(!empty($data))
        {
            foreach($data as $row)
            {
                if(substr($id,-1)==='L'){
                    $status = Status::find()->where([
                        'tahun' => $session['periodValue'],
                        'unit_id' => $row['unit_id'],
                    ])->count();

                    if($status=="0") {
                        $model = new Status(); //$params
                        $model->modul_4 = null;
                        $model->tahun = $session['periodValue'];
                        $model->unit_id = $row['unit_id'];
                        if($tw == '1'){$model->tw_1=null;}
                        if($tw == '2'){$model->tw_2=null;}
                        if($tw == '3'){$model->tw_3=null;}
                        if($tw == '4'){$model->tw_4=null;}
                        $model->save();
                    }else{
                        $status = Status::find()->where([
                            'tahun' => $session['periodValue'],
                            'unit_id' => $row['unit_id'],
                        ])->one();
        
                        $model = Status::findOne($status['id']);
                        $model->modul_4 = null;
                        $model->tahun = $session['periodValue'];
                        $model->unit_id = $row['unit_id'];
                        if($tw == '1'){$model->tw_1=null;}
                        if($tw == '2'){$model->tw_2=null;}
                        if($tw == '3'){$model->tw_3=null;}
                        if($tw == '4'){$model->tw_4=null;}
                        $model->save();
                    }
                }
            }
        }
        return $this->redirect(array('list', 'period'=>$session['periodValue']));    
    }

    public function actionLockallreal($id,$tw)
    {
        $session = Yii::$app->session;
        $data = Yii::$app->db->createCommand('select id unit_id from unit where mid(id,1,3) = "P33"')
        ->queryAll();

        if(!empty($data))
        {
            foreach($data as $row)
            {
                if(substr($id,-1)==='L'){
                    $status = Status::find()->where([
                        'tahun' => $session['periodValue'],
                        'unit_id' => $row['unit_id'],
                    ])->count();

                    if($status=="0") {
                        $model = new Status($params);
                        $model->modul_4 = substr($id,-1);
                        $model->tahun = $session['periodValue'];
                        $model->unit_id = $row['unit_id'];
                        if($tw == '1'){$model->tw_1=$tw;}
                        if($tw == '2'){$model->tw_2=$tw;}
                        if($tw == '3'){$model->tw_3=$tw;}
                        if($tw == '4'){$model->tw_4=$tw;}
                        $model->save();
                    }else{
                        $status = Status::find()->where([
                            'tahun' => $session['periodValue'],
                            'unit_id' => $row['unit_id'],
                        ])->one();
        
                        $model = Status::findOne($status['id']);
                        $model->modul_4 = substr($id,-1);
                        $model->tahun = $session['periodValue'];
                        $model->unit_id = $row['unit_id'];
                        if($tw == '1'){$model->tw_1=$tw;}
                        if($tw == '2'){$model->tw_2=$tw;}
                        if($tw == '3'){$model->tw_3=$tw;}
                        if($tw == '4'){$model->tw_4=$tw;}
                        $model->save();
                    }
                }
            }
        }
        return $this->redirect(array('list', 'period'=>$session['periodValue']));    
    }

    public function actionLock($id)
    {
        $session = Yii::$app->session;

        if(substr($id,-1)==='P'){
            $status = Status::find()->where([
                'tahun' => $session['periodValue'],
                'unit_id' => substr($id,0,11),
            ])->count();

            if($status=="0") {
                $model = new Status(); //$params
                $model->modul_1 = substr($id,-1);
                $model->tahun = $session['periodValue'];
                $model->unit_id = substr($id,0,11);
                $model->save();
            }else{
                $status = Status::find()->where([
                    'tahun' => $session['periodValue'],
                    'unit_id' => substr($id,0,11),
                ])->one();

                $model = Status::findOne($status['id']);

                if($model->modul_1 === substr($id,-1)){
                    $model->modul_1 = null;
                    $model->tahun = $session['periodValue'];
                    $model->unit_id = substr($id,0,11);
                    $model->save();
                }else{
                    $model->modul_1 = substr($id,-1);
                    $model->tahun = $session['periodValue'];
                    $model->unit_id = substr($id,0,11);
                    $model->save();
                }
            }
        }

        if(substr($id,-1)==='G'){
            $status = Status::find()->where([
                'tahun' => $session['periodValue'],
                'unit_id' => substr($id,0,11),
            ])->count();

            if($status=="0") {
                $model = new Status($params);
                $model->modul_2 = substr($id,-1);
                $model->tahun = $session['periodValue'];
                $model->unit_id = substr($id,0,11);
                $model->save();
            }else{
                $status = Status::find()->where([
                    'tahun' => $session['periodValue'],
                    'unit_id' => substr($id,0,11),
                ])->one();

                $model = Status::findOne($status['id']);

                if($model->modul_2 === substr($id,-1)){
                    $model->modul_2 = null;
                    $model->tahun = $session['periodValue'];
                    $model->unit_id = substr($id,0,11);
                    $model->save();
                }else{
                    $model->modul_2 = substr($id,-1);
                    $model->tahun = $session['periodValue'];
                    $model->unit_id = substr($id,0,11);
                    $model->save();
                }
            }
        }

        if(substr($id,-1)==='R'){
            $status = Status::find()->where([
                'tahun' => $session['periodValue'],
                'unit_id' => substr($id,0,11),
            ])->count();

            if($status=="0") {
                $model = new Status($params);
                $model->modul_3 = substr($id,-1);
                $model->tahun = $session['periodValue'];
                $model->unit_id = substr($id,0,11);
                $model->save();
            }else{
                $status = Status::find()->where([
                    'tahun' => $session['periodValue'],
                    'unit_id' => substr($id,0,11),
                ])->one();

                $model = Status::findOne($status['id']);

                if($model->modul_3 === substr($id,-1)){
                    $model->modul_3 = null;
                    $model->tahun = $session['periodValue'];
                    $model->unit_id = substr($id,0,11);
                    $model->save();
                }else{
                    $model->modul_3 = substr($id,-1);
                    $model->tahun = $session['periodValue'];
                    $model->unit_id = substr($id,0,11);
                    $model->save();
                }
            }
        }

        if(substr($id,-1)==='L'){
            $status = Status::find()->where([
                'tahun' => $session['periodValue'],
                'unit_id' => substr($id,0,11),
            ])->count();

            if($status=="0") {
                $model = new Status($params);
                $model->modul_4 = substr($id,-1);
                $model->tahun = $session['periodValue'];
                $model->unit_id = substr($id,0,11);
                $model->save();
            }else{
                $status = Status::find()->where([
                    'tahun' => $session['periodValue'],
                    'unit_id' => substr($id,0,11),
                ])->one();

                $model = Status::findOne($status['id']);

                if($model->modul_4 === substr($id,-1)){
                    $model->modul_4 = null;
                    $model->tahun = $session['periodValue'];
                    $model->unit_id = substr($id,0,11);
                    $model->save();
                }else{
                    $model->modul_4 = substr($id,-1);
                    $model->tahun = $session['periodValue'];
                    $model->unit_id = substr($id,0,11);
                    $model->save();
                }
            }
        }

        return $this->redirect(array('list', 'period'=>$session['periodValue']));    
    }

    public function actionLockreal($id,$tw)
    {
        $session = Yii::$app->session;

        if(substr($id,-1)==='L'){
            $status = Status::find()->where([
                'tahun' => $session['periodValue'],
                'unit_id' => substr($id,0,11),
            ])->count();

            if($status=="0") {
                $model = new Status($params);
                $model->modul_4 = substr($id,-1);
                $model->tahun = $session['periodValue'];
                $model->unit_id = substr($id,0,11);
                if($tw == '1'){$model->tw_1=$tw;}
                if($tw == '2'){$model->tw_2=$tw;}
                if($tw == '3'){$model->tw_3=$tw;}
                if($tw == '4'){$model->tw_4=$tw;}
                $model->save();
            }else{
                $status = Status::find()->where([
                    'tahun' => $session['periodValue'],
                    'unit_id' => substr($id,0,11),
                ])->one();

                $model = Status::findOne($status['id']);

                if($model->modul_4 === substr($id,-1)){
                    $model->modul_4 = null;
                    $model->tahun = $session['periodValue'];
                    $model->unit_id = substr($id,0,11);
                    if($tw == '1'){$model->tw_1=null;}
                    if($tw == '2'){$model->tw_2=null;}
                    if($tw == '3'){$model->tw_3=null;}
                    if($tw == '4'){$model->tw_4=null;}
                    $model->save();
                }else{
                    $model->modul_4 = substr($id,-1);
                    $model->tahun = $session['periodValue'];
                    $model->unit_id = substr($id,0,11);
                    if($tw == '1'){$model->tw_1=$tw;}
                    if($tw == '2'){$model->tw_2=$tw;}
                    if($tw == '3'){$model->tw_3=$tw;}
                    if($tw == '4'){$model->tw_4=$tw;}
                    $model->save();
                }
            }
        }

        return $this->redirect(array('list', 'period'=>$session['periodValue']));    
    }

    public function actionSelect($period)
    {
        // $session = Yii::$app->session;
        // $session['periodValue'] = $period;
        // $query = 'SELECT "POA", sum(IFNULL(e.jumlah,0)) prosentase 
        // FROM activity_detail e
        // LEFT JOIN activity_data a ON a.id=e.activity_data_id
        // LEFT JOIN activity v ON v.id=a.activity_id
        // LEFT JOIN service s ON s.id=v.service_id
        // LEFT JOIN period p ON p.id=a.period_id
        // LEFT JOIN unit u ON u.id=p.unit_id
        // WHERE p.tahun='.$period.' AND p.unit_id="'.Yii::$app->user->identity->unit_id.'"
        // GROUP BY p.unit_id, p.pagu
        // UNION 
        // SELECT "Sisa Pagu", CASE WHEN IFNULL(p.pagu,0)=0 THEN 0
        // ELSE IFNULL(p.pagu,0)-sum(IFNULL(e.jumlah,0))
        // END AS prosentase
        // FROM activity_detail e LEFT JOIN activity_data a ON a.id=e.activity_data_id 
        // LEFT JOIN activity v ON v.id=a.activity_id 
        // LEFT JOIN service s ON s.id=v.service_id 
        // LEFT JOIN period p ON p.id=a.period_id 
        // LEFT JOIN unit u ON u.id=p.unit_id WHERE p.tahun='.$period.' AND p.unit_id="'.Yii::$app->user->identity->unit_id.'" 
        // GROUP BY p.unit_id, p.pagu';

        // // return $query;

        // $dataProvider = new SqlDataProvider([
        //     'sql' => $query,
        //     'pagination' => false
        // ]);

        // $model = $dataProvider->getModels();

        // return $this->render('select', [
        //     'data' => $model,
        //     'dataProvider' => $dataProvider,
        // ]);
    }

    public function actionLevel($lvl)
    {
        $session = Yii::$app->session;
        $session['lvl'] = $lvl;

        return $this->redirect(['period/list', 'period' => date('Y')]);
    }

    public function actionList($period)
    {
        $count = 0;
        $session = Yii::$app->session;
        $session['periodValue'] = $period;

        if($session['lvl'] == 'dns'){
            $data = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as char),0),1,5) prosentase,
            CASE 
            WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) < 33.33 THEN "progress-bar-success"
            WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) >= 33.33 AND cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) <=66.66 THEN "progress-bar-warning"
            ELSE "progress-bar-danger"
            END AS bar_color,
            CASE WHEN t.modul_1 = "P" THEN "Buka Kunci" ELSE "Kunci" END AS status_poa,
            CASE WHEN t.modul_1 = "P" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-check" END AS status_poa_icon,
            
            CASE WHEN t.modul_2 = "G" THEN "Buka Kunci" ELSE "Kunci" END AS status_geser,
            CASE WHEN t.modul_2 = "G" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-transfer" END AS status_geser_icon,
            
            CASE WHEN t.modul_3 = "R" THEN "Buka Kunci" ELSE "Kunci" END AS status_rubah,
            CASE WHEN t.modul_3 = "R" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-pencil" END AS status_rubah_icon,
            
            CASE WHEN t.modul_4 = "L" THEN "Buka Kunci" ELSE "Kunci" END AS status_real,
            CASE WHEN t.modul_4 = "L" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-stats" END AS status_real_icon
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN unit u ON u.id=p.unit_id
            LEFT JOIN status t ON t.unit_id=p.unit_id AND t.tahun=:periode
            WHERE p.tahun=:periode
            GROUP BY p.unit_id, p.pagu
            ORDER BY u.puskesmas')
            ->bindValue(':periode', $period) 
            ->queryAll();

            $query = 'SELECT u.id unit_id, u.puskesmas, IFNULL(aw.pagu,0) pagu, IFNULL(aw.jumlah,0) jumlah, IFNULL(aw.prosentase,0) prosentase, aw.bar_color, aw.status_bar, 
            IFNULL(ub.pagu_ubah,0) pagu_ubah, IFNULL(ub.jumlah_ubah,0) jumlah_ubah, IFNULL(ub.prosentase_ubah,0) prosentase_ubah, ub.bar_color_ubah, ub.status_bar_ubah,
            CASE WHEN t.modul_1 = "P" THEN "Buka Kunci" ELSE "Kunci" END AS status_poa,
            CASE WHEN t.modul_1 = "P" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-check" END AS status_poa_icon,
            
            CASE WHEN t.modul_2 = "G" THEN "Buka Kunci" ELSE "Kunci" END AS status_geser,
            CASE WHEN t.modul_2 = "G" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-transfer" END AS status_geser_icon,
            
            CASE WHEN t.modul_3 = "R" THEN "Buka Kunci" ELSE "Kunci" END AS status_rubah,
            CASE WHEN t.modul_3 = "R" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-pencil" END AS status_rubah_icon,
            
            CASE WHEN t.modul_4 = "L" THEN "Buka Kunci" ELSE "Kunci" END AS status_real,
            CASE WHEN t.modul_4 = "L" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-stats" END AS status_real_icon
            FROM unit u
            LEFT JOIN
            (
                SELECT p.unit_id, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as char),0),1,5) prosentase,
                CASE 
                    WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) < 33.33 THEN "progress-bar-success"
                    WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) >= 33.33 AND 
                    cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) <=66.66 THEN "progress-bar-warning"
                ELSE "progress-bar-danger"
                END AS bar_color,
                CASE 
                WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as decimal(10,2)) < 33.33 THEN "active progress-striped"
                WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as decimal(10,2)) >= 33.33 AND 
                cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as decimal(10,2)) <=99.99 THEN "active progress-striped"
                ELSE "bar"
                END AS status_bar
                FROM dept_sub_activity_detail e
                LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
                LEFT JOIN dept_period p ON p.id=a.dept_period_id
                WHERE p.tahun='.$period.'
                GROUP BY p.unit_id
            ) aw ON aw.unit_id=u.id
            LEFT JOIN
            (
                SELECT p.unit_id, IFNULL(p.pagu_ubah,0) pagu_ubah, sum(IFNULL(e.jumlah,0)) jumlah_ubah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as char),0),1,5) prosentase_ubah,
                CASE 
                    WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as decimal(10,2)) < 33.33 THEN "progress-bar-success"
                    WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as decimal(10,2)) >= 33.33 AND 
                    cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as decimal(10,2)) <=66.66 THEN "progress-bar-warning"
                ELSE "progress-bar-danger"
                END AS bar_color_ubah,
                CASE 
                WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as decimal(10,2)) < 33.33 THEN "active progress-striped"
                WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as decimal(10,2)) >= 33.33 AND 
                cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as decimal(10,2)) <=99.99 THEN "active progress-striped"
                ELSE "bar"
                END AS status_bar_ubah
                FROM activity_detail_ubah e
                LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.tahun='.$period.'
                GROUP BY p.unit_id
            ) ub ON ub.unit_id=u.id
            LEFT JOIN `status` t ON t.unit_id=u.id AND t.tahun='.$period.'
            WHERE u.id != "DINKES" AND mid(u.id,1,2) != "P3"
            ORDER BY u.puskesmas';
        }else{
            $data = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as char),0),1,5) prosentase,
            CASE 
                WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) < 33.33 THEN "progress-bar-success"
                WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) >= 33.33 AND cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) <=66.66 THEN "progress-bar-warning"
                ELSE "progress-bar-danger"
            END AS bar_color

            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN unit u ON u.id=p.unit_id
            LEFT JOIN status t ON t.unit_id=p.unit_id AND t.tahun=:periode
            WHERE p.tahun=:periode
            GROUP BY p.unit_id, p.pagu
            ORDER BY u.puskesmas')
            ->bindValue(':periode', $period) 
            ->queryAll();

            $query = 'SELECT u.id unit_id, u.puskesmas, IFNULL(aw.pagu,0) pagu, IFNULL(aw.jumlah,0) jumlah, IFNULL(aw.prosentase,0) prosentase, aw.bar_color, aw.status_bar, 
            IFNULL(ub.pagu_ubah,0) pagu_ubah, IFNULL(ub.jumlah_ubah,0) jumlah_ubah, IFNULL(ub.prosentase_ubah,0) prosentase_ubah, ub.bar_color_ubah, ub.status_bar_ubah,
            CASE WHEN t.modul_1 = "P" THEN "Buka Kunci" ELSE "Kunci" END AS status_poa,
            CASE WHEN t.modul_1 = "P" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-check" END AS status_poa_icon,
            
            CASE WHEN t.modul_2 = "G" THEN "Buka Kunci" ELSE "Kunci" END AS status_geser,
            CASE WHEN t.modul_2 = "G" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-transfer" END AS status_geser_icon,
            
            CASE WHEN t.modul_3 = "R" THEN "Buka Kunci" ELSE "Kunci" END AS status_rubah,
            CASE WHEN t.modul_3 = "R" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-pencil" END AS status_rubah_icon,
            
            CASE WHEN t.modul_4 = "L" THEN "Buka Kunci" ELSE "Kunci" END AS status_real,
            CASE WHEN t.modul_4 = "L" THEN "glyphicon glyphicon-stats" ELSE "glyphicon glyphicon-stats" END AS status_real_icon,

            CASE WHEN t.tw_1 = "1" THEN "Buka" ELSE "Kunci" END AS status_real_tw1,
            CASE WHEN t.tw_1 = "1" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-stats" END AS status_real_icon_tw1,
            CASE WHEN t.tw_1 = "1" THEN "btn btn-xs btn-danger custom_button" ELSE "btn btn-xs btn-success custom_button" END AS color_real_icon_tw1,
            CASE WHEN t.tw_1 = "1" THEN "Buka" ELSE "Kunci" END AS label_real_icon_tw1,

            CASE WHEN t.tw_2 = "2" THEN "Buka" ELSE "Kunci" END AS status_real_tw2,
            CASE WHEN t.tw_2 = "2" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-stats" END AS status_real_icon_tw2,
            CASE WHEN t.tw_2 = "2" THEN "btn btn-xs btn-danger custom_button" ELSE "btn btn-xs btn-success custom_button" END AS color_real_icon_tw2,
            CASE WHEN t.tw_2 = "2" THEN "Buka" ELSE "Kunci" END AS label_real_icon_tw2,

            CASE WHEN t.tw_3 = "3" THEN "Buka" ELSE "Kunci" END AS status_real_tw3,
            CASE WHEN t.tw_3 = "3" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-stats" END AS status_real_icon_tw3,
            CASE WHEN t.tw_3 = "3" THEN "btn btn-xs btn-danger custom_button" ELSE "btn btn-xs btn-success custom_button" END AS color_real_icon_tw3,
            CASE WHEN t.tw_3 = "3" THEN "Buka" ELSE "Kunci" END AS label_real_icon_tw3,

            CASE WHEN t.tw_4 = "4" THEN "Buka" ELSE "Kunci" END AS status_real_tw4,
            CASE WHEN t.tw_4 = "4" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-stats" END AS status_real_icon_tw4,
            CASE WHEN t.tw_4 = "4" THEN "btn btn-xs btn-danger custom_button" ELSE "btn btn-xs btn-success custom_button" END AS color_real_icon_tw4,
            CASE WHEN t.tw_4 = "4" THEN "Buka" ELSE "Kunci" END AS label_real_icon_tw4,

            rl.jml realisasi,
            IFNULL(ROUND((rl.jml)/sum(IFNULL(aw.jumlah,0))*100,2),0) persen_real,
            CASE 
                WHEN cast(sum(IFNULL(rl.jml,0))/IFNULL(aw.jumlah,0)*100 as decimal(10,2)) < 33.33 THEN "progress-bar-danger"
                WHEN cast(sum(IFNULL(rl.jml,0))/IFNULL(aw.jumlah,0)*100 as decimal(10,2)) >= 33.33 AND cast(sum(IFNULL(rl.jml,0))/IFNULL(aw.jumlah,0)*100 as decimal(10,2)) <=66.66 THEN "progress-bar-warning"
                ELSE "progress-bar-success"
            END AS bar_color_real,
            CASE 
                WHEN cast(sum(IFNULL(rl.jml,0))/IFNULL(aw.jumlah,0)*100 as decimal(10,2)) < 33.33 THEN "active progress-striped"
                WHEN cast(sum(IFNULL(rl.jml,0))/IFNULL(aw.jumlah,0)*100 as decimal(10,2)) >= 33.33 AND 
                cast(sum(IFNULL(rl.jml,0))/IFNULL(aw.jumlah,0)*100 as decimal(10,2)) <=99.99 THEN "active progress-striped"
                ELSE "bar"
            END AS status_bar_real

            FROM unit u
            LEFT JOIN
            (
                SELECT p.unit_id, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as char),0),1,5) prosentase,
                CASE 
                    WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) < 33.33 THEN "progress-bar-success"
                    WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) >= 33.33 AND 
                    cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) <=66.66 THEN "progress-bar-warning"
                ELSE "progress-bar-danger"
                END AS bar_color,
                CASE 
                    WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as decimal(10,2)) < 33.33 THEN "active progress-striped"
                    WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as decimal(10,2)) >= 33.33 AND 
                    cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as decimal(10,2)) <=99.99 THEN "active progress-striped"
                    ELSE "bar"
                END AS status_bar
                FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.tahun='.$period.'
                GROUP BY p.unit_id
            ) aw ON aw.unit_id=u.id
            LEFT JOIN
            (
                SELECT p.unit_id, IFNULL(p.pagu_ubah,0) pagu_ubah, sum(IFNULL(e.jumlah,0)) jumlah_ubah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as char),0),1,5) prosentase_ubah,
                CASE 
                    WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as decimal(10,2)) < 33.33 THEN "progress-bar-success"
                    WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as decimal(10,2)) >= 33.33 AND 
                    cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as decimal(10,2)) <=66.66 THEN "progress-bar-warning"
                ELSE "progress-bar-danger"
                END AS bar_color_ubah,
                CASE 
                WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as decimal(10,2)) < 33.33 THEN "active progress-striped"
                WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as decimal(10,2)) >= 33.33 AND 
                cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu_ubah,0)*100 as decimal(10,2)) <=99.99 THEN "active progress-striped"
                ELSE "bar"
                END AS status_bar_ubah
                FROM activity_detail_ubah e
                LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.tahun='.$period.'
                GROUP BY p.unit_id
            ) ub ON ub.unit_id=u.id
            LEFT JOIN `status` t ON t.unit_id=u.id AND t.tahun='.$period.'
            LEFT JOIN
            (
                SELECT p.unit_id, u.puskesmas, z.activity_detail_id, SUM(IFNULL(z.jumlah,0)) jml FROM realization z 
                LEFT JOIN activity_detail d ON d.id=z.activity_detail_id
                LEFT JOIN activity_data a ON a.id=d.activity_data_id
                LEFT JOIN period p ON p.id=a.period_id
                LEFT JOIN unit u ON u.id=p.unit_id
                WHERE p.tahun='.$period.'
                GROUP BY p.unit_id
            ) rl ON rl.unit_id=u.id
            WHERE u.id != "DINKES" AND mid(u.id,1,2) = "P3"
            group BY u.id
            ORDER BY u.puskesmas';
        }

        foreach($data as $row){
            $count++;
        }

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 30,
                ],
            ]);

        $model = $dataProvider->getModels();

        return $this->render('list', [
            'data' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionMonth()
    {
        $model = new Period();
        $model->setScenario('pilihbulan');
        $POST_VARIABLE=Yii::$app->request->post('Period');
        $request = $POST_VARIABLE['tahun'];

        $period = Period::find()
        ->where(['unit_id' => Yii::$app->user->identity->unit_id, 'tahun' => $request])
        ->one();

        $session = Yii::$app->session;
        $session['periodValue'] = $period->tahun;

        // if ($model->load(Yii::$app->request->post()) && $model->save()) {
        //     return $this->redirect(['program/view', 'id' => $session['programId']]);
        // }

        return $this->render('option', [
            'model' => $model,
            'period' => $period->tahun 
        ]);
    }

    public function actionExportbln()
    {
        $session = Yii::$app->session;
        Yii::$app->db->createCommand()->truncateTable('export_perfomance_bln')->execute();
        $data = Yii::$app->db->createCommand('SELECT g.nama_program, i.nama_indikator indikator, ifnull(agg.jumlah,0) anggaran, 
        ifnull(k_lalu.jumlah,0) kinerja_lalu, 
        ifnull(kn_lalu.jumlah,0) keuangan_nilai_lalu, 
        ifnull(ROUND(ifnull(kn_lalu.jumlah,0)/ifnull(agg.jumlah,0)*100,2),0) keuangan_persen_lalu,
        
        ifnull(k_ini.jumlah,0) kinerja_ini, 
        CASE
            WHEN ifnull(kn_ini.jumlah,0)>0 THEN
                ifnull(kn_lalu.jumlah,0)+ifnull(kn_ini.jumlah,0) 
                -- ifnull(kn_ini.jumlah,0) 
            ELSE 0
        END keuangan_nilai_ini, 
        CASE 
            WHEN ifnull(kn_ini.jumlah,0)>0 THEN 
                ifnull(ROUND((ifnull(kn_lalu.jumlah,0)+ifnull(kn_ini.jumlah,0))/ifnull(agg.jumlah,0)*100,2),0) 
                -- ifnull(ROUND((ifnull(kn_ini.jumlah,0))/ifnull(agg.jumlah,0)*100,2),0) 
            ELSE 0
        END keuangan_persen_ini,

        CASE
            WHEN ifnull(k_ini.jumlah,0)>0 THEN ifnull(k_ini.jumlah,0)
            WHEN ifnull(k_lalu.jumlah,0)>0 THEN ifnull(k_lalu.jumlah,0)
            ELSE 0
        END total_kinerja,
        ifnull(kn_lalu.jumlah,0)+ifnull(kn_ini.jumlah,0) total_keuangan_nilai,
        ifnull(ROUND((ifnull(kn_lalu.jumlah,0)+ifnull(kn_ini.jumlah,0))/ifnull(agg.jumlah,0)*100,2),0) total_keuangan_persen

        FROM program g
        LEFT JOIN indicator i ON i.program_id=g.id
        LEFT JOIN
            (
                SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id=:unit_id AND p.tahun=:periode
                GROUP BY s.program_id
            ) agg ON agg.program_id=g.id
        LEFT JOIN
            (
                SELECT i.program_id, indicator_id, id.kinerja jumlah FROM indicator_data id
                LEFT JOIN indicator i ON i.id=id.indicator_id
                LEFT JOIN period p ON p.id=id.period_id
                WHERE p.unit_id=:unit_id AND bulan >= 1 AND bulan < :bulan
            ) k_lalu ON k_lalu.program_id=g.id AND k_lalu.indicator_id=i.id
        LEFT JOIN
            (
                SELECT i.program_id, indicator_id, id.keuangan jumlah FROM indicator_data id
                LEFT JOIN indicator i ON i.id=id.indicator_id
                LEFT JOIN period p ON p.id=id.period_id
                WHERE p.unit_id=:unit_id AND bulan >= 1 AND bulan < :bulan
            ) kn_lalu ON kn_lalu.program_id=g.id AND kn_lalu.indicator_id=i.id
        LEFT JOIN
            (
                SELECT i.program_id, indicator_id, id.kinerja jumlah FROM indicator_data id
                LEFT JOIN indicator i ON i.id=id.indicator_id
                LEFT JOIN period p ON p.id=id.period_id
                WHERE p.unit_id=:unit_id AND bulan = :bulan
            ) k_ini ON k_ini.program_id=g.id AND k_ini.indicator_id=i.id
        LEFT JOIN
            (
                SELECT i.program_id, indicator_id, id.keuangan jumlah FROM indicator_data id
                LEFT JOIN indicator i ON i.id=id.indicator_id
                LEFT JOIN period p ON p.id=id.period_id
                WHERE p.unit_id=:unit_id AND bulan = :bulan
            ) kn_ini ON kn_ini.program_id=g.id AND kn_ini.indicator_id=i.id
        
        WHERE g.tahun=:periode
        GROUP BY g.id, i.id
        ORDER BY g.id, i.id')
        ->bindValue(':unit_id', Yii::$app->user->identity->unit_id)
        ->bindValue(':periode', $session['periodValue'])
        ->bindValue(':bulan', $_COOKIE['bulan'])
        ->queryAll();

        $program = '';
        foreach ($data as $row) {
            $exportperfomancebln =  new Exportperfomancebln();
            if ($row['nama_program'] !== $program){
                $exportperfomancebln->nama_program=$row['nama_program']; $program = $row['nama_program'];
            }
            $exportperfomancebln->indikator=$row['indikator'];
            // $exportperfomance->target=$row['target'];   
            $exportperfomancebln->anggaran=$row['anggaran'];
            $exportperfomancebln->kinerja_lalu=$row['kinerja_lalu'];
            $exportperfomancebln->keuangan_nilai_lalu=$row['keuangan_nilai_lalu'];
            $exportperfomancebln->keuangan_persen_lalu=$row['keuangan_persen_lalu'];
            $exportperfomancebln->kinerja_ini=$row['kinerja_ini'];
            $exportperfomancebln->keuangan_nilai_ini=$row['keuangan_nilai_ini'];
            $exportperfomancebln->keuangan_persen_ini=$row['keuangan_persen_ini'];
            $exportperfomancebln->total_kinerja=$row['total_kinerja'];
            $exportperfomancebln->total_keuangan_nilai=$row['total_keuangan_nilai'];
            $exportperfomancebln->total_keuangan_persen=$row['total_keuangan_persen'];
            
            $exportperfomancebln->save();
        }

        $dataExcel = Yii::$app->db->createCommand('SELECT * FROM export_perfomance_bln WHERE (anggaran <> 0 OR kinerja_lalu <> 0 OR keuangan_nilai_lalu <> 0 OR 
        keuangan_persen_lalu <> 0 OR kinerja_ini <> 0 OR keuangan_nilai_ini <> 0 OR 
        keuangan_persen_ini)')
        ->queryAll();

        $inputFileName = Yii::getAlias('@app/views/exportaccount').'/_export_kinerja_bln.xlsx';

        /** Load $inputFileName to a Spreadsheet Object  **/
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A'.'1', 'LAPORAN CAPAIAN KINERJA DAN KEUANGAN BOK');
        $activeSheet->setCellValue('A'.'2', 'UPTD ' .strtoupper(Yii::$app->user->identity->alias));
        $activeSheet->setCellValue('A'.'3', 'BULAN ' .$_COOKIE['bulannya']. ' TAHUN ' .$session['periodValue']);
        $baseRow=8;
        $firstData=8;
        $baseRowProgram=$baseRow;
        foreach($dataExcel as $rowExcel) {
            if($rowExcel['nama_program']!==null){
                $activeSheet->setCellValue('A'.$baseRow, $baseRowProgram-7)
                ->setCellValue('E'.$baseRow, $rowExcel['anggaran']);
                $baseRowProgram = $baseRowProgram+1;
            }

            $activeSheet
                        // ->setCellValue('A'.$baseRow, $baseRow-7)
                        ->setCellValue('B'.$baseRow, $rowExcel['nama_program'])
                        ->setCellValue('C'.$baseRow, $rowExcel['indikator'])
                        ->setCellValue('D'.$baseRow, $rowExcel['target'])
                        // ->setCellValue('E'.$baseRow, $rowExcel['anggaran'])
                        ->setCellValue('F'.$baseRow, $rowExcel['kinerja_lalu'])
                        ->setCellValue('G'.$baseRow, $rowExcel['keuangan_nilai_lalu'])
                        ->setCellValue('H'.$baseRow, $rowExcel['keuangan_persen_lalu'])
                        ->setCellValue('I'.$baseRow, $rowExcel['kinerja_ini'])
                        ->setCellValue('J'.$baseRow, $rowExcel['keuangan_nilai_ini'])
                        ->setCellValue('K'.$baseRow, $rowExcel['keuangan_persen_ini'])
                        ->setCellValue('L'.$baseRow, $rowExcel['total_kinerja'])
                        ->setCellValue('M'.$baseRow, $rowExcel['total_keuangan_nilai'])
                        ->setCellValue('N'.$baseRow, $rowExcel['total_keuangan_persen'])
                        ;
            $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':N' .$baseRow)->applyFromArray($styleArray);

        $baseRow++;
        }
        
        if(!empty($dataExcel)){
            $lastData=$baseRow-1;
            $activeSheet->setCellValue('B'.$baseRow, 'JUMLAH');
            $spreadsheet->getActiveSheet()->mergeCells('B'.$baseRow. ':D' .$baseRow);
            $activeSheet->getStyle('B'.$baseRow. ':D' .$baseRow)->getAlignment()->setHorizontal('center'); 
            $activeSheet->getStyle('B'.$baseRow. ':D' .$baseRow)->getAlignment()->setWrapText(true);
            $activeSheet->setCellValue('E'.$baseRow, '=SUM(E' .$firstData. ':E' .$lastData. ')');
            $activeSheet->setCellValue('G'.$baseRow, '=SUM(G' .$firstData. ':G' .$lastData. ')');
            $activeSheet->setCellValue('J'.$baseRow, '=SUM(J' .$firstData. ':J' .$lastData. ')');
            $activeSheet->setCellValue('M'.$baseRow, '=SUM(M' .$firstData. ':M' .$lastData. ')');
            $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':N' .$baseRow)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':N' .$baseRow)->getFont()->setBold(true);
        }

        $unit = Unit::findOne(Yii::$app->user->identity->unit_id);
        $baseRow=$baseRow+3;

        $activeSheet->setCellValue('K'.$baseRow, $unit->puskesmas. ',                        ' .$session['periodValue']); 
        $spreadsheet->getActiveSheet()->getStyle('K'.$baseRow. ':M' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+1; 

        // $spreadsheet->getActiveSheet()->getStyle('Q:S')->getAlignment()->setHorizontal('center');
        $activeSheet->setCellValue('K'.$baseRow, 'Mengetahui,'); 
        $spreadsheet->getActiveSheet()->getStyle('K'.$baseRow. ':M' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+1;
        $activeSheet->setCellValue('K'.$baseRow, 'Kepala UPTD ' .Yii::$app->user->identity->alias); 
        $spreadsheet->getActiveSheet()->getStyle('K'.$baseRow. ':M' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+4;
        $activeSheet->setCellValue('K'.$baseRow, $unit->kepala); 
        $spreadsheet->getActiveSheet()->getStyle('K'.$baseRow. ':M' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+1;
        $activeSheet->setCellValue('K'.$baseRow, 'NIP. ' .$unit->nip_kepala);
        $spreadsheet->getActiveSheet()->getStyle('K'.$baseRow. ':M' .$baseRow)->getFont()->setBold( true );

        // $spreadsheet->getSecurity()->setLockWindows(true);
        // $spreadsheet->getSecurity()->setLockStructure(true);
        // $spreadsheet->getSecurity()->setWorkbookPassword("silverblack");

        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setPassword('silverblack');
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setSheet(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setSort(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setInsertRows(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setFormatCells(true);

        // $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_kinerja_bln_'.$_COOKIE['bulannya']. '_' .$session['periodValue']. '_'.Yii::$app->user->identity->username.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');

        // $mpdf = new \Mpdf\Mpdf();
        // $mpdf->WriteHTML('<h1>Hello world!</h1>');
        // $mpdf->Output();

        // $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Mpdf');
        // $writer->writeAllSheets();
        // header('Content-Type: application/pdf');
        // header('Content-Disposition: attachment;filename="_export.pdf"');
        // header('Cache-Control: max-age=0');
        // $writer->save('php://output');

        exit;
    }

    public function actionExporttw()
    {
        $POST_VARIABLE=Yii::$app->request->post('Period');
        $period = $POST_VARIABLE['tahun'];

        // $session = Yii::$app->session;
        // $period = $session['periodValue'];

        // return $period;

        // Yii::$app->db->createCommand()->truncateTable('export_perfomance')->execute();
        Yii::$app->db->createCommand('DELETE FROM export_perfomance WHERE username=:username AND period=:periodValue ')
        ->bindValue(':username', Yii::$app->user->identity->username)
        ->bindValue(':periodValue', $period)
        ->execute();

        $pagu = Period::find()->where(['unit_id' => Yii::$app->user->identity->unit_id, 'tahun' => $period])->all();
        foreach($pagu as $pagu);
        // return $pagu->pagu_ubah;

        if($pagu->pagu_ubah > 0){
            $data = Yii::$app->db->createCommand('SELECT g.nama_program, i.nama_indikator indikator, 

            CASE
                WHEN ifnull(agg_ubah.jumlah,0) > 0 THEN
                    ifnull(agg_ubah.jumlah,0)
                ELSE
                    ifnull(agg_ubah.jumlah,0)
            END anggaran,

            ifnull(k_1.jumlah,0) kinerja_1, 
            ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0) keuangan_nilai_1, 
            ifnull(ROUND((ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0))/ifnull(agg_ubah.jumlah,0)*100,2),0) keuangan_persen_1,
            
            ifnull(k_2.jumlah,0) kinerja_2, 
            CASE
                WHEN ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)>0 THEN
                    ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)
                ELSE ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)
            END keuangan_nilai_2, 
            CASE 
                WHEN ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)>0 THEN 
                    ifnull(ROUND((ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0))/ifnull(agg_ubah.jumlah,0)*100,2),0) 
                ELSE ifnull(ROUND((ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0))/ifnull(agg_ubah.jumlah,0)*100,2),0)
            END keuangan_persen_2,
            
            ifnull(k_3.jumlah,0) kinerja_3, 
            CASE
                WHEN ifnull(kn_3.jumlah,0)+ifnull(kn_3_ubah.jumlah,0)>0 THEN
                    ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)+ifnull(kn_3.jumlah,0)+ifnull(kn_3_ubah.jumlah,0)
                ELSE ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)
            END keuangan_nilai_3, 
            CASE 
                WHEN ifnull(kn_3.jumlah,0)+ifnull(kn_3_ubah.jumlah,0)>0 THEN 
                    ifnull(ROUND((ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)+ifnull(kn_3.jumlah,0)+ifnull(kn_3_ubah.jumlah,0))/ifnull(agg_ubah.jumlah,0)*100,2),0) 
                ELSE ifnull(ROUND((ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0))/ifnull(agg_ubah.jumlah,0)*100,2),0)
            END keuangan_persen_3,
            
            ifnull(k_4.jumlah,0) kinerja_4, 
            CASE
                WHEN ifnull(kn_4.jumlah,0)+ifnull(kn_4_ubah.jumlah,0)>0 THEN
                    ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)+ifnull(kn_3.jumlah,0)+ifnull(kn_3_ubah.jumlah,0)+ifnull(kn_4.jumlah,0)+ifnull(kn_4_ubah.jumlah,0)
                ELSE ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)+ifnull(kn_3.jumlah,0)+ifnull(kn_3_ubah.jumlah,0)
            END keuangan_nilai_4, 
            CASE
                WHEN ifnull(kn_4.jumlah,0)+ifnull(kn_4_ubah.jumlah,0)>0 THEN
                    ifnull(ROUND((ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)+ifnull(kn_3.jumlah,0)+ifnull(kn_3_ubah.jumlah,0)+ifnull(kn_4.jumlah,0)+ifnull(kn_4_ubah.jumlah,0))/ifnull(agg_ubah.jumlah,0)*100,2),0) 
                ELSE ifnull(ROUND((ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)+ifnull(kn_3.jumlah,0)+ifnull(kn_3_ubah.jumlah,0))/ifnull(agg_ubah.jumlah,0)*100,2),0) 
            END keuangan_persen_4,

            CASE
                WHEN ifnull(k_4.jumlah,0)>0 THEN ifnull(k_4.jumlah,0)
                WHEN ifnull(k_3.jumlah,0)>0 THEN ifnull(k_3.jumlah,0)
                WHEN ifnull(k_2.jumlah,0)>0 THEN ifnull(k_2.jumlah,0)
                WHEN ifnull(k_1.jumlah,0)>0 THEN ifnull(k_1.jumlah,0)
                ELSE 0
            END total_kinerja,
            ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)+ifnull(kn_3.jumlah,0)+ifnull(kn_3_ubah.jumlah,0)+ifnull(kn_4.jumlah,0)+ifnull(kn_4_ubah.jumlah,0) total_keuangan_nilai,
            ifnull(ROUND((ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)+ifnull(kn_3.jumlah,0)+ifnull(kn_3_ubah.jumlah,0)+ifnull(kn_4.jumlah,0)+ifnull(kn_4_ubah.jumlah,0))/ifnull(agg_ubah.jumlah,0)*100,2),0) total_keuangan_persen

            FROM program g
            LEFT JOIN indicator i ON i.program_id=g.id
            LEFT JOIN
                (
                    SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail e
                    LEFT JOIN activity_data a ON a.id=e.activity_data_id
                    LEFT JOIN activity v ON v.id=a.activity_id
                    LEFT JOIN service s ON s.id=v.service_id
                    LEFT JOIN period p ON p.id=a.period_id
                    WHERE p.unit_id=:unit_id AND p.tahun=2020
                    GROUP BY s.program_id
                ) agg ON agg.program_id=g.id
            LEFT JOIN
                (
                    SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
                    LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
                    LEFT JOIN activity v ON v.id=a.activity_id
                    LEFT JOIN service s ON s.id=v.service_id
                    LEFT JOIN period p ON p.id=a.period_id
                    WHERE p.unit_id=:unit_id AND p.tahun=2020
                    GROUP BY s.program_id
                ) agg_ubah ON agg_ubah.program_id=g.id
            LEFT JOIN
                (
                    SELECT i.program_id, indicator_id, max(id.kinerja) jumlah FROM indicator_data id
                    LEFT JOIN indicator i ON i.id=id.indicator_id
                    LEFT JOIN period p ON p.id=id.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 1 AND bulan <= 3
                ) k_1 ON k_1.program_id=g.id AND k_1.indicator_id=i.id
            LEFT JOIN
                (
                    SELECT s.program_id, e.activity_data_id, sum(id.realisasi_jumlah) jumlah FROM financial_realization id
                    LEFT JOIN activity_detail e ON e.id=id.activity_detail_id
                        LEFT JOIN activity_data i ON i.id=e.activity_data_id
                        LEFT JOIN activity v ON v.id=i.activity_id
                        LEFT JOIN service s ON s.id=v.service_id
                        LEFT JOIN period p ON p.id=i.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 1 AND bulan <= 3
                    GROUP BY s.program_id
                ) kn_1 ON kn_1.program_id=g.id
            LEFT JOIN
                (
                    SELECT s.program_id, e.activity_data_id, sum(id.realisasi_jumlah) jumlah FROM financial_realization id
                    LEFT JOIN activity_detail_ubah e ON e.id=id.activity_detail_id
                        LEFT JOIN activity_data_ubah i ON i.id=e.activity_data_id
                        LEFT JOIN activity v ON v.id=i.activity_id
                        LEFT JOIN service s ON s.id=v.service_id
                        LEFT JOIN period p ON p.id=i.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 1 AND bulan <= 3
                    GROUP BY s.program_id
                ) kn_1_ubah ON kn_1_ubah.program_id=g.id
            LEFT JOIN
                (
                    SELECT i.program_id, indicator_id, max(id.kinerja) jumlah FROM indicator_data id
                    LEFT JOIN indicator i ON i.id=id.indicator_id
                    LEFT JOIN period p ON p.id=id.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 4 AND bulan <= 6
                ) k_2 ON k_2.program_id=g.id AND k_2.indicator_id=i.id
            LEFT JOIN
                (
                    SELECT s.program_id, e.activity_data_id, sum(id.realisasi_jumlah) jumlah FROM financial_realization id
                    LEFT JOIN activity_detail_ubah e ON e.id=id.activity_detail_id
                        LEFT JOIN activity_data_ubah i ON i.id=e.activity_data_id
                        LEFT JOIN activity v ON v.id=i.activity_id
                        LEFT JOIN service s ON s.id=v.service_id
                        LEFT JOIN period p ON p.id=i.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 4 AND bulan <= 6
                    GROUP BY s.program_id
                ) kn_2_ubah ON kn_2_ubah.program_id=g.id
            LEFT JOIN
                (
                    SELECT s.program_id, e.activity_data_id, sum(id.realisasi_jumlah) jumlah FROM financial_realization id
                    LEFT JOIN activity_detail e ON e.id=id.activity_detail_id
                        LEFT JOIN activity_data i ON i.id=e.activity_data_id
                        LEFT JOIN activity v ON v.id=i.activity_id
                        LEFT JOIN service s ON s.id=v.service_id
                        LEFT JOIN period p ON p.id=i.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 4 AND bulan <= 6
                    GROUP BY s.program_id
                ) kn_2 ON kn_2.program_id=g.id
            LEFT JOIN
                (
                    SELECT i.program_id, indicator_id, max(id.kinerja) jumlah FROM indicator_data id
                    LEFT JOIN indicator i ON i.id=id.indicator_id
                    LEFT JOIN period p ON p.id=id.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 7 AND bulan <= 9
                ) k_3 ON k_3.program_id=g.id AND k_3.indicator_id=i.id
            LEFT JOIN
                (
                    SELECT s.program_id, e.activity_data_id, sum(id.realisasi_jumlah) jumlah FROM financial_realization id
                    LEFT JOIN activity_detail e ON e.id=id.activity_detail_id
                        LEFT JOIN activity_data i ON i.id=e.activity_data_id
                        LEFT JOIN activity v ON v.id=i.activity_id
                        LEFT JOIN service s ON s.id=v.service_id
                        LEFT JOIN period p ON p.id=i.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 7 AND bulan <= 9
                    GROUP BY s.program_id
                ) kn_3 ON kn_3.program_id=g.id
            LEFT JOIN
                (
                    SELECT s.program_id, e.activity_data_id, sum(id.realisasi_jumlah) jumlah FROM financial_realization id
                    LEFT JOIN activity_detail_ubah e ON e.id=id.activity_detail_id
                        LEFT JOIN activity_data_ubah i ON i.id=e.activity_data_id
                        LEFT JOIN activity v ON v.id=i.activity_id
                        LEFT JOIN service s ON s.id=v.service_id
                        LEFT JOIN period p ON p.id=i.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 7 AND bulan <= 9
                    GROUP BY s.program_id
                ) kn_3_ubah ON kn_3_ubah.program_id=g.id
            LEFT JOIN
                (
                    SELECT i.program_id, indicator_id, max(id.kinerja) jumlah FROM indicator_data id
                    LEFT JOIN indicator i ON i.id=id.indicator_id
                    LEFT JOIN period p ON p.id=id.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 10 AND bulan <= 12
                ) k_4 ON k_4.program_id=g.id AND k_4.indicator_id=i.id
            LEFT JOIN
                (
                    SELECT s.program_id, e.activity_data_id, sum(id.realisasi_jumlah) jumlah FROM financial_realization id
                    LEFT JOIN activity_detail e ON e.id=id.activity_detail_id
                        LEFT JOIN activity_data i ON i.id=e.activity_data_id
                        LEFT JOIN activity v ON v.id=i.activity_id
                        LEFT JOIN service s ON s.id=v.service_id
                        LEFT JOIN period p ON p.id=i.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 10 AND bulan <= 12
                    GROUP BY s.program_id
                ) kn_4 ON kn_4.program_id=g.id	
            LEFT JOIN
                (
                    SELECT s.program_id, e.activity_data_id, sum(id.realisasi_jumlah) jumlah FROM financial_realization id
                    LEFT JOIN activity_detail_ubah e ON e.id=id.activity_detail_id
                        LEFT JOIN activity_data_ubah i ON i.id=e.activity_data_id
                        LEFT JOIN activity v ON v.id=i.activity_id
                        LEFT JOIN service s ON s.id=v.service_id
                        LEFT JOIN period p ON p.id=i.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 10 AND bulan <= 12
                    GROUP BY s.program_id
                ) kn_4_ubah ON kn_4_ubah.program_id=g.id	
            LEFT JOIN period r ON r.tahun=g.tahun
            WHERE g.tahun=:periode
            GROUP BY g.id, i.id
            ORDER BY g.id, i.id')
            ->bindValue(':unit_id', Yii::$app->user->identity->unit_id)
            ->bindValue(':periode', $period)
            ->queryAll();
        }else{
            $data = Yii::$app->db->createCommand('SELECT g.nama_program, i.nama_indikator indikator, ifnull(agg.jumlah,0) anggaran, ifnull(agg_ubah.jumlah,0) anggaran_ubah, 

            ifnull(k_1.jumlah,0) kinerja_1, 
            ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0) keuangan_nilai_1, 
            ifnull(ROUND((ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0))/ifnull(agg.jumlah,0)*100,2),0) keuangan_persen_1,
            
            ifnull(k_2.jumlah,0) kinerja_2, 
            CASE
                WHEN ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)>0 THEN
                    ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)
                ELSE ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)
            END keuangan_nilai_2, 
            CASE 
                WHEN ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)>0 THEN 
                    ifnull(ROUND((ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0))/ifnull(agg.jumlah,0)*100,2),0) 
                ELSE ifnull(ROUND((ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0))/ifnull(agg.jumlah,0)*100,2),0)
            END keuangan_persen_2,
            
            ifnull(k_3.jumlah,0) kinerja_3, 
            CASE
                WHEN ifnull(kn_3.jumlah,0)+ifnull(kn_3_ubah.jumlah,0)>0 THEN
                    ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)+ifnull(kn_3.jumlah,0)+ifnull(kn_3_ubah.jumlah,0)
                ELSE ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)
            END keuangan_nilai_3, 
            CASE 
                WHEN ifnull(kn_3.jumlah,0)+ifnull(kn_3_ubah.jumlah,0)>0 THEN 
                    ifnull(ROUND((ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)+ifnull(kn_3.jumlah,0)+ifnull(kn_3_ubah.jumlah,0))/ifnull(agg.jumlah,0)*100,2),0) 
                ELSE ifnull(ROUND((ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0))/ifnull(agg.jumlah,0)*100,2),0)
            END keuangan_persen_3,
            
            ifnull(k_4.jumlah,0) kinerja_4, 
            CASE
                WHEN ifnull(kn_4.jumlah,0)+ifnull(kn_4_ubah.jumlah,0)>0 THEN
                    ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)+ifnull(kn_3.jumlah,0)+ifnull(kn_3_ubah.jumlah,0)+ifnull(kn_4.jumlah,0)+ifnull(kn_4_ubah.jumlah,0)
                ELSE ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)+ifnull(kn_3.jumlah,0)+ifnull(kn_3_ubah.jumlah,0)
            END keuangan_nilai_4, 
            CASE
                WHEN ifnull(kn_4.jumlah,0)>0 THEN
                    ifnull(ROUND((ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)+ifnull(kn_3.jumlah,0)+ifnull(kn_3_ubah.jumlah,0)+ifnull(kn_4.jumlah,0)+ifnull(kn_4_ubah.jumlah,0))/ifnull(agg.jumlah,0)*100,2),0) 
                ELSE ifnull(ROUND((ifnull(kn_1.jumlah,0)+ifnull(kn_1_ubah.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_2_ubah.jumlah,0)+ifnull(kn_3.jumlah,0)+ifnull(kn_3_ubah.jumlah,0))/ifnull(agg.jumlah,0)*100,2),0) 
            END keuangan_persen_4,

            CASE
                WHEN ifnull(k_4.jumlah,0)>0 THEN ifnull(k_4.jumlah,0)
                WHEN ifnull(k_3.jumlah,0)>0 THEN ifnull(k_3.jumlah,0)
                WHEN ifnull(k_2.jumlah,0)>0 THEN ifnull(k_2.jumlah,0)
                WHEN ifnull(k_1.jumlah,0)>0 THEN ifnull(k_1.jumlah,0)
                ELSE 0
            END total_kinerja,
            ifnull(kn_1.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_3.jumlah,0)+ifnull(kn_4.jumlah,0) total_keuangan_nilai,
            ifnull(ROUND((ifnull(kn_1.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_3.jumlah,0)+ifnull(kn_4.jumlah,0))/ifnull(agg.jumlah,0)*100,2),0) total_keuangan_persen

            FROM program g
            LEFT JOIN indicator i ON i.program_id=g.id
            LEFT JOIN
                (
                    SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail e
                    LEFT JOIN activity_data a ON a.id=e.activity_data_id
                    LEFT JOIN activity v ON v.id=a.activity_id
                    LEFT JOIN service s ON s.id=v.service_id
                    LEFT JOIN period p ON p.id=a.period_id
                    WHERE p.unit_id=:unit_id AND p.tahun=2020
                    GROUP BY s.program_id
                ) agg ON agg.program_id=g.id
            LEFT JOIN
                (
                    SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
                    LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
                    LEFT JOIN activity v ON v.id=a.activity_id
                    LEFT JOIN service s ON s.id=v.service_id
                    LEFT JOIN period p ON p.id=a.period_id
                    WHERE p.unit_id=:unit_id AND p.tahun=2020
                    GROUP BY s.program_id
                ) agg_ubah ON agg_ubah.program_id=g.id
            LEFT JOIN
                (
                    SELECT i.program_id, indicator_id, max(id.kinerja) jumlah FROM indicator_data id
                    LEFT JOIN indicator i ON i.id=id.indicator_id
                    LEFT JOIN period p ON p.id=id.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 1 AND bulan <= 3
                ) k_1 ON k_1.program_id=g.id AND k_1.indicator_id=i.id
            LEFT JOIN
                (
                    SELECT s.program_id, e.activity_data_id, sum(id.realisasi_jumlah) jumlah FROM financial_realization id
                    LEFT JOIN activity_detail e ON e.id=id.activity_detail_id
                        LEFT JOIN activity_data i ON i.id=e.activity_data_id
                        LEFT JOIN activity v ON v.id=i.activity_id
                        LEFT JOIN service s ON s.id=v.service_id
                        LEFT JOIN period p ON p.id=i.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 1 AND bulan <= 3
                    GROUP BY s.program_id
                ) kn_1 ON kn_1.program_id=g.id
            LEFT JOIN
                (
                    SELECT s.program_id, e.activity_data_id, sum(id.realisasi_jumlah) jumlah FROM financial_realization id
                    LEFT JOIN activity_detail_ubah e ON e.id=id.activity_detail_id
                        LEFT JOIN activity_data_ubah i ON i.id=e.activity_data_id
                        LEFT JOIN activity v ON v.id=i.activity_id
                        LEFT JOIN service s ON s.id=v.service_id
                        LEFT JOIN period p ON p.id=i.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 1 AND bulan <= 3
                    GROUP BY s.program_id
                ) kn_1_ubah ON kn_1_ubah.program_id=g.id
            LEFT JOIN
                (
                    SELECT i.program_id, indicator_id, max(id.kinerja) jumlah FROM indicator_data id
                    LEFT JOIN indicator i ON i.id=id.indicator_id
                    LEFT JOIN period p ON p.id=id.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 4 AND bulan <= 6
                ) k_2 ON k_2.program_id=g.id AND k_2.indicator_id=i.id
            LEFT JOIN
                (
                    SELECT s.program_id, e.activity_data_id, sum(id.realisasi_jumlah) jumlah FROM financial_realization id
                    LEFT JOIN activity_detail_ubah e ON e.id=id.activity_detail_id
                        LEFT JOIN activity_data_ubah i ON i.id=e.activity_data_id
                        LEFT JOIN activity v ON v.id=i.activity_id
                        LEFT JOIN service s ON s.id=v.service_id
                        LEFT JOIN period p ON p.id=i.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 4 AND bulan <= 6
                    GROUP BY s.program_id
                ) kn_2 ON kn_2.program_id=g.id
            LEFT JOIN
                (
                    SELECT s.program_id, e.activity_data_id, sum(id.realisasi_jumlah) jumlah FROM financial_realization id
                    LEFT JOIN activity_detail e ON e.id=id.activity_detail_id
                        LEFT JOIN activity_data i ON i.id=e.activity_data_id
                        LEFT JOIN activity v ON v.id=i.activity_id
                        LEFT JOIN service s ON s.id=v.service_id
                        LEFT JOIN period p ON p.id=i.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 4 AND bulan <= 6
                    GROUP BY s.program_id
                ) kn_2_ubah ON kn_2.program_id=g.id
            LEFT JOIN
                (
                    SELECT i.program_id, indicator_id, max(id.kinerja) jumlah FROM indicator_data id
                    LEFT JOIN indicator i ON i.id=id.indicator_id
                    LEFT JOIN period p ON p.id=id.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 7 AND bulan <= 9
                ) k_3 ON k_3.program_id=g.id AND k_3.indicator_id=i.id
            LEFT JOIN
                (
                    SELECT s.program_id, e.activity_data_id, sum(id.realisasi_jumlah) jumlah FROM financial_realization id
                    LEFT JOIN activity_detail e ON e.id=id.activity_detail_id
                        LEFT JOIN activity_data i ON i.id=e.activity_data_id
                        LEFT JOIN activity v ON v.id=i.activity_id
                        LEFT JOIN service s ON s.id=v.service_id
                        LEFT JOIN period p ON p.id=i.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 7 AND bulan <= 9
                    GROUP BY s.program_id
                ) kn_3 ON kn_3.program_id=g.id
            LEFT JOIN
                (
                    SELECT s.program_id, e.activity_data_id, sum(id.realisasi_jumlah) jumlah FROM financial_realization id
                    LEFT JOIN activity_detail_ubah e ON e.id=id.activity_detail_id
                        LEFT JOIN activity_data_ubah i ON i.id=e.activity_data_id
                        LEFT JOIN activity v ON v.id=i.activity_id
                        LEFT JOIN service s ON s.id=v.service_id
                        LEFT JOIN period p ON p.id=i.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 7 AND bulan <= 9
                    GROUP BY s.program_id
                ) kn_3_ubah ON kn_3.program_id=g.id
            LEFT JOIN
                (
                    SELECT i.program_id, indicator_id, max(id.kinerja) jumlah FROM indicator_data id
                    LEFT JOIN indicator i ON i.id=id.indicator_id
                    LEFT JOIN period p ON p.id=id.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 10 AND bulan <= 12
                ) k_4 ON k_4.program_id=g.id AND k_4.indicator_id=i.id
            LEFT JOIN
                (
                    SELECT s.program_id, e.activity_data_id, sum(id.realisasi_jumlah) jumlah FROM financial_realization id
                    LEFT JOIN activity_detail e ON e.id=id.activity_detail_id
                        LEFT JOIN activity_data i ON i.id=e.activity_data_id
                        LEFT JOIN activity v ON v.id=i.activity_id
                        LEFT JOIN service s ON s.id=v.service_id
                        LEFT JOIN period p ON p.id=i.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 10 AND bulan <= 12
                    GROUP BY s.program_id
                ) kn_4 ON kn_4.program_id=g.id	
            LEFT JOIN
                (
                    SELECT s.program_id, e.activity_data_id, sum(id.realisasi_jumlah) jumlah FROM financial_realization id
                    LEFT JOIN activity_detail_ubah e ON e.id=id.activity_detail_id
                        LEFT JOIN activity_data_ubah i ON i.id=e.activity_data_id
                        LEFT JOIN activity v ON v.id=i.activity_id
                        LEFT JOIN service s ON s.id=v.service_id
                        LEFT JOIN period p ON p.id=i.period_id
                    WHERE p.unit_id=:unit_id AND bulan >= 10 AND bulan <= 12
                    GROUP BY s.program_id
                ) kn_4_ubah ON kn_4.program_id=g.id	
            LEFT JOIN period r ON r.tahun=g.tahun
            WHERE g.tahun=:periode
            GROUP BY g.id, i.id
            ORDER BY g.id, i.id')
            ->bindValue(':unit_id', Yii::$app->user->identity->unit_id)
            ->bindValue(':periode', $period)
            ->queryAll();
        }

        foreach ($data as $row) {
            $exportperfomance =  new Exportperfomance();
            // if ($row['nama_program'] !== $program){
            //     $exportperfomance->nama_program=$row['nama_program']; $program = $row['nama_program'];
            // }
            $exportperfomance->nama_program=$row['nama_program']; $program = $row['nama_program'];
            $exportperfomance->indikator=$row['indikator'];
            // $exportperfomance->target=$row['target'];   
            $exportperfomance->anggaran=$row['anggaran'];
            $exportperfomance->kinerja_1=$row['kinerja_1'];
            $exportperfomance->keuangan_nilai_1=$row['keuangan_nilai_1'];
            $exportperfomance->keuangan_persen_1=$row['keuangan_persen_1'];
            $exportperfomance->kinerja_2=$row['kinerja_2'];
            $exportperfomance->keuangan_nilai_2=$row['keuangan_nilai_2'];
            $exportperfomance->keuangan_persen_2=$row['keuangan_persen_2'];
            $exportperfomance->kinerja_3=$row['kinerja_3'];
            $exportperfomance->keuangan_nilai_3=$row['keuangan_nilai_3'];
            $exportperfomance->keuangan_persen_3=$row['keuangan_persen_3'];
            $exportperfomance->kinerja_4=$row['kinerja_4'];
            $exportperfomance->keuangan_nilai_4=$row['keuangan_nilai_4'];
            $exportperfomance->keuangan_persen_4=$row['keuangan_persen_4'];
            $exportperfomance->total_kinerja=$row['total_kinerja'];
            $exportperfomance->total_keuangan_nilai=$row['total_keuangan_nilai'];
            $exportperfomance->total_keuangan_persen=$row['total_keuangan_persen'];
            $exportperfomance->username=Yii::$app->user->identity->username;
            $exportperfomance->period=$period;
            $exportperfomance->save();
        }

        $dataExcel = Yii::$app->db->createCommand('SELECT * FROM export_perfomance WHERE (anggaran <> 0 OR kinerja_1 <> 0 OR keuangan_nilai_1 <> 0 OR 
        keuangan_persen_1 <> 0 OR kinerja_2 <> 0 OR keuangan_nilai_2 <> 0 OR 
        keuangan_persen_2 OR kinerja_3 <> 0 OR keuangan_nilai_3 <> 0 OR 
        keuangan_persen_3 OR kinerja_4 <> 0 OR keuangan_nilai_4 <> 0 OR 
        keuangan_persen_4 ) AND username=:username AND period=:periodValue')
        ->bindValue(':username', Yii::$app->user->identity->username)
        ->bindValue(':periodValue', $period)
        ->queryAll();

        $inputFileName = Yii::getAlias('@app/views/exportaccount').'/_export_kinerja.xlsx';

        /** Load $inputFileName to a Spreadsheet Object  **/
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A'.'1', 'LAPORAN CAPAIAN KINERJA DAN KEUANGAN BOK');
        $activeSheet->setCellValue('A'.'2', 'UPTD ' .strtoupper(Yii::$app->user->identity->alias));
        $activeSheet->setCellValue('A'.'3', 'TAHUN ' .$period);
        $baseRow=8;
        $firstData=8;
        $baseRowProgram=$baseRow;
        $program = '';
        foreach($dataExcel as $rowExcel) {
            if($rowExcel['nama_program']!==null){
                $activeSheet->setCellValue('A'.$baseRow, $baseRowProgram-7);
                $baseRowProgram = $baseRowProgram+1;
            }
            if ($rowExcel['nama_program'] !== $program){
                $activeSheet->setCellValue('B'.$baseRow, $rowExcel['nama_program'])
                ->setCellValue('E'.$baseRow, $rowExcel['anggaran']); 
                $program = $rowExcel['nama_program'];
            }
            $activeSheet
                        // ->setCellValue('A'.$baseRow, $baseRow-7)
                        // ->setCellValue('B'.$baseRow, $rowExcel['nama_program'])
                        ->setCellValue('C'.$baseRow, $rowExcel['indikator'])
                        ->setCellValue('D'.$baseRow, $rowExcel['target'])
                        // ->setCellValue('E'.$baseRow, $rowExcel['anggaran'])
                        ->setCellValue('F'.$baseRow, $rowExcel['kinerja_1'])
                        ->setCellValue('G'.$baseRow, $rowExcel['keuangan_nilai_1'])
                        ->setCellValue('H'.$baseRow, $rowExcel['keuangan_persen_1'])
                        ->setCellValue('I'.$baseRow, $rowExcel['kinerja_2'])
                        ->setCellValue('J'.$baseRow, $rowExcel['keuangan_nilai_2'])
                        ->setCellValue('K'.$baseRow, $rowExcel['keuangan_persen_2'])
                        ->setCellValue('L'.$baseRow, $rowExcel['kinerja_3'])
                        ->setCellValue('M'.$baseRow, $rowExcel['keuangan_nilai_3'])
                        ->setCellValue('N'.$baseRow, $rowExcel['keuangan_persen_3'])
                        ->setCellValue('O'.$baseRow, $rowExcel['kinerja_4'])
                        ->setCellValue('P'.$baseRow, $rowExcel['keuangan_nilai_4'])
                        ->setCellValue('Q'.$baseRow, $rowExcel['keuangan_persen_4'])
                        ->setCellValue('R'.$baseRow, $rowExcel['total_kinerja'])
                        ->setCellValue('S'.$baseRow, $rowExcel['total_keuangan_nilai'])
                        ->setCellValue('T'.$baseRow, $rowExcel['total_keuangan_persen'])
                        ;
            $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':T' .$baseRow)->applyFromArray($styleArray);

        $baseRow++;
        }

        if(!empty($dataExcel)){
            $lastData=$baseRow-1;
            $activeSheet->setCellValue('B'.$baseRow, 'TOTAL');
            $spreadsheet->getActiveSheet()->mergeCells('B'.$baseRow. ':D' .$baseRow);
            $activeSheet->getStyle('B'.$baseRow. ':D' .$baseRow)->getAlignment()->setHorizontal('center'); 
            $activeSheet->getStyle('B'.$baseRow. ':D' .$baseRow)->getAlignment()->setWrapText(true);
            $activeSheet->setCellValue('E'.$baseRow, '=SUM(E' .$firstData. ':E' .$lastData. ')');
            $activeSheet->setCellValue('G'.$baseRow, '=SUM(G' .$firstData. ':G' .$lastData. ')');
            $activeSheet->setCellValue('J'.$baseRow, '=SUM(J' .$firstData. ':J' .$lastData. ')');
            $activeSheet->setCellValue('M'.$baseRow, '=SUM(M' .$firstData. ':M' .$lastData. ')');
            $activeSheet->setCellValue('P'.$baseRow, '=SUM(P' .$firstData. ':P' .$lastData. ')');
            $activeSheet->setCellValue('S'.$baseRow, '=SUM(S' .$firstData. ':S' .$lastData. ')');
            $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':T' .$baseRow)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':T' .$baseRow)->getFont()->setBold(true);
        }

        $unit = Unit::findOne(Yii::$app->user->identity->unit_id);
        $baseRow=$baseRow+3;

        // $activeSheet->setCellValue('D'.$baseRow, $unit->puskesmas. ',                        ' .$period); 
        // $spreadsheet->getActiveSheet()->getStyle('C'.$baseRow. ':E' .$baseRow)->getFont()->setBold( true );
        // $baseRow=$baseRow+1; 

        $activeSheet->setCellValue('D'.$baseRow, 'Mengetahui,'); 
        $spreadsheet->getActiveSheet()->getStyle('D'.$baseRow. ':F' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+1;

        $activeSheet->setCellValue('D'.$baseRow, 'Kepala UPTD ' .Yii::$app->user->identity->alias); 
        $activeSheet->setCellValue('O'.$baseRow, 'PPTK BOK'); 
        $spreadsheet->getActiveSheet()->getStyle('D'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+4;

        $activeSheet->setCellValue('D'.$baseRow, $unit->kepala); 
        $activeSheet->setCellValue('O'.$baseRow, $unit->petugas); 
        $spreadsheet->getActiveSheet()->getStyle('D'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+1;

        $activeSheet->setCellValue('D'.$baseRow, 'NIP. ' .$unit->nip_kepala);
        $activeSheet->setCellValue('O'.$baseRow, 'NIP. ' .$unit->nip_petugas);
        $spreadsheet->getActiveSheet()->getStyle('D'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );

        // $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        // $drawing->setName('Paid');
        // $drawing->setDescription('Paid');
        // $drawing->setPath('img/Draft.png'); // put your path and image here
        // $drawing->setCoordinates('B1');
        // $drawing->setOffsetX(110);
        // $drawing->setRotation(25);
        // $drawing->getShadow()->setVisible(true);
        // $drawing->getShadow()->setDirection(45);
        // $drawing->setWorksheet($spreadsheet->getActiveSheet());

        // $spreadsheet->getSecurity()->setLockWindows(true);
        // $spreadsheet->getSecurity()->setLockStructure(true);
        // $spreadsheet->getSecurity()->setWorkbookPassword("silverblack");

        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setPassword('silverblack');
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setSheet(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setSort(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setInsertRows(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setFormatCells(true);

        // $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_kinerja_'.$period. '_'.Yii::$app->user->identity->username.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');

        // $mpdf = new \Mpdf\Mpdf();
        // $mpdf->WriteHTML('<h1>Hello world!</h1>');
        // $mpdf->Output();

        // $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Mpdf');
        // $writer->writeAllSheets();
        // header('Content-Type: application/pdf');
        // header('Content-Disposition: attachment;filename="_export.pdf"');
        // header('Cache-Control: max-age=0');
        // $writer->save('php://output');

        exit;
    }

    public function actionExporttwadm($unit_id)
    {
        $session = Yii::$app->session;
        $period = $session['periodValue'];

        $unitnya = User::find()
        ->where(['unit_id' => $unit_id])
        ->one();

        Yii::$app->db->createCommand('DELETE FROM export_perfomance WHERE username=:username AND period=:periodValue ')
        ->bindValue(':username', Yii::$app->user->identity->username)
        ->bindValue(':periodValue', $period)
        ->execute();

        $data = Yii::$app->db->createCommand('SELECT g.nama_program, i.nama_indikator indikator, ifnull(agg.jumlah,0) anggaran, 
        ifnull(k_1.jumlah,0) kinerja_1, 
        ifnull(kn_1.jumlah,0) keuangan_nilai_1, 
        ifnull(ROUND(ifnull(kn_1.jumlah,0)/ifnull(agg.jumlah,0)*100,2),0) keuangan_persen_1,
        
        ifnull(k_2.jumlah,0) kinerja_2, 
        CASE
            WHEN ifnull(kn_2.jumlah,0)>0 THEN
                ifnull(kn_1.jumlah,0)+ifnull(kn_2.jumlah,0) 
            ELSE 0
        END keuangan_nilai_2, 
        CASE 
            WHEN ifnull(kn_2.jumlah,0)>0 THEN 
                ifnull(ROUND((ifnull(kn_1.jumlah,0)+ifnull(kn_2.jumlah,0))/ifnull(agg.jumlah,0)*100,2),0) 
            ELSE 0
        END keuangan_persen_2,
        
        ifnull(k_3.jumlah,0) kinerja_3, 
        CASE
            WHEN ifnull(kn_3.jumlah,0)>0 THEN
                ifnull(kn_1.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_3.jumlah,0) 
            ELSE 0
        END keuangan_nilai_3, 
        CASE 
            WHEN ifnull(kn_3.jumlah,0)>0 THEN 
                ifnull(ROUND((ifnull(kn_1.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_3.jumlah,0))/ifnull(agg.jumlah,0)*100,2),0) 
            ELSE 0
        END keuangan_persen_3,
        
        ifnull(k_4.jumlah,0) kinerja_4, 
        CASE
            WHEN ifnull(kn_4.jumlah,0)>0 THEN
                ifnull(kn_1.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_3.jumlah,0)+ifnull(kn_4.jumlah,0) 
            ELSE 0
        END keuangan_nilai_4, 
        CASE
            WHEN ifnull(kn_4.jumlah,0)>0 THEN
                ifnull(ROUND((ifnull(kn_1.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_3.jumlah,0)+ifnull(kn_4.jumlah,0))/ifnull(agg.jumlah,0)*100,2),0) 
            ELSE 0
        END keuangan_persen_4,

        CASE
            WHEN ifnull(k_4.jumlah,0)>0 THEN ifnull(k_4.jumlah,0)
            WHEN ifnull(k_3.jumlah,0)>0 THEN ifnull(k_3.jumlah,0)
            WHEN ifnull(k_2.jumlah,0)>0 THEN ifnull(k_2.jumlah,0)
            WHEN ifnull(k_1.jumlah,0)>0 THEN ifnull(k_1.jumlah,0)
            ELSE 0
        END total_kinerja,
        ifnull(kn_1.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_3.jumlah,0)+ifnull(kn_4.jumlah,0) total_keuangan_nilai,
        ifnull(ROUND((ifnull(kn_1.jumlah,0)+ifnull(kn_2.jumlah,0)+ifnull(kn_3.jumlah,0)+ifnull(kn_4.jumlah,0))/ifnull(agg.jumlah,0)*100,2),0) total_keuangan_persen

        FROM program g
        LEFT JOIN indicator i ON i.program_id=g.id
        LEFT JOIN
            (
                SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id=:unit_id AND p.tahun=:periode
                GROUP BY s.program_id
            ) agg ON agg.program_id=g.id
        LEFT JOIN
            (
                SELECT i.program_id, indicator_id, max(id.kinerja) jumlah FROM indicator_data id
                LEFT JOIN indicator i ON i.id=id.indicator_id
                LEFT JOIN period p ON p.id=id.period_id
                WHERE p.unit_id=:unit_id AND bulan >= 1 AND bulan <= 3
            ) k_1 ON k_1.program_id=g.id AND k_1.indicator_id=i.id
        LEFT JOIN
            (
                SELECT s.program_id, activity_data_id, sum(id.realisasi_jumlah) jumlah FROM financial_realization id
                     LEFT JOIN activity_detail e ON e.id=id.activity_detail_id
					 LEFT JOIN activity_data i ON i.id=e.activity_data_id
					 LEFT JOIN activity v ON v.id=i.activity_id
					 LEFT JOIN service s ON s.id=v.service_id
					 LEFT JOIN period p ON p.id=i.period_id
                WHERE p.unit_id=:unit_id AND bulan >= 1 AND bulan <= 3
                GROUP BY s.program_id
            ) kn_1 ON kn_1.program_id=g.id
        LEFT JOIN
            (
                SELECT i.program_id, indicator_id, max(id.kinerja) jumlah FROM indicator_data id
                LEFT JOIN indicator i ON i.id=id.indicator_id
                LEFT JOIN period p ON p.id=id.period_id
                WHERE p.unit_id=:unit_id AND bulan >= 4 AND bulan <= 6
            ) k_2 ON k_2.program_id=g.id AND k_2.indicator_id=i.id
        LEFT JOIN
            (
                SELECT s.program_id, activity_data_id, sum(id.realisasi_jumlah) jumlah FROM financial_realization id
                     LEFT JOIN activity_detail e ON e.id=id.activity_detail_id
					 LEFT JOIN activity_data i ON i.id=e.activity_data_id
					 LEFT JOIN activity v ON v.id=i.activity_id
					 LEFT JOIN service s ON s.id=v.service_id
					 LEFT JOIN period p ON p.id=i.period_id
                WHERE p.unit_id=:unit_id AND bulan >= 4 AND bulan <= 6
                GROUP BY s.program_id
            ) kn_2 ON kn_2.program_id=g.id
        LEFT JOIN
            (
                SELECT i.program_id, indicator_id, max(id.kinerja) jumlah FROM indicator_data id
                LEFT JOIN indicator i ON i.id=id.indicator_id
                LEFT JOIN period p ON p.id=id.period_id
                WHERE p.unit_id=:unit_id AND bulan >= 7 AND bulan <= 9
            ) k_3 ON k_3.program_id=g.id AND k_3.indicator_id=i.id
        LEFT JOIN
            (
                SELECT s.program_id, activity_data_id, sum(id.realisasi_jumlah) jumlah FROM financial_realization id
                     LEFT JOIN activity_detail e ON e.id=id.activity_detail_id
					 LEFT JOIN activity_data i ON i.id=e.activity_data_id
					 LEFT JOIN activity v ON v.id=i.activity_id
					 LEFT JOIN service s ON s.id=v.service_id
					 LEFT JOIN period p ON p.id=i.period_id
                WHERE p.unit_id=:unit_id AND bulan >= 7 AND bulan <= 9
                GROUP BY s.program_id
            ) kn_3 ON kn_3.program_id=g.id
        LEFT JOIN
            (
                SELECT i.program_id, indicator_id, max(id.kinerja) jumlah FROM indicator_data id
                LEFT JOIN indicator i ON i.id=id.indicator_id
                LEFT JOIN period p ON p.id=id.period_id
                WHERE p.unit_id=:unit_id AND bulan >= 10 AND bulan <= 12
            ) k_4 ON k_4.program_id=g.id AND k_4.indicator_id=i.id
        LEFT JOIN
            (
                SELECT s.program_id, activity_data_id, sum(id.realisasi_jumlah) jumlah FROM financial_realization id
                     LEFT JOIN activity_detail e ON e.id=id.activity_detail_id
					 LEFT JOIN activity_data i ON i.id=e.activity_data_id
					 LEFT JOIN activity v ON v.id=i.activity_id
					 LEFT JOIN service s ON s.id=v.service_id
					 LEFT JOIN period p ON p.id=i.period_id
                WHERE p.unit_id=:unit_id AND bulan >= 10 AND bulan <= 12
                GROUP BY s.program_id
            ) kn_4 ON kn_4.program_id=g.id	
        WHERE g.tahun=:periode
        GROUP BY g.id, i.id
        ORDER BY g.id, i.id')
        ->bindValue(':unit_id', $unit_id)
        ->bindValue(':periode', $period)
        ->queryAll();

        foreach ($data as $row) {
            $exportperfomance =  new Exportperfomance();
            // if ($row['nama_program'] !== $program){
            //     $exportperfomance->nama_program=$row['nama_program']; $program = $row['nama_program'];
            // }
            $exportperfomance->nama_program=$row['nama_program']; $program = $row['nama_program'];
            $exportperfomance->indikator=$row['indikator'];
            // $exportperfomance->target=$row['target'];   
            $exportperfomance->anggaran=$row['anggaran'];
            $exportperfomance->kinerja_1=$row['kinerja_1'];
            $exportperfomance->keuangan_nilai_1=$row['keuangan_nilai_1'];
            $exportperfomance->keuangan_persen_1=$row['keuangan_persen_1'];
            $exportperfomance->kinerja_2=$row['kinerja_2'];
            $exportperfomance->keuangan_nilai_2=$row['keuangan_nilai_2'];
            $exportperfomance->keuangan_persen_2=$row['keuangan_persen_2'];
            $exportperfomance->kinerja_3=$row['kinerja_3'];
            $exportperfomance->keuangan_nilai_3=$row['keuangan_nilai_3'];
            $exportperfomance->keuangan_persen_3=$row['keuangan_persen_3'];
            $exportperfomance->kinerja_4=$row['kinerja_4'];
            $exportperfomance->keuangan_nilai_4=$row['keuangan_nilai_4'];
            $exportperfomance->keuangan_persen_4=$row['keuangan_persen_4'];
            $exportperfomance->total_kinerja=$row['total_kinerja'];
            $exportperfomance->total_keuangan_nilai=$row['total_keuangan_nilai'];
            $exportperfomance->total_keuangan_persen=$row['total_keuangan_persen'];
            $exportperfomance->username=Yii::$app->user->identity->username;
            $exportperfomance->period=$period;
            $exportperfomance->save();
        }

        $dataExcel = Yii::$app->db->createCommand('SELECT * FROM export_perfomance WHERE (anggaran <> 0 OR kinerja_1 <> 0 OR keuangan_nilai_1 <> 0 OR 
        keuangan_persen_1 <> 0 OR kinerja_2 <> 0 OR keuangan_nilai_2 <> 0 OR 
        keuangan_persen_2 OR kinerja_3 <> 0 OR keuangan_nilai_3 <> 0 OR 
        keuangan_persen_3 OR kinerja_4 <> 0 OR keuangan_nilai_4 <> 0 OR 
        keuangan_persen_4 ) AND username=:username AND period=:periodValue')
        ->bindValue(':username', $unitnya->username)
        ->bindValue(':periodValue', $period)
        ->queryAll();

        $inputFileName = Yii::getAlias('@app/views/exportaccount').'/_export_kinerja.xlsx';

        /** Load $inputFileName to a Spreadsheet Object  **/
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A'.'1', 'LAPORAN CAPAIAN KINERJA DAN KEUANGAN BOK');
        $activeSheet->setCellValue('A'.'2', 'UPTD ' .strtoupper($unitnya->alias));
        $activeSheet->setCellValue('A'.'3', 'TAHUN ' .$period);
        $baseRow=8;
        $firstData=8;
        $baseRowProgram=$baseRow;
        $program = '';
        foreach($dataExcel as $rowExcel) {
            if($rowExcel['nama_program']!==null){
                $activeSheet->setCellValue('A'.$baseRow, $baseRowProgram-7);
                $baseRowProgram = $baseRowProgram+1;
            }
            if ($rowExcel['nama_program'] !== $program){
                $activeSheet->setCellValue('B'.$baseRow, $rowExcel['nama_program'])
                ->setCellValue('E'.$baseRow, $rowExcel['anggaran']); 
                $program = $rowExcel['nama_program'];
            }
            $activeSheet
                        // ->setCellValue('A'.$baseRow, $baseRow-7)
                        // ->setCellValue('B'.$baseRow, $rowExcel['nama_program'])
                        ->setCellValue('C'.$baseRow, $rowExcel['indikator'])
                        ->setCellValue('D'.$baseRow, $rowExcel['target'])
                        // ->setCellValue('E'.$baseRow, $rowExcel['anggaran'])
                        ->setCellValue('F'.$baseRow, $rowExcel['kinerja_1'])
                        ->setCellValue('G'.$baseRow, $rowExcel['keuangan_nilai_1'])
                        ->setCellValue('H'.$baseRow, $rowExcel['keuangan_persen_1'])
                        ->setCellValue('I'.$baseRow, $rowExcel['kinerja_2'])
                        ->setCellValue('J'.$baseRow, $rowExcel['keuangan_nilai_2'])
                        ->setCellValue('K'.$baseRow, $rowExcel['keuangan_persen_2'])
                        ->setCellValue('L'.$baseRow, $rowExcel['kinerja_3'])
                        ->setCellValue('M'.$baseRow, $rowExcel['keuangan_nilai_3'])
                        ->setCellValue('N'.$baseRow, $rowExcel['keuangan_persen_3'])
                        ->setCellValue('O'.$baseRow, $rowExcel['kinerja_4'])
                        ->setCellValue('P'.$baseRow, $rowExcel['keuangan_nilai_4'])
                        ->setCellValue('Q'.$baseRow, $rowExcel['keuangan_persen_4'])
                        ->setCellValue('R'.$baseRow, $rowExcel['total_kinerja'])
                        ->setCellValue('S'.$baseRow, $rowExcel['total_keuangan_nilai'])
                        ->setCellValue('T'.$baseRow, $rowExcel['total_keuangan_persen'])
                        ;
            $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':T' .$baseRow)->applyFromArray($styleArray);

        $baseRow++;
        }

        if(!empty($dataExcel)){
            $lastData=$baseRow-1;
            $activeSheet->setCellValue('B'.$baseRow, 'TOTAL');
            $spreadsheet->getActiveSheet()->mergeCells('B'.$baseRow. ':D' .$baseRow);
            $activeSheet->getStyle('B'.$baseRow. ':D' .$baseRow)->getAlignment()->setHorizontal('center'); 
            $activeSheet->getStyle('B'.$baseRow. ':D' .$baseRow)->getAlignment()->setWrapText(true);
            $activeSheet->setCellValue('E'.$baseRow, '=SUM(E' .$firstData. ':E' .$lastData. ')');
            $activeSheet->setCellValue('G'.$baseRow, '=SUM(G' .$firstData. ':G' .$lastData. ')');
            $activeSheet->setCellValue('J'.$baseRow, '=SUM(J' .$firstData. ':J' .$lastData. ')');
            $activeSheet->setCellValue('M'.$baseRow, '=SUM(M' .$firstData. ':M' .$lastData. ')');
            $activeSheet->setCellValue('P'.$baseRow, '=SUM(P' .$firstData. ':P' .$lastData. ')');
            $activeSheet->setCellValue('S'.$baseRow, '=SUM(S' .$firstData. ':S' .$lastData. ')');
            $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':T' .$baseRow)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':T' .$baseRow)->getFont()->setBold(true);
        }

        $unit = Unit::findOne($unitnya->unit_id);
        $baseRow=$baseRow+3;

        // $activeSheet->setCellValue('D'.$baseRow, $unit->puskesmas. ',                        ' .$period); 
        // $spreadsheet->getActiveSheet()->getStyle('C'.$baseRow. ':E' .$baseRow)->getFont()->setBold( true );
        // $baseRow=$baseRow+1; 

        $activeSheet->setCellValue('D'.$baseRow, 'Mengetahui,'); 
        $spreadsheet->getActiveSheet()->getStyle('D'.$baseRow. ':F' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+1;

        $activeSheet->setCellValue('D'.$baseRow, 'Kepala UPTD ' .$unitnya->alias); 
        $activeSheet->setCellValue('O'.$baseRow, 'PPTK BOK'); 
        $spreadsheet->getActiveSheet()->getStyle('D'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+4;

        $activeSheet->setCellValue('D'.$baseRow, $unit->kepala); 
        $activeSheet->setCellValue('O'.$baseRow, $unit->petugas); 
        $spreadsheet->getActiveSheet()->getStyle('D'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+1;

        $activeSheet->setCellValue('D'.$baseRow, 'NIP. ' .$unit->nip_kepala);
        $activeSheet->setCellValue('O'.$baseRow, 'NIP. ' .$unit->nip_petugas);
        $spreadsheet->getActiveSheet()->getStyle('D'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );

        // $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        // $drawing->setName('Paid');
        // $drawing->setDescription('Paid');
        // $drawing->setPath('img/Draft.png'); // put your path and image here
        // $drawing->setCoordinates('B1');
        // $drawing->setOffsetX(110);
        // $drawing->setRotation(25);
        // $drawing->getShadow()->setVisible(true);
        // $drawing->getShadow()->setDirection(45);
        // $drawing->setWorksheet($spreadsheet->getActiveSheet());

        // $spreadsheet->getSecurity()->setLockWindows(true);
        // $spreadsheet->getSecurity()->setLockStructure(true);
        // $spreadsheet->getSecurity()->setWorkbookPassword("silverblack");

        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setPassword('silverblack');
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setSheet(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setSort(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setInsertRows(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setFormatCells(true);

        // $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_kinerja_'.$period. '_'.$unitnya->username.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');

        // $mpdf = new \Mpdf\Mpdf();
        // $mpdf->WriteHTML('<h1>Hello world!</h1>');
        // $mpdf->Output();

        // $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Mpdf');
        // $writer->writeAllSheets();
        // header('Content-Type: application/pdf');
        // header('Content-Disposition: attachment;filename="_export.pdf"');
        // header('Cache-Control: max-age=0');
        // $writer->save('php://output');

        exit;
    }

    public function actionExportxls()
    {
        $session = Yii::$app->session;
        $period = $session['periodValue'];

        // Yii::$app->db->createCommand('DELETE FROM export_account WHERE username=:username AND period=:periodValue ')
        // ->bindValue(':username', Yii::$app->user->identity->username)
        // ->bindValue(':periodValue', $period)
        // ->execute();

        $akun = Yii::$app->db->createCommand('SELECT c.id, c.nama_rekening, sum(e.jumlah) jumlah
        FROM activity_detail e
        LEFT JOIN activity_data a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN program g ON g.id=s.program_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN account c ON c.id=e.account_id
        WHERE p.unit_id=:unit_id AND p.tahun=:periode AND jumlah > 0
        group BY c.nama_rekening ORDER BY c.id')
        ->bindValue(':unit_id', Yii::$app->user->identity->unit_id)
        ->bindValue(':periode', $period)
        ->queryAll();

        $col = 'C';
        foreach($akun as $acc) {
            $col++;
        }

        $jmlakun = count($akun);

        $inputFileName = Yii::getAlias('@app/views/exportaccount').'/_export.xlsx';

        /** Load $inputFileName to a Spreadsheet Object  **/
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        // $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $styleArrayBold = [
            'font' => [
                'bold' => true,
            ],
        ];

        // //set orientasi dan ukurankertas
        // $activeSheet->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE)
        //                             ->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_FOLIO);
        
        $activeSheet->setCellValue('A'.'1', 'POA DAK BOK TAHUNAN');
            $spreadsheet->getActiveSheet()->mergeCells('A1'. ':' .$col.'1');
            $activeSheet->getStyle('A1'. ':' .$col.'1')->getAlignment()->setHorizontal('center'); 
            $activeSheet->getStyle('A1'. ':' .$col.'1')->getAlignment()->setWrapText(true);
        $activeSheet->setCellValue('A'.'2', 'UPTD ' .strtoupper(Yii::$app->user->identity->alias));
            $spreadsheet->getActiveSheet()->mergeCells('A2'. ':' .$col.'2');
            $activeSheet->getStyle('A2'. ':' .$col.'2')->getAlignment()->setHorizontal('center'); 
            $activeSheet->getStyle('A2'. ':' .$col.'2')->getAlignment()->setWrapText(true);
        $activeSheet->setCellValue('A'.'3', 'TAHUN ' .$period);
            $spreadsheet->getActiveSheet()->mergeCells('A3'. ':' .$col.'3');
            $activeSheet->getStyle('A3'. ':' .$col.'3')->getAlignment()->setHorizontal('center'); 
            $activeSheet->getStyle('A3'. ':' .$col.'3')->getAlignment()->setWrapText(true);

        $col = 'C';
        foreach($akun as $acc) {
                $activeSheet->setCellValue($col++.'5', $acc['nama_rekening']);
                $lcol = $col--;
        }
        $activeSheet->setCellValue($col++.'5', 'JUMLAH');
        $spreadsheet->getActiveSheet()->getStyle('C'.'5'. ':' .$lcol.'5')->applyFromArray($styleArray)->getAlignment()->setWrapText(true)->setVertical('center');

        // $program = Yii::$app->db->createCommand('SELECT g.id, g.nama_program program FROM program g WHERE g.tahun=2022 AND g.aktif=1')
        // ->bindValue(':periode', $period)
        // ->queryAll();

        $program = Yii::$app->db->createCommand('SELECT g.id, g.nama_program program, sum(e.jumlah) jumlah
        FROM activity_detail e
        LEFT JOIN activity_data a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN program g ON g.id=s.program_id
        LEFT JOIN period p ON p.id=a.period_id
        WHERE p.unit_id=:unit_id AND p.tahun=:periode AND jumlah > 0 AND g.aktif=1
        group BY g.nama_program ORDER BY g.id')
        ->bindValue(':unit_id', Yii::$app->user->identity->unit_id)
        ->bindValue(':periode', $period)
        ->queryAll();


        $countProgram= count($program);

        $baseRow=6;
        $firstData=6;
        foreach($program as $prg) {
            $activeSheet->setCellValue('A'.$baseRow, $baseRow-5)
                        ->setCellValue('B'.$baseRow, $prg['program']);

            $col = 'C';
            $colnum = '1';
            foreach($akun as $acc) {
                $data = Yii::$app->db->createCommand('SELECT s.program_id, IFNULL(sum(e.jumlah),0) jumlah FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id=:account_id AND s.program_id=:program_id')
                ->bindValue(':unit_id', Yii::$app->user->identity->unit_id)
                ->bindValue(':periode', $period)
                ->bindValue(':account_id', $acc['id'])
                ->bindValue(':program_id', $prg['id'])
                ->queryAll();

                foreach($data as $dt);
                $lastcol = $col;
                $activeSheet->setCellValue($col++.$baseRow, $dt['jumlah']);
                $colnum++;
            }
            $spreadsheet->getActiveSheet()->getStyle($col.$baseRow)->getFont()->setBold( true );
            $activeSheet->setCellValue($col++.$baseRow, '=SUM(C'.$baseRow. ':'.$lastcol.$baseRow. ')');
            
            $baseRow++;
        }

        $colsum = 'C';
        $lastData=$baseRow-1;
        $activeSheet->setCellValue('B'.$baseRow, 'JUMLAH');
        for ($i = 1; $i <= $colnum; $i++) {
            $activeSheet->setCellValue($colsum.$baseRow, '=SUM('.$colsum.$firstData. ':'.$colsum.$lastData. ')');
            $lastcolsum = $colsum;
            $colsum++;
        }

        $spreadsheet->getActiveSheet()->getStyle('B'.$baseRow. ':'.$lastcolsum.$baseRow)->getFont()->setBold( true );

        $spreadsheet->getActiveSheet()->getStyle('A5:'.$lastcolsum.$baseRow)->applyFromArray($styleArray);

        $unit = Unit::findOne(Yii::$app->user->identity->unit_id);
        $baseRow=$baseRow+3;

        $activeSheet->setCellValue('P'.$baseRow, $unit->puskesmas. ',                        ' .$period); 
        $spreadsheet->getActiveSheet()->mergeCells('P'.$baseRow. ':R' .$baseRow);
        $activeSheet->getStyle('P'.$baseRow. ':R' .$baseRow)->getAlignment()->setHorizontal('center'); 
        $activeSheet->getStyle('P'.$baseRow. ':R' .$baseRow)->getAlignment()->setWrapText(true);
        $spreadsheet->getActiveSheet()->getStyle('P'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+1; 

        $spreadsheet->getActiveSheet()->getStyle('Q:S')->getAlignment()->setHorizontal('center');
        $activeSheet->setCellValue('Q'.$baseRow, 'Mengetahui,'); 
        $spreadsheet->getActiveSheet()->getStyle('Q'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+1;
        $activeSheet->setCellValue('Q'.$baseRow, 'Kepala UPTD ' .Yii::$app->user->identity->alias); 
        $spreadsheet->getActiveSheet()->getStyle('Q'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+4;
        $activeSheet->setCellValue('Q'.$baseRow, $unit->kepala); 
        $spreadsheet->getActiveSheet()->getStyle('Q'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+1;
        $activeSheet->setCellValue('Q'.$baseRow, 'NIP. ' .$unit->nip_kepala);
        $spreadsheet->getActiveSheet()->getStyle('Q'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );

        //QUERY PER PROGRAM
        // Yii::$app->db->createCommand()->truncateTable('export_program')->execute();
        
        Yii::$app->db->createCommand('DELETE FROM export_program WHERE username=:username AND period=:periodValue ')
        ->bindValue(':username', Yii::$app->user->identity->username)
        ->bindValue(':periodValue', $period)
        ->execute();

        $data = Yii::$app->db->createCommand('SELECT g.nama_program, s.nama_pelayanan, v.nama_kegiatan,  IFNULL(a.bentuk_kegiatan, v.nama_kegiatan) bentuk_kegiatan, 
        a.sasaran, a.target, a.lokasi, a.pelaksana, 
        c.nama_rekening, e.rincian, e.vol_1, e.satuan_1, IFNULL(e.vol_2,"") vol_2, IFNULL(e.satuan_2,"") satuan_2, IFNULL(e.vol_3,"") vol_3, IFNULL(e.satuan_3,"") satuan_3, 
        IFNULL(e.vol_4,"") vol_4, IFNULL(e.satuan_4,"") satuan_4, 
        vol_1*IFNULL(vol_2,1)*IFNULL(vol_3,1)*IFNULL(vol_4,1) vol, e.unit_cost, e.jumlah,  
        case when e.jan=1 then "V" ELSE "" END jan, case when e.feb=1 then "V" ELSE "" END feb, 
        case when e.mar=1 then "V" ELSE "" END mar, case when e.apr=1 then "V" ELSE "" END apr, 
        case when e.mei=1 then "V" ELSE "" END mei, case when e.jun=1 then "V" ELSE "" END jun, 
        case when e.jul=1 then "V" ELSE "" END jul, case when e.agu=1 then "V" ELSE "" END agu, 
        case when e.sep=1 then "V" ELSE "" END sep, case when e.okt=1 then "V" ELSE "" END okt, 
        case when e.nov=1 then "V" ELSE "" END nov, case when e.des=1 then "V" ELSE "" END des 
        FROM activity_detail e
        LEFT JOIN activity_data a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN program g ON g.id=s.program_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN account c ON c.id=e.account_id
        WHERE p.unit_id=:unit_id AND p.tahun=:periode 
        ORDER BY g.id, s.id, v.id, a.id')
        
        ->bindValue(':unit_id', Yii::$app->user->identity->unit_id)
        ->bindValue(':periode', $period)
        ->queryAll();

        $program = '';
        $pelayanan = '';
        $jeniskegiatan = '';
        $kegiatan = '';
        foreach ($data as $row) {
            $exportprogram =  new Exportprogram();
   
            $exportprogram->nama_program=$row['nama_program'];
            
            if ($pelayanan !== $row['nama_pelayanan']) {
                $exportprogram->nama_pelayanan=$row['nama_pelayanan']; $pelayanan = $row['nama_pelayanan'];
            } 

            if ($jeniskegiatan !== $row['nama_kegiatan']) {
                $exportprogram->nama_kegiatan=$row['nama_kegiatan']; $jeniskegiatan = $row['nama_kegiatan'];
            }

            if ($kegiatan !== $row['bentuk_kegiatan']) {
                $exportprogram->bentuk_kegiatan=$row['bentuk_kegiatan']; $kegiatan = $row['bentuk_kegiatan'];
                $exportprogram->sasaran=$row['sasaran'];
                $exportprogram->target=$row['target'];
                $exportprogram->lokasi=$row['lokasi'];
                $exportprogram->pelaksana=$row['pelaksana'];
            }
            
            // if(empty($row['rincian'])){
            //     $exportprogram->nama_rekening=$row['nama_rekening'];
            // }else{
            //     $exportprogram->nama_rekening=$row['nama_rekening'].' ('.$row['rincian'].')';
            // }

            $exportprogram->nama_rekening=$row['nama_rekening'];
            $exportprogram->rincian=$row['rincian'];

            $exportprogram->vol_1=$row['vol_1'];
            $exportprogram->satuan_1=$row['satuan_1'];
            $exportprogram->vol_2=$row['vol_2'];
            $exportprogram->satuan_2=$row['satuan_2'];

            $exportprogram->vol_3=$row['vol_3'];
            $exportprogram->satuan_3=$row['satuan_3'];
            $exportprogram->vol_4=$row['vol_4'];
            $exportprogram->satuan_4=$row['satuan_4'];

            $exportprogram->vol=$row['vol'];
            $exportprogram->unit_cost=$row['unit_cost'];
            $exportprogram->jumlah=$row['jumlah'];
            $exportprogram->jan=$row['jan'];
            $exportprogram->feb=$row['feb'];
            $exportprogram->mar=$row['mar'];
            $exportprogram->apr=$row['apr'];
            $exportprogram->mei=$row['mei'];
            $exportprogram->jun=$row['jun'];
            $exportprogram->jul=$row['jul'];
            $exportprogram->agu=$row['agu'];
            $exportprogram->sep=$row['sep'];
            $exportprogram->okt=$row['okt'];
            $exportprogram->nov=$row['nov'];
            $exportprogram->des=$row['des'];
            $exportprogram->username=Yii::$app->user->identity->username;
            $exportprogram->period=$period;
            $exportprogram->save();
        }

        //Sheet 2
        $program = '';
        $spreadsheet->setActiveSheetIndex(1);
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setCellValue('A'.'1', 'POA BANTUAN OPERSIONAL KESEHATAN ');
        $activeSheet->setCellValue('A'.'2', strtoupper(Yii::$app->user->identity->alias));
        $activeSheet->setCellValue('A'.'3', 'TAHUN ' .$period);

        // $dataprogram = Yii::$app->db->createCommand('SELECT * FROM program where id<>0')
        $dataprogram = Yii::$app->db->createCommand('SELECT p.* FROM program p
        RIGHT JOIN export_program e ON e.nama_program=p.nama_program
        WHERE tahun=:tahun AND e.username=:username
        GROUP BY p.nama_program
        ORDER BY p.id')
        ->bindValue(':tahun', $period)
        ->bindValue(':username', Yii::$app->user->identity->username)
        ->queryAll();

        $baseRowAwal = 0;
        $baseRowProgram = 5;
        $baseRowService = 0;

        $styleArrayHeader = [
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        
        foreach ($dataprogram as $dataprogram) {
            
            $dataExcel = Yii::$app->db->createCommand('SELECT e.*, p.id, CASE WHEN v.modul="P" THEN NULL ELSE "DRAFT" END status FROM export_program e
            LEFT JOIN program p ON p.nama_program=e.nama_program AND p.tahun=:periodValue
            LEFT JOIN verification v ON v.program_id=p.id AND v.unit_id=:unitId
            where e.nama_program=:namaprogram AND username=:username AND period=:periodValue ')
            ->bindValue(':unitId', Yii::$app->user->identity->unit_id)
            ->bindValue(':username', Yii::$app->user->identity->username)
            ->bindValue(':periodValue', $period)
            ->bindValue(':namaprogram', $dataprogram['nama_program'])
            ->queryAll();

            $count = count($dataExcel);

            $baseRowAwal = $baseRowAwal+1;
            $tabletitle = $baseRowProgram+2;
            if ($baseRowAwal > 1) {
                $baseRowProgram = $baseRowProgram+1;
                $activeSheet->setCellValue('A'.$baseRowProgram, $baseRowAwal) 
                ->setCellValue('C'.$baseRowProgram, $dataprogram['nama_program']);
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowProgram. ':C' .$baseRowProgram)->applyFromArray($styleArrayBold);

                if ($count > 0) {
                $activeSheet->setCellValue('A'.$tabletitle, 'No')
                ->setCellValue('C'.$tabletitle, '=C6')
                ->setCellValue('D'.$tabletitle, '=D6')
                ->setCellValue('E'.$tabletitle, '=E6')
                ->setCellValue('F'.$tabletitle, '=F6')
                ->setCellValue('G'.$tabletitle, '=G6')
                ->setCellValue('H'.$tabletitle, '=H6')
                ->setCellValue('I'.$tabletitle, '=I6')
                ->setCellValue('J'.$tabletitle, '=J6')
                ->setCellValue('K'.$tabletitle, '=K6')
                ->setCellValue('L'.$tabletitle, '=L6')
                // ->setCellValue('M'.$tabletitle, '=M6')
                // ->setCellValue('N'.$tabletitle, '=N6')
                // ->setCellValue('O'.$tabletitle, '=O6')
                // ->setCellValue('P'.$tabletitle, '=P6')
                // ->setCellValue('Q'.$tabletitle, '=Q6')

                // ->setCellValue('R'.$tabletitle, '=R6')
                // ->setCellValue('S'.$tabletitle, '=S6')
                // ->setCellValue('T'.$tabletitle, '=T6')
                // ->setCellValue('U'.$tabletitle, '=U6')
                // ->setCellValue('V'.$tabletitle, '=V6')
                // ->setCellValue('W'.$tabletitle, '=W6')

                // ->setCellValue('X'.$tabletitle, '=X6')
                ->setCellValue('Y'.$tabletitle, '=Y6')
                ->setCellValue('Z'.$tabletitle, '=Z6')
                ->setCellValue('AA'.$tabletitle, '=AA6')
                ->setCellValue('AB'.$tabletitle, '=AB6')
                ->setCellValue('AC'.$tabletitle, '=AC6')
                ->setCellValue('AD'.$tabletitle, '=AD6')
                ->setCellValue('AE'.$tabletitle, '=AE6')
                
                ->setCellValue('AF'.$tabletitle, '=AF6')
                ->setCellValue('AG'.$tabletitle, '=AG6')
                ->setCellValue('AH'.$tabletitle, '=AH6')
                ->setCellValue('AI'.$tabletitle, '=AI6')
                ->setCellValue('AJ'.$tabletitle, '=AJ6')
                ->setCellValue('AK'.$tabletitle, '=AK6')
                ->setCellValue('AL'.$tabletitle, '=AL6'); 
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowProgram. ':C' .$baseRowProgram)->applyFromArray($styleArrayBold);
                $spreadsheet->getActiveSheet()->mergeCells('L'.$tabletitle. ':X' .$tabletitle);
                $spreadsheet->getActiveSheet()->getStyle('A'.$tabletitle. ':AL' .$tabletitle)->applyFromArray($styleArrayHeader);
                $activeSheet->getStyle('A'.$tabletitle. ':AL' .$tabletitle)->getAlignment()->setHorizontal('center'); 
                $activeSheet->getStyle('A'.$tabletitle. ':AL' .$tabletitle)->getAlignment()->setWrapText(true);
                $activeSheet->getStyle('A'.$tabletitle. ':AL' .$tabletitle)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F8CBAD');
                }
            }else{
                $activeSheet->setCellValue('A'.$baseRowProgram, $baseRowAwal)
                ->setCellValue('C'.$baseRowProgram, $dataprogram['nama_program']); 

                $activeSheet->getStyle('A6:AL6')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F8CBAD');
            }
                 
            $baseRowService = 0;

            $baseRow = $baseRowProgram+2;
            $firstData = $baseRowProgram+2;
            $rowAkhir = '';
            
            if ($count > 0) {
                foreach($dataExcel as $rowExcel) {
                    if ($rowAkhir === $baseRowAwal) {
                        $rowAkhir = '';
                    }else{
                        $rowAkhir = $baseRowAwal;
                    }
                    $activeSheet
                                ->setCellValue('A'.$baseRow, $rowAkhir)
                                ->setCellValue('C'.$baseRow, $rowExcel['nama_pelayanan'])
                                ->setCellValue('D'.$baseRow, $rowExcel['nama_kegiatan'])
                                ->setCellValue('E'.$baseRow, $rowExcel['bentuk_kegiatan'])
                                ->setCellValue('F'.$baseRow, $rowExcel['sasaran'])
                                ->setCellValue('G'.$baseRow, $rowExcel['target'])
                                ->setCellValue('H'.$baseRow, $rowExcel['lokasi'])
                                ->setCellValue('I'.$baseRow, $rowExcel['pelaksana'])
                                ->setCellValue('J'.$baseRow, $rowExcel['nama_rekening'])
                                ->setCellValue('K'.$baseRow, $rowExcel['rincian'])
                                ->setCellValue('L'.$baseRow, $rowExcel['vol_1'])
                                ->setCellValue('M'.$baseRow, $rowExcel['satuan_1'])
                                ->setCellValue('N'.$baseRow, 'x')
                                ->setCellValue('O'.$baseRow, $rowExcel['vol_2'])
                                ->setCellValue('P'.$baseRow, $rowExcel['satuan_2'])
                                ->setCellValue('Q'.$baseRow, 'x')
                                ->setCellValue('R'.$baseRow, $rowExcel['vol_3'])
                                ->setCellValue('S'.$baseRow, $rowExcel['satuan_3'])
                                ->setCellValue('T'.$baseRow, 'x')
                                ->setCellValue('U'.$baseRow, $rowExcel['vol_4'])
                                ->setCellValue('V'.$baseRow, $rowExcel['satuan_4'])
                                ->setCellValue('W'.$baseRow, '=')
                                ->setCellValue('X'.$baseRow, $rowExcel['vol'])

                                ->setCellValue('Y'.$baseRow, $rowExcel['unit_cost'])
                                ->setCellValue('Z'.$baseRow, $rowExcel['jumlah'])
                                ->setCellValue('AA'.$baseRow, $rowExcel['jan'])
                                ->setCellValue('AB'.$baseRow, $rowExcel['feb'])
                                ->setCellValue('AC'.$baseRow, $rowExcel['mar'])
                                ->setCellValue('AD'.$baseRow, $rowExcel['apr'])
                                ->setCellValue('AE'.$baseRow, $rowExcel['mei'])
                                ->setCellValue('AF'.$baseRow, $rowExcel['jun'])
                                
                                ->setCellValue('AG'.$baseRow, $rowExcel['jul'])
                                ->setCellValue('AH'.$baseRow, $rowExcel['agu'])
                                ->setCellValue('AI'.$baseRow, $rowExcel['sep'])
                                ->setCellValue('AJ'.$baseRow, $rowExcel['okt'])
                                ->setCellValue('AK'.$baseRow, $rowExcel['nov'])
                                ->setCellValue('AL'.$baseRow, $rowExcel['des']);  
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AL' .$baseRow)->applyFromArray($styleArray);
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AL' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setVertical('top'); 
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AL' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setWrapText(true);
                                            
                    $baseRow++;
                    $baseRowService = $baseRowService+1;
                    $rowAkhir = $baseRowAwal;
                }
                
                
                $activeSheet->setCellValue('A'.$baseRow, 'Total');
                $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRow. ':Y' .$baseRow);
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AL' .$baseRow)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':Z' .$baseRow)->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFF00');
                
                $lastData = $baseRow-1;
                
                $activeSheet->setCellValue('Z'.$baseRow, '=SUM(Z' .$firstData. ':Z' .$lastData. ')'); $baseRowProgram=$baseRowProgram+1;
                $baseRowProgram=$baseRowProgram+$baseRowService+3;


                if ($baseRowAwal!=$countProgram){
                    $spreadsheet->getActiveSheet()->getStyle('X:Z')->getAlignment()->setHorizontal('center');
                    $spreadsheet->getActiveSheet()->getStyle('X:Z')->getFont()->setBold( true );

                    $styleArrayDraft = [
                        'font' => [
                            'bold' => true,
                            'size' => 30,
                        ],
                    ];
    
                    $baseRowDraft = $baseRow+1;
                    $baseRowDraft2 = $baseRowDraft+1;
                    $activeSheet->setCellValue('A'.$baseRowDraft, $rowExcel['status']);
                    if($rowExcel['status'] == 'DRAFT'){
                        $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowDraft. ':C' .$baseRowDraft2);
                        $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowDraft. ':C' .$baseRowDraft2)->getFill()
                                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                    ->getStartColor()->setARGB('BA0101');
                        $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowDraft. ':C' .$baseRowDraft2)->getAlignment()->setHorizontal('center');
                        $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowDraft. ':C' .$baseRowDraft)->applyFromArray($styleArrayDraft);
                    }

                    $activeSheet->setCellValue('X'.$baseRowProgram, 'Mengetahui'); 
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;
                    $activeSheet->setCellValue('X'.$baseRowProgram, 'Kepala UPTD ' .Yii::$app->user->identity->alias); 
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+4;
                    $activeSheet->setCellValue('X'.$baseRowProgram, $unit->kepala); 
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;
                    $activeSheet->setCellValue('X'.$baseRowProgram, 'NIP. ' .$unit->nip_kepala); 
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                }else{
                    $total = Yii::$app->db->createCommand('SELECT SUM(e.jumlah) jumlah
                    FROM activity_detail e LEFT JOIN activity_data a ON a.id=e.activity_data_id LEFT JOIN activity v ON v.id=a.activity_id
                    LEFT JOIN service s ON s.id=v.service_id LEFT JOIN program g ON g.id=s.program_id LEFT JOIN period p ON p.id=a.period_id
                    LEFT JOIN account c ON c.id=e.account_id WHERE p.unit_id=:unit_id AND p.tahun=:periode')
                    ->bindValue(':unit_id', Yii::$app->user->identity->unit_id)
                    ->bindValue(':periode', $period)
                    ->queryAll();

                    foreach ($total as $rowTotal);

                    $baseRow = $baseRow+1;
                    $activeSheet->setCellValue('A'.$baseRow, 'Total Semua Program');
                    $activeSheet->setCellValue('Z'.$baseRow, $rowTotal['jumlah']);
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRow. ':Y' .$baseRow);
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AL' .$baseRow)->applyFromArray($styleArray);
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':Z' .$baseRow)->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('0F9D58');

                    $spreadsheet->getActiveSheet()->getStyle('F:L')->getAlignment()->setHorizontal('center');
                    $spreadsheet->getActiveSheet()->getStyle('R:T')->getAlignment()->setHorizontal('center');

                    $spreadsheet->getActiveSheet()->getStyle('F:L')->getFont()->setBold( true );
                    $spreadsheet->getActiveSheet()->getStyle('R:T')->getFont()->setBold( true );

                    $baseRowProgram=$baseRowProgram+2;

                    $styleArrayDraft = [
                        'font' => [
                            'bold' => true,
                            'size' => 30,
                        ],
                    ];
    
                    $baseRowDraft = $baseRow+1;
                    $baseRowDraft2 = $baseRowDraft+1;
                    $activeSheet->setCellValue('A'.$baseRowDraft, $rowExcel['status']);
                    if($rowExcel['status'] == 'DRAFT'){
                        $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowDraft. ':C' .$baseRowDraft2);
                        $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowDraft. ':C' .$baseRowDraft2)->getFill()
                                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                    ->getStartColor()->setARGB('BA0101');
                        $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowDraft. ':C' .$baseRowDraft2)->getAlignment()->setHorizontal('center');
                        $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowDraft. ':C' .$baseRowDraft)->applyFromArray($styleArrayDraft);
                    }

                    $profile = Profile::find()->all();
                    foreach ($profile as $dataProfile);

                    $activeSheet->setCellValue('F'.$baseRowProgram, 'Mengetahui,'); 
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;

                    $activeSheet->setCellValue('A'.$baseRowProgram, 'Kepala UPTD ' .Yii::$app->user->identity->alias); 
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    $activeSheet->setCellValue('F'.$baseRowProgram, 'Sekretaris ' .$dataProfile->nama. ' '.$dataProfile->kota_kab); 
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    $activeSheet->setCellValue('W'.$baseRowProgram, 'PPTK BOK'); 
                    $spreadsheet->getActiveSheet()->mergeCells('W'.$baseRowProgram. ':Y' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+4;

                    $activeSheet->setCellValue('A'.$baseRowProgram, $unit->kepala); 
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    $activeSheet->setCellValue('F'.$baseRowProgram, $dataProfile->sekretaris); 
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    $activeSheet->setCellValue('X'.$baseRowProgram, $unit->petugas); 
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;

                    // $activeSheet->setCellValue('A'.$baseRowProgram, $unit->kepala); 
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    $activeSheet->setCellValue('F'.$baseRowProgram, $dataProfile->jabatan_sekretaris); 
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    $activeSheet->setCellValue('X'.$baseRowProgram, $unit->jabatan_petugas); 
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;

                    $activeSheet->setCellValue('A'.$baseRowProgram, 'NIP. ' .$unit->nip_kepala);
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    $activeSheet->setCellValue('F'.$baseRowProgram, 'NIP. ' .$dataProfile->nip_sekretaris);
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    $activeSheet->setCellValue('X'.$baseRowProgram, 'NIP. ' .$unit->nip_petugas);
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);

                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AK' .$baseRow)->applyFromArray($styleArrayBold);
                }
                $baseRowProgram=$baseRowProgram+20;

                $baseRowProgram++;   
            }else{
                // $baseRowProgram = $baseRowProgram+2;    
                $baseRowProgram++; 
            }   
             
        }

        $spreadsheet->getSecurity()->setLockWindows(true);
        $spreadsheet->getSecurity()->setLockStructure(true);
        $spreadsheet->getSecurity()->setWorkbookPassword("silverblack");

        $spreadsheet->getActiveSheet()
            ->getProtection()->setPassword('silverblack');
        $spreadsheet->getActiveSheet()
            ->getProtection()->setSheet(true);
        $spreadsheet->getActiveSheet()
            ->getProtection()->setSort(true);
        $spreadsheet->getActiveSheet()
            ->getProtection()->setInsertRows(true);
        $spreadsheet->getActiveSheet()
            ->getProtection()->setFormatCells(true);

        $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);

        $spreadsheet->setActiveSheetIndex(0);

        $spreadsheet->getSecurity()->setLockWindows(true);
        $spreadsheet->getSecurity()->setLockStructure(true);
        $spreadsheet->getSecurity()->setWorkbookPassword("silverblack");

        $spreadsheet->getActiveSheet()
            ->getProtection()->setPassword('silverblack');
        $spreadsheet->getActiveSheet()
            ->getProtection()->setSheet(true);
        $spreadsheet->getActiveSheet()
            ->getProtection()->setSort(true);
        $spreadsheet->getActiveSheet()
            ->getProtection()->setInsertRows(true);
        $spreadsheet->getActiveSheet()
            ->getProtection()->setFormatCells(true);

        $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
        

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_poa_'.$period. '_'.Yii::$app->user->identity->username.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');

        // $mpdf = new \Mpdf\Mpdf();
        // $mpdf->WriteHTML('<h1>Hello world!</h1>');
        // $mpdf->Output();

        // $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Mpdf');
        // $writer->writeAllSheets();
        // header('Content-Type: application/pdf');
        // header('Content-Disposition: attachment;filename="_export.pdf"');
        // header('Cache-Control: max-age=0');
        // $writer->save('php://output');

        exit;
    }

    public function actionExportxlsadm($unit_id)
    {
        // $session = Yii::$app->session;
        // $period = $session['periodValue'];

        $session = Yii::$app->session;
        $period = $session['periodValue'];

        $unitnya = User::find()
        ->where(['unit_id' => $unit_id])
        ->one();

        $akun = Yii::$app->db->createCommand('SELECT c.id, c.nama_rekening, sum(e.jumlah) jumlah
        FROM activity_detail e
        LEFT JOIN activity_data a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN program g ON g.id=s.program_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN account c ON c.id=e.account_id
        WHERE p.unit_id=:unit_id AND p.tahun=:periode AND jumlah > 0
        group BY c.nama_rekening ORDER BY c.id')
        ->bindValue(':unit_id', $unit_id)
        ->bindValue(':periode', $period)
        ->queryAll();

        $col = 'C';
        foreach($akun as $acc) {
            $col++;
        }

        $jmlakun = count($akun);

        $inputFileName = Yii::getAlias('@app/views/exportaccount').'/_export.xlsx';

        /** Load $inputFileName to a Spreadsheet Object  **/
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        // $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $styleArrayBold = [
            'font' => [
                'bold' => true,
            ],
        ];

        // //set orientasi dan ukurankertas
        // $activeSheet->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE)
        //                             ->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_FOLIO);
        
        $activeSheet->setCellValue('A'.'1', 'POA DAK BOK TAHUNAN');
            $spreadsheet->getActiveSheet()->mergeCells('A1'. ':' .$col.'1');
            $activeSheet->getStyle('A1'. ':' .$col.'1')->getAlignment()->setHorizontal('center'); 
            $activeSheet->getStyle('A1'. ':' .$col.'1')->getAlignment()->setWrapText(true);
        $activeSheet->setCellValue('A'.'2', 'UPTD ' .strtoupper($unitnya->alias));
            $spreadsheet->getActiveSheet()->mergeCells('A2'. ':' .$col.'2');
            $activeSheet->getStyle('A2'. ':' .$col.'2')->getAlignment()->setHorizontal('center'); 
            $activeSheet->getStyle('A2'. ':' .$col.'2')->getAlignment()->setWrapText(true);
        $activeSheet->setCellValue('A'.'3', 'TAHUN ' .$period);
            $spreadsheet->getActiveSheet()->mergeCells('A3'. ':' .$col.'3');
            $activeSheet->getStyle('A3'. ':' .$col.'3')->getAlignment()->setHorizontal('center'); 
            $activeSheet->getStyle('A3'. ':' .$col.'3')->getAlignment()->setWrapText(true);

        $col = 'C';
        foreach($akun as $acc) {
                $activeSheet->setCellValue($col++.'5', $acc['nama_rekening']);
                $lcol = $col--;
        }
        $activeSheet->setCellValue($col++.'5', 'JUMLAH');
        $spreadsheet->getActiveSheet()->getStyle('C'.'5'. ':' .$lcol.'5')->applyFromArray($styleArray)->getAlignment()->setWrapText(true)->setVertical('center');

        // $program = Yii::$app->db->createCommand('SELECT g.id, g.nama_program program FROM program g WHERE g.tahun=2022 AND g.aktif=1')
        // ->bindValue(':periode', $period)
        // ->queryAll();

        $program = Yii::$app->db->createCommand('SELECT g.id, g.nama_program program, sum(e.jumlah) jumlah
        FROM activity_detail e
        LEFT JOIN activity_data a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN program g ON g.id=s.program_id
        LEFT JOIN period p ON p.id=a.period_id
        WHERE p.unit_id=:unit_id AND p.tahun=:periode AND jumlah > 0 AND g.aktif=1
        group BY g.nama_program ORDER BY g.id')
        ->bindValue(':unit_id', $unit_id)
        ->bindValue(':periode', $period)
        ->queryAll();

        $countProgram= count($program);

        $baseRow=6;
        $firstData=6;
        foreach($program as $prg) {
            $activeSheet->setCellValue('A'.$baseRow, $baseRow-5)
                        ->setCellValue('B'.$baseRow, $prg['program']);

            $col = 'C';
            $colnum = '1';
            foreach($akun as $acc) {
                $data = Yii::$app->db->createCommand('SELECT s.program_id, IFNULL(sum(e.jumlah),0) jumlah FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id=:account_id AND s.program_id=:program_id')
                ->bindValue(':unit_id', $unit_id)
                ->bindValue(':periode', $period)
                ->bindValue(':account_id', $acc['id'])
                ->bindValue(':program_id', $prg['id'])
                ->queryAll();

                foreach($data as $dt);
                $lastcol = $col;
                $activeSheet->setCellValue($col++.$baseRow, $dt['jumlah']);
                $colnum++;
            }
            $spreadsheet->getActiveSheet()->getStyle($col.$baseRow)->getFont()->setBold( true );
            $activeSheet->setCellValue($col++.$baseRow, '=SUM(C'.$baseRow. ':'.$lastcol.$baseRow. ')');
            
            $baseRow++;
        }

        $colsum = 'C';
        $lastData=$baseRow-1;
        $activeSheet->setCellValue('B'.$baseRow, 'JUMLAH');
        for ($i = 1; $i <= $colnum; $i++) {
            $activeSheet->setCellValue($colsum.$baseRow, '=SUM('.$colsum.$firstData. ':'.$colsum.$lastData. ')');
            $lastcolsum = $colsum;
            $colsum++;
        }

        $spreadsheet->getActiveSheet()->getStyle('B'.$baseRow. ':'.$lastcolsum.$baseRow)->getFont()->setBold( true );

        $spreadsheet->getActiveSheet()->getStyle('A5:'.$lastcolsum.$baseRow)->applyFromArray($styleArray);

        $unit = Unit::findOne($unit_id);
        $baseRow=$baseRow+3;

        $activeSheet->setCellValue('Q'.$baseRow, $unit->puskesmas. ',                        ' .$period); 
        $spreadsheet->getActiveSheet()->getStyle('Q'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+1; 

        // $spreadsheet->getActiveSheet()->getStyle('Q:S')->getAlignment()->setHorizontal('center');
        $activeSheet->setCellValue('Q'.$baseRow, 'Mengetahui,'); 
        $spreadsheet->getActiveSheet()->getStyle('Q'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+1;
        $activeSheet->setCellValue('Q'.$baseRow, 'Kepala UPTD ' .$unitnya->alias); 
        $spreadsheet->getActiveSheet()->getStyle('Q'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+4;
        $activeSheet->setCellValue('Q'.$baseRow, $unit->kepala); 
        $spreadsheet->getActiveSheet()->getStyle('Q'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+1;
        $activeSheet->setCellValue('Q'.$baseRow, 'NIP. ' .$unit->nip_kepala);
        $spreadsheet->getActiveSheet()->getStyle('Q'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );

        //QUERY PER PROGRAM
        // Yii::$app->db->createCommand()->truncateTable('export_program')->execute();
        Yii::$app->db->createCommand('DELETE FROM export_program WHERE username=:username AND period=:periodValue ')
        ->bindValue(':username', $unitnya->username)
        ->bindValue(':periodValue', $period)
        ->execute();

        $data = Yii::$app->db->createCommand('SELECT g.nama_program, s.nama_pelayanan, v.nama_kegiatan,  IFNULL(a.bentuk_kegiatan, v.nama_kegiatan) bentuk_kegiatan, 
        a.sasaran, a.target, a.lokasi, a.pelaksana, 
        c.nama_rekening, e.rincian, e.vol_1, e.satuan_1, IFNULL(e.vol_2,"") vol_2, IFNULL(e.satuan_2,"") satuan_2, IFNULL(e.vol_3,"") vol_3, IFNULL(e.satuan_3,"") satuan_3, 
        IFNULL(e.vol_4,"") vol_4, IFNULL(e.satuan_4,"") satuan_4, 
        vol_1*IFNULL(vol_2,1)*IFNULL(vol_3,1)*IFNULL(vol_4,1) vol, e.unit_cost, e.jumlah,  
        case when e.jan=1 then "V" ELSE "" END jan, case when e.feb=1 then "V" ELSE "" END feb, 
        case when e.mar=1 then "V" ELSE "" END mar, case when e.apr=1 then "V" ELSE "" END apr, 
        case when e.mei=1 then "V" ELSE "" END mei, case when e.jun=1 then "V" ELSE "" END jun, 
        case when e.jul=1 then "V" ELSE "" END jul, case when e.agu=1 then "V" ELSE "" END agu, 
        case when e.sep=1 then "V" ELSE "" END sep, case when e.okt=1 then "V" ELSE "" END okt, 
        case when e.nov=1 then "V" ELSE "" END nov, case when e.des=1 then "V" ELSE "" END des 
        FROM activity_detail e
        LEFT JOIN activity_data a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN program g ON g.id=s.program_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN account c ON c.id=e.account_id
        WHERE p.unit_id=:unit_id AND p.tahun=:periode
        ORDER BY g.id, s.id, v.id, a.id')
        ->bindValue(':unit_id', $unit_id)
        ->bindValue(':periode', $period)
        ->queryAll();

        $program = '';
        $pelayanan = '';
        $jeniskegiatan = '';
        $kegiatan = '';
        foreach ($data as $row) {
            $exportprogram =  new Exportprogram();
   
            $exportprogram->nama_program=$row['nama_program'];
            if ($pelayanan !== $row['nama_pelayanan']) {
                $exportprogram->nama_pelayanan=$row['nama_pelayanan']; $pelayanan = $row['nama_pelayanan'];
            } 

            if ($jeniskegiatan !== $row['nama_kegiatan']) {
                $exportprogram->nama_kegiatan=$row['nama_kegiatan']; $jeniskegiatan = $row['nama_kegiatan'];
            }

            if ($kegiatan !== $row['bentuk_kegiatan']) {
                $exportprogram->bentuk_kegiatan=$row['bentuk_kegiatan']; $kegiatan = $row['bentuk_kegiatan'];
                $exportprogram->sasaran=$row['sasaran'];
                $exportprogram->target=$row['target'];
                $exportprogram->lokasi=$row['lokasi'];
                $exportprogram->pelaksana=$row['pelaksana'];
            }
            // if(empty($row['rincian'])){
            //     $exportprogram->nama_rekening=$row['nama_rekening'];
            // }else{
            //     $exportprogram->nama_rekening=$row['nama_rekening'].' ('.$row['rincian'].')';
            // }

            $exportprogram->nama_rekening=$row['nama_rekening'];
            $exportprogram->rincian=$row['rincian'];

            $exportprogram->vol_1=$row['vol_1'];
            $exportprogram->satuan_1=$row['satuan_1'];
            $exportprogram->vol_2=$row['vol_2'];
            $exportprogram->satuan_2=$row['satuan_2'];

            $exportprogram->vol_3=$row['vol_3'];
            $exportprogram->satuan_3=$row['satuan_3'];
            $exportprogram->vol_4=$row['vol_4'];
            $exportprogram->satuan_4=$row['satuan_4'];

            $exportprogram->vol=$row['vol'];
            $exportprogram->unit_cost=$row['unit_cost'];
            $exportprogram->jumlah=$row['jumlah'];
            $exportprogram->jan=$row['jan'];
            $exportprogram->feb=$row['feb'];
            $exportprogram->mar=$row['mar'];
            $exportprogram->apr=$row['apr'];
            $exportprogram->mei=$row['mei'];
            $exportprogram->jun=$row['jun'];
            $exportprogram->jul=$row['jul'];
            $exportprogram->agu=$row['agu'];
            $exportprogram->sep=$row['sep'];
            $exportprogram->okt=$row['okt'];
            $exportprogram->nov=$row['nov'];
            $exportprogram->des=$row['des'];
            $exportprogram->username=$unitnya->username;
            $exportprogram->period=$period;
            $exportprogram->save();
        }

        //Sheet 2

        $program = '';
        $spreadsheet->setActiveSheetIndex(1);
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setCellValue('A'.'1', 'POA BANTUAN OPERSIONAL KESEHATAN ');
        $activeSheet->setCellValue('A'.'2', strtoupper($unitnya->alias));
        $activeSheet->setCellValue('A'.'3', 'TAHUN ' .$period);

        // $dataprogram = Yii::$app->db->createCommand('SELECT * FROM program where id<>0')
        $dataprogram = Yii::$app->db->createCommand('SELECT p.* FROM program p
        RIGHT JOIN export_program e ON e.nama_program=p.nama_program
        WHERE tahun=:tahun AND e.username=:username
        GROUP BY p.nama_program
        ORDER BY p.id')
        ->bindValue(':tahun', $period)
        ->bindValue(':username', $unitnya->username)
        ->queryAll();

        $baseRowAwal = 0;
        $baseRowProgram = 5;
        $baseRowService = 0;

        $styleArrayHeader = [
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        
        foreach ($dataprogram as $dataprogram) {
            
            // $dataExcel = Yii::$app->db->createCommand('SELECT * FROM export_program where nama_program=:namaprogram AND username=:username AND period=:periodValue ')
            // ->bindValue(':username', $unitnya->username)
            // ->bindValue(':periodValue', $period)
            // ->bindValue(':namaprogram', $dataprogram['nama_program'])
            // ->queryAll();

            $dataExcel = Yii::$app->db->createCommand('SELECT e.*, p.id, CASE WHEN v.modul="P" THEN NULL ELSE "DRAFT" END status FROM export_program e
            LEFT JOIN program p ON p.nama_program=e.nama_program AND p.tahun=:periodValue
            LEFT JOIN verification v ON v.program_id=p.id AND v.unit_id=:unitId
            where e.nama_program=:namaprogram AND username=:username AND period=:periodValue ')
            ->bindValue(':unitId', $unit_id)
            ->bindValue(':username', $unitnya->username)
            ->bindValue(':periodValue', $period)
            ->bindValue(':namaprogram', $dataprogram['nama_program'])
            ->queryAll();

            $count = count($dataExcel);

            $baseRowAwal = $baseRowAwal+1;
            $tabletitle = $baseRowProgram+2;

            if ($baseRowAwal > 1) {
                $baseRowProgram = $baseRowProgram+1;
                $activeSheet->setCellValue('A'.$baseRowProgram, $baseRowAwal) 
                ->setCellValue('C'.$baseRowProgram, $dataprogram['nama_program']);
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowProgram. ':C' .$baseRowProgram)->applyFromArray($styleArrayBold);

                if ($count > 0) {
                $activeSheet->setCellValue('A'.$tabletitle, 'No')
                ->setCellValue('C'.$tabletitle, '=C6')
                ->setCellValue('D'.$tabletitle, '=D6')
                ->setCellValue('E'.$tabletitle, '=E6')
                ->setCellValue('F'.$tabletitle, '=F6')
                ->setCellValue('G'.$tabletitle, '=G6')
                ->setCellValue('H'.$tabletitle, '=H6')
                ->setCellValue('I'.$tabletitle, '=I6')
                ->setCellValue('J'.$tabletitle, '=J6')
                ->setCellValue('K'.$tabletitle, '=K6')
                ->setCellValue('L'.$tabletitle, '=L6')
                // ->setCellValue('M'.$tabletitle, '=M6')
                // ->setCellValue('N'.$tabletitle, '=N6')
                // ->setCellValue('O'.$tabletitle, '=O6')
                // ->setCellValue('P'.$tabletitle, '=P6')
                // ->setCellValue('Q'.$tabletitle, '=Q6')

                // ->setCellValue('R'.$tabletitle, '=R6')
                // ->setCellValue('S'.$tabletitle, '=S6')
                // ->setCellValue('T'.$tabletitle, '=T6')
                // ->setCellValue('U'.$tabletitle, '=U6')
                // ->setCellValue('V'.$tabletitle, '=V6')
                // ->setCellValue('W'.$tabletitle, '=W6')

                // ->setCellValue('X'.$tabletitle, '=X6')
                ->setCellValue('Y'.$tabletitle, '=Y6')
                ->setCellValue('Z'.$tabletitle, '=Z6')
                ->setCellValue('AA'.$tabletitle, '=AA6')
                ->setCellValue('AB'.$tabletitle, '=AB6')
                ->setCellValue('AC'.$tabletitle, '=AC6')
                ->setCellValue('AD'.$tabletitle, '=AD6')
                ->setCellValue('AE'.$tabletitle, '=AE6')
                
                ->setCellValue('AF'.$tabletitle, '=AF6')
                ->setCellValue('AG'.$tabletitle, '=AG6')
                ->setCellValue('AH'.$tabletitle, '=AH6')
                ->setCellValue('AI'.$tabletitle, '=AI6')
                ->setCellValue('AJ'.$tabletitle, '=AJ6')
                ->setCellValue('AK'.$tabletitle, '=AK6')
                ->setCellValue('AL'.$tabletitle, '=AL6'); 
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowProgram. ':C' .$baseRowProgram)->applyFromArray($styleArrayBold);
                $spreadsheet->getActiveSheet()->mergeCells('L'.$tabletitle. ':X' .$tabletitle);
                $spreadsheet->getActiveSheet()->getStyle('A'.$tabletitle. ':AL' .$tabletitle)->applyFromArray($styleArrayHeader);
                $activeSheet->getStyle('A'.$tabletitle. ':AL' .$tabletitle)->getAlignment()->setHorizontal('center'); 
                $activeSheet->getStyle('A'.$tabletitle. ':AL' .$tabletitle)->getAlignment()->setWrapText(true);
                $activeSheet->getStyle('A'.$tabletitle. ':AL' .$tabletitle)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F8CBAD');
                }
            }else{
                $activeSheet->setCellValue('A'.$baseRowProgram, $baseRowAwal)
                ->setCellValue('C'.$baseRowProgram, $dataprogram['nama_program']); 

                $activeSheet->getStyle('A6:AL6')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F8CBAD');
            }
                 
            $baseRowService = 0;

            $baseRow = $baseRowProgram+2;
            $firstData = $baseRowProgram+2;
            $rowAkhir = '';
            
            if ($count > 0) {
                foreach($dataExcel as $rowExcel) {
                    if ($rowAkhir === $baseRowAwal) {
                        $rowAkhir = '';
                    }else{
                        $rowAkhir = $baseRowAwal;
                    }
                    $activeSheet
                                ->setCellValue('A'.$baseRow, $rowAkhir)
                                ->setCellValue('C'.$baseRow, $rowExcel['nama_pelayanan'])
                                ->setCellValue('D'.$baseRow, $rowExcel['nama_kegiatan'])
                                ->setCellValue('E'.$baseRow, $rowExcel['bentuk_kegiatan'])
                                ->setCellValue('F'.$baseRow, $rowExcel['sasaran'])
                                ->setCellValue('G'.$baseRow, $rowExcel['target'])
                                ->setCellValue('H'.$baseRow, $rowExcel['lokasi'])
                                ->setCellValue('I'.$baseRow, $rowExcel['pelaksana'])
                                ->setCellValue('J'.$baseRow, $rowExcel['nama_rekening'])
                                ->setCellValue('K'.$baseRow, $rowExcel['rincian'])
                                ->setCellValue('L'.$baseRow, $rowExcel['vol_1'])
                                ->setCellValue('M'.$baseRow, $rowExcel['satuan_1'])
                                ->setCellValue('N'.$baseRow, 'x')
                                ->setCellValue('O'.$baseRow, $rowExcel['vol_2'])
                                ->setCellValue('P'.$baseRow, $rowExcel['satuan_2'])
                                ->setCellValue('Q'.$baseRow, 'x')
                                ->setCellValue('R'.$baseRow, $rowExcel['vol_3'])
                                ->setCellValue('S'.$baseRow, $rowExcel['satuan_3'])
                                ->setCellValue('T'.$baseRow, 'x')
                                ->setCellValue('U'.$baseRow, $rowExcel['vol_4'])
                                ->setCellValue('V'.$baseRow, $rowExcel['satuan_4'])
                                ->setCellValue('W'.$baseRow, '=')
                                ->setCellValue('X'.$baseRow, $rowExcel['vol'])

                                ->setCellValue('Y'.$baseRow, $rowExcel['unit_cost'])
                                ->setCellValue('Z'.$baseRow, $rowExcel['jumlah'])
                                ->setCellValue('AA'.$baseRow, $rowExcel['jan'])
                                ->setCellValue('AB'.$baseRow, $rowExcel['feb'])
                                ->setCellValue('AC'.$baseRow, $rowExcel['mar'])
                                ->setCellValue('AD'.$baseRow, $rowExcel['apr'])
                                ->setCellValue('AE'.$baseRow, $rowExcel['mei'])
                                ->setCellValue('AF'.$baseRow, $rowExcel['jun'])
                                
                                ->setCellValue('AG'.$baseRow, $rowExcel['jul'])
                                ->setCellValue('AH'.$baseRow, $rowExcel['agu'])
                                ->setCellValue('AI'.$baseRow, $rowExcel['sep'])
                                ->setCellValue('AJ'.$baseRow, $rowExcel['okt'])
                                ->setCellValue('AK'.$baseRow, $rowExcel['nov'])
                                ->setCellValue('AL'.$baseRow, $rowExcel['des']);  
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AL' .$baseRow)->applyFromArray($styleArray);
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AL' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setVertical('top'); 
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AL' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setWrapText(true);
                                            
                    $baseRow++;
                    $baseRowService = $baseRowService+1;
                    $rowAkhir = $baseRowAwal;
                }
                
                
                $activeSheet->setCellValue('A'.$baseRow, 'Total');
                $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRow. ':Y' .$baseRow);
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AL' .$baseRow)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':Z' .$baseRow)->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFF00');
                
                $lastData = $baseRow-1;
                
                $activeSheet->setCellValue('Z'.$baseRow, '=SUM(Z' .$firstData. ':Z' .$lastData. ')'); $baseRowProgram=$baseRowProgram+1;
                $baseRowProgram=$baseRowProgram+$baseRowService+3;


                if ($baseRowAwal!=$countProgram){
                    $spreadsheet->getActiveSheet()->getStyle('X:Z')->getAlignment()->setHorizontal('center');
                    $spreadsheet->getActiveSheet()->getStyle('X:Z')->getFont()->setBold( true );

                    $styleArrayDraft = [
                        'font' => [
                            'bold' => true,
                            'size' => 30,
                        ],
                    ];
    
                    $baseRowDraft = $baseRow+1;
                    $baseRowDraft2 = $baseRowDraft+1;
                    $activeSheet->setCellValue('A'.$baseRowDraft, $rowExcel['status']);
                    if($rowExcel['status'] == 'DRAFT'){
                        $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowDraft. ':C' .$baseRowDraft2);
                        $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowDraft. ':C' .$baseRowDraft2)->getFill()
                                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                    ->getStartColor()->setARGB('BA0101');
                        $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowDraft. ':C' .$baseRowDraft2)->getAlignment()->setHorizontal('center');
                        $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowDraft. ':C' .$baseRowDraft)->applyFromArray($styleArrayDraft);
                    }

                    $activeSheet->setCellValue('X'.$baseRowProgram, 'Mengetahui'); 
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;
                    $activeSheet->setCellValue('X'.$baseRowProgram, 'Kepala UPTD ' .$unitnya->alias); 
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+4;
                    $activeSheet->setCellValue('X'.$baseRowProgram, $unit->kepala); 
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;
                    $activeSheet->setCellValue('X'.$baseRowProgram, 'NIP. ' .$unit->nip_kepala); 
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                }else{
                    $total = Yii::$app->db->createCommand('SELECT SUM(e.jumlah) jumlah
                    FROM activity_detail e LEFT JOIN activity_data a ON a.id=e.activity_data_id LEFT JOIN activity v ON v.id=a.activity_id
                    LEFT JOIN service s ON s.id=v.service_id LEFT JOIN program g ON g.id=s.program_id LEFT JOIN period p ON p.id=a.period_id
                    LEFT JOIN account c ON c.id=e.account_id WHERE p.unit_id=:unit_id AND p.tahun=:periode')
                    ->bindValue(':unit_id', $unit_id)
                    ->bindValue(':periode', $period)
                    ->queryAll();

                    foreach ($total as $rowTotal);

                    $baseRow = $baseRow+1;
                    $activeSheet->setCellValue('A'.$baseRow, 'Total Semua Program');
                    $activeSheet->setCellValue('Z'.$baseRow, $rowTotal['jumlah']);
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRow. ':Y' .$baseRow);
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AL' .$baseRow)->applyFromArray($styleArray);
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':Z' .$baseRow)->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('0F9D58');

                    $spreadsheet->getActiveSheet()->getStyle('F:L')->getAlignment()->setHorizontal('center');
                    $spreadsheet->getActiveSheet()->getStyle('R:T')->getAlignment()->setHorizontal('center');

                    $spreadsheet->getActiveSheet()->getStyle('F:L')->getFont()->setBold( true );
                    $spreadsheet->getActiveSheet()->getStyle('R:T')->getFont()->setBold( true );

                    $baseRowProgram=$baseRowProgram+2;

                    $styleArrayDraft = [
                        'font' => [
                            'bold' => true,
                            'size' => 30,
                        ],
                    ];
    
                    $baseRowDraft = $baseRow+1;
                    $baseRowDraft2 = $baseRowDraft+1;
                    $activeSheet->setCellValue('A'.$baseRowDraft, $rowExcel['status']);
                    if($rowExcel['status'] == 'DRAFT'){
                        $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowDraft. ':C' .$baseRowDraft2);
                        $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowDraft. ':C' .$baseRowDraft2)->getFill()
                                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                    ->getStartColor()->setARGB('BA0101');
                        $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowDraft. ':C' .$baseRowDraft2)->getAlignment()->setHorizontal('center');
                        $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowDraft. ':C' .$baseRowDraft)->applyFromArray($styleArrayDraft);
                    }

                    $profile = Profile::find()->all();
                    foreach ($profile as $dataProfile);

                    $activeSheet->setCellValue('F'.$baseRowProgram, 'Mengetahui,'); 
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;

                    $activeSheet->setCellValue('A'.$baseRowProgram, 'Kepala UPTD ' .$unitnya->alias); 
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    $activeSheet->setCellValue('F'.$baseRowProgram, 'Sekretaris ' .$dataProfile->nama. ' '.$dataProfile->kota_kab); 
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    $activeSheet->setCellValue('X'.$baseRowProgram, 'PPTK BOK'); 
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+4;

                    $activeSheet->setCellValue('A'.$baseRowProgram, $unit->kepala); 
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    $activeSheet->setCellValue('F'.$baseRowProgram, $dataProfile->sekretaris); 
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    $activeSheet->setCellValue('X'.$baseRowProgram, $unit->petugas); 
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;

                    // $activeSheet->setCellValue('A'.$baseRowProgram, $unit->kepala); 
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    $activeSheet->setCellValue('F'.$baseRowProgram, $dataProfile->jabatan_sekretaris); 
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    $activeSheet->setCellValue('X'.$baseRowProgram, $unit->jabatan_petugas); 
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;

                    $activeSheet->setCellValue('A'.$baseRowProgram, 'NIP. ' .$unit->nip_kepala);
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    $activeSheet->setCellValue('F'.$baseRowProgram, 'NIP. ' .$dataProfile->nip_sekretaris);
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    $activeSheet->setCellValue('X'.$baseRowProgram, 'NIP. ' .$unit->nip_petugas);
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);

                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AK' .$baseRow)->applyFromArray($styleArrayBold);
                }
                $baseRowProgram=$baseRowProgram+20;

                $baseRowProgram++;   
            }else{
                // $baseRowProgram = $baseRowProgram+2;    
                $baseRowProgram++; 
            }   
             
        }

        $spreadsheet->getSecurity()->setLockWindows(true);
        $spreadsheet->getSecurity()->setLockStructure(true);
        $spreadsheet->getSecurity()->setWorkbookPassword("silverblack");

        $spreadsheet->getActiveSheet()
            ->getProtection()->setPassword('silverblack');
        $spreadsheet->getActiveSheet()
            ->getProtection()->setSheet(true);
        $spreadsheet->getActiveSheet()
            ->getProtection()->setSort(true);
        $spreadsheet->getActiveSheet()
            ->getProtection()->setInsertRows(true);
        $spreadsheet->getActiveSheet()
            ->getProtection()->setFormatCells(true);

        $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);

        $spreadsheet->setActiveSheetIndex(0);

        $spreadsheet->getSecurity()->setLockWindows(true);
        $spreadsheet->getSecurity()->setLockStructure(true);
        $spreadsheet->getSecurity()->setWorkbookPassword("silverblack");

        $spreadsheet->getActiveSheet()
            ->getProtection()->setPassword('silverblack');
        $spreadsheet->getActiveSheet()
            ->getProtection()->setSheet(true);
        $spreadsheet->getActiveSheet()
            ->getProtection()->setSort(true);
        $spreadsheet->getActiveSheet()
            ->getProtection()->setInsertRows(true);
        $spreadsheet->getActiveSheet()
            ->getProtection()->setFormatCells(true);

        $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_poa_'.$period. '_'.$unitnya->username.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');

        // $mpdf = new \Mpdf\Mpdf();
        // $mpdf->WriteHTML('<h1>Hello world!</h1>');
        // $mpdf->Output();

        // $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Mpdf');
        // $writer->writeAllSheets();
        // header('Content-Type: application/pdf');
        // header('Content-Disposition: attachment;filename="_export.pdf"');
        // header('Cache-Control: max-age=0');
        // $writer->save('php://output');

        exit;
    }

    public function actionExportxlsall()
    {
        // $session = Yii::$app->session;
        // $period = $session['periodValue'];

        $session = Yii::$app->session;
        $period = $session['periodValue'];

        $akun = Yii::$app->db->createCommand('SELECT c.id, c.nama_rekening, sum(e.jumlah) jumlah
        FROM activity_detail e
        LEFT JOIN activity_data a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN program g ON g.id=s.program_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN account c ON c.id=e.account_id
        WHERE p.tahun=:periode AND jumlah > 0
        group BY c.nama_rekening ORDER BY c.id')
        ->bindValue(':periode', $period)
        ->queryAll();

        $col = 'C';
        foreach($akun as $acc) {
            $col++;
        }

        $jmlakun = count($akun);

        $inputFileName = Yii::getAlias('@app/views/exportaccount').'/_export.xlsx';

        /** Load $inputFileName to a Spreadsheet Object  **/
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        // $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $styleArrayBold = [
            'font' => [
                'bold' => true,
            ],
        ];

        // //set orientasi dan ukurankertas
        // $activeSheet->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE)
        //                             ->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_FOLIO);
        
        $activeSheet->setCellValue('A'.'1', 'POA DAK BOK TAHUNAN');
            $spreadsheet->getActiveSheet()->mergeCells('A1'. ':' .$col.'1');
            $activeSheet->getStyle('A1'. ':' .$col.'1')->getAlignment()->setHorizontal('center'); 
            $activeSheet->getStyle('A1'. ':' .$col.'1')->getAlignment()->setWrapText(true);
        $activeSheet->setCellValue('A'.'2', 'SEMUA PUSKESMAS');
            $spreadsheet->getActiveSheet()->mergeCells('A2'. ':' .$col.'2');
            $activeSheet->getStyle('A2'. ':' .$col.'2')->getAlignment()->setHorizontal('center'); 
            $activeSheet->getStyle('A2'. ':' .$col.'2')->getAlignment()->setWrapText(true);
        $activeSheet->setCellValue('A'.'3', 'TAHUN ' .$period);
            $spreadsheet->getActiveSheet()->mergeCells('A3'. ':' .$col.'3');
            $activeSheet->getStyle('A3'. ':' .$col.'3')->getAlignment()->setHorizontal('center'); 
            $activeSheet->getStyle('A3'. ':' .$col.'3')->getAlignment()->setWrapText(true);

        $col = 'C';
        foreach($akun as $acc) {
                $activeSheet->setCellValue($col++.'5', $acc['nama_rekening']);
                $lcol = $col--;
        }
        $activeSheet->setCellValue($col++.'5', 'JUMLAH');
        $spreadsheet->getActiveSheet()->getStyle('C'.'5'. ':' .$lcol.'5')->applyFromArray($styleArray)->getAlignment()->setWrapText(true)->setVertical('center');

        // $program = Yii::$app->db->createCommand('SELECT g.id, g.nama_program program FROM program g WHERE g.tahun=2022 AND g.aktif=1')
        // ->bindValue(':periode', $period)
        // ->queryAll();

        $program = Yii::$app->db->createCommand('SELECT g.id, g.nama_program program, sum(e.jumlah) jumlah
        FROM activity_detail e
        LEFT JOIN activity_data a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN program g ON g.id=s.program_id
        LEFT JOIN period p ON p.id=a.period_id
        WHERE p.tahun=:periode AND jumlah > 0 AND g.aktif=1
        group BY g.nama_program ORDER BY g.id')
        ->bindValue(':periode', $period)
        ->queryAll();


        $countProgram= count($program);

        $baseRow=6;
        $firstData=6;
        foreach($program as $prg) {
            $activeSheet->setCellValue('A'.$baseRow, $baseRow-5)
                        ->setCellValue('B'.$baseRow, $prg['program']);

            $col = 'C';
            $colnum = '1';
            foreach($akun as $acc) {
                $data = Yii::$app->db->createCommand('SELECT s.program_id, IFNULL(sum(e.jumlah),0) jumlah FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.tahun=:periode AND e.account_id=:account_id AND s.program_id=:program_id')
                ->bindValue(':periode', $period)
                ->bindValue(':account_id', $acc['id'])
                ->bindValue(':program_id', $prg['id'])
                ->queryAll();

                foreach($data as $dt);
                $lastcol = $col;
                $activeSheet->setCellValue($col++.$baseRow, $dt['jumlah']);
                $colnum++;
            }
            $spreadsheet->getActiveSheet()->getStyle($col.$baseRow)->getFont()->setBold( true );
            $activeSheet->setCellValue($col++.$baseRow, '=SUM(C'.$baseRow. ':'.$lastcol.$baseRow. ')');
            
            $baseRow++;
        }

        $colsum = 'C';
        $lastData=$baseRow-1;
        $activeSheet->setCellValue('B'.$baseRow, 'JUMLAH');
        for ($i = 1; $i <= $colnum; $i++) {
            $activeSheet->setCellValue($colsum.$baseRow, '=SUM('.$colsum.$firstData. ':'.$colsum.$lastData. ')');
            $lastcolsum = $colsum;
            $colsum++;
        }

        $spreadsheet->getActiveSheet()->getStyle('B'.$baseRow. ':'.$lastcolsum.$baseRow)->getFont()->setBold( true );

        $spreadsheet->getActiveSheet()->getStyle('A5:'.$lastcolsum.$baseRow)->applyFromArray($styleArray);

        $spreadsheet->removeSheetByIndex(1);

        // $spreadsheet->getSecurity()->setLockWindows(true);
        // $spreadsheet->getSecurity()->setLockStructure(true);
        // $spreadsheet->getSecurity()->setWorkbookPassword("silverblack");

        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setPassword('silverblack');
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setSheet(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setSort(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setInsertRows(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setFormatCells(true);

        // $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_poa_'.$period. '_All.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');

        // $mpdf = new \Mpdf\Mpdf();
        // $mpdf->WriteHTML('<h1>Hello world!</h1>');
        // $mpdf->Output();

        // $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Mpdf');
        // $writer->writeAllSheets();
        // header('Content-Type: application/pdf');
        // header('Content-Disposition: attachment;filename="_export.pdf"');
        // header('Cache-Control: max-age=0');
        // $writer->save('php://output');

        exit;
    }

    public function actionExportxlsubah()
    {
        $session = Yii::$app->session;
        $period = $session['periodValue']; 
        $unit_id = $session['unitId'];
        $unit_name = $session['unitName'];

        // return $unit_name;

        //QUERY PER REKENING
        Yii::$app->db->createCommand('DELETE FROM export_account WHERE username=:username AND period=:periodValue ')
        ->bindValue(':username', Yii::$app->user->identity->username)
        ->bindValue(':periodValue', $period)
        ->execute();
        
        $data = Yii::$app->db->createCommand('SELECT g.id, g.nama_program program, ifnull(pb.jumlah,0) premi_bpjs, ifnull(pk.jumlah,0) premi_ketenagakerjaan, ifnull(ta.jumlah,0) transportasi_akomodasi, ifnull(h.jumlah,0) hadiah, 
        ifnull(tk.jumlah,0) tenaga_kontrak, ifnull(hn.jumlah,0) honorar_narsum, ifnull(smd.jumlah,0) sewa_mobil_darat, ifnull(sld.jumlah,0) sspd_luar_daerah, 
        ifnull(sdd.jumlah,0) sspd_dalam_daerah, ifnull(pm.jumlah,0) perangko_materai, ifnull(jtk.jumlah,0) jasa_transaksi_keuangan, ifnull(p.jumlah,0) penggandaan, 
        ifnull(ctk.jumlah,0) cetak, ifnull(atk.jumlah,0) atk, ifnull(bhp.jumlah,0) bahan_habis_pakai, ifnull(mmk.jumlah,0) makan_minum_kegiatan, 
        ifnull(jpk.jumlah,0) jpk, ifnull(bbm.jumlah,0) bbm, ifnull(ip.jumlah,0) internet_pulsa, 
        
        (ifnull(pb.jumlah,0)+ifnull(pk.jumlah,0)+ifnull(ta.jumlah,0)+ifnull(h.jumlah,0)+ifnull(tk.jumlah,0)+ifnull(hn.jumlah,0)+ifnull(smd.jumlah,0)+ifnull(sld.jumlah,0)+
        ifnull(sdd.jumlah,0)+ifnull(pm.jumlah,0)+ifnull(jtk.jumlah,0)+ifnull(p.jumlah,0)+ifnull(ctk.jumlah,0)+ifnull(atk.jumlah,0)+ifnull(bhp.jumlah,0)+
        ifnull(mmk.jumlah,0)+ifnull(jpk.jumlah,0)+ifnull(bbm.jumlah,0)+ifnull(ip.jumlah,0)) jumlah 
        FROM program g
        LEFT JOIN
        (
            SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id="1"
            GROUP BY s.program_id
        ) pb ON pb.program_id=g.id
        LEFT JOIN
        (
            SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id="2"
            GROUP BY s.program_id
        ) pk ON pk.program_id=g.id
        LEFT JOIN
        (
            SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id="3"
            GROUP BY s.program_id
        ) ta ON ta.program_id=g.id
        LEFT JOIN
        (
            SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id="4"
            GROUP BY s.program_id
        ) h ON h.program_id=g.id
        LEFT JOIN
        (
            SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id="5"
            GROUP BY s.program_id
        ) tk ON tk.program_id=g.id
        LEFT JOIN
        (
            SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id="6"
            GROUP BY s.program_id
        ) hn ON hn.program_id=g.id
        LEFT JOIN
        (
            SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id="7"
            GROUP BY s.program_id
        ) smd ON smd.program_id=g.id
        LEFT JOIN
        (
            SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id="8"
            GROUP BY s.program_id
        ) sld ON sld.program_id=g.id
        LEFT JOIN
        (
            SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id="9"
            GROUP BY s.program_id
        ) sdd ON sdd.program_id=g.id
        LEFT JOIN
        (
            SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id="10"
            GROUP BY s.program_id
        ) pm ON pm.program_id=g.id
        LEFT JOIN
        (
            SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id="11"
            GROUP BY s.program_id
        ) jtk ON jtk.program_id=g.id
        LEFT JOIN
        (
            SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id="12"
            GROUP BY s.program_id
        ) p ON p.program_id=g.id
        LEFT JOIN
        (
            SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id="13"
            GROUP BY s.program_id
        ) ctk ON ctk.program_id=g.id
        LEFT JOIN
        (
            SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id="14"
            GROUP BY s.program_id
        ) atk ON atk.program_id=g.id
        LEFT JOIN
        (
            SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id="15"
            GROUP BY s.program_id
        ) bhp ON bhp.program_id=g.id
        LEFT JOIN
        (
            SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id="16"
            GROUP BY s.program_id
        ) mmk ON mmk.program_id=g.id
        LEFT JOIN
        (
            SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id="17"
            GROUP BY s.program_id
        ) jpk ON jpk.program_id=g.id
        LEFT JOIN
        (
            SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id="18"
            GROUP BY s.program_id
        ) bbm ON bbm.program_id=g.id
        LEFT JOIN
        (
            SELECT s.program_id, sum(e.jumlah) jumlah FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id="19"
            GROUP BY s.program_id
        ) ip ON ip.program_id=g.id
        WHERE g.tahun=:periode')
        ->bindValue(':unit_id', $unit_id)
        ->bindValue(':periode', $period)
        ->queryAll();

        foreach ($data as $row) {
            $exportaccount =  new Exportaccount();
            $exportaccount->program_id=$row['id'];
            $exportaccount->program=$row['program'];
            $exportaccount->premi_bpjs=$row['premi_bpjs'];
            $exportaccount->premi_ketenagakerjaan=$row['premi_ketenagakerjaan'];
            $exportaccount->transportasi_akomodasi=$row['transportasi_akomodasi'];
            $exportaccount->hadiah=$row['hadiah'];
            $exportaccount->tenaga_kontrak=$row['tenaga_kontrak'];
            $exportaccount->honorar_narsum=$row['honorar_narsum'];
            $exportaccount->sewa_mobil_darat=$row['sewa_mobil_darat'];
            $exportaccount->sspd_luar_daerah=$row['sspd_luar_daerah'];
            $exportaccount->sspd_dalam_daerah=$row['sspd_dalam_daerah'];
            $exportaccount->perangko_materai=$row['perangko_materai'];
            $exportaccount->jasa_transaksi_keuangan=$row['jasa_transaksi_keuangan'];
            $exportaccount->penggandaan=$row['penggandaan'];
            $exportaccount->cetak=$row['cetak'];
            $exportaccount->atk=$row['atk'];
            $exportaccount->bahan_habis_pakai=$row['bahan_habis_pakai'];
            $exportaccount->makan_minum_kegiatan=$row['makan_minum_kegiatan'];
            $exportaccount->jpk=$row['jpk'];
            $exportaccount->bbm=$row['bbm'];
            $exportaccount->internet_pulsa=$row['internet_pulsa'];
            $exportaccount->jumlah=$row['jumlah'];
            $exportaccount->username=Yii::$app->user->identity->username;
            $exportaccount->period=$period;
            $exportaccount->save();
        }

        // $dataExcel = Yii::$app->db->createCommand('SELECT * FROM export_account')
        // ->queryAll();

        $dataExcel = Yii::$app->db->createCommand('SELECT * FROM export_account WHERE (premi_bpjs <> 0 OR premi_ketenagakerjaan <> 0 OR transportasi_akomodasi <> 0 OR 
        hadiah <> 0 OR tenaga_kontrak <> 0 OR honorar_narsum <> 0 OR sewa_mobil_darat <> 0 OR sspd_luar_daerah <> 0 OR
        sspd_dalam_daerah <> 0 OR perangko_materai <> 0 OR jasa_transaksi_keuangan <> 0 OR penggandaan <> 0 OR
        cetak <> 0 OR atk <> 0 OR bahan_habis_pakai <> 0 OR makan_minum_kegiatan <> 0 OR jpk <> 0 OR bbm <> 0 OR internet_pulsa <> 0) AND username=:username AND period=:periodValue
        ORDER BY program_id')
        ->bindValue(':username', Yii::$app->user->identity->username)
        ->bindValue(':periodValue', $period)
        ->queryAll();

        $countProgram= Count($dataExcel);

        $inputFileName = Yii::getAlias('@app/views/exportaccount').'/_export_perubahan.xlsx';

        /** Load $inputFileName to a Spreadsheet Object  **/
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        // $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $styleArrayBold = [
            'font' => [
                'bold' => true,
            ],
        ];

        // //set orientasi dan ukurankertas
        // $activeSheet->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE)
        //                             ->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_FOLIO);
        
        $activeSheet->setCellValue('A'.'1', 'POA DAK BOK TAHUNAN (PERUBAHAN)');
        $activeSheet->setCellValue('A'.'2', 'UPTD ' .strtoupper($unit_name));
        $activeSheet->setCellValue('A'.'3', 'TAHUN ' .$period);
        $baseRow=6;
        $firstData=6;
        foreach($dataExcel as $rowExcel) {
            $activeSheet->setCellValue('A'.$baseRow, $baseRow-5)
                        ->setCellValue('B'.$baseRow, $rowExcel['program'])
                        ->setCellValue('C'.$baseRow, $rowExcel['premi_bpjs'])
                        ->setCellValue('D'.$baseRow, $rowExcel['premi_ketenagakerjaan'])
                        ->setCellValue('E'.$baseRow, $rowExcel['transportasi_akomodasi'])
                        ->setCellValue('F'.$baseRow, $rowExcel['hadiah'])
                        ->setCellValue('G'.$baseRow, $rowExcel['tenaga_kontrak'])
                        ->setCellValue('H'.$baseRow, $rowExcel['honorar_narsum'])
                        ->setCellValue('I'.$baseRow, $rowExcel['sewa_mobil_darat'])
                        ->setCellValue('j'.$baseRow, $rowExcel['sspd_luar_daerah'])
                        ->setCellValue('K'.$baseRow, $rowExcel['sspd_dalam_daerah'])
                        ->setCellValue('L'.$baseRow, $rowExcel['perangko_materai'])
                        ->setCellValue('M'.$baseRow, $rowExcel['jasa_transaksi_keuangan'])
                        ->setCellValue('N'.$baseRow, $rowExcel['penggandaan'])
                        ->setCellValue('O'.$baseRow, $rowExcel['cetak'])
                        ->setCellValue('P'.$baseRow, $rowExcel['atk'])
                        ->setCellValue('Q'.$baseRow, $rowExcel['bahan_habis_pakai'])
                        ->setCellValue('R'.$baseRow, $rowExcel['makan_minum_kegiatan'])
                        ->setCellValue('S'.$baseRow, $rowExcel['jpk'])
                        ->setCellValue('T'.$baseRow, $rowExcel['bbm'])
                        ->setCellValue('U'.$baseRow, $rowExcel['internet_pulsa'])
                        ->setCellValue('V'.$baseRow, $rowExcel['jumlah']);
            $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':V' .$baseRow)->applyFromArray($styleArray);

        $baseRow++;
        }
        
        $lastData=$baseRow-1;
        $activeSheet->setCellValue('B'.$baseRow, 'JUMLAH');
        $activeSheet->setCellValue('C'.$baseRow, '=SUM(C' .$firstData. ':C' .$lastData. ')');
        $activeSheet->setCellValue('D'.$baseRow, '=SUM(D' .$firstData. ':D' .$lastData. ')');
        $activeSheet->setCellValue('E'.$baseRow, '=SUM(E' .$firstData. ':E' .$lastData. ')');
        $activeSheet->setCellValue('F'.$baseRow, '=SUM(F' .$firstData. ':F' .$lastData. ')');
        $activeSheet->setCellValue('G'.$baseRow, '=SUM(G' .$firstData. ':G' .$lastData. ')');
        $activeSheet->setCellValue('H'.$baseRow, '=SUM(H' .$firstData. ':H' .$lastData. ')');
        $activeSheet->setCellValue('I'.$baseRow, '=SUM(I' .$firstData. ':I' .$lastData. ')');
        $activeSheet->setCellValue('J'.$baseRow, '=SUM(J' .$firstData. ':J' .$lastData. ')');
        $activeSheet->setCellValue('K'.$baseRow, '=SUM(K' .$firstData. ':K' .$lastData. ')');
        $activeSheet->setCellValue('L'.$baseRow, '=SUM(L' .$firstData. ':L' .$lastData. ')');
        $activeSheet->setCellValue('M'.$baseRow, '=SUM(M' .$firstData. ':M' .$lastData. ')');
        $activeSheet->setCellValue('N'.$baseRow, '=SUM(N' .$firstData. ':N' .$lastData. ')');
        $activeSheet->setCellValue('O'.$baseRow, '=SUM(O' .$firstData. ':O' .$lastData. ')');
        $activeSheet->setCellValue('P'.$baseRow, '=SUM(P' .$firstData. ':P' .$lastData. ')');
        $activeSheet->setCellValue('Q'.$baseRow, '=SUM(Q' .$firstData. ':Q' .$lastData. ')');
        $activeSheet->setCellValue('R'.$baseRow, '=SUM(R' .$firstData. ':R' .$lastData. ')');
        $activeSheet->setCellValue('S'.$baseRow, '=SUM(S' .$firstData. ':S' .$lastData. ')');
        $activeSheet->setCellValue('T'.$baseRow, '=SUM(T' .$firstData. ':T' .$lastData. ')');
        $activeSheet->setCellValue('U'.$baseRow, '=SUM(U' .$firstData. ':U' .$lastData. ')');
        $activeSheet->setCellValue('V'.$baseRow, '=SUM(V' .$firstData. ':V' .$lastData. ')');
        $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':V' .$baseRow)->applyFromArray($styleArray);

        $unit = Unit::findOne($unit_id);
        $baseRow=$baseRow+3;

        $activeSheet->setCellValue('Q'.$baseRow, $unit->puskesmas. ',                        ' .$period); 
        $spreadsheet->getActiveSheet()->getStyle('Q'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+1; 

        // $spreadsheet->getActiveSheet()->getStyle('Q:S')->getAlignment()->setHorizontal('center');
        $activeSheet->setCellValue('Q'.$baseRow, 'Mengetahui,'); 
        $spreadsheet->getActiveSheet()->getStyle('Q'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+1;
        $activeSheet->setCellValue('Q'.$baseRow, 'Kepala UPTD ' .$unit_name); 
        $spreadsheet->getActiveSheet()->getStyle('Q'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+4;
        $activeSheet->setCellValue('Q'.$baseRow, $unit->kepala); 
        $spreadsheet->getActiveSheet()->getStyle('Q'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+1;
        $activeSheet->setCellValue('Q'.$baseRow, 'NIP. ' .$unit->nip_kepala);
        $spreadsheet->getActiveSheet()->getStyle('Q'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );

        //QUERY PER PROGRAM
        // Yii::$app->db->createCommand()->truncateTable('export_program')->execute();
        Yii::$app->db->createCommand('DELETE FROM export_program WHERE username=:username AND period=:periodValue ')
        ->bindValue(':username', Yii::$app->user->identity->username)
        ->bindValue(':periodValue', $period)
        ->execute();

        $data = Yii::$app->db->createCommand('SELECT g.nama_program, s.nama_pelayanan, v.nama_kegiatan,  IFNULL(a.bentuk_kegiatan, v.nama_kegiatan) bentuk_kegiatan, 
        a.sasaran, a.target, a.lokasi, a.pelaksana, 
        c.nama_rekening, e.vol_1, e.satuan_1, IFNULL(e.vol_2,1) vol_2, IFNULL(e.satuan_2,"") satuan_2, e.vol_1*IFNULL(e.vol_2,1) vol, e.unit_cost, e.jumlah,  
        case when e.jan=1 then "V" ELSE "" END jan, case when e.feb=1 then "V" ELSE "" END feb, 
        case when e.mar=1 then "V" ELSE "" END mar, case when e.apr=1 then "V" ELSE "" END apr, 
        case when e.mei=1 then "V" ELSE "" END mei, case when e.jun=1 then "V" ELSE "" END jun, 
        case when e.jul=1 then "V" ELSE "" END jul, case when e.agu=1 then "V" ELSE "" END agu, 
        case when e.sep=1 then "V" ELSE "" END sep, case when e.okt=1 then "V" ELSE "" END okt, 
        case when e.nov=1 then "V" ELSE "" END nov, case when e.des=1 then "V" ELSE "" END des,
        d.vol_1 vol_1_awal, d.satuan_1 satuan_1_awal, IFNULL(d.vol_2,1) vol_2_awal, IFNULL(d.satuan_2,"") satuan_2_awal, d.vol_1*IFNULL(d.vol_2,1) vol_awal, d.unit_cost unit_cost_awal, d.jumlah jumlah_awal
        FROM activity_detail_ubah e
        LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN program g ON g.id=s.program_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN account c ON c.id=e.account_id
        LEFT JOIN activity_detail d ON d.id=e.activity_detail_id
        WHERE p.unit_id=:unit_id AND p.tahun=:periode 
        -- AND g.id=31 AND v.id=224
        ORDER BY g.id, s.id, v.id, a.id')
        
        ->bindValue(':unit_id', $unit_id)
        ->bindValue(':periode', $period)
        ->queryAll();

        $program = '';
        $pelayanan = '';
        $jeniskegiatan = '';
        $kegiatan = '';
        foreach ($data as $row) {
            $exportprogram =  new Exportprogram();
   
            $exportprogram->nama_program=$row['nama_program'];
            
            if ($pelayanan !== $row['nama_pelayanan']) {
                $exportprogram->nama_pelayanan=$row['nama_pelayanan']; $pelayanan = $row['nama_pelayanan'];
            } 

            if ($jeniskegiatan !== $row['nama_kegiatan']) {
                $exportprogram->nama_kegiatan=$row['nama_kegiatan']; $jeniskegiatan = $row['nama_kegiatan'];
            }

            if ($kegiatan !== $row['bentuk_kegiatan']) {
                $exportprogram->bentuk_kegiatan=$row['bentuk_kegiatan']; $kegiatan = $row['bentuk_kegiatan'];
                $exportprogram->sasaran=$row['sasaran'];
                $exportprogram->target=$row['target'];
                $exportprogram->lokasi=$row['lokasi'];
                $exportprogram->pelaksana=$row['pelaksana'];
            }

            $exportprogram->nama_rekening=$row['nama_rekening'];
            $exportprogram->vol_1=$row['vol_1'];
            $exportprogram->satuan_1=$row['satuan_1'];
            $exportprogram->vol_2=$row['vol_2'];
            $exportprogram->satuan_2=$row['satuan_2'];
            $exportprogram->vol=$row['vol'];
            $exportprogram->unit_cost=$row['unit_cost'];
            $exportprogram->jumlah=$row['jumlah'];

            $exportprogram->vol_1_awal=$row['vol_1_awal'];
            $exportprogram->satuan_1_awal=$row['satuan_1_awal'];
            $exportprogram->vol_2_awal=$row['vol_2_awal'];
            $exportprogram->satuan_2_awal=$row['satuan_2_awal'];
            $exportprogram->vol_awal=$row['vol_awal'];
            $exportprogram->unit_cost_awal=$row['unit_cost_awal'];
            $exportprogram->jumlah_awal=$row['jumlah_awal'];

            // $exportprogram->jan=$row['jan'];
            // $exportprogram->feb=$row['feb'];
            // $exportprogram->mar=$row['mar'];
            // $exportprogram->apr=$row['apr'];
            // $exportprogram->mei=$row['mei'];
            // $exportprogram->jun=$row['jun'];
            // $exportprogram->jul=$row['jul'];
            // $exportprogram->agu=$row['agu'];
            // $exportprogram->sep=$row['sep'];
            // $exportprogram->okt=$row['okt'];
            // $exportprogram->nov=$row['nov'];
            // $exportprogram->des=$row['des'];
            $exportprogram->username=Yii::$app->user->identity->username;
            $exportprogram->period=$period;
            $exportprogram->save();
        }

        //Sheet 2
        $program = '';
        $spreadsheet->setActiveSheetIndex(1);
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setCellValue('A'.'1', 'POA BANTUAN OPERSIONAL KESEHATAN (PERUBAHAN) ');
        $activeSheet->setCellValue('A'.'2', strtoupper($unit_name));
        $activeSheet->setCellValue('A'.'3', 'TAHUN ' .$period);

        // $dataprogram = Yii::$app->db->createCommand('SELECT * FROM program where id<>0')
        $dataprogram = Yii::$app->db->createCommand('SELECT p.* FROM program p
        RIGHT JOIN export_program e ON e.nama_program=p.nama_program
        WHERE tahun=:tahun AND e.username=:username
        GROUP BY p.nama_program
        ORDER BY p.id')
        ->bindValue(':tahun', $period)
        ->bindValue(':username', Yii::$app->user->identity->username)
        ->queryAll();

        $baseRowAwal = 0;
        $baseRowProgram = 5;
        $baseRowService = 0;

        $styleArrayHeader = [
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        
        foreach ($dataprogram as $dataprogram) {
            
            $dataExcel = Yii::$app->db->createCommand('SELECT e.*, p.id, CASE WHEN v.modul="P" THEN NULL ELSE "DRAFT" END status FROM export_program e
            LEFT JOIN program p ON p.nama_program=e.nama_program AND p.tahun=:periodValue
            LEFT JOIN verification v ON v.program_id=p.id AND v.unit_id=:unitId
            where e.nama_program=:namaprogram AND username=:username AND period=:periodValue ')
            ->bindValue(':unitId', $unit_id)
            ->bindValue(':username', Yii::$app->user->identity->username)
            ->bindValue(':periodValue', $period)
            ->bindValue(':namaprogram', $dataprogram['nama_program'])
            ->queryAll();

            $count = count($dataExcel);

            $baseRowAwal = $baseRowAwal+1;
            $tabletitle = $baseRowProgram+2;
            $tabletitle2 = $tabletitle+1;
            if ($baseRowAwal > 1) {
                $baseRowProgram = $baseRowProgram+1;
                $activeSheet->setCellValue('A'.$baseRowProgram, $baseRowAwal) 
                ->setCellValue('C'.$baseRowProgram, $dataprogram['nama_program']);
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowProgram. ':C' .$baseRowProgram)->applyFromArray($styleArrayBold);

                if ($count > 0) {
                $activeSheet->setCellValue('A'.$tabletitle, 'No')
                ->setCellValue('C'.$tabletitle, '=C6')
                ->setCellValue('D'.$tabletitle, '=D6')
                ->setCellValue('E'.$tabletitle, '=E6')
                ->setCellValue('F'.$tabletitle, '=F6')
                ->setCellValue('G'.$tabletitle, '=G6')
                ->setCellValue('H'.$tabletitle, '=H6')
                ->setCellValue('I'.$tabletitle, '=I6')
                ->setCellValue('J'.$tabletitle, '=J6')
                ->setCellValue('K'.$tabletitle, '=K6')

                ->setCellValue('R'.$tabletitle, '=R6')
                ->setCellValue('S'.$tabletitle, '=S6')
                ->setCellValue('T'.$tabletitle, '=T6')

                ->setCellValue('AA'.$tabletitle, '=AA6')
                ->setCellValue('AB'.$tabletitle, '=AB6')

                ->setCellValue('C'.$tabletitle2, '=C7')
                ->setCellValue('D'.$tabletitle2, '=D7')
                ->setCellValue('E'.$tabletitle2, '=E7')
                ->setCellValue('F'.$tabletitle2, '=F7')
                ->setCellValue('G'.$tabletitle2, '=G7')
                ->setCellValue('H'.$tabletitle2, '=H7')
                ->setCellValue('I'.$tabletitle2, '=I7')
                ->setCellValue('J'.$tabletitle2, '=J7')
                ->setCellValue('K'.$tabletitle2, '=K7')

                ->setCellValue('R'.$tabletitle2, '=R7')
                ->setCellValue('S'.$tabletitle2, '=S7')
                ->setCellValue('T'.$tabletitle2, '=T7')

                ->setCellValue('AA'.$tabletitle2, '=AA7')
                ->setCellValue('AB'.$tabletitle2, '=AB7');
      
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowProgram. ':C' .$baseRowProgram)->applyFromArray($styleArrayBold);
                $spreadsheet->getActiveSheet()->mergeCells('K'.$tabletitle. ':S' .$tabletitle);
                $spreadsheet->getActiveSheet()->mergeCells('T'.$tabletitle. ':AB' .$tabletitle);
                $spreadsheet->getActiveSheet()->mergeCells('K'.$tabletitle2. ':Q' .$tabletitle2);
                $spreadsheet->getActiveSheet()->mergeCells('T'.$tabletitle2. ':Z' .$tabletitle2);

                $spreadsheet->getActiveSheet()->mergeCells('A'.$tabletitle. ':A' .$tabletitle2);
                $spreadsheet->getActiveSheet()->mergeCells('B'.$tabletitle. ':B' .$tabletitle2);
                $spreadsheet->getActiveSheet()->mergeCells('C'.$tabletitle. ':C' .$tabletitle2);
                $spreadsheet->getActiveSheet()->mergeCells('D'.$tabletitle. ':D' .$tabletitle2);
                $spreadsheet->getActiveSheet()->mergeCells('E'.$tabletitle. ':E' .$tabletitle2);
                $spreadsheet->getActiveSheet()->mergeCells('F'.$tabletitle. ':F' .$tabletitle2);
                $spreadsheet->getActiveSheet()->mergeCells('G'.$tabletitle. ':G' .$tabletitle2);
                $spreadsheet->getActiveSheet()->mergeCells('H'.$tabletitle. ':H' .$tabletitle2);
                $spreadsheet->getActiveSheet()->mergeCells('I'.$tabletitle. ':I' .$tabletitle2);
                $spreadsheet->getActiveSheet()->mergeCells('J'.$tabletitle. ':J' .$tabletitle2);

                $spreadsheet->getActiveSheet()->getStyle('A'.$tabletitle. ':AB' .$tabletitle)->applyFromArray($styleArrayHeader);
                $spreadsheet->getActiveSheet()->getStyle('A'.$tabletitle2. ':AB' .$tabletitle2)->applyFromArray($styleArrayHeader);
                $activeSheet->getStyle('A'.$tabletitle. ':AB' .$tabletitle)->getAlignment()->setHorizontal('center'); 
                $activeSheet->getStyle('A'.$tabletitle. ':AB' .$tabletitle)->getAlignment()->setWrapText(true);
                $activeSheet->getStyle('A'.$tabletitle. ':AB' .$tabletitle)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F8CBAD');
                $activeSheet->getStyle('A'.$tabletitle2. ':AB' .$tabletitle2)->getAlignment()->setHorizontal('center'); 
                $activeSheet->getStyle('A'.$tabletitle2. ':AB' .$tabletitle2)->getAlignment()->setWrapText(true);
                $activeSheet->getStyle('A'.$tabletitle2. ':AB' .$tabletitle2)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F8CBAD');
                }
            }else{
                $activeSheet->setCellValue('A'.$baseRowProgram, $baseRowAwal)
                ->setCellValue('C'.$baseRowProgram, $dataprogram['nama_program']); 

                $activeSheet->getStyle('A6:AB7')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F8CBAD');
            }
                 
            $baseRowService = 0;

            $baseRow = $baseRowProgram+3;
            $firstData = $baseRowProgram+3;
            $rowAkhir = '';
            
            if ($count > 0) {
                foreach($dataExcel as $rowExcel) {
                    if ($rowAkhir === $baseRowAwal) {
                        $rowAkhir = '';
                    }else{
                        $rowAkhir = $baseRowAwal;
                    }
                    $activeSheet
                                ->setCellValue('A'.$baseRow, $rowAkhir)
                                ->setCellValue('C'.$baseRow, $rowExcel['nama_pelayanan'])
                                ->setCellValue('D'.$baseRow, $rowExcel['nama_kegiatan'])
                                ->setCellValue('E'.$baseRow, $rowExcel['bentuk_kegiatan'])
                                ->setCellValue('F'.$baseRow, $rowExcel['sasaran'])
                                ->setCellValue('G'.$baseRow, $rowExcel['target'])
                                ->setCellValue('H'.$baseRow, $rowExcel['lokasi'])
                                ->setCellValue('I'.$baseRow, $rowExcel['pelaksana'])
                                ->setCellValue('J'.$baseRow, $rowExcel['nama_rekening'])
                                ->setCellValue('K'.$baseRow, $rowExcel['vol_1_awal'])
                                ->setCellValue('L'.$baseRow, $rowExcel['satuan_1_awal'])
                                ->setCellValue('M'.$baseRow, 'x')
                                ->setCellValue('N'.$baseRow, $rowExcel['vol_2_awal'])
                                ->setCellValue('O'.$baseRow, $rowExcel['satuan_2_awal'])
                                ->setCellValue('P'.$baseRow, '=')
                                ->setCellValue('Q'.$baseRow, $rowExcel['vol_awal'])
                                ->setCellValue('R'.$baseRow, $rowExcel['unit_cost_awal'])
                                ->setCellValue('S'.$baseRow, $rowExcel['jumlah_awal'])
                                // ->setCellValue('T'.$baseRow, $rowExcel['jan'])
                                // ->setCellValue('U'.$baseRow, $rowExcel['feb'])
                                // ->setCellValue('V'.$baseRow, $rowExcel['mar'])
                                // ->setCellValue('W'.$baseRow, $rowExcel['apr'])
                                // ->setCellValue('X'.$baseRow, $rowExcel['mei'])
                                // ->setCellValue('Y'.$baseRow, $rowExcel['jun'])
                                // ->setCellValue('Z'.$baseRow, $rowExcel['jul'])
                                // ->setCellValue('AA'.$baseRow, $rowExcel['agu'])
                                // ->setCellValue('AB'.$baseRow, $rowExcel['sep'])
                                // ->setCellValue('AC'.$baseRow, $rowExcel['okt'])
                                // ->setCellValue('AD'.$baseRow, $rowExcel['nov'])
                                // ->setCellValue('AE'.$baseRow, $rowExcel['des']);  
                                ->setCellValue('T'.$baseRow, $rowExcel['vol_1'])
                                ->setCellValue('U'.$baseRow, $rowExcel['satuan_1'])
                                ->setCellValue('V'.$baseRow, 'x')
                                ->setCellValue('W'.$baseRow, $rowExcel['vol_2'])
                                ->setCellValue('X'.$baseRow, $rowExcel['satuan_2'])
                                ->setCellValue('Y'.$baseRow, '=')
                                ->setCellValue('Z'.$baseRow, $rowExcel['vol'])
                                ->setCellValue('AA'.$baseRow, $rowExcel['unit_cost'])
                                ->setCellValue('AB'.$baseRow, $rowExcel['jumlah']);
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AB' .$baseRow)->applyFromArray($styleArray);
                                            
                    $baseRow++;
                    $baseRowService = $baseRowService+1;
                    $rowAkhir = $baseRowAwal;
                }
                
                
                $activeSheet->setCellValue('A'.$baseRow, 'Total');
                $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRow. ':R' .$baseRow);
                $spreadsheet->getActiveSheet()->mergeCells('T'.$baseRow. ':AA' .$baseRow);
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AB' .$baseRow)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AB' .$baseRow)->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFF00');
                
                $lastData = $baseRow-1;
                
                $activeSheet->setCellValue('S'.$baseRow, '=SUM(S' .$firstData. ':S' .$lastData. ')'); $baseRowProgram=$baseRowProgram+1;
                $activeSheet->setCellValue('AB'.$baseRow, '=SUM(AB' .$firstData. ':AB' .$lastData. ')'); $baseRowProgram=$baseRowProgram+1;
                $baseRowProgram=$baseRowProgram+$baseRowService+3;


                if ($baseRowAwal!=$countProgram){
                    // $spreadsheet->getActiveSheet()->getStyle('Q:S')->getAlignment()->setHorizontal('center');
                    // $spreadsheet->getActiveSheet()->getStyle('Q:S')->getFont()->setBold( true );

                    $styleArrayDraft = [
                        'font' => [
                            'bold' => true,
                            'size' => 30,
                        ],
                    ];
    
                    $baseRowDraft = $baseRow+1;
                    $baseRowDraft2 = $baseRowDraft+1;
                    $activeSheet->setCellValue('A'.$baseRowDraft, $rowExcel['status']);
                    if($rowExcel['status'] == 'DRAFT'){
                        $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowDraft. ':C' .$baseRowDraft2);
                        $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowDraft. ':C' .$baseRowDraft2)->getFill()
                                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                    ->getStartColor()->setARGB('BA0101');
                        $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowDraft. ':C' .$baseRowDraft2)->getAlignment()->setHorizontal('center');
                        $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowDraft. ':C' .$baseRowDraft)->applyFromArray($styleArrayDraft);
                    }

                    $activeSheet->setCellValue('Q'.$baseRowProgram, 'Mengetahui'); 
                    $spreadsheet->getActiveSheet()->mergeCells('Q'.$baseRowProgram. ':S' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;
                    $activeSheet->setCellValue('Q'.$baseRowProgram, 'Kepala UPTD ' .$unit_name); 
                    $spreadsheet->getActiveSheet()->mergeCells('Q'.$baseRowProgram. ':S' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+4;
                    $activeSheet->setCellValue('Q'.$baseRowProgram, $unit->kepala); 
                    $spreadsheet->getActiveSheet()->mergeCells('Q'.$baseRowProgram. ':S' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;
                    $activeSheet->setCellValue('Q'.$baseRowProgram, 'NIP. ' .$unit->nip_kepala); 
                    $spreadsheet->getActiveSheet()->mergeCells('Q'.$baseRowProgram. ':S' .$baseRowProgram);
                }else{
                    $total = Yii::$app->db->createCommand('SELECT SUM(d.jumlah) jumlah_awal, SUM(e.jumlah) jumlah
                    FROM activity_detail_ubah e 
                    LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id LEFT JOIN activity v ON v.id=a.activity_id
                    LEFT JOIN service s ON s.id=v.service_id 
                    LEFT JOIN program g ON g.id=s.program_id 
                    LEFT JOIN period p ON p.id=a.period_id
                    LEFT JOIN account c ON c.id=e.account_id 
                    LEFT JOIN activity_detail d ON d.id=e.activity_detail_id
                    WHERE p.unit_id=:unit_id AND p.tahun=:periode')
                    ->bindValue(':unit_id', $unit_id)
                    ->bindValue(':periode', $period)
                    ->queryAll();

                    foreach ($total as $rowTotal);

                    $baseRow = $baseRow+1;
                    $activeSheet->setCellValue('A'.$baseRow, 'Total Semua Program');
                    $activeSheet->setCellValue('S'.$baseRow, $rowTotal['jumlah_awal']);
                    $activeSheet->setCellValue('AB'.$baseRow, $rowTotal['jumlah']);
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRow. ':R' .$baseRow);
                    $spreadsheet->getActiveSheet()->mergeCells('T'.$baseRow. ':AA' .$baseRow);
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AB' .$baseRow)->applyFromArray($styleArray);
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AB' .$baseRow)->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('0F9D58');

                    $spreadsheet->getActiveSheet()->getStyle('F:K')->getAlignment()->setHorizontal('center');
                    $spreadsheet->getActiveSheet()->getStyle('Q:S')->getAlignment()->setHorizontal('center');

                    $spreadsheet->getActiveSheet()->getStyle('F:K')->getFont()->setBold( true );
                    $spreadsheet->getActiveSheet()->getStyle('Q:S')->getFont()->setBold( true );

                    $baseRowProgram=$baseRowProgram+2;

                    $styleArrayDraft = [
                        'font' => [
                            'bold' => true,
                            'size' => 30,
                        ],
                    ];
    
                    $baseRowDraft = $baseRow+1;
                    $baseRowDraft2 = $baseRowDraft+1;
                    $activeSheet->setCellValue('A'.$baseRowDraft, $rowExcel['status']);
                    if($rowExcel['status'] == 'DRAFT'){
                        $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowDraft. ':C' .$baseRowDraft2);
                        $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowDraft. ':C' .$baseRowDraft2)->getFill()
                                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                    ->getStartColor()->setARGB('BA0101');
                        $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowDraft. ':C' .$baseRowDraft2)->getAlignment()->setHorizontal('center');
                        $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowDraft. ':C' .$baseRowDraft)->applyFromArray($styleArrayDraft);
                    }

                    $profile = Profile::find()->all();
                    foreach ($profile as $dataProfile);

                    $activeSheet->setCellValue('F'.$baseRowProgram, 'Mengetahui,'); 
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;

                    $activeSheet->setCellValue('A'.$baseRowProgram, 'Kepala UPTD ' .$unit_name); 
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    $activeSheet->setCellValue('F'.$baseRowProgram, 'Sekretaris ' .$dataProfile->nama. ' '.$dataProfile->kota_kab); 
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    $activeSheet->setCellValue('Q'.$baseRowProgram, 'PPTK BOK'); 
                    $spreadsheet->getActiveSheet()->mergeCells('Q'.$baseRowProgram. ':S' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+4;

                    $activeSheet->setCellValue('A'.$baseRowProgram, $unit->kepala); 
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    $activeSheet->setCellValue('F'.$baseRowProgram, $dataProfile->sekretaris); 
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    $activeSheet->setCellValue('Q'.$baseRowProgram, $unit->petugas); 
                    $spreadsheet->getActiveSheet()->mergeCells('Q'.$baseRowProgram. ':S' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;

                    // $activeSheet->setCellValue('A'.$baseRowProgram, $unit->kepala); 
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    $activeSheet->setCellValue('F'.$baseRowProgram, $dataProfile->jabatan_sekretaris); 
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    $activeSheet->setCellValue('Q'.$baseRowProgram, $unit->jabatan_petugas); 
                    $spreadsheet->getActiveSheet()->mergeCells('Q'.$baseRowProgram. ':S' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;

                    $activeSheet->setCellValue('A'.$baseRowProgram, 'NIP. ' .$unit->nip_kepala);
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    $activeSheet->setCellValue('F'.$baseRowProgram, 'NIP. ' .$dataProfile->nip_sekretaris);
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    $activeSheet->setCellValue('Q'.$baseRowProgram, 'NIP. ' .$unit->nip_petugas);
                    $spreadsheet->getActiveSheet()->mergeCells('Q'.$baseRowProgram. ':S' .$baseRowProgram);

                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AB' .$baseRow)->applyFromArray($styleArrayBold);
                }
                $baseRowProgram=$baseRowProgram+20;

                $baseRowProgram++;   
            }else{
                // $baseRowProgram = $baseRowProgram+2;    
                $baseRowProgram++; 
            }   
             
        }

        $spreadsheet->getSecurity()->setLockWindows(true);
        $spreadsheet->getSecurity()->setLockStructure(true);
        $spreadsheet->getSecurity()->setWorkbookPassword("silverblack");

        $spreadsheet->getActiveSheet()
            ->getProtection()->setPassword('silverblack');
        $spreadsheet->getActiveSheet()
            ->getProtection()->setSheet(true);
        $spreadsheet->getActiveSheet()
            ->getProtection()->setSort(true);
        $spreadsheet->getActiveSheet()
            ->getProtection()->setInsertRows(true);
        $spreadsheet->getActiveSheet()
            ->getProtection()->setFormatCells(true);

        $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);

        $spreadsheet->setActiveSheetIndex(0);

        $spreadsheet->getSecurity()->setLockWindows(true);
        $spreadsheet->getSecurity()->setLockStructure(true);
        $spreadsheet->getSecurity()->setWorkbookPassword("silverblack");

        $spreadsheet->getActiveSheet()
            ->getProtection()->setPassword('silverblack');
        $spreadsheet->getActiveSheet()
            ->getProtection()->setSheet(true);
        $spreadsheet->getActiveSheet()
            ->getProtection()->setSort(true);
        $spreadsheet->getActiveSheet()
            ->getProtection()->setInsertRows(true);
        $spreadsheet->getActiveSheet()
            ->getProtection()->setFormatCells(true);

        $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
        

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_poa_perubahan_'.$period. '_'.Yii::$app->user->identity->username.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');

        // $mpdf = new \Mpdf\Mpdf();
        // $mpdf->WriteHTML('<h1>Hello world!</h1>');
        // $mpdf->Output();

        // $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Mpdf');
        // $writer->writeAllSheets();
        // header('Content-Type: application/pdf');
        // header('Content-Disposition: attachment;filename="_export.pdf"');
        // header('Cache-Control: max-age=0');
        // $writer->save('php://output');

        exit;
    }

    public function actionDatapoa($p)
    {
        $session = Yii::$app->session;
        unset($session['rak']);
        $POST_VARIABLE=Yii::$app->request->post('Period');

        if(isset($POST_VARIABLE['tahun'])){
            $period = $POST_VARIABLE['tahun'];
            $session['periodValue'] = $period;
        }else{
            $period = $session['periodValue'];
        }

        if($p == 'def'){
            $session['poaLabel'] = ' Awal';
        }elseif($p == 'pergeseran'){
            $session['poaLabel'] = ' Pergeseran';
        }elseif($p == 'perubahan'){
            $session['poaLabel'] = ' Perubahan';
        }

        $query = 'SELECT e.id, g.nama_program, s.nama_pelayanan, v.nama_kegiatan,  IFNULL(a.bentuk_kegiatan, v.nama_kegiatan) bentuk_kegiatan, 
        a.sasaran, a.target, a.lokasi, a.pelaksana, c.kode,
        c.nama_rekening, e.rincian, e.vol_1, e.satuan_1, 
        IFNULL(e.vol_2,"") vol_2, IFNULL(e.satuan_2,"") satuan_2, 
        IFNULL(e.vol_3,"") vol_3, IFNULL(e.satuan_3,"") satuan_3,
        IFNULL(e.vol_4,"") vol_4, IFNULL(e.satuan_4,"") satuan_4,
        vol_1*IFNULL(vol_2,1)*IFNULL(vol_3,1)*IFNULL(vol_4,1) vol, 
        e.unit_cost, e.jumlah, e.jan_val, e.feb_val, e.mar_val, e.apr_val, e.mei_val, e.jun_val, e.jul_val, e.agu_val, e.sep_val, e.okt_val, e.nov_val, e.des_val
        FROM activity_detail e
        LEFT JOIN activity_data a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN program g ON g.id=s.program_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN account c ON c.id=e.account_id
        WHERE p.unit_id="'.Yii::$app->user->identity->unit_id.'" AND p.tahun="'.$period.'"
        ORDER BY g.id, s.id, v.id, a.id';

        $session['qrypoa'] = $query;

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model = $dataProvider->getModels();

        if(isset($_COOKIE['rak'])){
            if($_COOKIE['rak'] == '1'){
                $rak = true;
            }else{
                $rak = false;
            }
            $session['rak'] = $_COOKIE['rak'];
        }else{
            $rak = false;
            $session['rak'] = 0;
        }

        return $this->render('detail', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'id' => null,
            'namaUnit' => Yii::$app->user->identity->alias,
            'rak' => $rak
        ]);
    }

    public function actionDatapoaadm($id,$p)
    {
        $session = Yii::$app->session;
        $period = $session['periodValue'];

        if(isset($POST_VARIABLE['tahun'])){
            $period = $POST_VARIABLE['tahun'];
            $session['periodValue'] = $period;
        }else{
            $period = $session['periodValue'];
        }

        if($p == 'def'){
            $session['poaLabel'] = ' Awal';
        }elseif($p == 'pergeseran'){
            $session['poaLabel'] = ' Pergeseran';
        }elseif($p == 'perubahan'){
            $session['poaLabel'] = ' Perubahan';
        }

        $query = 'SELECT e.id, g.nama_program, s.nama_pelayanan, v.nama_kegiatan,  IFNULL(a.bentuk_kegiatan, v.nama_kegiatan) bentuk_kegiatan, 
        a.sasaran, a.target, a.lokasi, a.pelaksana, c.kode,
        c.nama_rekening, e.rincian, e.vol_1, e.satuan_1, 
        IFNULL(e.vol_2,"") vol_2, IFNULL(e.satuan_2,"") satuan_2, 
        IFNULL(e.vol_3,"") vol_3, IFNULL(e.satuan_3,"") satuan_3,
        IFNULL(e.vol_4,"") vol_4, IFNULL(e.satuan_4,"") satuan_4,
        vol_1*IFNULL(vol_2,1)*IFNULL(vol_3,1)*IFNULL(vol_4,1) vol, 
        e.unit_cost, e.jumlah, e.jan_val, e.feb_val, e.mar_val, e.apr_val, e.mei_val, e.jun_val, e.jul_val, e.agu_val, e.sep_val, e.okt_val, e.nov_val, e.des_val
        FROM activity_detail e
        LEFT JOIN activity_data a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN program g ON g.id=s.program_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN account c ON c.id=e.account_id
        WHERE p.unit_id="'.$id.'" AND p.tahun="'.$period.'"
        ORDER BY g.id, s.id, v.id, a.id';

        $session['qrypoa'] = $query;

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model = $dataProvider->getModels();

        if(isset($_COOKIE['rak'])){
            if($_COOKIE['rak'] == '1'){
                $rak = true;
            }else{
                $rak = false;
            }
            $session['rak'] = $_COOKIE['rak'];
        }else{
            $rak = false;
            $session['rak'] = 0;
        }

        $unit = Unit::findOne($id);
        $session['namaPkm'] = $unit->puskesmas;

        return $this->render('detail', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'id' => $id,
            'namaUnit' => $unit->puskesmas,
            'rak' => $rak
        ]);
    }

    public function actionExportxlsdesk()
    {
        $session = Yii::$app->session;
        $period = $session['periodValue'];

        if(Yii::$app->user->identity->username == 'admin'){
            $namapkm = 'Puskesmas '.$session['namaPkm'];
        }else{
            $namapkm = Yii::$app->user->identity->alias;
        }

        Yii::$app->db->createCommand('DELETE FROM export_program WHERE username=:username') //AND period=:periodValue 
        ->bindValue(':username', Yii::$app->user->identity->unit_id)
        // ->bindValue(':periodValue', $period)
        ->execute();

        $data = Yii::$app->db->createCommand($session['qrypoa'])
        ->queryAll();

        $program = '';
        $komponen = '';
        $kegiatan = '';
        $bentuk = '';
        foreach ($data as $row) {
            $exportprogram =  new Exportprogram();

            if ($program !== $row['nama_program']) {
                $exportprogram->unit=Yii::$app->user->identity->unit_id; $program = $row['nama_program'];
                
                $kegiatan = '';
                if ($kegiatan !== $row['nama_kegiatan']) {
                    $exportprogram->nama_kegiatan=$row['nama_kegiatan']; $kegiatan = $row['nama_kegiatan'];

                    $bentuk = '';
                    if ($bentuk !== $row['bentuk_kegiatan']) {
                        $exportprogram->bentuk_kegiatan=$row['bentuk_kegiatan']; $bentuk = $row['bentuk_kegiatan'];
                    }
                }
            }else{
                $exportprogram->unit=Yii::$app->user->identity->unit_id; $pkm = $row['nama_program'];

                if ($kegiatan !== $row['nama_kegiatan']) {
                    $exportprogram->nama_kegiatan=$row['nama_kegiatan']; $kegiatan = $row['nama_kegiatan'];
                    $bentuk = '';
                    if ($bentuk !== $row['bentuk_kegiatan']) {
                        $exportprogram->bentuk_kegiatan=$row['bentuk_kegiatan']; $bentuk = $row['bentuk_kegiatan'];
                    }
                }else{
                    if ($bentuk !== $row['bentuk_kegiatan']) {
                        $exportprogram->bentuk_kegiatan=$row['bentuk_kegiatan']; $bentuk = $row['bentuk_kegiatan'];
                    }
                }
            }
   
            $exportprogram->nama_program=$row['nama_program'];
            
            // if ($komponen !== $row['nama_pelayanan']) {
            //     $exportprogram->nama_pelayanan=$row['nama_pelayanan']; $komponen = $row['nama_pelayanan'];
            // }
            $exportprogram->nama_pelayanan=$row['nama_pelayanan'];
            

            // if ($kegiatan !== $row['nama_kegiatan']) {
            //     $exportprogram->nama_kegiatan=$row['nama_kegiatan']; $kegiatan = $row['nama_kegiatan'];
            // }

            // if ($bentuk !== $row['bentuk_kegiatan']) {
                // $exportprogram->bentuk_kegiatan=$row['bentuk_kegiatan']; //$bentuk = $row['bentuk_kegiatan'];
                $exportprogram->sasaran=$row['sasaran'];
                $exportprogram->target=$row['target'];
                $exportprogram->lokasi=$row['lokasi'];
                $exportprogram->pelaksana=$row['pelaksana'];
            // }

            $exportprogram->rek=$row['kode'];
            $exportprogram->nama_rekening=$row['nama_rekening'];
            $exportprogram->rincian=$row['rincian'];

            $exportprogram->vol_1=$row['vol_1'];
            $exportprogram->satuan_1=$row['satuan_1'];
            $exportprogram->vol_2=$row['vol_2'];
            $exportprogram->satuan_2=$row['satuan_2'];

            $exportprogram->vol_3=$row['vol_3'];
            $exportprogram->satuan_3=$row['satuan_3'];
            $exportprogram->vol_4=$row['vol_4'];
            $exportprogram->satuan_4=$row['satuan_4'];

            $exportprogram->vol=$row['vol'];
            $exportprogram->unit_cost=$row['unit_cost'];
            $exportprogram->jumlah=$row['jumlah'];
            $exportprogram->username=Yii::$app->user->identity->unit_id;
            $exportprogram->period=$period;
            $exportprogram->save();
        }

        $dataprogram = Yii::$app->db->createCommand('SELECT p.*, e.unit, SUM(e.jumlah) total FROM unit p
        RIGHT JOIN export_program e ON e.unit=p.id
        WHERE e.username=:username
        GROUP BY p.puskesmas
        ORDER BY p.puskesmas')
        // ->bindValue(':tahun', $period)
        ->bindValue(':username', Yii::$app->user->identity->unit_id)
        ->queryAll();

        $inputFileName = Yii::getAlias('@app/views/exportaccount').'/_export_komponen_dinas.xlsx';

        /** Load $inputFileName to a Spreadsheet Object  **/
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        // $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();

        $styleArrayBold = [
            'font' => [
                'bold' => true,
            ],
        ];

        // $service = Service::findOne($session['komponen']);
        // $activeSheet->setCellValue('A1', $service->nama_pelayanan);
        // $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayBold);

        $baseRowAwal = 0;
        $baseRowProgram = 4;
        $baseRowService = 0;

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $styleArrayHeader = [
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        foreach ($dataprogram as $dataprogram) {
            $dataExcel = Yii::$app->db->createCommand('SELECT e.*, p.id FROM export_program e
            LEFT JOIN program p ON p.nama_program=e.nama_program AND p.tahun=:periodValue
            where username=:username AND period=:periodValue order by e.id')
            ->bindValue(':username', Yii::$app->user->identity->unit_id)
            ->bindValue(':periodValue', $period)
            ->queryAll();

            $count = count($dataExcel);

            $baseRowAwal = $baseRowAwal+1;
            $activeSheet->setCellValue('A'.$baseRowProgram, '') 
            ->setCellValue('C'.$baseRowProgram, $namapkm)
            ->setCellValue('X'.$baseRowProgram, $dataprogram['total']);

            $spreadsheet->getActiveSheet()->mergeCells('C'.$baseRowProgram. ':W' .$baseRowProgram);
            $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowProgram. ':X' .$baseRowProgram)->applyFromArray($styleArrayHeader);
            $activeSheet->getStyle('A'.$baseRowProgram. ':X' .$baseRowProgram)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('9BC2E6');
                 
            $baseRowService = 0;
            $baseRow = $baseRowProgram+1;
            $namaPelayanan = '';
            
            if ($count > 0) {
                foreach($dataExcel as $rowExcel) {
                    $jumlahkomponen = Yii::$app->db->createCommand('SELECT SUM(e.jumlah) total FROM export_program e
                    -- LEFT JOIN program p ON p.nama_program=e.nama_program AND p.tahun=:periodValue
                    where username=:username AND period=:periodValue and nama_pelayanan=:namaPelayanan')
                    ->bindValue(':username', Yii::$app->user->identity->unit_id)
                    ->bindValue(':periodValue', $period)
                    ->bindValue(':namaPelayanan', $rowExcel['nama_pelayanan'])
                    ->queryAll();
        
                    foreach($jumlahkomponen as $jmlkmp);

                    if($namaPelayanan !== $rowExcel['nama_pelayanan']){
                        $activeSheet->setCellValue('A'.$baseRow, $baseRowAwal) 
                        ->setCellValue('C'.$baseRow, $rowExcel['nama_pelayanan'])
                        ->setCellValue('X'.$baseRow, $jmlkmp['total']);
                        $spreadsheet->getActiveSheet()->mergeCells('C'.$baseRow. ':W' .$baseRow);
                        $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':X' .$baseRow)->applyFromArray($styleArrayHeader);
                        $activeSheet->getStyle('A'.$baseRow. ':X' .$baseRow)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('9BC2E6');
                    
                        $namaPelayanan = $rowExcel['nama_pelayanan'];
                        $baseRow = $baseRow+1;
                        $baseRowAwal++;
                    }else{
                        
                    }

                    $activeSheet->setCellValue('A'.$baseRow, '')
                    ->setCellValue('B'.$baseRow, '')
                    ->setCellValue('C'.$baseRow, $rowExcel['nama_kegiatan'])
                    ->setCellValue('D'.$baseRow, $rowExcel['bentuk_kegiatan'])
                    ->setCellValue('E'.$baseRow, $rowExcel['rek'])
                    ->setCellValue('F'.$baseRow, $rowExcel['nama_rekening'])
                    ->setCellValue('G'.$baseRow, $rowExcel['rincian'])
                    ->setCellValue('H'.$baseRow, '')
                    ->setCellValue('I'.$baseRow, $rowExcel['vol_1'])
                    ->setCellValue('J'.$baseRow, $rowExcel['satuan_1'])
                    ->setCellValue('K'.$baseRow, 'x')
                    ->setCellValue('L'.$baseRow, $rowExcel['vol_2'])
                    ->setCellValue('M'.$baseRow, $rowExcel['satuan_2'])
                    ->setCellValue('N'.$baseRow, 'x')
                    ->setCellValue('O'.$baseRow, $rowExcel['vol_3'])
                    ->setCellValue('P'.$baseRow, $rowExcel['satuan_3'])
                    ->setCellValue('Q'.$baseRow, 'x')
                    ->setCellValue('R'.$baseRow, $rowExcel['vol_4'])
                    ->setCellValue('S'.$baseRow, $rowExcel['satuan_4'])
                    ->setCellValue('T'.$baseRow, '=')
                    ->setCellValue('U'.$baseRow, $rowExcel['vol'])
                    ->setCellValue('V'.$baseRow, $rowExcel['unit_cost'])
                    ->setCellValue('W'.$baseRow, $rowExcel['jumlah'])
                    ->setCellValue('X'.$baseRow, $rowExcel['jumlah_awal']);

                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':X' .$baseRow)->applyFromArray($styleArray);
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':X' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setVertical('top'); 
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':X' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setWrapText(true);
                                            
                    $baseRow++;
                    $baseRowService = $baseRowService+1;
                }
                
                $baseRowProgram=$baseRowProgram+$baseRowService;
                $baseRowProgram++;   
            }else{  
                $baseRowProgram++; 
            }
        }

        // $spreadsheet->getSecurity()->setLockWindows(true);
        // $spreadsheet->getSecurity()->setLockStructure(true);
        // $spreadsheet->getSecurity()->setWorkbookPassword("silverblack");

        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setPassword('silverblack');
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setSheet(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setSort(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setInsertRows(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setFormatCells(true);

        // $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
        

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_poa_desk_'.str_replace(' ','_',strtolower($namapkm)).'_'.$period.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function actionRealperform($id,$tw)
    {
        $session = Yii::$app->session;
        $period = $session['periodValue'];
        $session['triwulan'] = $tw;

        $query = 'SELECT g.nama_program, s.nama_pelayanan, v.nama_kegiatan, IFNULL(a.bentuk_kegiatan, v.nama_kegiatan) bentuk_kegiatan, m.target_awal, 
        m.satuan_awal, m.target_real, (m.target_real)/m.target_awal*100 prosentase
        FROM activity_data a
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN program g ON g.id=s.program_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN perfomance m ON m.activity_data_id=a.id AND m.triwulan="'.$tw.'"
        WHERE p.unit_id="'.$id.'" AND p.tahun="'.$period.'"
        ORDER BY g.id, s.id, v.id, a.id';

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model = $dataProvider->getModels();
        $unit = Unit::findOne($id);

        return $this->render('realperform', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'id' => $id,
            'namaUnit' => $unit->puskesmas
        ]);
    }

    public function actionDetailpoa($p)
    {
        $POST_VARIABLE=Yii::$app->request->post('Period');
        $period = $POST_VARIABLE['tahun'];

        $session = Yii::$app->session;
        if(!isset($period)){
            $period = $session['periodValue'];
        }else{
            $session['periodValue'] = $period;
        }

        $triwulan = $p;

        if($p == 1){
            $b1 = 1;
            $b2 = 3;
            $r = 'I';
            $twprev = '-';
        }

        if($p == 2){
            $b1 = 4;
            $b2 = 6;
            $r = 'II';
            $twprev = 'Triwulan I';
        }

        if($p == 3){
            $b1 = 7;
            $b2 = 9;
            $r = 'III';
            $twprev = 'Triwulan I - II';
        }

        if($p == 4){
            $b1 = 10;
            $b2 = 12;
            $r = 'IV';
            $twprev = 'Triwulan I - III';
        }

        $session['triwulan'] = $p;
        $session['label_tw'] = $twprev;

        $query = 'SELECT e.id, f.id perfomance_id, r.id realization_id, r.activity_detail_id, g.nama_program, s.nama_pelayanan, v.nama_kegiatan,  
        a.id activity_data_id, IFNULL(a.bentuk_kegiatan, v.nama_kegiatan) bentuk_kegiatan, a.target,
        a.sasaran, a.target, a.lokasi, a.pelaksana, 
        c.nama_rekening, e.rincian, e.vol_1, e.satuan_1, 
        IFNULL(e.vol_2,"") vol_2, IFNULL(e.satuan_2,"") satuan_2, 
        IFNULL(e.vol_3,"") vol_3, IFNULL(e.satuan_3,"") satuan_3,
        IFNULL(e.vol_4,"") vol_4, IFNULL(e.satuan_4,"") satuan_4,
        vol_1*IFNULL(vol_2,1)*IFNULL(vol_3,1)*IFNULL(vol_4,1) vol, 
        e.unit_cost, e.jumlah, IFNULL(reallalu.jml,0) jml_real_lalu, IFNULL(r.jumlah,0) jml_real, 
        ifnull(ROUND((IFNULL(reallalu.jml,0)+IFNULL(r.jumlah,0))/e.jumlah*100,2),0) persen
        FROM activity_detail e
        LEFT JOIN activity_data a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN program g ON g.id=s.program_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN account c ON c.id=e.account_id
        LEFT JOIN
        (
            SELECT z.activity_detail_id, SUM(z.jumlah) jml FROM realization z 
            WHERE z.triwulan < "'.$p.'"
            GROUP BY z.activity_detail_id
        ) reallalu ON reallalu.activity_detail_id=e.id
        LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan="'.$p.'"
        LEFT JOIN perfomance f ON f.activity_data_id=a.id AND f.triwulan="'.$p.'"
        WHERE p.unit_id="'.Yii::$app->user->identity->unit_id.'" AND p.tahun="'.$period.'"
        ORDER BY g.id, s.id, v.id, a.id';

        $session['qryreal'] = $query;

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model = $dataProvider->getModels();

        $unit = Unit::findOne(Yii::$app->user->identity->unit_id);

        $ceksp2d = Yii::$app->db->createCommand('SELECT IFNULL(SUM(s.jumlah),0) total FROM sp2d s WHERE YEAR(s.tanggal)=:tahun AND unit_id=:unit')
        ->bindValue(':unit', Yii::$app->user->identity->unit_id)
        ->bindValue(':tahun', $period)
        ->queryAll();

        foreach($ceksp2d as $sp2d){
            $session['sp2d'] = $sp2d['total'];
        }

        $ceksp2dbln = Yii::$app->db->createCommand('SELECT IFNULL(SUM(s.jumlah),0) total FROM sp2d s WHERE YEAR(s.tanggal)=:tahun AND MONTH(s.tanggal) >= :bulan1 AND MONTH(s.tanggal) <= :bulan2 AND unit_id=:unit')
        ->bindValue(':unit', Yii::$app->user->identity->unit_id)
        ->bindValue(':bulan1', $b1)
        ->bindValue(':bulan2', $b2)
        ->bindValue(':tahun', $period)
        ->queryAll();

        foreach($ceksp2dbln as $sp2dbln){
            $session['sp2dBln'] = $sp2dbln['total'];
        }

        if($sp2dbln['total'] == 0){
            Yii::$app->session->setFlash('danger', "SP2D belum dientri.");
            $session['sp2dStatus'] = false;
        }else{
            Yii::$app->session->setFlash('success', "Total SP2D Triwulan ".$r." Rp. ".number_format($sp2dbln['total'],0,',','.'));
            $session['sp2dStatus'] = true;
        }

        $cekstsreal = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, 

        CASE WHEN t.tw_1 = "1" THEN "Buka" ELSE "Kunci" END AS status_real_tw1,
        CASE WHEN t.tw_1 = "1" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-stats" END AS status_real_icon_tw1,
        
        CASE WHEN t.tw_2 = "2" THEN "Buka" ELSE "Kunci" END AS status_real_tw2,
        CASE WHEN t.tw_2 = "2" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-stats" END AS status_real_icon_tw2,
        
        CASE WHEN t.tw_3 = "3" THEN "Buka" ELSE "Kunci" END AS status_real_tw3,
        CASE WHEN t.tw_3 = "3" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-stats" END AS status_real_icon_tw3,
        
        CASE WHEN t.tw_4 = "4" THEN "Buka" ELSE "Kunci" END AS status_real_tw4,
        CASE WHEN t.tw_4 = "4" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-stats" END AS status_real_icon_tw4
        
        FROM activity_detail e
        LEFT JOIN activity_data a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN unit u ON u.id=p.unit_id
        LEFT JOIN status t ON t.unit_id=p.unit_id AND t.tahun=:tahun
        WHERE p.tahun=:tahun AND p.unit_id=:unit
        GROUP BY p.unit_id, p.pagu')
        ->bindValue(':unit', Yii::$app->user->identity->unit_id)
        ->bindValue(':tahun', $period)
        ->queryAll();

        foreach($cekstsreal as $ceksts){
            if($p == 1){$stsreal = $ceksts['status_real_tw1'];}
            if($p == 2){$stsreal = $ceksts['status_real_tw2'];}
            if($p == 3){$stsreal = $ceksts['status_real_tw3'];}
            if($p == 4){$stsreal = $ceksts['status_real_tw4'];}
        }

        return $this->render('real', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'id' => Yii::$app->user->identity->unit_id,
            'namaUnit' => $unit->puskesmas,
            'triwulan' => $triwulan,
            'twprev' => $twprev,
            'stsreal' => $stsreal
        ]);
    }

    public function actionDetailpoaadm($p,$unit_id)
    {
        $session = Yii::$app->session;
        if(!isset($period)){
            $period = $session['periodValue'];
        }else{
            $POST_VARIABLE=Yii::$app->request->post('Period');
            $period = $POST_VARIABLE['tahun'];

            $session['periodValue'] = $period;
        }

        $triwulan = $p;

        if($p == 1){
            $b1 = 1;
            $b2 = 3;
            $r = 'I';
            $twprev = '-';
        }

        if($p == 2){
            $b1 = 4;
            $b2 = 6;
            $r = 'II';
            $twprev = 'Triwulan I';
        }

        if($p == 3){
            $b1 = 7;
            $b2 = 9;
            $r = 'III';
            $twprev = 'Triwulan I - II';
        }

        if($p == 4){
            $b1 = 10;
            $b2 = 12;
            $r = 'IV';
            $twprev = 'Triwulan I - III';
        }

        $session['triwulan'] = $p;
        $session['label_tw'] = $twprev;

        $query = 'SELECT e.id, f.id perfomance_id, r.id realization_id, r.activity_detail_id, g.nama_program, s.nama_pelayanan, v.nama_kegiatan,  
        a.id activity_data_id, IFNULL(a.bentuk_kegiatan, v.nama_kegiatan) bentuk_kegiatan, a.target,
        a.sasaran, a.target, a.lokasi, a.pelaksana, e.tw1, e.tw2, e.tw3, e.tw4,
        c.nama_rekening, e.rincian, e.vol_1, e.satuan_1, 
        IFNULL(e.vol_2,"") vol_2, IFNULL(e.satuan_2,"") satuan_2, 
        IFNULL(e.vol_3,"") vol_3, IFNULL(e.satuan_3,"") satuan_3,
        IFNULL(e.vol_4,"") vol_4, IFNULL(e.satuan_4,"") satuan_4,
        vol_1*IFNULL(vol_2,1)*IFNULL(vol_3,1)*IFNULL(vol_4,1) vol, 
        e.unit_cost, e.jumlah, IFNULL(reallalu.jml,0) jml_real_lalu, IFNULL(r.jumlah,0) jml_real, 
        ifnull(ROUND((IFNULL(reallalu.jml,0)+IFNULL(r.jumlah,0))/e.jumlah*100,2),0) persen
        FROM activity_detail e
        LEFT JOIN activity_data a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN program g ON g.id=s.program_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN account c ON c.id=e.account_id
        LEFT JOIN
        (
            SELECT z.activity_detail_id, SUM(z.jumlah) jml FROM realization z 
            WHERE z.triwulan < "'.$p.'"
            GROUP BY z.activity_detail_id
        ) reallalu ON reallalu.activity_detail_id=e.id
        LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan="'.$p.'"
        LEFT JOIN perfomance f ON f.activity_data_id=a.id AND f.triwulan="'.$p.'"
        WHERE p.unit_id="'.$unit_id.'" AND p.tahun="'.$period.'"
        ORDER BY g.id, s.id, v.id, a.id';

        $session['qryreal'] = $query;

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model = $dataProvider->getModels();

        foreach($model as $model);

        $unit = Unit::findOne($unit_id);

        $ceksp2d = Yii::$app->db->createCommand('SELECT IFNULL(SUM(s.jumlah),0) total FROM sp2d s WHERE YEAR(s.tanggal)=:tahun AND unit_id=:unit')
        ->bindValue(':unit', $unit_id)
        ->bindValue(':tahun', $period)
        ->queryAll();

        foreach($ceksp2d as $sp2d){
            $session['sp2d'] = $sp2d['total'];
        }

        $ceksp2dbln = Yii::$app->db->createCommand('SELECT IFNULL(SUM(s.jumlah),0) total FROM sp2d s WHERE YEAR(s.tanggal)=:tahun AND MONTH(s.tanggal) >= :bulan1 AND MONTH(s.tanggal) <= :bulan2 AND unit_id=:unit')
        ->bindValue(':unit', $unit_id)
        ->bindValue(':bulan1', $b1)
        ->bindValue(':bulan2', $b2)
        ->bindValue(':tahun', $period)
        ->queryAll();

        foreach($ceksp2dbln as $sp2dbln){
            $session['sp2dBln'] = $sp2dbln['total'];
        }

        if($sp2dbln['total'] == 0){
            Yii::$app->session->setFlash('danger', "SP2D belum dientri.");
            $session['sp2dStatus'] = false;
        }else{
            Yii::$app->session->setFlash('success', "Total SP2D Triwulan ".$r." Rp. ".number_format($sp2dbln['total'],0,',','.'));
            $session['sp2dStatus'] = true;
        }

        $cekstsreal = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, 

        CASE WHEN t.tw_1 = "1" THEN "Buka" ELSE "Kunci" END AS status_real_tw1,
        CASE WHEN t.tw_1 = "1" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-stats" END AS status_real_icon_tw1,
        
        CASE WHEN t.tw_2 = "2" THEN "Buka" ELSE "Kunci" END AS status_real_tw2,
        CASE WHEN t.tw_2 = "2" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-stats" END AS status_real_icon_tw2,
        
        CASE WHEN t.tw_3 = "3" THEN "Buka" ELSE "Kunci" END AS status_real_tw3,
        CASE WHEN t.tw_3 = "3" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-stats" END AS status_real_icon_tw3,
        
        CASE WHEN t.tw_4 = "4" THEN "Buka" ELSE "Kunci" END AS status_real_tw4,
        CASE WHEN t.tw_4 = "4" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-stats" END AS status_real_icon_tw4
        
        FROM activity_detail e
        LEFT JOIN activity_data a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN unit u ON u.id=p.unit_id
        LEFT JOIN status t ON t.unit_id=p.unit_id AND t.tahun=:tahun
        WHERE p.tahun=:tahun AND p.unit_id=:unit
        GROUP BY p.unit_id, p.pagu')
        ->bindValue(':unit', $unit_id)
        ->bindValue(':tahun', $period)
        ->queryAll();

        foreach($cekstsreal as $ceksts){
            if($p == 1){$stsreal = $ceksts['status_real_tw1'];}
            if($p == 2){$stsreal = $ceksts['status_real_tw2'];}
            if($p == 3){$stsreal = $ceksts['status_real_tw3'];}
            if($p == 4){$stsreal = $ceksts['status_real_tw4'];}
        }

        return $this->render('realadm', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'id' => $unit_id,
            'namaUnit' => $unit->puskesmas,
            'triwulan' => $triwulan,
            'twprev' => $twprev,
            'stsreal' => $stsreal,
            'detailId' => $model['id'],
            'label' => $r
        ]);
    }

    public function actionPostTw($id,$tw,$val)
    {
        if($val == 'true'){$value='1';}else{$value='0';}
        if($tw == '1'){
            $update = Yii::$app->db->createCommand('update activity_detail set tw1=:val where id=:id')
            ->bindValue(':id',$id)
            ->bindValue(':val',$value)
            ->execute();
        }
        if($tw == '2'){
            $update = Yii::$app->db->createCommand('update activity_detail set tw2=:val where id=:id')
            ->bindValue(':id',$id)
            ->bindValue(':val',$value)
            ->execute();
        }
        if($tw == '3'){
            $update = Yii::$app->db->createCommand('update activity_detail set tw3=:val where id=:id')
            ->bindValue(':id',$id)
            ->bindValue(':val',$value)
            ->execute();
        }
        if($tw == '4'){
            $update = Yii::$app->db->createCommand('update activity_detail set tw4=:val where id=:id')
            ->bindValue(':id',$id)
            ->bindValue(':val',$value)
            ->execute();
        }
        // return $id.' '.$tw.' '.$val;
    }

    public function actionExportdetailpoa()
    {
        $session = Yii::$app->session;
        $data = Yii::$app->db->createCommand($session['qryreal'])->queryAll();

        $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $activeSheet->setCellValue('A'.'1', 'DATA REALISASI POA TW '.$session['triwulan'].' PUSKESMAS '.strtoupper(Yii::$app->user->identity->username));
        $activeSheet->setCellValue('A'.'2', 'PERIODE ' .$session['periodValue']);
        $baseRow=5;

        $activeSheet
            ->setCellValue('A'.'4', 'NO')
            ->setCellValue('B'.'4', 'KOMPONEN')
            ->setCellValue('C'.'4', 'SUB KOMPONEN')
            ->setCellValue('D'.'4', 'BENTUK KEGIATAN')
            ->setCellValue('E'.'4', 'REKENING')
            ->setCellValue('F'.'4', 'RINCIAN')
            ->setCellValue('G'.'4', 'POA')
            ->setCellValue('H'.'4', strtoupper($session['label_tw']))
            ->setCellValue('I'.'4', 'REALISASI')
            ->setCellValue('J'.'4', '%');

        $komponen = '';
        $subkomponen = '';
        foreach($data as $rowdata) {
            if($komponen == $rowdata['nama_pelayanan']){
                $komponen = '';
            }else{
                $komponen = $rowdata['nama_pelayanan'];
            }

            if($subkomponen == $rowdata['nama_kegiatan']){
                $subkomponen = '';
            }else{
                $subkomponen = $rowdata['nama_kegiatan'];
            }

            $activeSheet
            ->setCellValue('A'.$baseRow, $baseRow-4)
            ->setCellValue('B'.$baseRow, $komponen)
            ->setCellValue('C'.$baseRow, $subkomponen)
            ->setCellValue('D'.$baseRow, $rowdata['bentuk_kegiatan'])
            ->setCellValue('E'.$baseRow, $rowdata['nama_rekening'])
            ->setCellValue('F'.$baseRow, $rowdata['rincian'])
            ->setCellValue('G'.$baseRow, $rowdata['jumlah'])
            ->setCellValue('H'.$baseRow, $rowdata['jml_real_lalu'])
            ->setCellValue('I'.$baseRow, $rowdata['jml_real'])
            ->setCellValue('J'.$baseRow, round($rowdata['persen']));

            $komponen = $rowdata['nama_pelayanan'];
            $subkomponen = $rowdata['nama_kegiatan'];
            $baseRow++;
        }

        // $spreadsheet->getSecurity()->setLockWindows(true);
        // $spreadsheet->getSecurity()->setLockStructure(true);
        // $spreadsheet->getSecurity()->setWorkbookPassword("silverblack");

        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setPassword('silverblack');
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setSheet(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setSort(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setInsertRows(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setFormatCells(true);

        // $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_data_realisasi_tw_'.$session['triwulan'].'_'.$session['periodValue'].'_'.Yii::$app->user->identity->username.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function actionDataubah($id, $p)
    {
        if(Yii::$app->user->identity->unit_id !== 'DINKES'){
            $POST_VARIABLE=Yii::$app->request->post('Period');
            $period = $POST_VARIABLE['tahun'];
            $session = Yii::$app->session;
            $session['periodValue'] = $period;
            $unit = Yii::$app->user->identity->unit_id;
        }else{
            $session = Yii::$app->session;
            $period = $session['periodValue'];
            $unit = $id;
        }

        $session['unitId'] = $unit;

        if($p == 'def'){
            $session['poa'] = 'def';
            $session['poaLabel'] = ' Awal';
        }elseif($p == 'pergeseran'){
            $session['poa'] = 'pergeseran';
            $session['poaLabel'] = ' Pergeseran';
        }elseif($p == 'perubahan'){
            $session['poa'] = 'perubahan';
            $session['poaLabel'] = ' Perubahan';
        }

        if(Yii::$app->user->identity->unit_id == 'DINKES'){
            $query = 'SELECT g.nama_program, s.nama_pelayanan, v.nama_kegiatan,  IFNULL(a.bentuk_kegiatan, v.nama_kegiatan) bentuk_kegiatan, 
            a.sasaran, a.target, a.lokasi, a.pelaksana, c.nama_rekening, 
            d.vol_1 vol_1_awal, e.vol_1, d.satuan_1 satuan_1_awal, e.satuan_1, IFNULL(d.vol_2,1) vol_2_awal, IFNULL(e.vol_2,1) vol_2,
            IFNULL(d.satuan_2,"") satuan_2_awal, IFNULL(e.satuan_2,"") satuan_2, d.vol_1*IFNULL(d.vol_2,1) vol_awal, e.vol_1*IFNULL(e.vol_2,1) vol, 
            d.unit_cost unit_cost_awal, e.unit_cost, d.jumlah jumlah_awal, e.jumlah
            FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN program g ON g.id=s.program_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN account c ON c.id=e.account_id
            LEFT JOIN activity_detail d ON d.id=e.activity_detail_id
            WHERE p.unit_id="'.$unit.'" AND p.tahun='.$period.'
            ORDER BY g.id, s.id, v.id, a.id';
        }else{
            $query = 'SELECT g.nama_program, s.nama_pelayanan, v.nama_kegiatan,  IFNULL(a.bentuk_kegiatan, v.nama_kegiatan) bentuk_kegiatan, 
            a.sasaran, a.target, a.lokasi, a.pelaksana, c.nama_rekening, 
            d.vol_1 vol_1_awal, e.vol_1, d.satuan_1 satuan_1_awal, e.satuan_1, IFNULL(d.vol_2,1) vol_2_awal, IFNULL(e.vol_2,1) vol_2,
            IFNULL(d.satuan_2,"") satuan_2_awal, IFNULL(e.satuan_2,"") satuan_2, d.vol_1*IFNULL(d.vol_2,1) vol_awal, e.vol_1*IFNULL(e.vol_2,1) vol, 
            d.unit_cost unit_cost_awal, e.unit_cost, d.jumlah jumlah_awal, e.jumlah
            FROM activity_detail_ubah e
            LEFT JOIN activity_data_ubah a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN program g ON g.id=s.program_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN account c ON c.id=e.account_id
            LEFT JOIN activity_detail d ON d.id=e.activity_detail_id
            WHERE p.unit_id="'.$unit.'" AND p.tahun='.$period.'
            ORDER BY g.id, s.id, v.id, a.id';
        }

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model = $dataProvider->getModels();

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
            ->bindValue(':periodValue', $period)
            ->bindValue(':unitId', $unit)
            ->queryAll();

            foreach($cekPoa as $cekPoa){
                if($cekPoa['jumlah'] > $cekPoa['pagu'] && $cekPoa['pagu'] > 0){
                    Yii::$app->session->setFlash('error', 'PERHATIAN!, Total Entri POA melebihi Pagu BOK.');
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
            ->bindValue(':periodValue', $period)
            ->bindValue(':unitId', $unit)
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
            ->bindValue(':periode', $period)
            ->bindValue(':unit', $unit)
            ->queryAll();

            foreach($progress as $progress);

            $session = Yii::$app->session;
            $session['pagu'] = $progress['pagu'];
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
            ->bindValue(':periode', $period)
            ->bindValue(':unit', $unit)
            ->queryAll();

            foreach($progress as $progress);

            if(!empty($progress)){
                $session['pagu_ubah'] = $progress['pagu_ubah'];
            }else{
                $session['pagu_ubah'] = 0;
                $progress['prosentase'] = null;
            }
        }

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

        $unitnya = Unit::findOne($id);
        $session['unitName'] = 'Puskesmas '.$unitnya->puskesmas;

        return $this->render('detail_perubahan', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'namaUnit' => 'Puskesmas '.$unitnya->puskesmas,
        ]);
    }

    public function actionCekprd()
    {
        $POST_VARIABLE=Yii::$app->request->post('Period');
        $tahun = $POST_VARIABLE['tahun'];

        $session = Yii::$app->session;
        unset($session['bulan']);

        if(!empty($tahun)){
            return $this->redirect(['ukm/index', 'tahun' => $tahun]);
        }else{
            if(!empty($session['tahun'])){
                return $this->redirect(['ukm/index', 'tahun' => $session['tahun']]);
            }else{
                Yii::$app->session->setFlash('error', "Periode Tahun belum dipilih!");
                return $this->redirect(['period/create', 'p' => 'def']);
            }
        }
        
    }

    public function actionCekprd2()
    {
        $POST_VARIABLE=Yii::$app->request->post('Period');
        $tahun = $POST_VARIABLE['tahun'];

        $session = Yii::$app->session;
        unset($session['bulan']);

        if(!empty($tahun)){
            return $this->redirect(['covid/index', 'tahun' => $tahun]);
        }else{
            if(!empty($session['tahun'])){
                return $this->redirect(['covid/index', 'tahun' => $session['tahun']]);
            }else{
                Yii::$app->session->setFlash('error', "Periode Tahun belum dipilih!");
                return $this->redirect(['period/create', 'p' => 'def']);
            }
        }
        
    }

    public function actionCeksp2d()
    {
        $POST_VARIABLE=Yii::$app->request->post('Period');
        $tahun = $POST_VARIABLE['tahun'];

        $session = Yii::$app->session;
        $session['periodValue'] = $tahun;

        if(!empty($tahun)){
            return $this->redirect(['/sp2d', 'tahun' => $tahun, 'unit' => Yii::$app->user->identity->unit_id]);
        }else{
            if(!empty($session['tahun'])){
                return $this->redirect(['/sp2d', 'tahun' => $session['tahun'], 'unit' => Yii::$app->user->identity->unit_id]);
            }else{
                Yii::$app->session->setFlash('error', "Periode Tahun belum dipilih!");
                return $this->redirect(['period/create', 'p' => 'def']);
            }
        }
    }

    public function actionRekapSp2d($id)
    {
        if($id == 21){$twawal=1;$twakhir=3;}
        if($id == 22){$twawal=4;$twakhir=6;}
        if($id == 23){$twawal=7;$twakhir=9;}
        if($id == 24){$twawal=10;$twakhir=12;}
        if($id == 0){$twawal=1;$twakhir=12;}
        if($id == 1){$twawal=$id;$twakhir=$id;}
        if($id == 2){$twawal=$id;$twakhir=$id;}
        if($id == 3){$twawal=$id;$twakhir=$id;}
        if($id == 4){$twawal=$id;$twakhir=$id;}
        if($id == 5){$twawal=$id;$twakhir=$id;}
        if($id == 6){$twawal=$id;$twakhir=$id;}
        if($id == 7){$twawal=$id;$twakhir=$id;}
        if($id == 8){$twawal=$id;$twakhir=$id;}
        if($id == 9){$twawal=$id;$twakhir=$id;}
        if($id == 10){$twawal=$id;$twakhir=$id;}
        if($id == 11){$twawal=$id;$twakhir=$id;}
        if($id == 12){$twawal=$id;$twakhir=$id;}

        $session = Yii::$app->session;
        $rekap = 'SELECT u.puskesmas, DATE_FORMAT(s.tanggal,"%d-%m-%Y") tanggal, s.no_sp2d, s.jenis_spm, s.uraian, s.jumlah FROM sp2d s
        LEFT JOIN unit u ON u.id=s.unit_id
        WHERE YEAR(s.tanggal)='.$session['periodValue'].' AND MONTH(s.tanggal)>='.$twawal.' AND MONTH(s.tanggal)<='.$twakhir.'
        ORDER BY u.puskesmas, s.tanggal';

        $session['tw'] = $id;
        $session['qrysp2d'] = $rekap;

        $dataProvider = new SqlDataProvider([
            'sql' => $rekap,
            'pagination' => false
        ]);

        return $this->render('rekapsp2d',[
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionRekapReal($id)
    {
        $session = Yii::$app->session;
        $session['tw'] = $id;
        $real = 'SELECT p.id, p.nama_program, s.id komid, s.nama_pelayanan, IFNULL(kalijambe.jml_real,0) kalijambe, IFNULL(plupuh1.jml_real,0) plupuh1, IFNULL(plupuh2.jml_real,0) plupuh2, IFNULL(masaran1.jml_real,0) masaran1,
            IFNULL(masaran2.jml_real,0) masaran2, IFNULL(kedawung1.jml_real,0) kedawung1, IFNULL(kedawung2.jml_real,0) kedawung2, IFNULL(sambirejo.jml_real,0) sambirejo, IFNULL(gondang.jml_real,0) gondang,
            IFNULL(sambungmacan1.jml_real,0) sambungmacan1, IFNULL(sambungmacan2.jml_real,0) sambungmacan2, IFNULL(ngrampal.jml_real,0) ngrampal, IFNULL(karangmalang.jml_real,0) karangmalang, IFNULL(sragen.jml_real,0) sragen,
            IFNULL(sidoharjo.jml_real,0) sidoharjo, IFNULL(tanon1.jml_real,0) tanon1, IFNULL(tanon2.jml_real,0) tanon2, IFNULL(gemolong.jml_real,0) gemolong, IFNULL(miri.jml_real,0) miri,
            IFNULL(sumberlawang.jml_real,0) sumberlawang, IFNULL(mondokan.jml_real,0) mondokan, IFNULL(sukodono.jml_real,0) sukodono, IFNULL(gesi.jml_real,0) gesi, IFNULL(tangen.jml_real,0) tangen,
            IFNULL(jenar.jml_real,0) jenar,
            
            IFNULL(kalijambe.jml_real,0) + IFNULL(plupuh1.jml_real,0) + IFNULL(plupuh2.jml_real,0) + IFNULL(masaran1.jml_real,0) +
            IFNULL(masaran2.jml_real,0) + IFNULL(kedawung1.jml_real,0) + IFNULL(kedawung2.jml_real,0) + IFNULL(sambirejo.jml_real,0) + IFNULL(gondang.jml_real,0) +
            IFNULL(sambungmacan1.jml_real,0) + IFNULL(sambungmacan2.jml_real,0) + IFNULL(ngrampal.jml_real,0) + IFNULL(karangmalang.jml_real,0) + IFNULL(sragen.jml_real,0) +
            IFNULL(sidoharjo.jml_real,0) + IFNULL(tanon1.jml_real,0) + IFNULL(tanon2.jml_real,0) + IFNULL(gemolong.jml_real,0) + IFNULL(miri.jml_real,0) +
            IFNULL(sumberlawang.jml_real,0) + IFNULL(mondokan.jml_real,0) + IFNULL(sukodono.jml_real,0) + IFNULL(gesi.jml_real,0) + IFNULL(tangen.jml_real,0) +
            IFNULL(jenar.jml_real,0) total

        FROM service s
        LEFT JOIN program p ON p.id=s.program_id
        LEFT JOIN
        (
            -- kalijambe	
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309010101" group BY s.id
        ) kalijambe ON kalijambe.id=s.id
        LEFT JOIN
        (
            -- plupuh1
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309020101" group BY s.id
        ) plupuh1 ON plupuh1.id=s.id
        LEFT JOIN
        (
            -- plupuh2
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309020202" group BY s.id
        ) plupuh2 ON plupuh2.id=s.id
        LEFT JOIN
        (
            -- masaran1
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309030101" group BY s.id
        ) masaran1 ON masaran1.id=s.id
        LEFT JOIN
        (
            -- masaran2
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309040102" group BY s.id
        ) masaran2 ON masaran2.id=s.id
        LEFT JOIN
        (
            -- kedawung1
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309040201" group BY s.id
        ) kedawung1 ON kedawung1.id=s.id
        LEFT JOIN
        (
            -- kedawung2
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309050201" group BY s.id
        ) kedawung2 ON kedawung2.id=s.id
        LEFT JOIN
        (
            -- sambirejo
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309050202" group BY s.id
        ) sambirejo ON sambirejo.id=s.id
        LEFT JOIN
        (
            -- gondang
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309060201" group BY s.id
        ) gondang ON gondang.id=s.id
        LEFT JOIN
        (
            -- sambungmacan1
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309070201" group BY s.id
        ) sambungmacan1 ON sambungmacan1.id=s.id
        LEFT JOIN
        (
            -- sambungmacan2
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309080102" group BY s.id
        ) sambungmacan2 ON sambungmacan2.id=s.id
        LEFT JOIN
        (
            -- ngrampal
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309090201" group BY s.id
        ) ngrampal ON ngrampal.id=s.id
        LEFT JOIN
        (
            -- karangmalang
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309090202" group BY s.id
        ) karangmalang ON karangmalang.id=s.id
        LEFT JOIN
        (
            -- sragen
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309100201" group BY s.id
        ) sragen ON sragen.id=s.id
        LEFT JOIN
        (
            -- sidoharjo
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309110101" group BY s.id
        ) sidoharjo ON sidoharjo.id=s.id
        LEFT JOIN
        (
            -- tanon1
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309120101" group BY s.id
        ) tanon1 ON tanon1.id=s.id
        LEFT JOIN
        (
            -- tanon2
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309130201" group BY s.id
        ) tanon2 ON tanon2.id=s.id
        LEFT JOIN
        (
            -- gemolong
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309140101" group BY s.id
        ) gemolong ON gemolong.id=s.id
        LEFT JOIN
        (
            -- miri
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309150101" group BY s.id
        ) miri ON miri.id=s.id
        LEFT JOIN
        (
            -- sumberlawang
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309150202" group BY s.id
        ) sumberlawang ON sumberlawang.id=s.id
        LEFT JOIN
        (
            -- mondokan
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309160101" group BY s.id
        ) mondokan ON mondokan.id=s.id
        LEFT JOIN
        (
            -- sukodono
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309170101" group BY s.id
        ) sukodono ON sukodono.id=s.id
        LEFT JOIN
        (
            -- gesi
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309180101" group BY s.id
        ) gesi ON gesi.id=s.id
        LEFT JOIN
        (
            -- tangen
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309180102" group BY s.id
        ) tangen ON tangen.id=s.id
        LEFT JOIN
        (
            -- jenar
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309190101" group BY s.id
        ) jenar ON jenar.id=s.id
        WHERE p.tahun='.$session['periodValue'].' AND p.aktif=1 AND p.detail=0
        UNION
        SELECT p.id, p.nama_program, a.id komid, a.nama_kegiatan nama_pelayanan, IFNULL(kalijambe.jml_real,0) kalijambe, IFNULL(plupuh1.jml_real,0) plupuh1, IFNULL(plupuh2.jml_real,0) plupuh2, IFNULL(masaran1.jml_real,0) masaran1,
            IFNULL(masaran2.jml_real,0) masaran2, IFNULL(kedawung1.jml_real,0) kedawung1, IFNULL(kedawung2.jml_real,0) kedawung2, IFNULL(sambirejo.jml_real,0) sambirejo, IFNULL(gondang.jml_real,0) gondang,
            IFNULL(sambungmacan1.jml_real,0) sambungmacan1, IFNULL(sambungmacan2.jml_real,0) sambungmacan2, IFNULL(ngrampal.jml_real,0) ngrampal, IFNULL(karangmalang.jml_real,0) karangmalang, IFNULL(sragen.jml_real,0) sragen,
            IFNULL(sidoharjo.jml_real,0) sidoharjo, IFNULL(tanon1.jml_real,0) tanon1, IFNULL(tanon2.jml_real,0) tanon2, IFNULL(gemolong.jml_real,0) gemolong, IFNULL(miri.jml_real,0) miri,
            IFNULL(sumberlawang.jml_real,0) sumberlawang, IFNULL(mondokan.jml_real,0) mondokan, IFNULL(sukodono.jml_real,0) sukodono, IFNULL(gesi.jml_real,0) gesi, IFNULL(tangen.jml_real,0) tangen,
            IFNULL(jenar.jml_real,0) jenar,
            
            IFNULL(kalijambe.jml_real,0) + IFNULL(plupuh1.jml_real,0) + IFNULL(plupuh2.jml_real,0) + IFNULL(masaran1.jml_real,0) +
            IFNULL(masaran2.jml_real,0) + IFNULL(kedawung1.jml_real,0) + IFNULL(kedawung2.jml_real,0) + IFNULL(sambirejo.jml_real,0) + IFNULL(gondang.jml_real,0) +
            IFNULL(sambungmacan1.jml_real,0) + IFNULL(sambungmacan2.jml_real,0) + IFNULL(ngrampal.jml_real,0) + IFNULL(karangmalang.jml_real,0) + IFNULL(sragen.jml_real,0) +
            IFNULL(sidoharjo.jml_real,0) + IFNULL(tanon1.jml_real,0) + IFNULL(tanon2.jml_real,0) + IFNULL(gemolong.jml_real,0) + IFNULL(miri.jml_real,0) +
            IFNULL(sumberlawang.jml_real,0) + IFNULL(mondokan.jml_real,0) + IFNULL(sukodono.jml_real,0) + IFNULL(gesi.jml_real,0) + IFNULL(tangen.jml_real,0) +
            IFNULL(jenar.jml_real,0) total

        FROM activity a
        LEFT JOIN service s ON s.id=a.service_id
        LEFT JOIN program p ON p.id=s.program_id
        LEFT JOIN
        (
            -- kalijambe
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309010101" group BY v.id
        ) kalijambe ON kalijambe.id=a.id
        LEFT JOIN
        (
            -- plupuh1
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309020101" group BY v.id
        ) plupuh1 ON plupuh1.id=a.id
        LEFT JOIN
        (
            -- plupuh2
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309020202" group BY v.id
        ) plupuh2 ON plupuh2.id=a.id
        LEFT JOIN
        (
            -- masaran1
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309030101" group BY v.id
        ) masaran1 ON masaran1.id=a.id
        LEFT JOIN
        (
            -- masaran2
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309040102" group BY v.id
        ) masaran2 ON masaran2.id=a.id
        LEFT JOIN
        (
            -- kedawung1
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309040201" group BY v.id
        ) kedawung1 ON kedawung1.id=a.id
        LEFT JOIN
        (
            -- kedawung2
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309050201" group BY v.id
        ) kedawung2 ON kedawung2.id=a.id
        LEFT JOIN
        (
            -- sambirejo
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309050202" group BY v.id
        ) sambirejo ON sambirejo.id=a.id
        LEFT JOIN
        (
            -- gondang
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309060201" group BY v.id
        ) gondang ON gondang.id=a.id
        LEFT JOIN
        (
            -- sambungmacan1
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309070201" group BY v.id
        ) sambungmacan1 ON sambungmacan1.id=a.id
        LEFT JOIN
        (
            -- sambungmacan2
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309080102" group BY v.id
        ) sambungmacan2 ON sambungmacan2.id=a.id
        LEFT JOIN
        (
            -- ngrampal
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309090201" group BY v.id
        ) ngrampal ON ngrampal.id=a.id
        LEFT JOIN
        (
            -- karangmalang
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309090202" group BY v.id
        ) karangmalang ON karangmalang.id=a.id
        LEFT JOIN
        (
            -- sragen
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309100201" group BY v.id
        ) sragen ON sragen.id=a.id
        LEFT JOIN
        (
            -- sidoharjo
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309110101" group BY v.id
        ) sidoharjo ON sidoharjo.id=a.id
        LEFT JOIN
        (
            -- tanon1
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309120101" group BY v.id
        ) tanon1 ON tanon1.id=a.id
        LEFT JOIN
        (
            -- tanon2
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309130201" group BY v.id
        ) tanon2 ON tanon2.id=a.id
        LEFT JOIN
        (
            -- gemolong
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309140101" group BY v.id
        ) gemolong ON gemolong.id=a.id
        LEFT JOIN
        (
            -- miri
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309150101" group BY v.id
        ) miri ON miri.id=a.id
        LEFT JOIN
        (
            -- sumberlawang
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309150202" group BY v.id
        ) sumberlawang ON sumberlawang.id=a.id
        LEFT JOIN
        (
            -- mondokan
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309160101" group BY v.id
        ) mondokan ON mondokan.id=a.id
        LEFT JOIN
        (
            -- sukodono
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309170101" group BY v.id
        ) sukodono ON sukodono.id=a.id
        LEFT JOIN
        (
            -- gesi
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309180101" group BY v.id
        ) gesi ON gesi.id=a.id
        LEFT JOIN
        (
            -- tangen
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309180102" group BY v.id
        ) tangen ON tangen.id=a.id
        LEFT JOIN
        (
            -- jenar
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM activity_detail e
            LEFT JOIN activity_data a ON a.id=e.activity_data_id
            LEFT JOIN activity v ON v.id=a.activity_id
            LEFT JOIN service s ON s.id=v.service_id
            LEFT JOIN period p ON p.id=a.period_id
            LEFT JOIN realization r ON r.activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P3309190101" group BY v.id
        ) jenar ON jenar.id=a.id
        WHERE p.tahun='.$session['periodValue'].' AND p.aktif=1 AND p.detail=1
        ORDER BY id, komid';

        $session['qryrekapreal'] = $real;

        $dataProvider = new SqlDataProvider([
            'sql' => $real,
            'pagination' => false
        ]);

        return $this->render('rekapreal',[
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionExportRekapReal()
    {
        $session = Yii::$app->session;

        $data = Yii::$app->db->createCommand($session['qryrekapreal'])
        ->queryAll();

        $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $activeSheet->setCellValue('A'.'1', 'REKAP REALISASI PER UPAYA');
        $activeSheet->setCellValue('A'.'2', 'PERIODE ' .$session['periodValue']);
        $baseRow=5;

        $activeSheet
            ->setCellValue('A'.'4', 'NO')
            ->setCellValue('B'.'4', 'RINCIAN MENU KEGIATAN')
            ->setCellValue('C'.'4', 'KOMPONEN')
            ->setCellValue('D'.'4', 'AMPEL')
            ->setCellValue('E'.'4', 'ANDONG')
            ->setCellValue('F'.'4', 'BANYUDONO 1')
            ->setCellValue('G'.'4', 'BANYUDONO 2')
            ->setCellValue('H'.'4', 'BOYOLALI 1')
            ->setCellValue('I'.'4', 'BOYOLALI 2')
            ->setCellValue('J'.'4', 'CEPOGO')
            ->setCellValue('K'.'4', 'GLADAGSARI')
            ->setCellValue('L'.'4', 'JUWANGI')
            ->setCellValue('M'.'4', 'KARANGGEDE')
            ->setCellValue('N'.'4', 'KEMUSU')
            ->setCellValue('O'.'4', 'KLEGO 1')
            ->setCellValue('P'.'4', 'KLEGO 2')
            ->setCellValue('Q'.'4', 'MOJOSONGO')
            ->setCellValue('R'.'4', 'MUSUK')
            ->setCellValue('S'.'4', 'NGEMPLAK')
            ->setCellValue('T'.'4', 'NOGOSARI')
            ->setCellValue('U'.'4', 'SAMBI')
            ->setCellValue('V'.'4', 'SAWIT')
            ->setCellValue('W'.'4', 'SELO')
            ->setCellValue('X'.'4', 'SIMO')
            ->setCellValue('Y'.'4', 'TAMANSARI')
            ->setCellValue('Z'.'4', 'TERAS')
            ->setCellValue('AA'.'4', 'WONOSAMODRO')
            ->setCellValue('AB'.'4', 'WONOSEGORO')
            ->setCellValue('AC'.'4', 'TOTAL');

        $rinci = '';

        foreach($data as $rowdata) {
            if($rinci == $rowdata['nama_program']){
                $rinci = '';
            }else{
                $rinci = $rowdata['nama_program'];
            }
        
            $activeSheet
            ->setCellValue('A'.$baseRow, $baseRow-4)
            ->setCellValue('B'.$baseRow, $rinci)
            ->setCellValue('C'.$baseRow, $rowdata['nama_pelayanan'])
            ->setCellValue('D'.$baseRow, $rowdata['ampel'])
            ->setCellValue('E'.$baseRow, $rowdata['andong'])
            ->setCellValue('F'.$baseRow, $rowdata['banyudono1'])
            ->setCellValue('G'.$baseRow, $rowdata['banyudono2'])
            ->setCellValue('H'.$baseRow, $rowdata['boyolali1'])
            ->setCellValue('I'.$baseRow, $rowdata['boyolali2'])
            ->setCellValue('J'.$baseRow, $rowdata['cepogo'])
            ->setCellValue('K'.$baseRow, $rowdata['gladagsari'])
            ->setCellValue('L'.$baseRow, $rowdata['juwangi'])
            ->setCellValue('M'.$baseRow, $rowdata['karanggede'])
            ->setCellValue('N'.$baseRow, $rowdata['kemusu'])
            ->setCellValue('O'.$baseRow, $rowdata['klego1'])
            ->setCellValue('P'.$baseRow, $rowdata['klego2'])
            ->setCellValue('Q'.$baseRow, $rowdata['mojosongo'])
            ->setCellValue('R'.$baseRow, $rowdata['musuk'])
            ->setCellValue('S'.$baseRow, $rowdata['ngemplak'])
            ->setCellValue('T'.$baseRow, $rowdata['nogosari'])
            ->setCellValue('U'.$baseRow, $rowdata['sambi'])
            ->setCellValue('V'.$baseRow, $rowdata['sawit'])
            ->setCellValue('W'.$baseRow, $rowdata['selo'])
            ->setCellValue('X'.$baseRow, $rowdata['simo'])
            ->setCellValue('Y'.$baseRow, $rowdata['tamansari'])
            ->setCellValue('Z'.$baseRow, $rowdata['teras'])
            ->setCellValue('AA'.$baseRow, $rowdata['wonosamodro'])
            ->setCellValue('AB'.$baseRow, $rowdata['wonosegoro'])
            ->setCellValue('AC'.$baseRow, $rowdata['total']);
            $rinci = $rowdata['nama_program'];
            // $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':E' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setWrapText(true)->setVertical('top');
            $baseRow++;
        }

        // $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_rekap_realisasi_per_upaya_tw_'.$session['tw'].'_'.$session['periodValue'].'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function actionExportRekapSp2d()
    {
        $session = Yii::$app->session;
        $period = $session['periodValue'];

        $data = Yii::$app->db->createCommand($session['qrysp2d'])
        ->queryAll();

        $puskesmas = '';
        $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $activeSheet->setCellValue('A'.'1', 'REKAP SP2D PER PUSKESMAS');
        $activeSheet->setCellValue('A'.'2', 'PERIODE ' .$session['periodValue']);
        $baseRow=5;

        $activeSheet
            ->setCellValue('A'.'4', 'NO')
            ->setCellValue('B'.'4', 'PUSKESMAS')
            ->setCellValue('C'.'4', 'TANGGAL')
            ->setCellValue('D'.'4', 'NO SP2D')
            ->setCellValue('E'.'4', 'JENIS SPM')
            ->setCellValue('F'.'4', 'URAIAN')
            ->setCellValue('G'.'4', 'JUMLAH');

        $pkm = '';
        $jml = 0;
        $total = 0;
        $row = 1;

        foreach($data as $rowdata) {
            if($pkm == $rowdata['puskesmas']){
                $pkm = '';
            }else{
                if($baseRow > 6){
                    // $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRow. ':E' .$baseRow);
                    $activeSheet->setCellValue('F'.$baseRow, 'Jumlah')
                    ->setCellValue('G'.$baseRow, $jml);
                    $baseRow = $baseRow + 1;
                    $total = $total + $jml;
                    $jml = 0;
                }
                $activeSheet->setCellValue('A'.$baseRow, $row);
                $pkm = $rowdata['puskesmas'];
                $row++;
            }

            $activeSheet
            ->setCellValue('B'.$baseRow, $pkm)
            ->setCellValue('C'.$baseRow, $rowdata['tanggal'])
            ->setCellValue('D'.$baseRow, $rowdata['no_sp2d'])
            ->setCellValue('E'.$baseRow, $rowdata['jenis_spm'])
            ->setCellValue('F'.$baseRow, $rowdata['uraian'])
            ->setCellValue('G'.$baseRow, $rowdata['jumlah']);


            $pkm = $rowdata['puskesmas'];
            $jml = $jml + $rowdata['jumlah'];
            $baseRow++;
        }

        // $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRow. ':F' .$baseRow);
        $activeSheet->setCellValue('F'.$baseRow, 'Jumlah')
        ->setCellValue('G'.$baseRow, $jml);

        $totalfix = $total +$jml;

        $baseRow = $baseRow + 1;
        $activeSheet->setCellValue('F'.$baseRow, 'Total')
        ->setCellValue('G'.$baseRow, $totalfix);

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_rekap_sp2d_'.$period.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function actionRekapRekening()
    {
        $session = Yii::$app->session;
        $data = 'SELECT g.nama_program, v.nama_kegiatan, c.nama_rekening, IFNULL(SUM(e.vol_1),0) jml_vol_1, IFNULL(SUM(e.vol_2),0) jml_vol_2, IFNULL(SUM(e.vol_3),0) jml_vol_3, IFNULL(SUM(e.vol_4),0) jml_vol_4, SUM(e.jumlah) sub_total
        FROM activity_detail e
        LEFT JOIN activity_data a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN program g ON g.id=s.program_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN account c ON c.id=e.account_id
        WHERE p.tahun='.$session['periodValue'].'
        group BY g.nama_program, v.nama_kegiatan, c.nama_rekening
        ORDER BY g.id, v.id, c.id';

        $dataProvider = new SqlDataProvider([
            'sql' => $data,
            'pagination' => false
        ]);

        return $this->render('rekaprek',[
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionExportxlsrek()
    {
        $session = Yii::$app->session;

        $data = Yii::$app->db->createCommand('SELECT g.nama_program, v.nama_kegiatan, c.nama_rekening, IFNULL(SUM(e.vol_1),0) jml_vol_1, IFNULL(SUM(e.vol_2),0) jml_vol_2, IFNULL(SUM(e.vol_3),0) jml_vol_3, IFNULL(SUM(e.vol_4),0) jml_vol_4, IFNULL(SUM(e.jumlah),0) sub_total
        FROM activity_detail e
        LEFT JOIN activity_data a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN program g ON g.id=s.program_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN account c ON c.id=e.account_id
        WHERE p.tahun=:period
        group BY g.nama_program, v.nama_kegiatan, c.nama_rekening
        ORDER BY g.id, v.id, c.id')
        ->bindValue(':period', $session['periodValue'])
        ->queryAll();

        $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $activeSheet->setCellValue('A'.'1', 'REKAP PER REKENING');
        $activeSheet->setCellValue('A'.'2', 'PERIODE ' .$session['periodValue']);
        $baseRow=5;

        $activeSheet
            ->setCellValue('A'.'4', 'NO')
            ->setCellValue('B'.'4', 'RINCIAN MENU KEGIATAN')
            ->setCellValue('C'.'4', 'KOMPONEN')
            ->setCellValue('D'.'4', 'REKENING PEMBIAYAAN')
            ->setCellValue('E'.'4', 'VOl 1')
            ->setCellValue('F'.'4', 'VOL 2')
            ->setCellValue('G'.'4', 'VOl 3')
            ->setCellValue('H'.'4', 'VOL 4')
            ->setCellValue('I'.'4', 'SUB TOTAL');

        $rinci = '';
        $komponen = '';

        foreach($data as $rowdata) {
            if($rinci == $rowdata['nama_program']){
                $rinci = '';
            }else{
                $rinci = $rowdata['nama_program'];
            }
            if($komponen == $rowdata['nama_kegiatan']){
                $komponen = '';
            }else{
                $komponen = $rowdata['nama_kegiatan'];
            }

            $activeSheet
            ->setCellValue('A'.$baseRow, $baseRow-4)
            ->setCellValue('B'.$baseRow, $rinci)
            ->setCellValue('C'.$baseRow, $komponen)
            ->setCellValue('D'.$baseRow, $rowdata['nama_rekening'])
            ->setCellValue('E'.$baseRow, $rowdata['jml_vol_1'])
            ->setCellValue('F'.$baseRow, $rowdata['jml_vol_2'])
            ->setCellValue('G'.$baseRow, $rowdata['jml_vol_3'])
            ->setCellValue('H'.$baseRow, $rowdata['jml_vol_4'])
            ->setCellValue('I'.$baseRow, $rowdata['sub_total']);
            $rinci = $rowdata['nama_program'];
            $komponen = $rowdata['nama_kegiatan'];
            // $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':E' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setWrapText(true)->setVertical('top');
            $baseRow++;
        }

        // $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_rekap_rekening_'.$session['periodValue'].'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function actionRekapPkmDetail($cond)
    {
        $session = Yii::$app->session;
        if($cond == 'def'){
            unset($session['puskesmas']);
            unset($session['komponen']);
            unset($session['subkomponen']);
            $session['cond'] = $cond;
            // $session['puskesmas'] = $_COOKIE['puskesmas'];
            $session['komponen'] = 0;
        }else{
            $session['puskesmas'] = $_COOKIE['puskesmas'];
            $session['komponen'] = $_COOKIE['komponen'];
            $session['subkomponen'] = $_COOKIE['subkomponen'];
            $session['cond'] = $cond;
        }
        
        $query = 'SELECT u.puskesmas, g.nama_program, s.nama_pelayanan, v.nama_kegiatan, IFNULL(a.bentuk_kegiatan, v.nama_kegiatan) bentuk_kegiatan, 
        a.sasaran, a.target, a.lokasi, a.pelaksana, c.kode,
        c.nama_rekening, e.rincian, e.vol_1, e.satuan_1, 
        IFNULL(e.vol_2,"") vol_2, IFNULL(e.satuan_2,"") satuan_2, 
        IFNULL(e.vol_3,"") vol_3, IFNULL(e.satuan_3,"") satuan_3,
        IFNULL(e.vol_4,"") vol_4, IFNULL(e.satuan_4,"") satuan_4,
        vol_1*IFNULL(vol_2,1)*IFNULL(vol_3,1)*IFNULL(vol_4,1) vol, 
        e.unit_cost, e.jumlah
        FROM activity_detail e
        LEFT JOIN activity_data a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN program g ON g.id=s.program_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN account c ON c.id=e.account_id
        LEFT JOIN unit u ON u.id=p.unit_id
        WHERE p.tahun="'.$session['periodValue'].'" 
        AND s.id = "'.$session['komponen'].'" 
        AND v.id LIKE "'.$session['subkomponen'].'%"
        AND u.puskesmas LIKE "%'.$session['puskesmas'].'"
        ORDER BY u.puskesmas, g.id, s.id, v.id, a.id';

        $session['qrydetail'] = $query;

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model = $dataProvider->getModels();

        // return $query;
        return $this->render('detail_komponen', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'namaUnit' => Yii::$app->user->identity->alias
        ]);

        // return $session['qrydetail'];
    }

    public function actionGetActivity($id)
    {
        $countActivity = Activity::find()
        ->where(['service_id' => $id])
        ->count();
        
        $posts = Activity::find()
        ->where(['service_id' => $id])
        // ->orderBy('nama_kegiatan ASC')
        ->all();
        
        if($countActivity>0){
            echo "<option>Pilih Sub Komponen</option>";
            foreach($posts as $post){
            echo "<option value='".$post->id."'>".$post->nama_kegiatan."</option>";
            }
        }
        else{
            echo "<option>Pilih Sub Komponen</option>";
        }
    }

    public function actionExportxlsdetail()
    {
        $session = Yii::$app->session;
        $period = $session['periodValue'];

        Yii::$app->db->createCommand('DELETE FROM export_program WHERE username=:username AND period=:periodValue ')
        ->bindValue(':username', Yii::$app->user->identity->unit_id)
        ->bindValue(':periodValue', $period)
        ->execute();

        $data = Yii::$app->db->createCommand($session['qrydetail'])
        ->queryAll();

        $pkm = '';
        $program = '';
        $komponen = '';
        $kegiatan = '';
        $bentuk = '';
        foreach ($data as $row) {
            $exportprogram =  new Exportprogram();

            if ($pkm !== $row['puskesmas']) {
                $exportprogram->unit=$row['puskesmas']; $pkm = $row['puskesmas'];
                
                $kegiatan = '';
                if ($kegiatan !== $row['nama_kegiatan']) {
                    $exportprogram->nama_kegiatan=$row['nama_kegiatan']; $kegiatan = $row['nama_kegiatan'];

                    $bentuk = '';
                    if ($bentuk !== $row['bentuk_kegiatan']) {
                        $exportprogram->bentuk_kegiatan=$row['bentuk_kegiatan']; $bentuk = $row['bentuk_kegiatan'];
                    }
                }
            }else{
                $exportprogram->unit=$row['puskesmas']; $pkm = $row['puskesmas'];

                if ($kegiatan !== $row['nama_kegiatan']) {
                    $exportprogram->nama_kegiatan=$row['nama_kegiatan']; $kegiatan = $row['nama_kegiatan'];
                    $bentuk = '';
                    if ($bentuk !== $row['bentuk_kegiatan']) {
                        $exportprogram->bentuk_kegiatan=$row['bentuk_kegiatan']; $bentuk = $row['bentuk_kegiatan'];
                    }
                }else{
                    if ($bentuk !== $row['bentuk_kegiatan']) {
                        $exportprogram->bentuk_kegiatan=$row['bentuk_kegiatan']; $bentuk = $row['bentuk_kegiatan'];
                    }
                }
            }
   
            $exportprogram->nama_program=$row['nama_program'];
            
            if ($komponen !== $row['nama_pelayanan']) {
                $exportprogram->nama_pelayanan=$row['nama_pelayanan']; $komponen = $row['nama_pelayanan'];
            }

            // if ($kegiatan !== $row['nama_kegiatan']) {
            //     $exportprogram->nama_kegiatan=$row['nama_kegiatan']; $kegiatan = $row['nama_kegiatan'];
            // }

            // if ($bentuk !== $row['bentuk_kegiatan']) {
                // $exportprogram->bentuk_kegiatan=$row['bentuk_kegiatan']; //$bentuk = $row['bentuk_kegiatan'];
                $exportprogram->sasaran=$row['sasaran'];
                $exportprogram->target=$row['target'];
                $exportprogram->lokasi=$row['lokasi'];
                $exportprogram->pelaksana=$row['pelaksana'];
            // }

            $exportprogram->rek=$row['kode'];
            $exportprogram->nama_rekening=$row['nama_rekening'];
            $exportprogram->rincian=$row['rincian'];

            $exportprogram->vol_1=$row['vol_1'];
            $exportprogram->satuan_1=$row['satuan_1'];
            $exportprogram->vol_2=$row['vol_2'];
            $exportprogram->satuan_2=$row['satuan_2'];

            $exportprogram->vol_3=$row['vol_3'];
            $exportprogram->satuan_3=$row['satuan_3'];
            $exportprogram->vol_4=$row['vol_4'];
            $exportprogram->satuan_4=$row['satuan_4'];

            $exportprogram->vol=$row['vol'];
            $exportprogram->unit_cost=$row['unit_cost'];
            $exportprogram->jumlah=$row['jumlah'];
            $exportprogram->username=Yii::$app->user->identity->unit_id;
            $exportprogram->period=$period;
            $exportprogram->save();
        }

        $dataprogram = Yii::$app->db->createCommand('SELECT p.*, e.unit FROM unit p
        RIGHT JOIN export_program e ON e.unit=p.puskesmas
        WHERE e.username=:username
        GROUP BY p.puskesmas
        ORDER BY p.puskesmas')
        // ->bindValue(':tahun', $period)
        ->bindValue(':username', Yii::$app->user->identity->unit_id)
        ->queryAll();

        $inputFileName = Yii::getAlias('@app/views/exportaccount').'/_export_komponen_dinas.xlsx';

        /** Load $inputFileName to a Spreadsheet Object  **/
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        // $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();

        $styleArrayBold = [
            'font' => [
                'bold' => true,
            ],
        ];

        $service = Service::findOne($session['komponen']);
        $activeSheet->setCellValue('A1', $service->nama_pelayanan);
        $spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray($styleArrayBold);

        $baseRowAwal = 0;
        $baseRowProgram = 4;
        $baseRowService = 0;

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $styleArrayHeader = [
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        foreach ($dataprogram as $dataprogram) {
            $dataExcel = Yii::$app->db->createCommand('SELECT e.*, p.id FROM export_program e
            LEFT JOIN dept_program p ON p.nama_program=e.nama_program AND p.tahun=:periodValue
            where e.unit=:unitnya AND username=:username AND period=:periodValue')
            ->bindValue(':username', Yii::$app->user->identity->unit_id)
            ->bindValue(':periodValue', $period)
            ->bindValue(':unitnya', $dataprogram['unit'])
            ->queryAll();

            $jumlahPkm = Yii::$app->db->createCommand('SELECT SUM(e.jumlah) total FROM export_program e
            LEFT JOIN dept_program p ON p.nama_program=e.nama_program AND p.tahun=:periodValue
            where e.unit=:unitnya AND username=:username AND period=:periodValue')
            ->bindValue(':username', Yii::$app->user->identity->unit_id)
            ->bindValue(':periodValue', $period)
            ->bindValue(':unitnya', $dataprogram['unit'])
            ->queryAll();

            foreach($jumlahPkm as $jmlpkm);
            // $exportprogram->jumlah_awal = $jmlpkm['total'];

            $count = count($dataExcel);

            $baseRowAwal = $baseRowAwal+1;
            $activeSheet->setCellValue('A'.$baseRowProgram, $baseRowAwal) 
            ->setCellValue('C'.$baseRowProgram, $dataprogram['unit'])
            ->setCellValue('X'.$baseRowProgram, $jmlpkm['total']);

            $spreadsheet->getActiveSheet()->mergeCells('C'.$baseRowProgram. ':W' .$baseRowProgram);
            $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowProgram. ':X' .$baseRowProgram)->applyFromArray($styleArrayHeader);
            $activeSheet->getStyle('A'.$baseRowProgram. ':X' .$baseRowProgram)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('9BC2E6');
                 
            $baseRowService = 0;
            $baseRow = $baseRowProgram+1;
            
            if ($count > 0) {
                foreach($dataExcel as $rowExcel) {
                    $activeSheet->setCellValue('A'.$baseRow, '')
                    ->setCellValue('B'.$baseRow, '')
                    ->setCellValue('C'.$baseRow, $rowExcel['nama_kegiatan'])
                    ->setCellValue('D'.$baseRow, $rowExcel['bentuk_kegiatan'])
                    ->setCellValue('E'.$baseRow, $rowExcel['rek'])
                    ->setCellValue('F'.$baseRow, $rowExcel['nama_rekening'])
                    ->setCellValue('G'.$baseRow, $rowExcel['rincian'])
                    ->setCellValue('H'.$baseRow, '')
                    ->setCellValue('I'.$baseRow, $rowExcel['vol_1'])
                    ->setCellValue('J'.$baseRow, $rowExcel['satuan_1'])
                    ->setCellValue('K'.$baseRow, 'x')
                    ->setCellValue('L'.$baseRow, $rowExcel['vol_2'])
                    ->setCellValue('M'.$baseRow, $rowExcel['satuan_2'])
                    ->setCellValue('N'.$baseRow, 'x')
                    ->setCellValue('O'.$baseRow, $rowExcel['vol_3'])
                    ->setCellValue('P'.$baseRow, $rowExcel['satuan_3'])
                    ->setCellValue('Q'.$baseRow, 'x')
                    ->setCellValue('R'.$baseRow, $rowExcel['vol_4'])
                    ->setCellValue('S'.$baseRow, $rowExcel['satuan_4'])
                    ->setCellValue('T'.$baseRow, '=')
                    ->setCellValue('U'.$baseRow, $rowExcel['vol'])
                    ->setCellValue('V'.$baseRow, $rowExcel['unit_cost'])
                    ->setCellValue('W'.$baseRow, $rowExcel['jumlah'])
                    ->setCellValue('X'.$baseRow, $rowExcel['jumlah_awal']);

                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':X' .$baseRow)->applyFromArray($styleArray);
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':X' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setVertical('top'); 
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':X' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setWrapText(true);
                                            
                    $baseRow++;
                    $baseRowService = $baseRowService+1;
                }
                
                $baseRowProgram=$baseRowProgram+$baseRowService;
                $baseRowProgram++;   
            }else{  
                $baseRowProgram++; 
            }
        }

        // $spreadsheet->getSecurity()->setLockWindows(true);
        // $spreadsheet->getSecurity()->setLockStructure(true);
        // $spreadsheet->getSecurity()->setWorkbookPassword("silverblack");

        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setPassword('silverblack');
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setSheet(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setSort(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setInsertRows(true);
        // $spreadsheet->getActiveSheet()
        //     ->getProtection()->setFormatCells(true);

        // $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
        

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_rekap_komponen_pkm_'.$period.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function actionRekapPkm($id)
    {
        $session = Yii::$app->session;
        $session['option'] = $id;
        if($id == 'k'){
            $data = 'SELECT g.nama_program, s.id idnya, s.nama_pelayanan komponen, 
				IFNULL(kalijambe.sub_total,0) kalijambe, IFNULL(plupuh1.sub_total,0) plupuh1, IFNULL(plupuh2.sub_total,0) plupuh2, IFNULL(masaran1.sub_total,0) masaran1,
            IFNULL(masaran2.sub_total,0) masaran2, IFNULL(kedawung1.sub_total,0) kedawung1, IFNULL(kedawung2.sub_total,0) kedawung2, IFNULL(sambirejo.sub_total,0) sambirejo, IFNULL(gondang.sub_total,0) gondang,
            IFNULL(sambungmacan1.sub_total,0) sambungmacan1, IFNULL(sambungmacan2.sub_total,0) sambungmacan2, IFNULL(ngrampal.sub_total,0) ngrampal, IFNULL(karangmalang.sub_total,0) karangmalang, IFNULL(sragen.sub_total,0) sragen,
            IFNULL(sidoharjo.sub_total,0) sidoharjo, IFNULL(tanon1.sub_total,0) tanon1, IFNULL(tanon2.sub_total,0) tanon2, IFNULL(gemolong.sub_total,0) gemolong, IFNULL(miri.sub_total,0) miri,
            IFNULL(sumberlawang.sub_total,0) sumberlawang, IFNULL(mondokan.sub_total,0) mondokan, IFNULL(sukodono.sub_total,0) sukodono, IFNULL(gesi.sub_total,0) gesi, IFNULL(tangen.sub_total,0) tangen,
            IFNULL(jenar.sub_total,0) jenar,
            
            IFNULL(kalijambe.sub_total,0) + IFNULL(plupuh1.sub_total,0) + IFNULL(plupuh2.sub_total,0) + IFNULL(masaran1.sub_total,0) +
            IFNULL(masaran2.sub_total,0) + IFNULL(kedawung1.sub_total,0) + IFNULL(kedawung2.sub_total,0) + IFNULL(sambirejo.sub_total,0) + IFNULL(gondang.sub_total,0) +
            IFNULL(sambungmacan1.sub_total,0) + IFNULL(sambungmacan2.sub_total,0) + IFNULL(ngrampal.sub_total,0) + IFNULL(karangmalang.sub_total,0) + IFNULL(sragen.sub_total,0) +
            IFNULL(sidoharjo.sub_total,0) + IFNULL(tanon1.sub_total,0) + IFNULL(tanon2.sub_total,0) + IFNULL(gemolong.sub_total,0) + IFNULL(miri.sub_total,0) +
            IFNULL(sumberlawang.sub_total,0) + IFNULL(mondokan.sub_total,0) + IFNULL(sukodono.sub_total,0) + IFNULL(gesi.sub_total,0) + IFNULL(tangen.sub_total,0) +
            IFNULL(jenar.sub_total,0) total

            FROM service s
            LEFT JOIN program g ON g.id=s.program_id
            LEFT JOIN 
            (
                -- kalijambe	
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309010101"
                group BY s.id ORDER BY s.id
            ) kalijambe ON kalijambe.id=s.id
            LEFT JOIN 
            (
                -- plupuh1
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309020101"
                group BY s.id ORDER BY s.id
            ) plupuh1 ON plupuh1.id=s.id
            LEFT JOIN 
            (
                -- plupuh2
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309020202"
                group BY s.id ORDER BY s.id
            ) plupuh2 ON plupuh2.id=s.id
            LEFT JOIN 
            (
                -- masaran1
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309030101"
                group BY s.id ORDER BY s.id
            ) masaran1 ON masaran1.id=s.id
            LEFT JOIN 
            (
                -- masaran2
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309040102"
                group BY s.id ORDER BY s.id
            ) masaran2 ON masaran2.id=s.id
            LEFT JOIN 
            (
                -- kedawung1
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309040201"
                group BY s.id ORDER BY s.id
            ) kedawung1 ON kedawung1.id=s.id
            LEFT JOIN 
            (
                -- kedawung2
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309050201"
                group BY s.id ORDER BY s.id
            ) kedawung2 ON kedawung2.id=s.id
            LEFT JOIN 
            (
                -- sambirejo
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309050202"
                group BY s.id ORDER BY s.id
            ) sambirejo ON sambirejo.id=s.id
            LEFT JOIN 
            (
                -- gondang
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309060201"
                group BY s.id ORDER BY s.id
            ) gondang ON gondang.id=s.id
            LEFT JOIN 
            (
                -- sambungmacan1
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309070201"
                group BY s.id ORDER BY s.id
            ) sambungmacan1 ON sambungmacan1.id=s.id
            LEFT JOIN 
            (
                -- sambungmacan2
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309080102"
                group BY s.id ORDER BY s.id
            ) sambungmacan2 ON sambungmacan2.id=s.id
            LEFT JOIN 
            (
                -- ngrampal
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309090201"
                group BY s.id ORDER BY s.id
            ) ngrampal ON ngrampal.id=s.id
            LEFT JOIN 
            (
                -- karangmalang
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309090202"
                group BY s.id ORDER BY s.id
            ) karangmalang ON karangmalang.id=s.id
            LEFT JOIN 
            (
                -- sragen
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309100201"
                group BY s.id ORDER BY s.id
            ) sragen ON sragen.id=s.id
            LEFT JOIN 
            (
                -- sidoharjo
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309110101"
                group BY s.id ORDER BY s.id
            ) sidoharjo ON sidoharjo.id=s.id
            LEFT JOIN 
            (
                -- tanon1
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309120101"
                group BY s.id ORDER BY s.id
            ) tanon1 ON tanon1.id=s.id
            LEFT JOIN 
            (
                -- tanon2
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309130201"
                group BY s.id ORDER BY s.id
            ) tanon2 ON tanon2.id=s.id
            LEFT JOIN 
            (
                -- gemolong
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309140101"
                group BY s.id ORDER BY s.id
            ) gemolong ON gemolong.id=s.id
            LEFT JOIN 
            (
                -- miri
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309150101"
                group BY s.id ORDER BY s.id
            ) miri ON miri.id=s.id
            LEFT JOIN 
            (
                -- sumberlawang
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309150202"
                group BY s.id ORDER BY s.id
            ) sumberlawang ON sumberlawang.id=s.id
            LEFT JOIN 
            (
                -- mondokan
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309160101"
                group BY s.id ORDER BY s.id
            ) mondokan ON mondokan.id=s.id
            LEFT JOIN 
            (
                -- sukodono
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309170101"
                group BY s.id ORDER BY s.id
            ) sukodono ON sukodono.id=s.id
            LEFT JOIN 
            (
                -- gesi
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309180101"
                group BY s.id ORDER BY s.id
            ) gesi ON gesi.id=s.id
            LEFT JOIN 
            (
                -- tangen
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309180102"
                group BY s.id ORDER BY s.id
            ) tangen ON tangen.id=s.id
            LEFT JOIN 
            (
                -- jenar
                SELECT s.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309190101"
                group BY s.id ORDER BY s.id
            ) jenar ON jenar.id=s.id
            WHERE g.tahun='.$session['periodValue'].' AND g.aktif=1 AND s.sub=0
            group BY s.id

            UNION

            SELECT g.nama_program, s.id idnya, s.nama_kegiatan komponen, 
				IFNULL(kalijambe.sub_total,0) kalijambe, IFNULL(plupuh1.sub_total,0) plupuh1, IFNULL(plupuh2.sub_total,0) plupuh2, IFNULL(masaran1.sub_total,0) masaran1,
            IFNULL(masaran2.sub_total,0) masaran2, IFNULL(kedawung1.sub_total,0) kedawung1, IFNULL(kedawung2.sub_total,0) kedawung2, IFNULL(sambirejo.sub_total,0) sambirejo, IFNULL(gondang.sub_total,0) gondang,
            IFNULL(sambungmacan1.sub_total,0) sambungmacan1, IFNULL(sambungmacan2.sub_total,0) sambungmacan2, IFNULL(ngrampal.sub_total,0) ngrampal, IFNULL(karangmalang.sub_total,0) karangmalang, IFNULL(sragen.sub_total,0) sragen,
            IFNULL(sidoharjo.sub_total,0) sidoharjo, IFNULL(tanon1.sub_total,0) tanon1, IFNULL(tanon2.sub_total,0) tanon2, IFNULL(gemolong.sub_total,0) gemolong, IFNULL(miri.sub_total,0) miri,
            IFNULL(sumberlawang.sub_total,0) sumberlawang, IFNULL(mondokan.sub_total,0) mondokan, IFNULL(sukodono.sub_total,0) sukodono, IFNULL(gesi.sub_total,0) gesi, IFNULL(tangen.sub_total,0) tangen,
            IFNULL(jenar.sub_total,0) jenar,
            
            IFNULL(kalijambe.sub_total,0) + IFNULL(plupuh1.sub_total,0) + IFNULL(plupuh2.sub_total,0) + IFNULL(masaran1.sub_total,0) +
            IFNULL(masaran2.sub_total,0) + IFNULL(kedawung1.sub_total,0) + IFNULL(kedawung2.sub_total,0) + IFNULL(sambirejo.sub_total,0) + IFNULL(gondang.sub_total,0) +
            IFNULL(sambungmacan1.sub_total,0) + IFNULL(sambungmacan2.sub_total,0) + IFNULL(ngrampal.sub_total,0) + IFNULL(karangmalang.sub_total,0) + IFNULL(sragen.sub_total,0) +
            IFNULL(sidoharjo.sub_total,0) + IFNULL(tanon1.sub_total,0) + IFNULL(tanon2.sub_total,0) + IFNULL(gemolong.sub_total,0) + IFNULL(miri.sub_total,0) +
            IFNULL(sumberlawang.sub_total,0) + IFNULL(mondokan.sub_total,0) + IFNULL(sukodono.sub_total,0) + IFNULL(gesi.sub_total,0) + IFNULL(tangen.sub_total,0) +
            IFNULL(jenar.sub_total,0) total
            
            FROM activity s
            LEFT JOIN service v ON v.id=s.service_id
            LEFT JOIN program g ON g.id=v.program_id
            LEFT JOIN 
            (
                -- kalijambe	
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309010101"
                group BY v.id ORDER BY v.id
            ) kalijambe ON kalijambe.id=s.id
            LEFT JOIN 
            (
                -- plupuh1
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309020101"
                group BY v.id ORDER BY v.id
            ) plupuh1 ON plupuh1.id=s.id
            LEFT JOIN 
            (
                -- plupuh2
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309020202"
                group BY v.id ORDER BY v.id
            ) plupuh2 ON plupuh2.id=s.id
            LEFT JOIN 
            (
                -- masaran1
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309030101"
                group BY v.id ORDER BY v.id
            ) masaran1 ON masaran1.id=s.id
            LEFT JOIN 
            (
                -- masaran2
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309040102"
                group BY v.id ORDER BY v.id
            ) masaran2 ON masaran2.id=s.id
            LEFT JOIN 
            (
                -- kedawung1
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309040201"
                group BY v.id ORDER BY v.id
            ) kedawung1 ON kedawung1.id=s.id
            LEFT JOIN 
            (
                -- kedawung2
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309050201"
                group BY v.id ORDER BY v.id
            ) kedawung2 ON kedawung2.id=s.id
            LEFT JOIN 
            (
                -- sambirejo
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309050202"
                group BY v.id ORDER BY v.id
            ) sambirejo ON sambirejo.id=s.id
            LEFT JOIN 
            (
                -- gondang
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309060201"
                group BY v.id ORDER BY v.id
            ) gondang ON gondang.id=s.id
            LEFT JOIN 
            (
                -- sambungmacan1
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309070201"
                group BY v.id ORDER BY v.id
            ) sambungmacan1 ON sambungmacan1.id=s.id
            LEFT JOIN 
            (
                -- sambungmacan2
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309080102"
                group BY v.id ORDER BY v.id
            ) sambungmacan2 ON sambungmacan2.id=s.id
            LEFT JOIN 
            (
                -- ngrampal
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309090201"
                group BY v.id ORDER BY v.id
            ) ngrampal ON ngrampal.id=s.id
            LEFT JOIN 
            (
                -- karangmalang
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309090202"
                group BY v.id ORDER BY v.id
            ) karangmalang ON karangmalang.id=s.id
            LEFT JOIN 
            (
                -- sragen
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309100201"
                group BY v.id ORDER BY v.id
            ) sragen ON sragen.id=s.id
            LEFT JOIN 
            (
                -- sidoharjo
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309110101"
                group BY v.id ORDER BY v.id
            ) sidoharjo ON sidoharjo.id=s.id
            LEFT JOIN 
            (
                -- tanon1
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309120101"
                group BY v.id ORDER BY v.id
            ) tanon1 ON tanon1.id=s.id
            LEFT JOIN 
            (
                -- tanon2
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309130201"
                group BY v.id ORDER BY v.id
            ) tanon2 ON tanon2.id=s.id
            LEFT JOIN 
            (
                -- gemolong
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309140101"
                group BY v.id ORDER BY v.id
            ) gemolong ON gemolong.id=s.id
            LEFT JOIN 
            (
                -- miri
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309150101"
                group BY v.id ORDER BY v.id
            ) miri ON miri.id=s.id
            LEFT JOIN 
            (
                -- sumberlawang
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309150202"
                group BY v.id ORDER BY v.id
            ) sumberlawang ON sumberlawang.id=s.id
            LEFT JOIN 
            (
                -- mondokan
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309160101"
                group BY v.id ORDER BY v.id
            ) mondokan ON mondokan.id=s.id
            LEFT JOIN 
            (
                -- sukodono
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309170101"
                group BY v.id ORDER BY v.id
            ) sukodono ON sukodono.id=s.id
            LEFT JOIN 
            (
                -- gesi
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309180101"
                group BY v.id ORDER BY v.id
            ) gesi ON gesi.id=s.id
            LEFT JOIN 
            (
                -- tangen
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309180102"
                group BY v.id ORDER BY v.id
            ) tangen ON tangen.id=s.id
            LEFT JOIN 
            (
                -- jenar
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309190101"
                group BY v.id ORDER BY v.id
            ) jenar ON jenar.id=s.id
            WHERE g.tahun='.$session['periodValue'].' AND g.aktif=1 AND v.sub=1
            group BY s.id ORDER BY idnya';
            
            $session['rekappkm'] = $data;

            $dataProvider = new SqlDataProvider([
                'sql' => $data,
                'pagination' => false
            ]);

            return $this->render('rekappkm',[
                'dataProvider' => $dataProvider,
            ]);
        }else{
            //PER SUB KOMPONEN
            $data = 'SELECT g.nama_program, s.nama_pelayanan komponen, a.id idnya, a.nama_kegiatan subkomponen, 
				IFNULL(kalijambe.sub_total,0) kalijambe, IFNULL(plupuh1.sub_total,0) plupuh1, IFNULL(plupuh2.sub_total,0) plupuh2, IFNULL(masaran1.sub_total,0) masaran1,
            IFNULL(masaran2.sub_total,0) masaran2, IFNULL(kedawung1.sub_total,0) kedawung1, IFNULL(kedawung2.sub_total,0) kedawung2, IFNULL(sambirejo.sub_total,0) sambirejo, IFNULL(gondang.sub_total,0) gondang,
            IFNULL(sambungmacan1.sub_total,0) sambungmacan1, IFNULL(sambungmacan2.sub_total,0) sambungmacan2, IFNULL(ngrampal.sub_total,0) ngrampal, IFNULL(karangmalang.sub_total,0) karangmalang, IFNULL(sragen.sub_total,0) sragen,
            IFNULL(sidoharjo.sub_total,0) sidoharjo, IFNULL(tanon1.sub_total,0) tanon1, IFNULL(tanon2.sub_total,0) tanon2, IFNULL(gemolong.sub_total,0) gemolong, IFNULL(miri.sub_total,0) miri,
            IFNULL(sumberlawang.sub_total,0) sumberlawang, IFNULL(mondokan.sub_total,0) mondokan, IFNULL(sukodono.sub_total,0) sukodono, IFNULL(gesi.sub_total,0) gesi, IFNULL(tangen.sub_total,0) tangen,
            IFNULL(jenar.sub_total,0) jenar,
            
            IFNULL(kalijambe.sub_total,0) + IFNULL(plupuh1.sub_total,0) + IFNULL(plupuh2.sub_total,0) + IFNULL(masaran1.sub_total,0) +
            IFNULL(masaran2.sub_total,0) + IFNULL(kedawung1.sub_total,0) + IFNULL(kedawung2.sub_total,0) + IFNULL(sambirejo.sub_total,0) + IFNULL(gondang.sub_total,0) +
            IFNULL(sambungmacan1.sub_total,0) + IFNULL(sambungmacan2.sub_total,0) + IFNULL(ngrampal.sub_total,0) + IFNULL(karangmalang.sub_total,0) + IFNULL(sragen.sub_total,0) +
            IFNULL(sidoharjo.sub_total,0) + IFNULL(tanon1.sub_total,0) + IFNULL(tanon2.sub_total,0) + IFNULL(gemolong.sub_total,0) + IFNULL(miri.sub_total,0) +
            IFNULL(sumberlawang.sub_total,0) + IFNULL(mondokan.sub_total,0) + IFNULL(sukodono.sub_total,0) + IFNULL(gesi.sub_total,0) + IFNULL(tangen.sub_total,0) +
            IFNULL(jenar.sub_total,0) total

            FROM activity a
            LEFT JOIN service s ON s.id=a.service_id
            LEFT JOIN program g ON g.id=s.program_id
            LEFT JOIN 
            (
                -- kalijambe	
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309010101"
                group BY v.id ORDER BY v.id
            ) kalijambe ON kalijambe.id=a.id
            LEFT JOIN 
            (
                -- plupuh1
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309020101"
                group BY v.id ORDER BY v.id
            ) plupuh1 ON plupuh1.id=a.id
            LEFT JOIN 
            (
                -- plupuh2
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309020202"
                group BY v.id ORDER BY v.id
            ) plupuh2 ON plupuh2.id=a.id
            LEFT JOIN 
            (
                -- masaran1
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309030101"
                group BY v.id ORDER BY v.id
            ) masaran1 ON masaran1.id=a.id
            LEFT JOIN 
            (
                -- masaran2
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309040102"
                group BY v.id ORDER BY v.id
            ) masaran2 ON masaran2.id=a.id
            LEFT JOIN 
            (
                -- kedawung1
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309040201"
                group BY v.id ORDER BY v.id
            ) kedawung1 ON kedawung1.id=a.id
            LEFT JOIN 
            (
                -- kedawung2
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309050201"
                group BY v.id ORDER BY v.id
            ) kedawung2 ON kedawung2.id=a.id
            LEFT JOIN 
            (
                -- sambirejo
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309050202"
                group BY v.id ORDER BY v.id
            ) sambirejo ON sambirejo.id=a.id
            LEFT JOIN 
            (
                -- gondang
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309060201"
                group BY v.id ORDER BY v.id
            ) gondang ON gondang.id=a.id
            LEFT JOIN 
            (
                -- sambungmacan1
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309070201"
                group BY v.id ORDER BY v.id
            ) sambungmacan1 ON sambungmacan1.id=a.id
            LEFT JOIN 
            (
                -- sambungmacan2
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309080102"
                group BY v.id ORDER BY v.id
            ) sambungmacan2 ON sambungmacan2.id=a.id
            LEFT JOIN 
            (
                -- ngrampal
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309090201"
                group BY v.id ORDER BY v.id
            ) ngrampal ON ngrampal.id=a.id
            LEFT JOIN 
            (
                -- karangmalang
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309090202"
                group BY v.id ORDER BY v.id
            ) karangmalang ON karangmalang.id=a.id
            LEFT JOIN 
            (
                -- sragen
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309100201"
                group BY v.id ORDER BY v.id
            ) sragen ON sragen.id=a.id
            LEFT JOIN 
            (
                -- sidoharjo
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309110101"
                group BY v.id ORDER BY v.id
            ) sidoharjo ON sidoharjo.id=a.id
            LEFT JOIN 
            (
                -- tanon1
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309120101"
                group BY v.id ORDER BY v.id
            ) tanon1 ON tanon1.id=a.id
            LEFT JOIN 
            (
                -- tanon2
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309130201"
                group BY v.id ORDER BY v.id
            ) tanon2 ON tanon2.id=a.id
            LEFT JOIN 
            (
                -- gemolong
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309140101"
                group BY v.id ORDER BY v.id
            ) gemolong ON gemolong.id=a.id
            LEFT JOIN 
            (
                -- miri
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309150101"
                group BY v.id ORDER BY v.id
            ) miri ON miri.id=a.id
            LEFT JOIN 
            (
                -- sumberlawang
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309150202"
                group BY v.id ORDER BY v.id
            ) sumberlawang ON sumberlawang.id=a.id
            LEFT JOIN 
            (
                -- mondokan
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309160101"
                group BY v.id ORDER BY v.id
            ) mondokan ON mondokan.id=a.id
            LEFT JOIN 
            (
                -- sukodono
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309170101"
                group BY v.id ORDER BY v.id
            ) sukodono ON sukodono.id=a.id
            LEFT JOIN 
            (
                -- gesi
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309180101"
                group BY v.id ORDER BY v.id
            ) gesi ON gesi.id=a.id
            LEFT JOIN 
            (
                -- tangen
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309180102"
                group BY v.id ORDER BY v.id
            ) tangen ON tangen.id=a.id
            LEFT JOIN 
            (
                -- jenar
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309190101"
                group BY v.id ORDER BY v.id
            ) jenar ON jenar.id=a.id
            WHERE g.tahun='.$session['periodValue'].' AND g.aktif=1 AND s.sub=0
            group BY a.id

            UNION

            SELECT g.nama_program, v.nama_pelayanan komponen, s.id idnya, s.nama_kegiatan subkomponen, 
				IFNULL(kalijambe.sub_total,0) kalijambe, IFNULL(plupuh1.sub_total,0) plupuh1, IFNULL(plupuh2.sub_total,0) plupuh2, IFNULL(masaran1.sub_total,0) masaran1,
            IFNULL(masaran2.sub_total,0) masaran2, IFNULL(kedawung1.sub_total,0) kedawung1, IFNULL(kedawung2.sub_total,0) kedawung2, IFNULL(sambirejo.sub_total,0) sambirejo, IFNULL(gondang.sub_total,0) gondang,
            IFNULL(sambungmacan1.sub_total,0) sambungmacan1, IFNULL(sambungmacan2.sub_total,0) sambungmacan2, IFNULL(ngrampal.sub_total,0) ngrampal, IFNULL(karangmalang.sub_total,0) karangmalang, IFNULL(sragen.sub_total,0) sragen,
            IFNULL(sidoharjo.sub_total,0) sidoharjo, IFNULL(tanon1.sub_total,0) tanon1, IFNULL(tanon2.sub_total,0) tanon2, IFNULL(gemolong.sub_total,0) gemolong, IFNULL(miri.sub_total,0) miri,
            IFNULL(sumberlawang.sub_total,0) sumberlawang, IFNULL(mondokan.sub_total,0) mondokan, IFNULL(sukodono.sub_total,0) sukodono, IFNULL(gesi.sub_total,0) gesi, IFNULL(tangen.sub_total,0) tangen,
            IFNULL(jenar.sub_total,0) jenar,
            
            IFNULL(kalijambe.sub_total,0) + IFNULL(plupuh1.sub_total,0) + IFNULL(plupuh2.sub_total,0) + IFNULL(masaran1.sub_total,0) +
            IFNULL(masaran2.sub_total,0) + IFNULL(kedawung1.sub_total,0) + IFNULL(kedawung2.sub_total,0) + IFNULL(sambirejo.sub_total,0) + IFNULL(gondang.sub_total,0) +
            IFNULL(sambungmacan1.sub_total,0) + IFNULL(sambungmacan2.sub_total,0) + IFNULL(ngrampal.sub_total,0) + IFNULL(karangmalang.sub_total,0) + IFNULL(sragen.sub_total,0) +
            IFNULL(sidoharjo.sub_total,0) + IFNULL(tanon1.sub_total,0) + IFNULL(tanon2.sub_total,0) + IFNULL(gemolong.sub_total,0) + IFNULL(miri.sub_total,0) +
            IFNULL(sumberlawang.sub_total,0) + IFNULL(mondokan.sub_total,0) + IFNULL(sukodono.sub_total,0) + IFNULL(gesi.sub_total,0) + IFNULL(tangen.sub_total,0) +
            IFNULL(jenar.sub_total,0) total
            
            FROM activity s
            LEFT JOIN service v ON v.id=s.service_id
            LEFT JOIN program g ON g.id=v.program_id
            LEFT JOIN 
            (
                -- kalijambe	
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309010101"
                group BY v.id ORDER BY v.id
            ) kalijambe ON kalijambe.id=s.id
            LEFT JOIN 
            (
                -- plupuh1
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309020101"
                group BY v.id ORDER BY v.id
            ) plupuh1 ON plupuh1.id=s.id
            LEFT JOIN 
            (
                -- plupuh2
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309020202"
                group BY v.id ORDER BY v.id
            ) plupuh2 ON plupuh2.id=s.id
            LEFT JOIN 
            (
                -- masaran1
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309030101"
                group BY v.id ORDER BY v.id
            ) masaran1 ON masaran1.id=s.id
            LEFT JOIN 
            (
                -- masaran2
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309040102"
                group BY v.id ORDER BY v.id
            ) masaran2 ON masaran2.id=s.id
            LEFT JOIN 
            (
                -- kedawung1
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309040201"
                group BY v.id ORDER BY v.id
            ) kedawung1 ON kedawung1.id=s.id
            LEFT JOIN 
            (
                -- kedawung2
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309050201"
                group BY v.id ORDER BY v.id
            ) kedawung2 ON kedawung2.id=s.id
            LEFT JOIN 
            (
                -- sambirejo
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309050202"
                group BY v.id ORDER BY v.id
            ) sambirejo ON sambirejo.id=s.id
            LEFT JOIN 
            (
                -- gondang
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309060201"
                group BY v.id ORDER BY v.id
            ) gondang ON gondang.id=s.id
            LEFT JOIN 
            (
                -- sambungmacan1
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309070201"
                group BY v.id ORDER BY v.id
            ) sambungmacan1 ON sambungmacan1.id=s.id
            LEFT JOIN 
            (
                -- sambungmacan2
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309080102"
                group BY v.id ORDER BY v.id
            ) sambungmacan2 ON sambungmacan2.id=s.id
            LEFT JOIN 
            (
                -- ngrampal
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309090201"
                group BY v.id ORDER BY v.id
            ) ngrampal ON ngrampal.id=s.id
            LEFT JOIN 
            (
                -- karangmalang
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309090202"
                group BY v.id ORDER BY v.id
            ) karangmalang ON karangmalang.id=s.id
            LEFT JOIN 
            (
                -- sragen
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309100201"
                group BY v.id ORDER BY v.id
            ) sragen ON sragen.id=s.id
            LEFT JOIN 
            (
                -- sidoharjo
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309110101"
                group BY v.id ORDER BY v.id
            ) sidoharjo ON sidoharjo.id=s.id
            LEFT JOIN 
            (
                -- tanon1
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309120101"
                group BY v.id ORDER BY v.id
            ) tanon1 ON tanon1.id=s.id
            LEFT JOIN 
            (
                -- tanon2
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309130201"
                group BY v.id ORDER BY v.id
            ) tanon2 ON tanon2.id=s.id
            LEFT JOIN 
            (
                -- gemolong
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309140101"
                group BY v.id ORDER BY v.id
            ) gemolong ON gemolong.id=s.id
            LEFT JOIN 
            (
                -- miri
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309150101"
                group BY v.id ORDER BY v.id
            ) miri ON miri.id=s.id
            LEFT JOIN 
            (
                -- sumberlawang
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309150202"
                group BY v.id ORDER BY v.id
            ) sumberlawang ON sumberlawang.id=s.id
            LEFT JOIN 
            (
                -- mondokan
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309160101"
                group BY v.id ORDER BY v.id
            ) mondokan ON mondokan.id=s.id
            LEFT JOIN 
            (
                -- sukodono
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309170101"
                group BY v.id ORDER BY v.id
            ) sukodono ON sukodono.id=s.id
            LEFT JOIN 
            (
                -- gesi
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309180101"
                group BY v.id ORDER BY v.id
            ) gesi ON gesi.id=s.id
            LEFT JOIN 
            (
                -- tangen
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309180102"
                group BY v.id ORDER BY v.id
            ) tangen ON tangen.id=s.id
            LEFT JOIN 
            (
                -- jenar
                SELECT v.id, IFNULL(SUM(e.jumlah),0) sub_total FROM activity_detail e
                LEFT JOIN activity_data a ON a.id=e.activity_data_id
                LEFT JOIN activity v ON v.id=a.activity_id
                LEFT JOIN service s ON s.id=v.service_id
                LEFT JOIN program g ON g.id=s.program_id
                LEFT JOIN period p ON p.id=a.period_id
                WHERE p.unit_id="P3309190101"
                group BY v.id ORDER BY v.id
            ) jenar ON jenar.id=s.id
            WHERE g.tahun='.$session['periodValue'].' AND g.aktif=1 AND v.sub=1
            group BY s.id ORDER BY idnya';
            
            $session['rekappkmsub'] = $data;

            $dataProvider = new SqlDataProvider([
                'sql' => $data,
                'pagination' => false
            ]);

            return $this->render('rekappkmsub',[
                'dataProvider' => $dataProvider,
            ]);
        }
    }
    public function actionExportxlspkm()
    {
        $session = Yii::$app->session;
        $id = $session['option'];
        if($id == 'k'){
            $data = Yii::$app->db->createCommand($session['rekappkm'])->queryAll();
        }else{
            $data = Yii::$app->db->createCommand($session['rekappkmsub'])->queryAll();
        }

        $spreadsheet = new Spreadsheet();
        $activeSheet = $spreadsheet->getActiveSheet();

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $activeSheet->setCellValue('A'.'1', 'REKAP PER PUSKESMAS');
        $activeSheet->setCellValue('A'.'2', 'PERIODE ' .$session['periodValue']);
        $baseRow=5;

        if($id == 'k'){
            $activeSheet
                ->setCellValue('A'.'4', 'NO')
                ->setCellValue('B'.'4', 'RINCIAN MENU')
                ->setCellValue('C'.'4', 'KOMPONEN')
                ->setCellValue('D'.'4', 'KALIJAMBE')
                ->setCellValue('E'.'4', 'PLUPUH I')
                ->setCellValue('F'.'4', 'PLUPUH II')
                ->setCellValue('G'.'4', 'MASARAN I')
                ->setCellValue('H'.'4', 'MASARAN II')
                ->setCellValue('I'.'4', 'KEDAWUNG I')
                ->setCellValue('J'.'4', 'KEDAWUNG II')
                ->setCellValue('K'.'4', 'SAMBIREJO')
                ->setCellValue('L'.'4', 'GONDANG')
                ->setCellValue('M'.'4', 'SAMBUNGMACAN I')
                ->setCellValue('N'.'4', 'SAMBUNGMACAN II')
                ->setCellValue('O'.'4', 'NGRAMPAL')
                ->setCellValue('P'.'4', 'KARANGMALANG')
                ->setCellValue('Q'.'4', 'SRAGEN')
                ->setCellValue('R'.'4', 'SIDOHARJO')
                ->setCellValue('S'.'4', 'TANON I')
                ->setCellValue('T'.'4', 'TANON II')
                ->setCellValue('U'.'4', 'GEMOLONG')
                ->setCellValue('V'.'4', 'MIRI')
                ->setCellValue('W'.'4', 'SUMBERLAWANG')
                ->setCellValue('X'.'4', 'MONDOKAN')
                ->setCellValue('Y'.'4', 'SUKODONO')
                ->setCellValue('Z'.'4', 'GESI')
                ->setCellValue('AA'.'4', 'TANGEN')
                ->setCellValue('AB'.'4', 'JENAR')
                ->setCellValue('AC'.'4', 'TOTAL');
        }else{
            $activeSheet
                ->setCellValue('A'.'4', 'NO')
                ->setCellValue('B'.'4', 'RINCIAN MENU')
                ->setCellValue('C'.'4', 'KOMPONEN')
                ->setCellValue('D'.'4', 'SUB KOMPONEN')
                ->setCellValue('E'.'4', 'KALIJAMBE')
                ->setCellValue('F'.'4', 'PLUPUH I')
                ->setCellValue('G'.'4', 'PLUPUH II')
                ->setCellValue('H'.'4', 'MASARAN I')
                ->setCellValue('I'.'4', 'MASARAN II')
                ->setCellValue('J'.'4', 'KEDAWUNG I')
                ->setCellValue('K'.'4', 'KEDAWUNG II')
                ->setCellValue('L'.'4', 'SAMBIREJO')
                ->setCellValue('M'.'4', 'GONDANG')
                ->setCellValue('N'.'4', 'SAMBUNGMACAN I')
                ->setCellValue('O'.'4', 'SAMBUNGMACAN II')
                ->setCellValue('P'.'4', 'NGRAMPAL')
                ->setCellValue('Q'.'4', 'KARANGMALANG')
                ->setCellValue('R'.'4', 'SRAGEN')
                ->setCellValue('S'.'4', 'SIDOHARJO')
                ->setCellValue('T'.'4', 'TANON I')
                ->setCellValue('U'.'4', 'TANON II')
                ->setCellValue('V'.'4', 'GEMOLONG')
                ->setCellValue('W'.'4', 'MIRI')
                ->setCellValue('X'.'4', 'SUMBERLAWANG')
                ->setCellValue('Y'.'4', 'MONDOKAN')
                ->setCellValue('Z'.'4', 'SUKODONO')
                ->setCellValue('AA'.'4', 'GESI')
                ->setCellValue('AB'.'4', 'TANGEN')
                ->setCellValue('AC'.'4', 'JENAR')
                ->setCellValue('AD'.'4', 'TOTAL');
        }

        $rinci = '';
        $kom = '';

        foreach($data as $rowdata) {
            if($rinci == $rowdata['nama_program']){
                $rinci = '';
            }else{
                $rinci = $rowdata['nama_program'];
            }

            if($kom == $rowdata['komponen']){
                $kom = '';
            }else{
                $kom = $rowdata['komponen'];
            }
        
            if($id == 'k'){
                $activeSheet
                ->setCellValue('A'.$baseRow, $baseRow-4)
                ->setCellValue('B'.$baseRow, $rinci)
                ->setCellValue('C'.$baseRow, $rowdata['komponen'])
                ->setCellValue('D'.$baseRow, $rowdata['kalijambe'])
                ->setCellValue('E'.$baseRow, $rowdata['plupuh1'])
                ->setCellValue('F'.$baseRow, $rowdata['plupuh2'])
                ->setCellValue('G'.$baseRow, $rowdata['masaran1'])
                ->setCellValue('H'.$baseRow, $rowdata['masaran2'])
                ->setCellValue('I'.$baseRow, $rowdata['kedawung1'])
                ->setCellValue('J'.$baseRow, $rowdata['kedawung2'])
                ->setCellValue('K'.$baseRow, $rowdata['sambirejo'])
                ->setCellValue('L'.$baseRow, $rowdata['gondang'])
                ->setCellValue('M'.$baseRow, $rowdata['sambungmacan1'])
                ->setCellValue('N'.$baseRow, $rowdata['sambungmacan2'])
                ->setCellValue('O'.$baseRow, $rowdata['ngrampal'])
                ->setCellValue('P'.$baseRow, $rowdata['karangmalang'])
                ->setCellValue('Q'.$baseRow, $rowdata['sragen'])
                ->setCellValue('R'.$baseRow, $rowdata['sidoharjo'])
                ->setCellValue('S'.$baseRow, $rowdata['tanon1'])
                ->setCellValue('T'.$baseRow, $rowdata['tanon2'])
                ->setCellValue('U'.$baseRow, $rowdata['gemolong'])
                ->setCellValue('V'.$baseRow, $rowdata['miri'])
                ->setCellValue('W'.$baseRow, $rowdata['sumberlawang'])
                ->setCellValue('X'.$baseRow, $rowdata['mondokan'])
                ->setCellValue('Y'.$baseRow, $rowdata['sukodono'])
                ->setCellValue('Z'.$baseRow, $rowdata['gesi'])
                ->setCellValue('AA'.$baseRow, $rowdata['tangen'])
                ->setCellValue('AB'.$baseRow, $rowdata['jenar'])
                ->setCellValue('AC'.$baseRow, $rowdata['total']);   
            }else{
                $activeSheet
                ->setCellValue('A'.$baseRow, $baseRow-4)
                ->setCellValue('B'.$baseRow, $rinci)
                ->setCellValue('C'.$baseRow, $kom)
                ->setCellValue('D'.$baseRow, $rowdata['subkomponen'])
                ->setCellValue('E'.$baseRow, $rowdata['kalijambe'])
                ->setCellValue('F'.$baseRow, $rowdata['plupuh1'])
                ->setCellValue('G'.$baseRow, $rowdata['plupuh2'])
                ->setCellValue('H'.$baseRow, $rowdata['masaran1'])
                ->setCellValue('I'.$baseRow, $rowdata['masaran2'])
                ->setCellValue('J'.$baseRow, $rowdata['kedawung1'])
                ->setCellValue('K'.$baseRow, $rowdata['kedawung2'])
                ->setCellValue('L'.$baseRow, $rowdata['sambirejo'])
                ->setCellValue('M'.$baseRow, $rowdata['gondang'])
                ->setCellValue('N'.$baseRow, $rowdata['sambungmacan1'])
                ->setCellValue('O'.$baseRow, $rowdata['sambungmacan2'])
                ->setCellValue('P'.$baseRow, $rowdata['ngrampal'])
                ->setCellValue('Q'.$baseRow, $rowdata['karangmalang'])
                ->setCellValue('R'.$baseRow, $rowdata['sragen'])
                ->setCellValue('S'.$baseRow, $rowdata['sidoharjo'])
                ->setCellValue('T'.$baseRow, $rowdata['tanon1'])
                ->setCellValue('U'.$baseRow, $rowdata['tanon2'])
                ->setCellValue('V'.$baseRow, $rowdata['gemolong'])
                ->setCellValue('W'.$baseRow, $rowdata['miri'])
                ->setCellValue('X'.$baseRow, $rowdata['sumberlawang'])
                ->setCellValue('Y'.$baseRow, $rowdata['mondokan'])
                ->setCellValue('Z'.$baseRow, $rowdata['sukodono'])
                ->setCellValue('AA'.$baseRow, $rowdata['gesi'])
                ->setCellValue('AB'.$baseRow, $rowdata['tangen'])
                ->setCellValue('AC'.$baseRow, $rowdata['jenar'])
                ->setCellValue('AD'.$baseRow, $rowdata['total']);   
            }
            $rinci = $rowdata['nama_program'];
            $kom = $rowdata['komponen'];
            // $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':E' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setWrapText(true)->setVertical('top');
            $baseRow++;
        }

        // $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_rekap_puskesmas_'.$session['periodValue'].'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function actionLaporanUkmAll($id,$mo)
    {
        if($id==0){ 
            $bok = 'UKM';
        }else{
            $bok = 'COVID';
        }
        $session = Yii::$app->session;
        $session['tahun'] = $session['periodValue'];
        $session['id'] = $id;
        $session['mo'] = $mo;
        $session['bok'] = $bok;
        $real = Yii::$app->db->createCommand('SELECT bd.jenis, bd.bd_desk bidang, uk.uk_desk upaya, MID(uk.uk_nama,4,2) no, SUM(IFNULL(p.jumlah,0)) jumlah FROM uk_label uk
            LEFT JOIN bd_label bd ON bd.id=uk.bd_id
            LEFT JOIN uk_pagu p ON p.uk_id=uk.id
            WHERE uk.tahun=:tahun AND bd.jenis =:bok
            GROUP BY uk.id ORDER BY uk.id')
        ->bindValue(':bok', $bok)
        ->bindValue(':tahun', $session['periodValue'])
        ->queryAll();

        return $this->render('ukmall', [
            'data' => $real,
            'mo' => $mo
        ]);
    }

    public function actionGetRealUkm($no, $id, $pagu)
    {
        $session = Yii::$app->session;

        if($session['id'] == 0) {
            if($id == '0'){
                $real = Yii::$app->db->createCommand('SELECT "ALL UNIT" unit_id, 
                IFNULL(SUM(u.re_1),0) re_1, IFNULL(SUM(u.re_2),0) re_2, IFNULL(SUM(u.re_3),0) re_3, IFNULL(SUM(u.re_4),0) re_4, IFNULL(SUM(u.re_5),0) re_5, 
                IFNULL(SUM(u.re_6),0) re_6, IFNULL(SUM(u.re_7),0) re_7, IFNULL(SUM(u.re_8),0) re_8, IFNULL(SUM(u.re_9),0) re_9, IFNULL(SUM(u.re_10),0) re_10, 
                IFNULL(SUM(u.re_11),0) re_11, IFNULL(SUM(u.re_12),0) re_12, IFNULL(SUM(u.re_13),0) re_13, IFNULL(SUM(u.re_14),0) re_14, IFNULL(SUM(u.re_15),0) re_15, 
                IFNULL(SUM(u.re_16),0) re_16, IFNULL(SUM(u.re_17),0) re_17, IFNULL(SUM(u.re_18),0) re_18, IFNULL(SUM(u.re_19),0) re_19, IFNULL(SUM(u.re_20),0) re_20, 
                IFNULL(SUM(u.re_21),0) re_21, IFNULL(SUM(u.re_22),0) re_22, IFNULL(SUM(u.re_23),0) re_23, IFNULL(SUM(u.re_24),0) re_24, IFNULL(SUM(u.re_25),0) re_25,
                IFNULL(SUM(u.re_26),0) re_26, IFNULL(SUM(u.re_27),0) re_27, IFNULL(SUM(u.re_28),0) re_28, IFNULL(SUM(u.re_29),0) re_29, IFNULL(SUM(u.re_30),0) re_30 
                FROM ukm u WHERE u.tahun=:tahun')
                ->bindValue(':tahun', $session['tahun'])
                ->queryAll();
            }elseif($id == '1'){
                $real = Yii::$app->db->createCommand('SELECT "ALL UNIT" unit_id, 
                IFNULL(SUM(u.re_1),0) re_1, IFNULL(SUM(u.re_2),0) re_2, IFNULL(SUM(u.re_3),0) re_3, IFNULL(SUM(u.re_4),0) re_4, IFNULL(SUM(u.re_5),0) re_5, 
                IFNULL(SUM(u.re_6),0) re_6, IFNULL(SUM(u.re_7),0) re_7, IFNULL(SUM(u.re_8),0) re_8, IFNULL(SUM(u.re_9),0) re_9, IFNULL(SUM(u.re_10),0) re_10, 
                IFNULL(SUM(u.re_11),0) re_11, IFNULL(SUM(u.re_12),0) re_12, IFNULL(SUM(u.re_13),0) re_13, IFNULL(SUM(u.re_14),0) re_14, IFNULL(SUM(u.re_15),0) re_15, 
                IFNULL(SUM(u.re_16),0) re_16, IFNULL(SUM(u.re_17),0) re_17, IFNULL(SUM(u.re_18),0) re_18, IFNULL(SUM(u.re_19),0) re_19, IFNULL(SUM(u.re_20),0) re_20, 
                IFNULL(SUM(u.re_21),0) re_21, IFNULL(SUM(u.re_22),0) re_22, IFNULL(SUM(u.re_23),0) re_23, IFNULL(SUM(u.re_24),0) re_24, IFNULL(SUM(u.re_25),0) re_25,
                IFNULL(SUM(u.re_26),0) re_26, IFNULL(SUM(u.re_27),0) re_27, IFNULL(SUM(u.re_28),0) re_28, IFNULL(SUM(u.re_29),0) re_29, IFNULL(SUM(u.re_30),0) re_30 
                FROM ukm u WHERE u.bulan>=1 AND u.bulan<=3 AND u.tahun=:tahun')
                ->bindValue(':tahun', $session['tahun'])
                ->queryAll();
            }elseif($id == '2'){
                $real = Yii::$app->db->createCommand('SELECT "ALL UNIT" unit_id, 
                IFNULL(SUM(u.re_1),0) re_1, IFNULL(SUM(u.re_2),0) re_2, IFNULL(SUM(u.re_3),0) re_3, IFNULL(SUM(u.re_4),0) re_4, IFNULL(SUM(u.re_5),0) re_5, 
                IFNULL(SUM(u.re_6),0) re_6, IFNULL(SUM(u.re_7),0) re_7, IFNULL(SUM(u.re_8),0) re_8, IFNULL(SUM(u.re_9),0) re_9, IFNULL(SUM(u.re_10),0) re_10, 
                IFNULL(SUM(u.re_11),0) re_11, IFNULL(SUM(u.re_12),0) re_12, IFNULL(SUM(u.re_13),0) re_13, IFNULL(SUM(u.re_14),0) re_14, IFNULL(SUM(u.re_15),0) re_15, 
                IFNULL(SUM(u.re_16),0) re_16, IFNULL(SUM(u.re_17),0) re_17, IFNULL(SUM(u.re_18),0) re_18, IFNULL(SUM(u.re_19),0) re_19, IFNULL(SUM(u.re_20),0) re_20, 
                IFNULL(SUM(u.re_21),0) re_21, IFNULL(SUM(u.re_22),0) re_22, IFNULL(SUM(u.re_23),0) re_23, IFNULL(SUM(u.re_24),0) re_24, IFNULL(SUM(u.re_25),0) re_25,
                IFNULL(SUM(u.re_26),0) re_26, IFNULL(SUM(u.re_27),0) re_27, IFNULL(SUM(u.re_28),0) re_28, IFNULL(SUM(u.re_29),0) re_29, IFNULL(SUM(u.re_30),0) re_30 
                FROM ukm u WHERE u.bulan>3 AND u.bulan<=6 AND u.tahun=:tahun')
                ->bindValue(':tahun', $session['tahun'])
                ->queryAll();
            }elseif($id == '3'){
                $real = Yii::$app->db->createCommand('SELECT "ALL UNIT" unit_id, 
                IFNULL(SUM(u.re_1),0) re_1, IFNULL(SUM(u.re_2),0) re_2, IFNULL(SUM(u.re_3),0) re_3, IFNULL(SUM(u.re_4),0) re_4, IFNULL(SUM(u.re_5),0) re_5, 
                IFNULL(SUM(u.re_6),0) re_6, IFNULL(SUM(u.re_7),0) re_7, IFNULL(SUM(u.re_8),0) re_8, IFNULL(SUM(u.re_9),0) re_9, IFNULL(SUM(u.re_10),0) re_10, 
                IFNULL(SUM(u.re_11),0) re_11, IFNULL(SUM(u.re_12),0) re_12, IFNULL(SUM(u.re_13),0) re_13, IFNULL(SUM(u.re_14),0) re_14, IFNULL(SUM(u.re_15),0) re_15, 
                IFNULL(SUM(u.re_16),0) re_16, IFNULL(SUM(u.re_17),0) re_17, IFNULL(SUM(u.re_18),0) re_18, IFNULL(SUM(u.re_19),0) re_19, IFNULL(SUM(u.re_20),0) re_20, 
                IFNULL(SUM(u.re_21),0) re_21, IFNULL(SUM(u.re_22),0) re_22, IFNULL(SUM(u.re_23),0) re_23, IFNULL(SUM(u.re_24),0) re_24, IFNULL(SUM(u.re_25),0) re_25,
                IFNULL(SUM(u.re_26),0) re_26, IFNULL(SUM(u.re_27),0) re_27, IFNULL(SUM(u.re_28),0) re_28, IFNULL(SUM(u.re_29),0) re_29, IFNULL(SUM(u.re_30),0) re_30 
                FROM ukm u WHERE u.bulan>6 AND u.bulan<=9 AND u.tahun=:tahun')
                ->bindValue(':tahun', $session['tahun'])
                ->queryAll();
            }elseif($id == '4'){
                $real = Yii::$app->db->createCommand('SELECT "ALL UNIT" unit_id, 
                IFNULL(SUM(u.re_1),0) re_1, IFNULL(SUM(u.re_2),0) re_2, IFNULL(SUM(u.re_3),0) re_3, IFNULL(SUM(u.re_4),0) re_4, IFNULL(SUM(u.re_5),0) re_5, 
                IFNULL(SUM(u.re_6),0) re_6, IFNULL(SUM(u.re_7),0) re_7, IFNULL(SUM(u.re_8),0) re_8, IFNULL(SUM(u.re_9),0) re_9, IFNULL(SUM(u.re_10),0) re_10, 
                IFNULL(SUM(u.re_11),0) re_11, IFNULL(SUM(u.re_12),0) re_12, IFNULL(SUM(u.re_13),0) re_13, IFNULL(SUM(u.re_14),0) re_14, IFNULL(SUM(u.re_15),0) re_15, 
                IFNULL(SUM(u.re_16),0) re_16, IFNULL(SUM(u.re_17),0) re_17, IFNULL(SUM(u.re_18),0) re_18, IFNULL(SUM(u.re_19),0) re_19, IFNULL(SUM(u.re_20),0) re_20, 
                IFNULL(SUM(u.re_21),0) re_21, IFNULL(SUM(u.re_22),0) re_22, IFNULL(SUM(u.re_23),0) re_23, IFNULL(SUM(u.re_24),0) re_24, IFNULL(SUM(u.re_25),0) re_25,
                IFNULL(SUM(u.re_26),0) re_26, IFNULL(SUM(u.re_27),0) re_27, IFNULL(SUM(u.re_28),0) re_28, IFNULL(SUM(u.re_29),0) re_29, IFNULL(SUM(u.re_30),0) re_30 
                FROM ukm u WHERE u.bulan>9 AND u.bulan<=12 AND u.tahun=:tahun')
                ->bindValue(':tahun', $session['tahun'])
                ->queryAll();
            }
        }else{
            $real = Yii::$app->db->createCommand('SELECT "ALL UNIT" unit_id, 
            IFNULL(SUM(u.re_1),0) re_1, IFNULL(SUM(u.re_2),0) re_2, IFNULL(SUM(u.re_3),0) re_3, IFNULL(SUM(u.re_4),0) re_4, IFNULL(SUM(u.re_5),0) re_5, 
            IFNULL(SUM(u.re_6),0) re_6, IFNULL(SUM(u.re_7),0) re_7, IFNULL(SUM(u.re_8),0) re_8, IFNULL(SUM(u.re_9),0) re_9, IFNULL(SUM(u.re_10),0) re_10, 
            IFNULL(SUM(u.re_11),0) re_11, IFNULL(SUM(u.re_12),0) re_12, IFNULL(SUM(u.re_13),0) re_13, IFNULL(SUM(u.re_14),0) re_14, IFNULL(SUM(u.re_15),0) re_15, 
            IFNULL(SUM(u.re_16),0) re_16, IFNULL(SUM(u.re_17),0) re_17, IFNULL(SUM(u.re_18),0) re_18, IFNULL(SUM(u.re_19),0) re_19, IFNULL(SUM(u.re_20),0) re_20, 
            IFNULL(SUM(u.re_21),0) re_21, IFNULL(SUM(u.re_22),0) re_22, IFNULL(SUM(u.re_23),0) re_23, IFNULL(SUM(u.re_24),0) re_24, IFNULL(SUM(u.re_25),0) re_25,
            IFNULL(SUM(u.re_26),0) re_26, IFNULL(SUM(u.re_27),0) re_27, IFNULL(SUM(u.re_28),0) re_28, IFNULL(SUM(u.re_29),0) re_29, IFNULL(SUM(u.re_30),0) re_30 
            FROM ukm u WHERE u.bulan=:bulan AND u.tahun=:tahun')
            ->bindValue(':bulan', $id)
            ->bindValue(':tahun', $session['tahun'])
            ->queryAll();
        }

        if (!empty($real)){
            foreach($real as $datareal);

            $re = 're_' .$no;
            
            if($pagu > 0 && $datareal[$re] > 0){
                $session['prosentase'] = number_format($datareal[$re] / $pagu * 100,2,",",".");
            }else{
                $session['prosentase'] = '0';
            }

            return number_format($datareal[$re],0,"",".");
        }else{
            $session['prosentase'] = '0';
            return number_format(0,0,"",".");
        }
    }

    public function actionExportRealisasiUkmAll()
    {
        $session = Yii::$app->session;
        $id = $session['mo'];
        if($id == 1){$tribulan = 'I';}
        if($id == 2){$tribulan = 'II';}
        if($id == 3){$tribulan = 'III';}
        if($id == 4){$tribulan = 'IV';}

        $session = Yii::$app->session;
        $period = $session['periodValue'];

        $data = Yii::$app->db->createCommand('SELECT bd.jenis, bd.bd_desk bidang, uk.uk_desk upaya, MID(uk.uk_nama,4,2) no, SUM(IFNULL(p.jumlah,0)) jumlah FROM uk_label uk
                LEFT JOIN bd_label bd ON bd.id=uk.bd_id
                LEFT JOIN uk_pagu p ON p.uk_id=uk.id
                WHERE uk.tahun=:tahun AND bd.jenis =:bok
                GROUP BY uk.id ORDER BY uk.id')
            ->bindValue(':tahun', $session['tahun'])
            ->bindValue(':bok', $session['bok'])
            ->queryAll();
        
        // $spreadsheet = new Spreadsheet();

        $inputFileName = Yii::getAlias('@app/views/exportaccount').'/_export_ukm.xlsx';

        /** Load $inputFileName to a Spreadsheet Object  **/
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);

        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A'.'1', 'LAPORAN REALIASASI TRIBULAN '.$tribulan.' BOK UKM PUSKESMAS PER BIDANG UPAYA TAHUN ' .$period);
        $activeSheet->setCellValue('A'.'2', 'SEMUA PUSKESMAS');

        $baseRow=5;
        $firstData=5;

        $activeSheet
            ->setCellValue('A'.'4', 'NO')
            ->setCellValue('B'.'4', 'BIDANG')
            ->setCellValue('C'.'4', 'UPAYA KESEHATAN')
            ->setCellValue('D'.'4', 'PAGU')
            ->setCellValue('E'.'4', 'REALISASI')
            ->setCellValue('F'.'4', '%');

        $bidang = '';
        $isianbidang = '';
        foreach($data as $rowdata) {

            if($id == '1'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                SUM(u.re_1) re_1, SUM(u.re_2) re_2, SUM(u.re_3) re_3, SUM(u.re_4) re_4, SUM(u.re_5) re_5, 
                SUM(u.re_6) re_6, SUM(u.re_7) re_7, SUM(u.re_8) re_8, SUM(u.re_9) re_9, SUM(u.re_10) re_10, 
                SUM(u.re_11) re_11, SUM(u.re_12) re_12, SUM(u.re_13) re_13, SUM(u.re_14) re_14, SUM(u.re_15) re_15, 
                SUM(u.re_16) re_16, SUM(u.re_17) re_17, SUM(u.re_18) re_18, SUM(u.re_19) re_19, SUM(u.re_20) re_20, 
                SUM(u.re_21) re_21, SUM(u.re_22) re_22, SUM(u.re_23) re_23, SUM(u.re_24) re_24, SUM(u.re_25) re_25,
                SUM(u.re_26) re_26, SUM(u.re_27) re_27, SUM(u.re_28) re_28, SUM(u.re_29) re_29, SUM(u.re_30) re_30 
                FROM ukm u WHERE u.bulan>=1 AND u.bulan<=3 AND u.tahun=:tahun')
                ->bindValue(':tahun', $session['tahun'])
                ->queryAll();
            }elseif($id == '2'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                SUM(u.re_1) re_1, SUM(u.re_2) re_2, SUM(u.re_3) re_3, SUM(u.re_4) re_4, SUM(u.re_5) re_5, 
                SUM(u.re_6) re_6, SUM(u.re_7) re_7, SUM(u.re_8) re_8, SUM(u.re_9) re_9, SUM(u.re_10) re_10, 
                SUM(u.re_11) re_11, SUM(u.re_12) re_12, SUM(u.re_13) re_13, SUM(u.re_14) re_14, SUM(u.re_15) re_15, 
                SUM(u.re_16) re_16, SUM(u.re_17) re_17, SUM(u.re_18) re_18, SUM(u.re_19) re_19, SUM(u.re_20) re_20, 
                SUM(u.re_21) re_21, SUM(u.re_22) re_22, SUM(u.re_23) re_23, SUM(u.re_24) re_24, SUM(u.re_25) re_25,
                SUM(u.re_26) re_26, SUM(u.re_27) re_27, SUM(u.re_28) re_28, SUM(u.re_29) re_29, SUM(u.re_30) re_30 
                FROM ukm u WHERE u.bulan>3 AND u.bulan<=6 AND u.tahun=:tahun')
                ->bindValue(':tahun', $session['tahun'])
                ->queryAll();
            }elseif($id == '3'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                SUM(u.re_1) re_1, SUM(u.re_2) re_2, SUM(u.re_3) re_3, SUM(u.re_4) re_4, SUM(u.re_5) re_5, 
                SUM(u.re_6) re_6, SUM(u.re_7) re_7, SUM(u.re_8) re_8, SUM(u.re_9) re_9, SUM(u.re_10) re_10, 
                SUM(u.re_11) re_11, SUM(u.re_12) re_12, SUM(u.re_13) re_13, SUM(u.re_14) re_14, SUM(u.re_15) re_15, 
                SUM(u.re_16) re_16, SUM(u.re_17) re_17, SUM(u.re_18) re_18, SUM(u.re_19) re_19, SUM(u.re_20) re_20, 
                SUM(u.re_21) re_21, SUM(u.re_22) re_22, SUM(u.re_23) re_23, SUM(u.re_24) re_24, SUM(u.re_25) re_25,
                SUM(u.re_26) re_26, SUM(u.re_27) re_27, SUM(u.re_28) re_28, SUM(u.re_29) re_29, SUM(u.re_30) re_30 
                FROM ukm u WHERE u.bulan>6 AND u.bulan<=9 AND u.tahun=:tahun')
                ->bindValue(':tahun', $session['tahun'])
                ->queryAll();
            }elseif($id == '4'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                SUM(u.re_1) re_1, SUM(u.re_2) re_2, SUM(u.re_3) re_3, SUM(u.re_4) re_4, SUM(u.re_5) re_5, 
                SUM(u.re_6) re_6, SUM(u.re_7) re_7, SUM(u.re_8) re_8, SUM(u.re_9) re_9, SUM(u.re_10) re_10, 
                SUM(u.re_11) re_11, SUM(u.re_12) re_12, SUM(u.re_13) re_13, SUM(u.re_14) re_14, SUM(u.re_15) re_15, 
                SUM(u.re_16) re_16, SUM(u.re_17) re_17, SUM(u.re_18) re_18, SUM(u.re_19) re_19, SUM(u.re_20) re_20, 
                SUM(u.re_21) re_21, SUM(u.re_22) re_22, SUM(u.re_23) re_23, SUM(u.re_24) re_24, SUM(u.re_25) re_25,
                SUM(u.re_26) re_26, SUM(u.re_27) re_27, SUM(u.re_28) re_28, SUM(u.re_29) re_29, SUM(u.re_30) re_30 
                FROM ukm u WHERE u.bulan>9 AND u.bulan<=12 AND u.tahun=:tahun')
                ->bindValue(':tahun', $session['tahun'])
                ->queryAll();
            }

            if($isianbidang == ''){
                $bidang = $rowdata['bidang'];
            }elseif($isianbidang == $rowdata['bidang']){
                $bidang = '';
            }else{
                $bidang = $rowdata['bidang'];
            }

            if (!empty($real)){
                foreach($real as $datareal);

                $re = 're_' .$rowdata['no'];
                
                if($rowdata['jumlah'] > 0 && $datareal[$re] > 0){
                    $session['prosentase'] = $datareal[$re] / $rowdata['jumlah'] * 100;
                }else{
                    $session['prosentase'] = '0';
                }
                
                $activeSheet
                ->setCellValue('A'.$baseRow, $baseRow-4)
                ->setCellValue('B'.$baseRow, $bidang)
                ->setCellValue('C'.$baseRow, $rowdata['upaya'])
                ->setCellValue('D'.$baseRow, $rowdata['jumlah'])
                ->setCellValue('E'.$baseRow, $datareal[$re])
                ->setCellValue('F'.$baseRow, $session['prosentase']);
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':F' .$baseRow)->applyFromArray($styleArray);
                $isianbidang = $rowdata['bidang'];
                $baseRow++;
            }
        }

        if(!empty($rowdata)){
            $lastData=$baseRow-1;
            $activeSheet->setCellValue('B'.$baseRow, 'TOTAL');
            $spreadsheet->getActiveSheet()->mergeCells('B'.$baseRow. ':C' .$baseRow);
            $activeSheet->getStyle('B'.$baseRow. ':C' .$baseRow)->getAlignment()->setHorizontal('center'); 
            $activeSheet->getStyle('B'.$baseRow. ':C' .$baseRow)->getAlignment()->setWrapText(true);
            $activeSheet->setCellValue('D'.$baseRow, '=SUM(D' .$firstData. ':D' .$lastData. ')');
            $activeSheet->setCellValue('E'.$baseRow, '=SUM(E' .$firstData. ':E' .$lastData. ')');
            $activeSheet->setCellValue('F'.$baseRow, '=SUM(F' .$firstData. ':F' .$lastData. ')');
            $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':F' .$baseRow)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':F' .$baseRow)->getFont()->setBold(true);
        }

        $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_realisasi_bok_ukm_semua_puskesmas_tribulan_'.$tribulan.'_'.$period.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function actionGetPeriod()
    {
        $session = Yii::$app->session;
        $session['periodValue'] = $_COOKIE['bulan'];
        return $_COOKIE['bulan'];
    }

    public function actionExportxlsrak()
    {
        $session = Yii::$app->session;
        $period = $session['periodValue'];

        if(Yii::$app->user->identity->username == 'admin'){
            $namapkm = 'Puskesmas '.$session['namaPkm'];
        }else{
            $namapkm = Yii::$app->user->identity->alias;
        }

        Yii::$app->db->createCommand('DELETE FROM export_program WHERE username=:username') //AND period=:periodValue 
        ->bindValue(':username', Yii::$app->user->identity->unit_id)
        // ->bindValue(':periodValue', $period)
        ->execute();

        $data = Yii::$app->db->createCommand($session['qrypoa'])
        ->queryAll();

        $program = 'program';
        $komponen = 'komponen';
        $kegiatan = 'kegiatan';

        foreach ($data as $row) {
            $exportprogram = new Exportprogram();

            if($program !== $row['nama_program']) {
                $exportprogram->nama_program = $row['nama_program'];
                $program = $row['nama_program'];
            }
            
            if($komponen !== $row['nama_pelayanan']) {
                $exportprogram->nama_pelayanan = $row['nama_pelayanan']; 
                $komponen = $row['nama_pelayanan'];
            }

            // if($kegiatan !== $row['nama_kegiatan']) {
            //     $exportprogram->nama_kegiatan = $row['nama_kegiatan']; 
            //     $kegiatan = $row['nama_kegiatan'];
            // }
            $exportprogram->nama_kegiatan = $row['nama_kegiatan']; 

            $exportprogram->bentuk_kegiatan = $row['bentuk_kegiatan']; 

            $exportprogram->jan_val=$row['jan_val'];
            $exportprogram->feb_val=$row['feb_val'];
            $exportprogram->mar_val=$row['mar_val'];
            $exportprogram->apr_val=$row['apr_val'];
            $exportprogram->mei_val=$row['mei_val'];
            $exportprogram->jun_val=$row['jun_val'];
            $exportprogram->jul_val=$row['jul_val'];
            $exportprogram->agu_val=$row['agu_val'];
            $exportprogram->sep_val=$row['sep_val'];
            $exportprogram->okt_val=$row['okt_val'];
            $exportprogram->nov_val=$row['nov_val'];
            $exportprogram->des_val=$row['des_val'];
            
            $exportprogram->jumlah=$row['jumlah'];
            $exportprogram->unit=Yii::$app->user->identity->unit_id; 
            $exportprogram->username=Yii::$app->user->identity->unit_id;
            $exportprogram->period=$period;
            $exportprogram->save();
        }

        $dataprogram = Yii::$app->db->createCommand('SELECT p.*, e.unit, SUM(e.jumlah) total FROM unit p
        RIGHT JOIN export_program e ON e.unit=p.id
        WHERE e.username=:username
        GROUP BY p.puskesmas
        ORDER BY p.puskesmas')
        ->bindValue(':username', Yii::$app->user->identity->unit_id)
        ->queryAll();

        $inputFileName = Yii::getAlias('@app/views/exportaccount').'/_rak.xlsx';

        /** Load $inputFileName to a Spreadsheet Object  **/
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A'.'2', 'RAK '.strtoupper($namapkm).' TAHUN '.$session['periodValue']);

        $styleArrayBold = [
            'font' => [
                'bold' => true,
            ],
        ];

        $baseRowAwal = 0;
        $baseRowProgram = 4;
        $baseRowService = 0;

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $styleArrayHeader = [
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        foreach ($dataprogram as $dataprogram) {
            $dataExcel = Yii::$app->db->createCommand('SELECT e.id, e.nama_program, e.nama_pelayanan, e.nama_kegiatan, SUM(e.jumlah) jumlah, sum(e.jan_val) jan_val, sum(e.feb_val) feb_val, sum(e.mar_val) mar_val, sum(e.apr_val) apr_val, 
            sum(e.mei_val) mei_val, sum(e.jun_val) jun_val, sum(e.jul_val) jul_val, sum(e.agu_val) agu_val, sum(e.sep_val) sep_val, sum(e.okt_val) okt_val, sum(e.nov_val) nov_val, sum(e.des_val) des_val,
            e.unit, e.username, e.period, p.id FROM export_program e
            LEFT JOIN program p ON p.nama_program=e.nama_program AND p.tahun=:periodValue
            where username=:username AND period=:periodValue group BY e.nama_kegiatan order by e.id')
            ->bindValue(':username', Yii::$app->user->identity->unit_id)
            ->bindValue(':periodValue', $period)
            ->queryAll();

            $count = count($dataExcel);
                 
            $baseRowService = 0;
            $baseRow = $baseRowProgram+1;
            
            if ($count > 0) {
                foreach($dataExcel as $rowExcel) {
                    $activeSheet->setCellValue('A'.$baseRow, $rowExcel['nama_program'])
                    ->setCellValue('B'.$baseRow, $rowExcel['nama_pelayanan'])
                    ->setCellValue('C'.$baseRow, $rowExcel['nama_kegiatan'])
                    ->setCellValue('D'.$baseRow, $rowExcel['jumlah'])
                    ->setCellValue('E'.$baseRow, $rowExcel['jan_val'])
                    ->setCellValue('F'.$baseRow, $rowExcel['feb_val'])
                    ->setCellValue('G'.$baseRow, $rowExcel['mar_val'])
                    ->setCellValue('H'.$baseRow, $rowExcel['apr_val'])
                    ->setCellValue('I'.$baseRow, $rowExcel['mei_val'])
                    ->setCellValue('J'.$baseRow, $rowExcel['jun_val'])
                    ->setCellValue('K'.$baseRow, $rowExcel['jul_val'])
                    ->setCellValue('L'.$baseRow, $rowExcel['agu_val'])
                    ->setCellValue('M'.$baseRow, $rowExcel['sep_val'])
                    ->setCellValue('N'.$baseRow, $rowExcel['okt_val'])
                    ->setCellValue('O'.$baseRow, $rowExcel['nov_val'])
                    ->setCellValue('P'.$baseRow, $rowExcel['des_val']);

                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':P' .$baseRow)->applyFromArray($styleArray);
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':P' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setVertical('top'); 
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':P' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setWrapText(true);
                                            
                    $baseRow++;
                    $baseRowService = $baseRowService+1;
                }
                
                $baseRowProgram=$baseRowProgram+$baseRowService;
                $baseRowProgram++;   
            }else{  
                $baseRowProgram++; 
            }
        }

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_rak_'.str_replace(' ','_',strtolower($namapkm)).'_'.$period.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function actionExportxlsrakrek($id)
    {
        $session = Yii::$app->session;
        $period = $session['periodValue'];

        if(Yii::$app->user->identity->username == 'admin'){
            $namapkm = 'Puskesmas '.$session['namaPkm'];
        }else{
            $namapkm = Yii::$app->user->identity->alias;
        }

        $inputFileName = Yii::getAlias('@app/views/exportaccount').'/_rak_rek.xlsx';

        /** Load $inputFileName to a Spreadsheet Object  **/
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        $activeSheet = $spreadsheet->getActiveSheet();

        $activeSheet->setCellValue('A'.'2', 'RAK PER REKENING '.strtoupper($namapkm).' TAHUN '.$session['periodValue']);

        $styleArrayBold = [
            'font' => [
                'bold' => true,
            ],
        ];

        $baseRow = 5;

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $styleArrayHeader = [
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        
        $dataExcel = Yii::$app->db->createCommand('SELECT c.nama_rekening, SUM(IFNULL(e.jumlah,0)) jumlah, SUM(IFNULL(e.jan_val,0)) jan_val, SUM(IFNULL(e.feb_val,0)) feb_val, SUM(IFNULL(e.mar_val,0)) mar_val, SUM(IFNULL(e.apr_val,0)) apr_val, SUM(IFNULL(e.mei_val,0)) mei_val, 
        SUM(IFNULL(e.jun_val,0)) jun_val, SUM(IFNULL(e.jul_val,0)) jul_val, SUM(IFNULL(e.agu_val,0)) agu_val, SUM(IFNULL(e.sep_val,0)) sep_val, SUM(IFNULL(e.okt_val,0)) okt_val, SUM(IFNULL(e.nov_val,0)) nov_val, SUM(IFNULL(e.des_val,0)) des_val
        FROM activity_detail e
        LEFT JOIN activity_data a ON a.id=e.activity_data_id
        LEFT JOIN activity v ON v.id=a.activity_id
        LEFT JOIN service s ON s.id=v.service_id
        LEFT JOIN program g ON g.id=s.program_id
        LEFT JOIN period p ON p.id=a.period_id
        LEFT JOIN account c ON c.id=e.account_id
        WHERE p.unit_id=:unitid AND p.tahun=:tahun
        group BY c.id ORDER BY c.nama_rekening')
        ->bindValue(':unitid', $id)
        ->bindValue(':tahun', $period)
        ->queryAll();

        $count = count($dataExcel);
        
        if ($count > 0) {
            foreach($dataExcel as $rowExcel) {
                $activeSheet->setCellValue('A'.$baseRow, $rowExcel['nama_rekening'])
                ->setCellValue('B'.$baseRow, $rowExcel['jumlah'])
                ->setCellValue('C'.$baseRow, $rowExcel['jan_val'])
                ->setCellValue('D'.$baseRow, $rowExcel['feb_val'])
                ->setCellValue('E'.$baseRow, $rowExcel['mar_val'])
                ->setCellValue('F'.$baseRow, $rowExcel['apr_val'])
                ->setCellValue('G'.$baseRow, $rowExcel['mei_val'])
                ->setCellValue('H'.$baseRow, $rowExcel['jun_val'])
                ->setCellValue('I'.$baseRow, $rowExcel['jul_val'])
                ->setCellValue('J'.$baseRow, $rowExcel['agu_val'])
                ->setCellValue('K'.$baseRow, $rowExcel['sep_val'])
                ->setCellValue('L'.$baseRow, $rowExcel['okt_val'])
                ->setCellValue('M'.$baseRow, $rowExcel['nov_val'])
                ->setCellValue('N'.$baseRow, $rowExcel['des_val']);

                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':N' .$baseRow)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':N' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setVertical('top'); 
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':N' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setWrapText(true);
                                        
                $baseRow++;
            }
            
            $baseRow++;   
        }else{  
            $baseRow++; 
        }

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_rak_rek_'.str_replace(' ','_',strtolower($namapkm)).'_'.$period.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
