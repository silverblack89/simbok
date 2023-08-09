<?php

namespace app\controllers;

use Yii;
use app\models\Deptperiod;
use yii\data\ActiveDataProvider;
use app\models\Exportaccount;
use app\models\Unit;
use app\models\Exportprogram;
use app\models\Profile;
use app\models\Deptstatus;
use app\models\User;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;
use yii\data\SqlDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

/**
 * DeptperiodController implements the CRUD actions for Deptperiod model.
 */
class DeptperiodController extends Controller
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

    public function actionRkaApbd($cond)
    {
        $session = Yii::$app->session;

        if($cond == 'def'){
            unset($session['sumber']);
            unset($session['subkegiatan']);
            $sumber = ' <> 0';
            $subkegiatan = ' <> 0';
        }else{
            if ($_COOKIE['sumber'] == ''){
                $sumber = ' <> 0';
            }else{
                $sumber = ' = '.$_COOKIE['sumber'];
            }
    
            if ($_COOKIE['subkegiatan'] == ''){
                $subkegiatan = ' <> 0';
            }else{
                $subkegiatan = ' = '.$_COOKIE['subkegiatan'];
            }
    
            $session['sumber'] = $_COOKIE['sumber'];
            $session['subkegiatan'] = $_COOKIE['subkegiatan'];
        }

        $session['cond'] = $cond;

        $query = 'SELECT sd.nama sumber_dana, g.nama_program, v.nama_kegiatan, s.nama_sub_kegiatan, IFNULL(a.bentuk_kegiatan, s.nama_sub_kegiatan) bentuk_kegiatan, 
        a.indikator_hasil, a.target_hasil, a.indikator_keluaran, a.target_keluaran, c.nama_rekening, e.rincian,
        e.vol_1, e.satuan_1, 
        
        IFNULL(e.vol_2,"") vol_2, IFNULL(e.satuan_2,"") satuan_2, 
        IFNULL(e.vol_3,"") vol_3, IFNULL(e.satuan_3,"") satuan_3,
        IFNULL(e.vol_4,"") vol_4, IFNULL(e.satuan_4,"") satuan_4,
        vol_1*IFNULL(vol_2,1)*IFNULL(vol_3,1)*IFNULL(vol_4,1) vol, 

        case when e.tw1=1 then "V" ELSE "" END tw1, case when e.tw2=1 then "V" ELSE "" END tw2, 
        case when e.tw3=1 then "V" ELSE "" END tw3, case when e.tw4=1 then "V" ELSE "" END tw4,
        
        e.unit_cost, e.jumlah
        FROM dept_sub_activity_detail e
        LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
        LEFT JOIN dept_sub_activity s ON s.id=a.dept_sub_activity_id
        LEFT JOIN dept_activity v ON v.id=s.dept_activity_id
        LEFT JOIN dept_program g ON g.id=v.dept_program_id
        LEFT JOIN dept_period p ON p.id=a.dept_period_id
        LEFT JOIN account c ON c.id=e.account_id
        LEFT JOIN sumber_dana sd ON sd.id=e.sumber_dana_id
        LEFT JOIN dpa dp ON dp.id=a.dpa_id 
        WHERE p.tahun="'.$session['deptPeriodValue'].'" AND e.sumber_dana_id '.$sumber.' 
        AND (s.id '.$subkegiatan.' OR dp.dept_sub_activity_id '.$subkegiatan.')
        ORDER BY g.id, s.id, v.id, a.id';

        $session['qrka'] = $query;
        $session['sumbernya'] = $sumber;
        $session['subnya'] = $subkegiatan;

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model = $dataProvider->getModels();

        return $this->render('rka', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'namaUnit' => Yii::$app->user->identity->alias,
            'id' => 0,
        ]);
    }

    public function actionExportrkaadm()
    {
        $session = Yii::$app->session;
        $period = $session['deptPeriodValue'];
        //QUERY PER PROGRAM
        // Yii::$app->db->createCommand()->truncateTable('export_program')->execute();
        Yii::$app->db->createCommand('DELETE FROM export_program WHERE username=:username AND period=:periodValue ')
        ->bindValue(':username', Yii::$app->user->identity->username)
        ->bindValue(':periodValue', $period)
        ->execute();

        $data = Yii::$app->db->createCommand($session['qrka'])
        ->queryAll();

        $rinci = '';
        $komponen = '';
        $kegiatan = '';
        $bentuk = '';
        foreach ($data as $row) {
            $exportprogram =  new Exportprogram();
   
            $exportprogram->nama_program=$row['nama_program'];
            
            if ($komponen !== $row['nama_kegiatan']) {
                $exportprogram->nama_pelayanan=$row['nama_kegiatan']; $komponen = $row['nama_kegiatan'];
            }

            if ($kegiatan !== $row['nama_sub_kegiatan']) {
                $exportprogram->nama_kegiatan=$row['nama_sub_kegiatan']; $kegiatan = $row['nama_sub_kegiatan'];
            }

            if ($bentuk !== $row['bentuk_kegiatan']) {
                $exportprogram->bentuk_kegiatan=$row['bentuk_kegiatan']; $bentuk = $row['bentuk_kegiatan'];
                $exportprogram->sasaran=$row['indikator_hasil'];
                $exportprogram->target=$row['target_hasil'];
                $exportprogram->lokasi=$row['indikator_keluaran'];
                $exportprogram->pelaksana=$row['target_keluaran'];
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
            $exportprogram->jan=$row['tw1'];
            $exportprogram->feb=$row['tw2'];
            $exportprogram->mar=$row['tw3'];
            $exportprogram->apr=$row['tw4'];
            $exportprogram->username=Yii::$app->user->identity->username;
            $exportprogram->period=$period;
            $exportprogram->save();
        }

        //Sheet 2
        $rinci = '';
        $inputFileName = Yii::getAlias('@app/views/exportaccount').'/_export_detail.xlsx';

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

        $activeSheet->setCellValue('A'.'1', 'RKA APBD ');
        $activeSheet->setCellValue('A'.'2', 'DINAS KESEHATAN KABUPASTEN BOYOLALI');
        $activeSheet->setCellValue('A'.'3', 'TAHUN ' .$period);

        // $dataprogram = Yii::$app->db->createCommand('SELECT * FROM program where id<>0')
        $dataprogram = Yii::$app->db->createCommand('SELECT p.* FROM dept_program p
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
            LEFT JOIN dept_program p ON p.nama_program=e.nama_program AND p.tahun=:periodValue
            LEFT JOIN verification v ON v.program_id=p.id 
            where e.nama_program=:namaprogram AND username=:username AND period=:periodValue ')
            // ->bindValue(':unitId', $unit_id)
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
                ->setCellValue('AD'.$tabletitle, '=AD6'); 
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowProgram. ':C' .$baseRowProgram)->applyFromArray($styleArrayBold);
                $spreadsheet->getActiveSheet()->mergeCells('L'.$tabletitle. ':X' .$tabletitle);
                $spreadsheet->getActiveSheet()->getStyle('A'.$tabletitle. ':AD' .$tabletitle)->applyFromArray($styleArrayHeader);
                $activeSheet->getStyle('A'.$tabletitle. ':AD' .$tabletitle)->getAlignment()->setHorizontal('center'); 
                $activeSheet->getStyle('A'.$tabletitle. ':AD' .$tabletitle)->getAlignment()->setWrapText(true);
                $activeSheet->getStyle('A'.$tabletitle. ':AD' .$tabletitle)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F8CBAD');
                }
            }else{
                $activeSheet->setCellValue('A'.$baseRowProgram, $baseRowAwal)
                ->setCellValue('C'.$baseRowProgram, $dataprogram['nama_program']); 

                $activeSheet->getStyle('A6:AD6')->getFill()
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
                                ;  
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AD' .$baseRow)->applyFromArray($styleArray);
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AD' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setVertical('top'); 
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AD' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setWrapText(true);
                                            
                    $baseRow++;
                    $baseRowService = $baseRowService+1;
                    $rowAkhir = $baseRowAwal;
                }
                
                
                $activeSheet->setCellValue('A'.$baseRow, 'Total');
                $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRow. ':Y' .$baseRow);
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AD' .$baseRow)->applyFromArray($styleArray);
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':Z' .$baseRow)->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFF00');
                
                $lastData = $baseRow-1;
                
                $activeSheet->setCellValue('Z'.$baseRow, '=SUM(Z' .$firstData. ':Z' .$lastData. ')'); $baseRowProgram=$baseRowProgram+1;
                $baseRowProgram=$baseRowProgram+$baseRowService+3;


                if ($baseRowAwal!=$count){
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

                    // $activeSheet->setCellValue('X'.$baseRowProgram, 'Mengetahui'); 
                    // $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                    // $baseRowProgram=$baseRowProgram+1;
                    // $activeSheet->setCellValue('X'.$baseRowProgram, 'Kepala ' .$unitnya->alias); 
                    // $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                    // $baseRowProgram=$baseRowProgram+4;
                    // $activeSheet->setCellValue('X'.$baseRowProgram, $unit->kepala); 
                    // $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                    // $baseRowProgram=$baseRowProgram+1;
                    // $activeSheet->setCellValue('X'.$baseRowProgram, 'NIP. ' .$unit->nip_kepala); 
                    // $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                }else{
                    $query = 'SELECT SUM(e.jumlah) jumlah
                    FROM dept_sub_activity_detail e 
                    LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id 
                    LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
                    LEFT JOIN dept_activity s ON s.id=v.dept_activity_id 
                    LEFT JOIN dept_program g ON g.id=s.dept_program_id 
                    LEFT JOIN dept_period p ON p.id=a.dept_period_id
                    LEFT JOIN account c ON c.id=e.account_id 
                    LEFT JOIN sumber_dana sd ON sd.id=e.sumber_dana_id
                    LEFT JOIN dpa dp ON dp.id=a.dpa_id 
                    WHERE p.tahun="'.$session['deptPeriodValue'].'" AND e.sumber_dana_id '.$session['sumbernya'].' 
                    AND (s.id '.$session['subnya'].' OR dp.dept_sub_activity_id '.$session['subnya'].')';

                    $total = Yii::$app->db->createCommand($query)->queryAll();

                    foreach ($total as $rowTotal);
                    // return $rowTotal['jumlah'];

                    $baseRow = $baseRow+1;
                    $activeSheet->setCellValue('A'.$baseRow, 'Total Semua Program');
                    $activeSheet->setCellValue('Z'.$baseRow, $rowTotal['jumlah']);
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRow. ':Y' .$baseRow);
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AD' .$baseRow)->applyFromArray($styleArray);
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

                    // $activeSheet->setCellValue('F'.$baseRowProgram, 'Mengetahui,'); 
                    // $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    // $baseRowProgram=$baseRowProgram+1;

                    // $activeSheet->setCellValue('A'.$baseRowProgram, 'Kepala ' .$unitnya->alias); 
                    // $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    // $activeSheet->setCellValue('F'.$baseRowProgram, 'Sekretaris ' .$dataProfile->nama. ' '.$dataProfile->kota_kab); 
                    // $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    // // $activeSheet->setCellValue('W'.$baseRowProgram, 'PPTK BOK'); 
                    // // $spreadsheet->getActiveSheet()->mergeCells('W'.$baseRowProgram. ':Y' .$baseRowProgram);
                    // $baseRowProgram=$baseRowProgram+4;

                    // $activeSheet->setCellValue('A'.$baseRowProgram, $unit->kepala); 
                    // $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    // $activeSheet->setCellValue('F'.$baseRowProgram, $dataProfile->sekretaris); 
                    // $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    // // $activeSheet->setCellValue('W'.$baseRowProgram, $unit->petugas); 
                    // // $spreadsheet->getActiveSheet()->mergeCells('W'.$baseRowProgram. ':Y' .$baseRowProgram);
                    // $baseRowProgram=$baseRowProgram+1;

                    // // $activeSheet->setCellValue('A'.$baseRowProgram, $unit->kepala); 
                    // $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    // $activeSheet->setCellValue('F'.$baseRowProgram, $dataProfile->jabatan_sekretaris); 
                    // $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    // // $activeSheet->setCellValue('W'.$baseRowProgram, $unit->jabatan_petugas); 
                    // // $spreadsheet->getActiveSheet()->mergeCells('W'.$baseRowProgram. ':Y' .$baseRowProgram);
                    // $baseRowProgram=$baseRowProgram+1;

                    // $activeSheet->setCellValue('A'.$baseRowProgram, 'NIP. ' .$unit->nip_kepala);
                    // $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    // $activeSheet->setCellValue('F'.$baseRowProgram, 'NIP. ' .$dataProfile->nip_sekretaris);
                    // $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    // // $activeSheet->setCellValue('W'.$baseRowProgram, 'NIP. ' .$unit->nip_petugas);
                    // // $spreadsheet->getActiveSheet()->mergeCells('W'.$baseRowProgram. ':Y' .$baseRowProgram);

                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AC' .$baseRow)->applyFromArray($styleArrayBold);
                }
                $baseRowProgram=$baseRowProgram+20;

                $baseRowProgram++;   
            }else{
                // $baseRowProgram = $baseRowProgram+2;    
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

        $spreadsheet->setActiveSheetIndex(0);

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

        $filename = 'export_rka_apbd_'.$period.'.xlsx';

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

    public function actionDatapoa($id)
    {
        $session = Yii::$app->session;
        $session['bok'] = $id;

        $p = 'def';

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

        $query = 'SELECT g.nama_program, v.nama_kegiatan, s.nama_sub_kegiatan, IFNULL(a.bentuk_kegiatan, s.nama_sub_kegiatan) bentuk_kegiatan, 
        a.indikator_hasil, a.target_hasil, a.indikator_keluaran, a.target_keluaran, c.kode, c.nama_rekening, e.rincian,
        e.vol_1, e.satuan_1, 
        
        IFNULL(e.vol_2,"") vol_2, IFNULL(e.satuan_2,"") satuan_2, 
        IFNULL(e.vol_3,"") vol_3, IFNULL(e.satuan_3,"") satuan_3,
        IFNULL(e.vol_4,"") vol_4, IFNULL(e.satuan_4,"") satuan_4,
        vol_1*IFNULL(vol_2,1)*IFNULL(vol_3,1)*IFNULL(vol_4,1) vol, 
        
        e.unit_cost, e.jumlah
        FROM dept_sub_activity_detail e
        LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
        LEFT JOIN dept_sub_activity s ON s.id=a.dept_sub_activity_id
        LEFT JOIN dept_activity v ON v.id=s.dept_activity_id
        LEFT JOIN dept_program g ON g.id=v.dept_program_id
        LEFT JOIN dept_period p ON p.id=a.dept_period_id
        LEFT JOIN account c ON c.id=e.account_id
        WHERE p.unit_id="'.Yii::$app->user->identity->username.'" AND p.tahun="'.$session['deptPeriodValue'].'" AND g.bok_id="'.$id.'"
        ORDER BY g.id, s.id, v.id, a.id';

        $session['qrypoa'] = $query;

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model = $dataProvider->getModels();

        return $this->render('detail', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'namaUnit' => Yii::$app->user->identity->alias,
            'id' => $id,
        ]);
    }

    public function actionDatapoaadm($bok, $id)
    {
        $session = Yii::$app->session;
        $period = $session['deptPeriodValue'];
        $session['bok'] = $bok;

        $session = Yii::$app->session;
        // $session['deptPeriodValue'] = $period;

        $p = 'def';

        if($p == 'def'){
            $session['poaLabel'] = ' Awal';
        }elseif($p == 'pergeseran'){
            $session['poaLabel'] = ' Pergeseran';
        }elseif($p == 'perubahan'){
            $session['poaLabel'] = ' Perubahan';
        }

        $query = 'SELECT g.nama_program, s.nama_kegiatan, v.nama_sub_kegiatan, a.id, a.dept_period_id, p.unit_id, IFNULL(a.bentuk_kegiatan, v.nama_sub_kegiatan) bentuk_kegiatan, 
        a.indikator_hasil, a.target_hasil, a.indikator_keluaran, a.target_keluaran, c.kode, c.nama_rekening, e.rincian, 
        e.vol_1, e.satuan_1, 
        
        IFNULL(e.vol_2,"") vol_2, IFNULL(e.satuan_2,"") satuan_2, 
        IFNULL(e.vol_3,"") vol_3, IFNULL(e.satuan_3,"") satuan_3,
        IFNULL(e.vol_4,"") vol_4, IFNULL(e.satuan_4,"") satuan_4,
        vol_1*IFNULL(vol_2,1)*IFNULL(vol_3,1)*IFNULL(vol_4,1) vol, 
        
        e.unit_cost, e.jumlah 
        FROM dept_sub_activity_detail e 
        LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id 
        LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id 
        LEFT JOIN dept_activity s ON s.id=v.dept_activity_id 
        LEFT JOIN dept_program g ON g.id=s.dept_program_id 
        LEFT JOIN dept_period p ON p.id=a.dept_period_id 
        LEFT JOIN account c ON c.id=e.account_id 
        WHERE p.unit_id="'.$id.'" AND p.tahun="'.$period.'" AND g.bok_id="'.$bok.'"
        ORDER BY g.id, s.id, v.id, a.id';

        $session['qrypoa'] = $query;

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model = $dataProvider->getModels();

        $unit = Unit::findOne($id);
        $session['namaUnit'] = $unit->puskesmas;

        return $this->render('detail', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'id' => $id,
            'namaUnit' => $unit->puskesmas,
        ]);

        // return $query;
    }

    public function actionExportxlsdesk()
    {
        $session = Yii::$app->session;
        $period = $session['deptPeriodValue'];

        if(Yii::$app->user->identity->username == 'admin'){
            $namapkm = $session['namaUnit'];
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
                if ($kegiatan !== $row['nama_sub_kegiatan']) {
                    $exportprogram->nama_kegiatan=$row['nama_sub_kegiatan']; $kegiatan = $row['nama_sub_kegiatan'];

                    $bentuk = '';
                    if ($bentuk !== $row['bentuk_kegiatan']) {
                        $exportprogram->bentuk_kegiatan=$row['bentuk_kegiatan']; $bentuk = $row['bentuk_kegiatan'];
                    }
                }
            }else{
                $exportprogram->unit=Yii::$app->user->identity->unit_id; $pkm = $row['nama_program'];

                if ($kegiatan !== $row['nama_sub_kegiatan']) {
                    $exportprogram->nama_kegiatan=$row['nama_sub_kegiatan']; $kegiatan = $row['nama_sub_kegiatan'];
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
            $exportprogram->nama_pelayanan=$row['nama_kegiatan'];
            

            // if ($kegiatan !== $row['nama_kegiatan']) {
            //     $exportprogram->nama_kegiatan=$row['nama_kegiatan']; $kegiatan = $row['nama_kegiatan'];
            // }

            // if ($bentuk !== $row['bentuk_kegiatan']) {
                // $exportprogram->bentuk_kegiatan=$row['bentuk_kegiatan']; //$bentuk = $row['bentuk_kegiatan'];
                $exportprogram->sasaran=$row['indikator_hasil'];
                $exportprogram->target=$row['target_hasil'];
                $exportprogram->lokasi=$row['indikator_keluaran'];
                $exportprogram->pelaksana=$row['target_keluaran'];
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
            LEFT JOIN dept_program p ON p.nama_program=e.nama_program AND p.tahun=:periodValue
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

    /**
     * Lists all Deptperiod models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Deptperiod::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Deptperiod model.
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
     * Creates a new Deptperiod model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $session = Yii::$app->session;
        $request = $session['deptPeriodValue'];

        // $POST_VARIABLE=Yii::$app->request->post('Deptperiod');
        // $request = $POST_VARIABLE['tahun'];

        $period = Deptperiod::find()
        ->where(['unit_id' => Yii::$app->user->identity->username, 'tahun' => $request])
        ->one();

        if ($period == null) {
            $model = new Deptperiod();
            $model->unit_id = Yii::$app->user->identity->username;
        }else{
            $model = $this->findModel($period);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $session = Yii::$app->session;
            $session->open();
            $session['deptPeriodId'] = $model->id;
            $session['deptPeriodValue'] = $model->tahun;
            return $this->redirect(['deptprogram/list']);
        }

        // $tahunnya = $session['periodValue']; 

        // $query = 'SELECT u.puskesmas POA, CASE WHEN IFNULL(p.pagu,0)=0 THEN 0
        // ELSE IFNULL(p.pagu,0)-sum(IFNULL(e.jumlah,0))
        // END AS prosentase
        // FROM activity_detail e LEFT JOIN activity_data a ON a.id=e.activity_data_id 
        // LEFT JOIN activity v ON v.id=a.activity_id 
        // LEFT JOIN service s ON s.id=v.service_id 
        // LEFT JOIN period p ON p.id=a.period_id 
        // LEFT JOIN unit u ON u.id=p.unit_id WHERE p.tahun='.$tahunnya.' 
        // GROUP BY p.unit_id, p.pagu';

        // $dataProvider = new SqlDataProvider([
        //     'sql' => $query,
        //     'pagination' => false
        // ]);

        // $model2 = $dataProvider->getModels();

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Deptperiod model.
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
     * Deletes an existing Deptperiod model.
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
     * Finds the Deptperiod model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Deptperiod the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Deptperiod::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionRekapRekening()
    {
        $session = Yii::$app->session;
        $data = 'SELECT g.nama_program, v.nama_sub_kegiatan, c.nama_rekening, IFNULL(SUM(e.vol_1),0) jml_vol_1, IFNULL(SUM(e.vol_2),0) jml_vol_2, IFNULL(SUM(e.vol_3),0) jml_vol_3, IFNULL(SUM(e.vol_4),0) jml_vol_4, SUM(e.jumlah) sub_total
        FROM dept_sub_activity_detail e
        LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
        LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
        LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
        LEFT JOIN dept_program g ON g.id=s.dept_program_id
        LEFT JOIN dept_period p ON p.id=a.dept_period_id
        LEFT JOIN account c ON c.id=e.account_id
        WHERE p.tahun='.$session['deptPeriodValue'].' AND g.bok_id=1
        group BY g.nama_program, v.nama_sub_kegiatan, c.nama_rekening
        ORDER BY g.id, v.id, c.id';

        $dataProvider = new SqlDataProvider([
            'sql' => $data,
            'pagination' => false
        ]);

        return $this->render('rekaprek',[
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionRekapReal($cond)
    {
        $session = Yii::$app->session;

        if($cond == 'def'){
            unset($session['tw']);
            unset($session['bok']);
            $id = '1';
            $bok = '1';
            $session['tw'] = 1;
            $session['bok'] = 1;
        }else{
            $id = $_COOKIE['tw'];
            $bok = $_COOKIE['bok'];
            $session['tw'] = $_COOKIE['tw'];
            $session['bok'] = $_COOKIE['bok'];
        }

        $real = 'SELECT p.id, p.nama_program, s.id komid, s.nama_kegiatan,
        
        IFNULL(UMPEG.jml_real,0) umpeg, IFNULL(FARMAMIN.jml_real,0) farmamin, IFNULL(KESGA.jml_real,0) kesga, IFNULL(KESLING.jml_real,0) kesling, IFNULL(YANKESRUJ.jml_real,0) yankesruj, 
        IFNULL(YANKESTRAD.jml_real,0) yankestrad, IFNULL(KEUSET.jml_real,0) keuset, IFNULL(YANKESPRIM.jml_real,0) yankesprim, IFNULL(P2PM.jml_real,0) p2pm, IFNULL(P2PTM.jml_real,0) p2ptm, 
        IFNULL(PERBEKES.jml_real,0) perbekes, IFNULL(PROMKES.jml_real,0) promkes, IFNULL(RENLAP.jml_real,0) renlap, IFNULL(SDMK.jml_real,0) sdmk, IFNULL(SURVEILANS.jml_real,0) surveilans,

        IFNULL(UMPEG.jml_real,0) + IFNULL(FARMAMIN.jml_real,0) + IFNULL(KESGA.jml_real,0) + IFNULL(KESLING.jml_real,0) +
        IFNULL(YANKESRUJ.jml_real,0) + IFNULL(YANKESTRAD.jml_real,0) + IFNULL(KEUSET.jml_real,0) + IFNULL(YANKESPRIM.jml_real,0) + IFNULL(P2PM.jml_real,0) +
        IFNULL(P2PTM.jml_real,0) + IFNULL(PERBEKES.jml_real,0) + IFNULL(PROMKES.jml_real,0) + IFNULL(RENLAP.jml_real,0) + IFNULL(SDMK.jml_real,0) +
        IFNULL(SURVEILANS.jml_real,0) total

        FROM dept_activity s
        LEFT JOIN dept_program p ON p.id=s.dept_program_id
        LEFT JOIN
        (
            -- UMPEG
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="UMPEG" group BY s.id
        ) UMPEG ON UMPEG.id=s.id
        LEFT JOIN
        (
            -- FARMAMIN
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="FARMAMIN" group BY s.id
        ) FARMAMIN ON FARMAMIN.id=s.id
        LEFT JOIN
        (
            -- KESGA
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="KESGA" group BY s.id
        ) KESGA ON KESGA.id=s.id
        LEFT JOIN
        (
            -- KESLING
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="KESLING" group BY s.id
        ) KESLING ON KESLING.id=s.id
        LEFT JOIN
        (
            -- YANKESRUJ
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="YANKESRUJ" group BY s.id
        ) YANKESRUJ ON YANKESRUJ.id=s.id
        LEFT JOIN
        (
            -- YANKESTRAD
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="YANKESTRAD" group BY s.id
        ) YANKESTRAD ON YANKESTRAD.id=s.id
        LEFT JOIN
        (
            -- KEUSET
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="KEUSET" group BY s.id
        ) KEUSET ON KEUSET.id=s.id
        LEFT JOIN
        (
            -- YANKESPRIM
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="YANKESPRIM" group BY s.id
        ) YANKESPRIM ON YANKESPRIM.id=s.id
        LEFT JOIN
        (
            -- P2PM
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P2PM" group BY s.id
        ) P2PM ON P2PM.id=s.id
        LEFT JOIN
        (
            -- P2PTM
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P2PTM" group BY s.id
        ) P2PTM ON P2PTM.id=s.id
        LEFT JOIN
        (
            -- PERBEKES
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="PERBEKES" group BY s.id
        ) PERBEKES ON PERBEKES.id=s.id
        LEFT JOIN
        (
            -- PROMKES
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="PROMKES" group BY s.id
        ) PROMKES ON PROMKES.id=s.id
        LEFT JOIN
        (
            -- RENLAP
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="RENLAP" group BY s.id
        ) RENLAP ON RENLAP.id=s.id
        LEFT JOIN
        (
            -- SDMK
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="SDMK" group BY s.id
        ) SDMK ON SDMK.id=s.id
        LEFT JOIN
        (
            -- SURVEILANS
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="SURVEILANS" group BY s.id
        ) SURVEILANS ON SURVEILANS.id=s.id
        WHERE p.tahun='.$session['deptPeriodValue'].' AND p.bok_id='.$bok.' AND p.aktif=1 AND p.detail=0
        UNION
        SELECT p.id, p.nama_program, a.id komid, a.nama_sub_kegiatan nama_kegiatan,
        
        IFNULL(UMPEG.jml_real,0) umpeg, IFNULL(FARMAMIN.jml_real,0) farmamin, IFNULL(KESGA.jml_real,0) kesga, IFNULL(KESLING.jml_real,0) kesling, IFNULL(YANKESRUJ.jml_real,0) yankesruj, 
        IFNULL(YANKESTRAD.jml_real,0) yankestrad, IFNULL(KEUSET.jml_real,0) keuset, IFNULL(YANKESPRIM.jml_real,0) yankesprim, IFNULL(P2PM.jml_real,0) p2pm, IFNULL(P2PTM.jml_real,0) p2ptm, 
        IFNULL(PERBEKES.jml_real,0) perbekes, IFNULL(PROMKES.jml_real,0) promkes, IFNULL(RENLAP.jml_real,0) renlap, IFNULL(SDMK.jml_real,0) sdmk, IFNULL(SURVEILANS.jml_real,0) surveilans,

        IFNULL(UMPEG.jml_real,0) + IFNULL(FARMAMIN.jml_real,0) + IFNULL(KESGA.jml_real,0) + IFNULL(KESLING.jml_real,0) +
        IFNULL(YANKESRUJ.jml_real,0) + IFNULL(YANKESTRAD.jml_real,0) + IFNULL(KEUSET.jml_real,0) + IFNULL(YANKESPRIM.jml_real,0) + IFNULL(P2PM.jml_real,0) +
        IFNULL(P2PTM.jml_real,0) + IFNULL(PERBEKES.jml_real,0) + IFNULL(PROMKES.jml_real,0) + IFNULL(RENLAP.jml_real,0) + IFNULL(SDMK.jml_real,0) +
        IFNULL(SURVEILANS.jml_real,0) total

        FROM dept_sub_activity a
        LEFT JOIN dept_activity s ON s.id=a.dept_activity_id
        LEFT JOIN dept_program p ON p.id=s.dept_program_id

        LEFT JOIN
        (
            -- UMPEG
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="UMPEG" group BY v.id
        ) UMPEG ON UMPEG.id=a.id
        LEFT JOIN
        (
            -- FARMAMIN
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="FARMAMIN" group BY v.id
        ) FARMAMIN ON FARMAMIN.id=a.id
        LEFT JOIN
        (
            -- KESGA
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="KESGA" group BY v.id
        ) KESGA ON KESGA.id=a.id
        LEFT JOIN
        (
            -- KESLING
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="KESLING" group BY v.id
        ) KESLING ON KESLING.id=a.id
        LEFT JOIN
        (
            -- YANKESRUJ
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="YANKESRUJ" group BY v.id
        ) YANKESRUJ ON YANKESRUJ.id=a.id
        LEFT JOIN
        (
            -- YANKESTRAD
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="YANKESTRAD" group BY v.id
        ) YANKESTRAD ON YANKESTRAD.id=a.id
        LEFT JOIN
        (
            -- KEUSET
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="KEUSET" group BY v.id
        ) KEUSET ON KEUSET.id=a.id
        LEFT JOIN
        (
            -- YANKESPRIM
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="YANKESPRIM" group BY v.id
        ) YANKESPRIM ON YANKESPRIM.id=a.id
        LEFT JOIN
        (
            -- P2PM
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P2PM" group BY v.id
        ) P2PM ON P2PM.id=a.id
        LEFT JOIN
        (
            -- P2PTM
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="P2PTM" group BY v.id
        ) P2PTM ON P2PTM.id=a.id
        LEFT JOIN
        (
            -- PERBEKES
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="PERBEKES" group BY v.id
        ) PERBEKES ON PERBEKES.id=a.id
        LEFT JOIN
        (
            -- PROMKES
            SELECT s.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="PROMKES" group BY v.id
        ) PROMKES ON PROMKES.id=a.id
        LEFT JOIN
        (
            -- RENLAP
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="RENLAP" group BY v.id
        ) RENLAP ON RENLAP.id=a.id
        LEFT JOIN
        (
            -- SDMK
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="SDMK" group BY v.id
        ) SDMK ON SDMK.id=a.id
        LEFT JOIN
        (
            -- SURVEILANS
            SELECT v.id, SUM(IFNULL(r.jumlah,0)) jml_real
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan='.$id.'
            WHERE p.unit_id="SURVEILANS" group BY v.id
        ) SURVEILANS ON SURVEILANS.id=a.id
        WHERE p.tahun='.$session['deptPeriodValue'].' AND p.bok_id='.$bok.' AND p.aktif=1 AND p.detail=1
        ORDER BY id, komid';

        $session['qryrekapreal'] = $real;

        $dataProvider = new SqlDataProvider([
            'sql' => $real,
            'pagination' => false
        ]);

        return $this->render('rekapreal',[
            'dataProvider' => $dataProvider,
            'id' => $id
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

        $activeSheet->setCellValue('A'.'1', 'Rekap Realisasi per Upaya');
        $activeSheet->setCellValue('A'.'2', 'PERIODE ' .$session['deptPeriodValue']);
        $baseRow=5;

        $activeSheet
            ->setCellValue('A'.'4', 'NO')
            ->setCellValue('B'.'4', 'RINCIAN MENU KEGIATAN')
            ->setCellValue('C'.'4', 'KOMPONEN')
            ->setCellValue('D'.'4', 'UMPEG')
            ->setCellValue('E'.'4', 'FARMAMIN')
            ->setCellValue('F'.'4', 'KESGA')
            ->setCellValue('G'.'4', 'KESLING')
            ->setCellValue('H'.'4', 'YANKESRUJ')
            ->setCellValue('I'.'4', 'YANKESTRAD')
            ->setCellValue('J'.'4', 'KEUSET')
            ->setCellValue('K'.'4', 'YANKESPRIM')
            ->setCellValue('L'.'4', 'P2PM')
            ->setCellValue('M'.'4', 'P2PTM')
            ->setCellValue('N'.'4', 'PERBEKES')
            ->setCellValue('O'.'4', 'PROMKES')
            ->setCellValue('P'.'4', 'RENLAP')
            ->setCellValue('Q'.'4', 'SDMK')
            ->setCellValue('R'.'4', 'SURVEILANS')
            ->setCellValue('S'.'4', 'TOTAL');

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
            ->setCellValue('C'.$baseRow, $rowdata['nama_kegiatan'])
            ->setCellValue('D'.$baseRow, $rowdata['umpeg'])
            ->setCellValue('E'.$baseRow, $rowdata['farmamin'])
            ->setCellValue('F'.$baseRow, $rowdata['kesga'])
            ->setCellValue('G'.$baseRow, $rowdata['kesling'])
            ->setCellValue('H'.$baseRow, $rowdata['yankesruj'])
            ->setCellValue('I'.$baseRow, $rowdata['yankestrad'])
            ->setCellValue('J'.$baseRow, $rowdata['keuset'])
            ->setCellValue('K'.$baseRow, $rowdata['yankesprim'])
            ->setCellValue('L'.$baseRow, $rowdata['p2pm'])
            ->setCellValue('M'.$baseRow, $rowdata['p2ptm'])
            ->setCellValue('N'.$baseRow, $rowdata['perbekes'])
            ->setCellValue('O'.$baseRow, $rowdata['promkes'])
            ->setCellValue('P'.$baseRow, $rowdata['renlap'])
            ->setCellValue('Q'.$baseRow, $rowdata['sdmk'])
            ->setCellValue('R'.$baseRow, $rowdata['surveilans'])
            ->setCellValue('S'.$baseRow, $rowdata['total']);
            $rinci = $rowdata['nama_program'];
            // $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':E' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setWrapText(true)->setVertical('top');
            $baseRow++;
        }

        // $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_rekap_realisasi_per_upaya_tw_'.$session['tw'].'_'.$session['deptPeriodValue'].'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function actionExportxlsrek()
    {
        $session = Yii::$app->session;

        $data = Yii::$app->db->createCommand('SELECT g.nama_program, v.nama_sub_kegiatan, c.nama_rekening, IFNULL(SUM(e.vol_1),0) jml_vol_1, IFNULL(SUM(e.vol_2),0) jml_vol_2, IFNULL(SUM(e.vol_3),0) jml_vol_3, IFNULL(SUM(e.vol_4),0) jml_vol_4, SUM(e.jumlah) sub_total
        FROM dept_sub_activity_detail e
        LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
        LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
        LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
        LEFT JOIN dept_program g ON g.id=s.dept_program_id
        LEFT JOIN dept_period p ON p.id=a.dept_period_id
        LEFT JOIN account c ON c.id=e.account_id
        WHERE p.tahun=:period AND g.bok_id=1
        group BY g.nama_program, v.nama_sub_kegiatan, c.nama_rekening
        ORDER BY g.id, v.id, c.id')
        ->bindValue(':period', $session['deptPeriodValue'])
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
        $activeSheet->setCellValue('A'.'2', 'PERIODE ' .$session['deptPeriodValue']);
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
            if($komponen == $rowdata['nama_sub_kegiatan']){
                $komponen = '';
            }else{
                $komponen = $rowdata['nama_sub_kegiatan'];
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
            $komponen = $rowdata['nama_sub_kegiatan'];
            // $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':E' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setWrapText(true)->setVertical('top');
            $baseRow++;
        }

        // $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_rekap_rekening_'.$session['deptPeriodValue'].'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function actionExportxls()
    {
        // $POST_VARIABLE=Yii::$app->request->post('Period');
        // $period = $POST_VARIABLE['tahun'];

        $session = Yii::$app->session;
        $period = $session['deptPeriodValue'];

        //QUERY PER REKENING
        // Yii::$app->db->createCommand('DELETE FROM export_account WHERE username=:username AND period=:periodValue ')
        // ->bindValue(':username', Yii::$app->user->identity->username)
        // ->bindValue(':periodValue', $period)
        // ->execute();
        
        $akun = Yii::$app->db->createCommand('SELECT c.id, c.nama_rekening, sum(e.jumlah) jumlah
        FROM dept_sub_activity_detail e
        LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
        LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
        LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
        LEFT JOIN dept_program g ON g.id=s.dept_program_id
        LEFT JOIN dept_period p ON p.id=a.dept_period_id
        LEFT JOIN account c ON c.id=e.account_id
        WHERE p.unit_id=:unit_id AND p.tahun=:periode AND jumlah > 0 AND g.bok_id=:bok
        group BY c.nama_rekening ORDER BY c.id')
        ->bindValue(':unit_id', Yii::$app->user->identity->unit_id)
        ->bindValue(':periode', $period)
        ->bindValue(':bok', $session['bok'])
        ->queryAll();

        $col = 'C';
        foreach($akun as $acc) {
            $col++;
        }

        $jmlakun = count($akun);

        $inputFileName = Yii::getAlias('@app/views/exportaccount').'/_export_dinas.xlsx';

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
        
        if($session['bok'] !== '6'){
            $activeSheet->setCellValue('A'.'1', 'POA DAK NON FISIK');
        }else{
            $activeSheet->setCellValue('A'.'1', 'RKA APBD DAU');
        }
            $spreadsheet->getActiveSheet()->mergeCells('A1'. ':' .$col.'1');
            $activeSheet->getStyle('A1'. ':' .$col.'1')->getAlignment()->setHorizontal('center'); 
            $activeSheet->getStyle('A1'. ':' .$col.'1')->getAlignment()->setWrapText(true);
        $activeSheet->setCellValue('A'.'2', strtoupper(Yii::$app->user->identity->alias));
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

        // $program = Yii::$app->db->createCommand('SELECT g.id, g.nama_program program FROM dept_program g WHERE g.tahun=:periode AND g.aktif=1 AND g.bok_id=:bok')
        // ->bindValue(':periode', $period)
        // ->bindValue(':bok', $session['bok'])
        // ->queryAll();

        $program = Yii::$app->db->createCommand('SELECT g.id, g.nama_program program, sum(e.jumlah) jumlah
        FROM dept_sub_activity_detail e
        LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
        LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
        LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
        LEFT JOIN dept_program g ON g.id=s.dept_program_id
        LEFT JOIN dept_period p ON p.id=a.dept_period_id
        WHERE p.unit_id=:unit_id AND p.tahun=:periode AND jumlah > 0 AND g.bok_id=:bok
        group BY g.nama_program ORDER BY g.id')
        ->bindValue(':unit_id', Yii::$app->user->identity->unit_id)
        ->bindValue(':periode', $period)
        ->bindValue(':bok', $session['bok'])
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
                $data = Yii::$app->db->createCommand('SELECT s.dept_program_id, IFNULL(sum(e.jumlah),0) jumlah FROM dept_sub_activity_detail e
                LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
                LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
                LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
                LEFT JOIN dept_period p ON p.id=a.dept_period_id
                WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id=:account_id AND s.dept_program_id=:program_id')
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

        // $activeSheet->setCellValue('Q'.$baseRow, $unit->puskesmas. ',                        ' .$period); 
        $spreadsheet->getActiveSheet()->getStyle('Q'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+1; 

        // $spreadsheet->getActiveSheet()->getStyle('Q:S')->getAlignment()->setHorizontal('center');
        $activeSheet->setCellValue('Q'.$baseRow, 'Mengetahui,'); 
        $spreadsheet->getActiveSheet()->getStyle('Q'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+1;
        $activeSheet->setCellValue('Q'.$baseRow, 'Kepala ' .Yii::$app->user->identity->alias); 
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
        ->bindValue(':username', Yii::$app->user->identity->unit_id)
        ->bindValue(':periodValue', $period)
        ->execute();

        $data = Yii::$app->db->createCommand('SELECT g.nama_program, s.nama_kegiatan, v.nama_sub_kegiatan,  IFNULL(a.bentuk_kegiatan, v.nama_sub_kegiatan) bentuk_kegiatan, 
        a.indikator_hasil, a.target_hasil, a.indikator_keluaran, a.target_keluaran, 
        c.nama_rekening, e.rincian, e.vol_1, e.satuan_1, IFNULL(e.vol_2,"") vol_2, IFNULL(e.satuan_2,"") satuan_2, IFNULL(e.vol_3,"") vol_3, IFNULL(e.satuan_3,"") satuan_3, 
        IFNULL(e.vol_4,"") vol_4, IFNULL(e.satuan_4,"") satuan_4,
        vol_1*IFNULL(vol_2,1)*IFNULL(vol_3,1)*IFNULL(vol_4,1) vol, e.unit_cost, e.jumlah,  
        case when e.tw1=1 then "V" ELSE "" END tw1, case when e.tw2=1 then "V" ELSE "" END tw2, 
        case when e.tw3=1 then "V" ELSE "" END tw3, case when e.tw4=1 then "V" ELSE "" END tw4
        FROM dept_sub_activity_detail e
        LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
        LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
        LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
        LEFT JOIN dept_program g ON g.id=s.dept_program_id
        LEFT JOIN dept_period p ON p.id=a.dept_period_id
        LEFT JOIN account c ON c.id=e.account_id
        WHERE p.unit_id=:unit_id AND p.tahun=:periode AND g.bok_id=:bok
        ORDER BY g.id, s.id, v.id, a.id')
        
        ->bindValue(':unit_id', Yii::$app->user->identity->unit_id)
        ->bindValue(':periode', $period)
        ->bindValue(':bok', $session['bok'])
        ->queryAll();

        $rinci = '';
        $komponen = '';
        $kegiatan = '';
        $bentuk = '';
        foreach ($data as $row) {
            $exportprogram =  new Exportprogram();
   
            $exportprogram->nama_program=$row['nama_program'];
            
            if ($komponen !== $row['nama_kegiatan']) {
                $exportprogram->nama_pelayanan=$row['nama_kegiatan']; $komponen = $row['nama_kegiatan'];
            }

            if ($kegiatan !== $row['nama_sub_kegiatan']) {
                $exportprogram->nama_kegiatan=$row['nama_sub_kegiatan']; $kegiatan = $row['nama_sub_kegiatan'];
            }

            if ($bentuk !== $row['bentuk_kegiatan']) {
                $exportprogram->bentuk_kegiatan=$row['bentuk_kegiatan']; $bentuk = $row['bentuk_kegiatan'];
                $exportprogram->sasaran=$row['indikator_hasil'];
                $exportprogram->target=$row['target_hasil'];
                $exportprogram->lokasi=$row['indikator_keluaran'];
                $exportprogram->pelaksana=$row['target_keluaran'];
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
            $exportprogram->jan=$row['tw1'];
            $exportprogram->feb=$row['tw2'];
            $exportprogram->mar=$row['tw3'];
            $exportprogram->apr=$row['tw4'];
            $exportprogram->username=Yii::$app->user->identity->unit_id;
            $exportprogram->period=$period;
            $exportprogram->save();
        }

        //Sheet 2
        $rinci = '';
        $spreadsheet->setActiveSheetIndex(1);
        $activeSheet = $spreadsheet->getActiveSheet();
        if($session['bok'] !== '6'){
            $activeSheet->setCellValue('A'.'1', 'POA DAK NON FISIK');
        }else{
            $activeSheet->setCellValue('A'.'1', 'RKA APBD DAU');
        }
        $activeSheet->setCellValue('A'.'2', strtoupper(Yii::$app->user->identity->alias));
        $activeSheet->setCellValue('A'.'3', 'TAHUN ' .$period);

        // $dataprogram = Yii::$app->db->createCommand('SELECT * FROM program where id<>0')
        $dataprogram = Yii::$app->db->createCommand('SELECT p.* FROM dept_program p
        RIGHT JOIN export_program e ON e.nama_program=p.nama_program
        WHERE tahun=:tahun AND e.username=:username
        GROUP BY p.nama_program
        ORDER BY p.id')
        ->bindValue(':tahun', $period)
        ->bindValue(':username', Yii::$app->user->identity->unit_id)
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
            LEFT JOIN dept_program p ON p.nama_program=e.nama_program AND p.tahun=:periodValue
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
                ->setCellValue('AD'.$tabletitle, '=AD6'); 
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowProgram. ':C' .$baseRowProgram)->applyFromArray($styleArrayBold);
                $spreadsheet->getActiveSheet()->mergeCells('L'.$tabletitle. ':X' .$tabletitle);
                $spreadsheet->getActiveSheet()->getStyle('A'.$tabletitle. ':AD' .$tabletitle)->applyFromArray($styleArrayHeader);
                $activeSheet->getStyle('A'.$tabletitle. ':AD' .$tabletitle)->getAlignment()->setHorizontal('center'); 
                $activeSheet->getStyle('A'.$tabletitle. ':AD' .$tabletitle)->getAlignment()->setWrapText(true);
                $activeSheet->getStyle('A'.$tabletitle. ':AD' .$tabletitle)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F8CBAD');
                }
            }else{
                $activeSheet->setCellValue('A'.$baseRowProgram, $baseRowAwal)
                ->setCellValue('C'.$baseRowProgram, $dataprogram['nama_program']); 

                $activeSheet->getStyle('A6:AD6')->getFill()
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
                                ;  
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AD' .$baseRow)->applyFromArray($styleArray);
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AD' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setVertical('top'); 
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AD' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setWrapText(true);
                                            
                    $baseRow++;
                    $baseRowService = $baseRowService+1;
                    $rowAkhir = $baseRowAwal;
                }
                
                
                $activeSheet->setCellValue('A'.$baseRow, 'Total');
                $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRow. ':Y' .$baseRow);
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AD' .$baseRow)->applyFromArray($styleArray);
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
                    $activeSheet->setCellValue('X'.$baseRowProgram, 'Kepala ' .Yii::$app->user->identity->alias); 
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+4;
                    $activeSheet->setCellValue('X'.$baseRowProgram, $unit->kepala); 
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;
                    $activeSheet->setCellValue('X'.$baseRowProgram, 'NIP. ' .$unit->nip_kepala); 
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                }else{
                    $total = Yii::$app->db->createCommand('SELECT SUM(e.jumlah) jumlah
                    FROM dept_sub_activity_detail e 
                    LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id 
                    LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
                    LEFT JOIN dept_activity s ON s.id=v.dept_activity_id 
                    LEFT JOIN dept_program g ON g.id=s.dept_program_id 
                    LEFT JOIN dept_period p ON p.id=a.dept_period_id
                    LEFT JOIN account c ON c.id=e.account_id WHERE p.unit_id=:unit_id AND p.tahun=:periode AND g.bok_id=:bok')
                    ->bindValue(':unit_id', Yii::$app->user->identity->unit_id)
                    ->bindValue(':periode', $period)
                    ->bindValue(':bok', $session['bok'])
                    ->queryAll();

                    foreach ($total as $rowTotal);

                    $baseRow = $baseRow+1;
                    $activeSheet->setCellValue('A'.$baseRow, 'Total Semua Program');
                    $activeSheet->setCellValue('Z'.$baseRow, $rowTotal['jumlah']);
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRow. ':Y' .$baseRow);
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AD' .$baseRow)->applyFromArray($styleArray);
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

                    $activeSheet->setCellValue('A'.$baseRowProgram, 'Kepala ' .Yii::$app->user->identity->alias); 
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    $activeSheet->setCellValue('F'.$baseRowProgram, 'Sekretaris ' .$dataProfile->nama. ' '.$dataProfile->kota_kab); 
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    // $activeSheet->setCellValue('W'.$baseRowProgram, 'PPTK BOK'); 
                    // $spreadsheet->getActiveSheet()->mergeCells('W'.$baseRowProgram. ':Y' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+4;

                    $activeSheet->setCellValue('A'.$baseRowProgram, $unit->kepala); 
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    $activeSheet->setCellValue('F'.$baseRowProgram, $dataProfile->sekretaris); 
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    // $activeSheet->setCellValue('W'.$baseRowProgram, $unit->petugas); 
                    // $spreadsheet->getActiveSheet()->mergeCells('W'.$baseRowProgram. ':Y' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;

                    // $activeSheet->setCellValue('A'.$baseRowProgram, $unit->kepala); 
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    $activeSheet->setCellValue('F'.$baseRowProgram, $dataProfile->jabatan_sekretaris); 
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    // $activeSheet->setCellValue('W'.$baseRowProgram, $unit->jabatan_petugas); 
                    // $spreadsheet->getActiveSheet()->mergeCells('W'.$baseRowProgram. ':Y' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;

                    $activeSheet->setCellValue('A'.$baseRowProgram, 'NIP. ' .$unit->nip_kepala);
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    $activeSheet->setCellValue('F'.$baseRowProgram, 'NIP. ' .$dataProfile->nip_sekretaris);
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    // $activeSheet->setCellValue('W'.$baseRowProgram, 'NIP. ' .$unit->nip_petugas);
                    // $spreadsheet->getActiveSheet()->mergeCells('W'.$baseRowProgram. ':Y' .$baseRowProgram);

                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AD' .$baseRow)->applyFromArray($styleArrayBold);
                }
                $baseRowProgram=$baseRowProgram+20;

                $baseRowProgram++;   
            }else{
                // $baseRowProgram = $baseRowProgram+2;    
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

        $spreadsheet->setActiveSheetIndex(0);

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

        if($session['bok'] !== '6'){
            $filename = 'export_poa_'.$period. '_'.Yii::$app->user->identity->username.'.xlsx';
        }else{
            $filename = 'export_rka_apbd_'.$period. '_'.Yii::$app->user->identity->username.'.xlsx';
        }

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
        // $POST_VARIABLE=Yii::$app->request->post('Period');
        // $period = $POST_VARIABLE['tahun'];

        $session = Yii::$app->session;
        $period = $session['deptPeriodValue'];

        $unitnya = User::find()
        ->where(['unit_id' => $unit_id])
        ->one();

        //QUERY PER REKENING
        // Yii::$app->db->createCommand()->truncateTable('export_account')->execute();
        // Yii::$app->db->createCommand('DELETE FROM export_account WHERE username=:username AND period=:periodValue ')
        // ->bindValue(':username', $unitnya->username)
        // ->bindValue(':periodValue', $period)
        // ->execute();
        
        $akun = Yii::$app->db->createCommand('SELECT c.id, c.nama_rekening, sum(e.jumlah) jumlah
        FROM dept_sub_activity_detail e
        LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
        LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
        LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
        LEFT JOIN dept_program g ON g.id=s.dept_program_id
        LEFT JOIN dept_period p ON p.id=a.dept_period_id
        LEFT JOIN account c ON c.id=e.account_id
        WHERE p.unit_id=:unit_id AND p.tahun=:periode AND jumlah > 0 AND g.bok_id=:bok
        group BY c.nama_rekening ORDER BY c.id')
        ->bindValue(':unit_id', $unit_id)
        ->bindValue(':periode', $period)
        ->bindValue(':bok', $session['bok'])
        ->queryAll();

        $col = 'C';
        foreach($akun as $acc) {
            $col++;
        }

        $jmlakun = count($akun);

        $inputFileName = Yii::getAlias('@app/views/exportaccount').'/_export_dinas.xlsx';

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
        
        $activeSheet->setCellValue('A'.'1', 'POA DAK NON FISIK');
            $spreadsheet->getActiveSheet()->mergeCells('A1'. ':' .$col.'1');
            $activeSheet->getStyle('A1'. ':' .$col.'1')->getAlignment()->setHorizontal('center'); 
            $activeSheet->getStyle('A1'. ':' .$col.'1')->getAlignment()->setWrapText(true);
        $activeSheet->setCellValue('A'.'2', strtoupper($unitnya->alias));
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

        // $program = Yii::$app->db->createCommand('SELECT g.id, g.nama_program program FROM dept_program g WHERE g.tahun=:periode AND g.aktif=1 AND g.bok_id=:bok')
        // ->bindValue(':periode', $period)
        // ->bindValue(':bok', $session['bok'])
        // ->queryAll();

        $program = Yii::$app->db->createCommand('SELECT g.id, g.nama_program program, sum(e.jumlah) jumlah
        FROM dept_sub_activity_detail e
        LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
        LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
        LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
        LEFT JOIN dept_program g ON g.id=s.dept_program_id
        LEFT JOIN dept_period p ON p.id=a.dept_period_id
        WHERE p.unit_id=:unit_id AND p.tahun=:periode AND jumlah > 0 AND g.bok_id=:bok
        group BY g.nama_program ORDER BY g.id')
        ->bindValue(':unit_id', $unit_id)
        ->bindValue(':periode', $period)
        ->bindValue(':bok', $session['bok'])
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
                $data = Yii::$app->db->createCommand('SELECT s.dept_program_id, IFNULL(sum(e.jumlah),0) jumlah FROM dept_sub_activity_detail e
                LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
                LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
                LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
                LEFT JOIN dept_period p ON p.id=a.dept_period_id
                WHERE p.unit_id=:unit_id AND p.tahun=:periode AND e.account_id=:account_id AND s.dept_program_id=:program_id')
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

        $unit = Unit::findOne(Yii::$app->user->identity->unit_id);
        $baseRow=$baseRow+3;

        // $activeSheet->setCellValue('Q'.$baseRow, $unit->puskesmas. ',                        ' .$period); 
        $spreadsheet->getActiveSheet()->getStyle('Q'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+1; 

        // $spreadsheet->getActiveSheet()->getStyle('Q:S')->getAlignment()->setHorizontal('center');
        $activeSheet->setCellValue('Q'.$baseRow, 'Mengetahui,'); 
        $spreadsheet->getActiveSheet()->getStyle('Q'.$baseRow. ':S' .$baseRow)->getFont()->setBold( true );
        $baseRow=$baseRow+1;
        $activeSheet->setCellValue('Q'.$baseRow, 'Kepala ' .$unitnya->alias); 
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

        $data = Yii::$app->db->createCommand('SELECT g.nama_program, s.nama_kegiatan, v.nama_sub_kegiatan,  IFNULL(a.bentuk_kegiatan, v.nama_sub_kegiatan) bentuk_kegiatan, 
        a.indikator_hasil, a.target_hasil, a.indikator_keluaran, a.target_keluaran, 
        c.nama_rekening, e.rincian, e.vol_1, e.satuan_1, IFNULL(e.vol_2,"") vol_2, IFNULL(e.satuan_2,"") satuan_2, IFNULL(e.vol_3,"") vol_3, IFNULL(e.satuan_3,"") satuan_3, 
        IFNULL(e.vol_4,"") vol_4, IFNULL(e.satuan_4,"") satuan_4,
        vol_1*IFNULL(vol_2,1)*IFNULL(vol_3,1)*IFNULL(vol_4,1) vol, e.unit_cost, e.jumlah,    
        case when e.tw1=1 then "V" ELSE "" END tw1, case when e.tw2=1 then "V" ELSE "" END tw2, 
        case when e.tw3=1 then "V" ELSE "" END tw3, case when e.tw4=1 then "V" ELSE "" END tw4
        FROM dept_sub_activity_detail e
        LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
        LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
        LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
        LEFT JOIN dept_program g ON g.id=s.dept_program_id
        LEFT JOIN dept_period p ON p.id=a.dept_period_id
        LEFT JOIN account c ON c.id=e.account_id
        WHERE p.unit_id=:unit_id AND p.tahun=:periode AND g.bok_id=:bok
        ORDER BY g.id, s.id, v.id, a.id')
        
        ->bindValue(':unit_id', $unit_id)
        ->bindValue(':periode', $period)
        ->bindValue(':bok', $session['bok'])
        ->queryAll();

        $rinci = '';
        $komponen = '';
        $kegiatan = '';
        $bentuk = '';
        foreach ($data as $row) {
            $exportprogram =  new Exportprogram();
   
            $exportprogram->nama_program=$row['nama_program'];
            
            if ($komponen !== $row['nama_kegiatan']) {
                $exportprogram->nama_pelayanan=$row['nama_kegiatan']; $komponen = $row['nama_kegiatan'];
            }

            if ($kegiatan !== $row['nama_sub_kegiatan']) {
                $exportprogram->nama_kegiatan=$row['nama_sub_kegiatan']; $kegiatan = $row['nama_sub_kegiatan'];
            }

            if ($bentuk !== $row['bentuk_kegiatan']) {
                $exportprogram->bentuk_kegiatan=$row['bentuk_kegiatan']; $bentuk = $row['bentuk_kegiatan'];
                $exportprogram->sasaran=$row['indikator_hasil'];
                $exportprogram->target=$row['target_hasil'];
                $exportprogram->lokasi=$row['indikator_keluaran'];
                $exportprogram->pelaksana=$row['target_keluaran'];
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
            $exportprogram->jan=$row['tw1'];
            $exportprogram->feb=$row['tw2'];
            $exportprogram->mar=$row['tw3'];
            $exportprogram->apr=$row['tw4'];
            $exportprogram->username=$unitnya->username;
            $exportprogram->period=$period;
            $exportprogram->save();
        }

        //Sheet 2
        $rinci = '';
        $spreadsheet->setActiveSheetIndex(1);
        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setCellValue('A'.'1', 'POA DAK NON FISIK ');
        $activeSheet->setCellValue('A'.'2', strtoupper($unitnya->alias));
        $activeSheet->setCellValue('A'.'3', 'TAHUN ' .$period);

        // $dataprogram = Yii::$app->db->createCommand('SELECT * FROM program where id<>0')
        $dataprogram = Yii::$app->db->createCommand('SELECT p.* FROM dept_program p
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
            
            $dataExcel = Yii::$app->db->createCommand('SELECT e.*, p.id, CASE WHEN v.modul="P" THEN NULL ELSE "DRAFT" END status FROM export_program e
            LEFT JOIN dept_program p ON p.nama_program=e.nama_program AND p.tahun=:periodValue
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
                ->setCellValue('AD'.$tabletitle, '=AD6'); 
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRowProgram. ':C' .$baseRowProgram)->applyFromArray($styleArrayBold);
                $spreadsheet->getActiveSheet()->mergeCells('L'.$tabletitle. ':X' .$tabletitle);
                $spreadsheet->getActiveSheet()->getStyle('A'.$tabletitle. ':AD' .$tabletitle)->applyFromArray($styleArrayHeader);
                $activeSheet->getStyle('A'.$tabletitle. ':AD' .$tabletitle)->getAlignment()->setHorizontal('center'); 
                $activeSheet->getStyle('A'.$tabletitle. ':AD' .$tabletitle)->getAlignment()->setWrapText(true);
                $activeSheet->getStyle('A'.$tabletitle. ':AD' .$tabletitle)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('F8CBAD');
                }
            }else{
                $activeSheet->setCellValue('A'.$baseRowProgram, $baseRowAwal)
                ->setCellValue('C'.$baseRowProgram, $dataprogram['nama_program']); 

                $activeSheet->getStyle('A6:AD6')->getFill()
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
                                ;  
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AD' .$baseRow)->applyFromArray($styleArray);
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AD' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setVertical('top'); 
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AD' .$baseRow)->applyFromArray($styleArray)->getAlignment()->setWrapText(true);
                                            
                    $baseRow++;
                    $baseRowService = $baseRowService+1;
                    $rowAkhir = $baseRowAwal;
                }
                
                
                $activeSheet->setCellValue('A'.$baseRow, 'Total');
                $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRow. ':Y' .$baseRow);
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AD' .$baseRow)->applyFromArray($styleArray);
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
                    $activeSheet->setCellValue('X'.$baseRowProgram, 'Kepala ' .$unitnya->alias); 
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+4;
                    $activeSheet->setCellValue('X'.$baseRowProgram, $unit->kepala); 
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;
                    $activeSheet->setCellValue('X'.$baseRowProgram, 'NIP. ' .$unit->nip_kepala); 
                    $spreadsheet->getActiveSheet()->mergeCells('X'.$baseRowProgram. ':Z' .$baseRowProgram);
                }else{
                    $total = Yii::$app->db->createCommand('SELECT SUM(e.jumlah) jumlah
                    FROM dept_sub_activity_detail e 
                    LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id 
                    LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
                    LEFT JOIN dept_activity s ON s.id=v.dept_activity_id 
                    LEFT JOIN dept_program g ON g.id=s.dept_program_id 
                    LEFT JOIN dept_period p ON p.id=a.dept_period_id
                    LEFT JOIN account c ON c.id=e.account_id WHERE p.unit_id=:unit_id AND p.tahun=:periode AND g.bok_id=:bok')
                    ->bindValue(':unit_id', $unitnya->unit_id)
                    ->bindValue(':periode', $period)
                    ->bindValue(':bok', $session['bok'])
                    ->queryAll();

                    foreach ($total as $rowTotal);

                    $baseRow = $baseRow+1;
                    $activeSheet->setCellValue('A'.$baseRow, 'Total Semua Program');
                    $activeSheet->setCellValue('Z'.$baseRow, $rowTotal['jumlah']);
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRow. ':Y' .$baseRow);
                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AD' .$baseRow)->applyFromArray($styleArray);
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

                    $activeSheet->setCellValue('A'.$baseRowProgram, 'Kepala ' .$unitnya->alias); 
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    $activeSheet->setCellValue('F'.$baseRowProgram, 'Sekretaris ' .$dataProfile->nama. ' '.$dataProfile->kota_kab); 
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    // $activeSheet->setCellValue('W'.$baseRowProgram, 'PPTK BOK'); 
                    // $spreadsheet->getActiveSheet()->mergeCells('W'.$baseRowProgram. ':Y' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+4;

                    $activeSheet->setCellValue('A'.$baseRowProgram, $unit->kepala); 
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    $activeSheet->setCellValue('F'.$baseRowProgram, $dataProfile->sekretaris); 
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    // $activeSheet->setCellValue('W'.$baseRowProgram, $unit->petugas); 
                    // $spreadsheet->getActiveSheet()->mergeCells('W'.$baseRowProgram. ':Y' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;

                    // $activeSheet->setCellValue('A'.$baseRowProgram, $unit->kepala); 
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    $activeSheet->setCellValue('F'.$baseRowProgram, $dataProfile->jabatan_sekretaris); 
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    // $activeSheet->setCellValue('W'.$baseRowProgram, $unit->jabatan_petugas); 
                    // $spreadsheet->getActiveSheet()->mergeCells('W'.$baseRowProgram. ':Y' .$baseRowProgram);
                    $baseRowProgram=$baseRowProgram+1;

                    $activeSheet->setCellValue('A'.$baseRowProgram, 'NIP. ' .$unit->nip_kepala);
                    $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRowProgram. ':D' .$baseRowProgram);
                    $activeSheet->setCellValue('F'.$baseRowProgram, 'NIP. ' .$dataProfile->nip_sekretaris);
                    $spreadsheet->getActiveSheet()->mergeCells('F'.$baseRowProgram. ':K' .$baseRowProgram);
                    // $activeSheet->setCellValue('W'.$baseRowProgram, 'NIP. ' .$unit->nip_petugas);
                    // $spreadsheet->getActiveSheet()->mergeCells('W'.$baseRowProgram. ':Y' .$baseRowProgram);

                    $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':AC' .$baseRow)->applyFromArray($styleArrayBold);
                }
                $baseRowProgram=$baseRowProgram+20;

                $baseRowProgram++;   
            }else{
                // $baseRowProgram = $baseRowProgram+2;    
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

        $spreadsheet->setActiveSheetIndex(0);

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

    public function actionGetYear()
    {
        $session = Yii::$app->session;
        $session['deptPeriodValue'] =$_COOKIE['tahun'];
    }

    public function actionLevel($lvl)
    {
        $session = Yii::$app->session;
        $session['lvl'] = $lvl;

        return $this->redirect(['deptperiod/list', 'period' => date('Y')]);
    }

    public function actionList($period)
    {
        $count = 0;
        $session = Yii::$app->session;
        $session['deptPeriodValue'] = $period;

        $data = Yii::$app->db->createCommand('SELECT p.unit_id, u.puskesmas, IFNULL(p.pagu,0) pagu, sum(IFNULL(e.jumlah,0)) jumlah, SUBSTRING(IFNULL(cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as char),0),1,5) prosentase,
        CASE 
        WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) < 33.33 THEN "progress-bar-success"
        WHEN cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) >= 33.33 AND cast(sum(IFNULL(e.jumlah,0))/IFNULL(p.pagu,0)*100 as decimal(10,2)) <=66.66 THEN "progress-bar-warning"
        ELSE "progress-bar-danger"
        END AS bar_color
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
        CASE WHEN t.modul_4 = "L" THEN "glyphicon glyphicon-lock" ELSE "glyphicon glyphicon-stats" END AS status_real_icon,

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
        CASE WHEN t.tw_4 = "4" THEN "Buka" ELSE "Kunci" END AS label_real_icon_tw4
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
        LEFT JOIN `dept_status` t ON t.unit_id=u.id AND t.tahun='.$period.'
        WHERE u.id != "DINKES" AND mid(u.id,1,1) != "1"
        ORDER BY u.puskesmas';

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

    public function actionUnlockallreal($id,$tw)
    {
        $session = Yii::$app->session;
        $data = Yii::$app->db->createCommand('select id unit_id from unit where mid(id,1,1) <> "1" AND id <> "DINKES"')
        ->queryAll();

        if(!empty($data))
        {
            foreach($data as $row)
            {
                if(substr($id,-1)==='L'){
                    $status = Deptstatus::find()->where([
                        'tahun' => $session['deptPeriodValue'],
                        'unit_id' => $row['unit_id'],
                    ])->count();

                    if($status=="0") {
                        $model = new Deptstatus(); //$params
                        $model->modul_4 = null;
                        $model->tahun = $session['deptPeriodValue'];
                        $model->unit_id = $row['unit_id'];
                        if($tw == '1'){$model->tw_1=null;}
                        if($tw == '2'){$model->tw_2=null;}
                        if($tw == '3'){$model->tw_3=null;}
                        if($tw == '4'){$model->tw_4=null;}
                        $model->save();
                    }else{
                        $status = Deptstatus::find()->where([
                            'tahun' => $session['deptPeriodValue'],
                            'unit_id' => $row['unit_id'],
                        ])->one();
        
                        $model = Deptstatus::findOne($status['id']);
                        $model->modul_4 = null;
                        $model->tahun = $session['deptPeriodValue'];
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
        return $this->redirect(array('list', 'period'=>$session['deptPeriodValue']));    
    }

    public function actionLockallreal($id,$tw)
    {
        $session = Yii::$app->session;
        $data = Yii::$app->db->createCommand('select id unit_id from unit where mid(id,1,1) <> "1" AND id <> "DINKES"')
        ->queryAll();

        if(!empty($data))
        {
            foreach($data as $row)
            {
                if(substr($id,-1)==='L'){
                    $status = Deptstatus::find()->where([
                        'tahun' => $session['deptPeriodValue'],
                        'unit_id' => $row['unit_id'],
                    ])->count();

                    if($status=="0") {
                        $model = new Deptstatus(); //$params
                        $model->modul_4 = substr($id,-1);
                        $model->tahun = $session['deptPeriodValue'];
                        $model->unit_id = $row['unit_id'];
                        if($tw == '1'){$model->tw_1=$tw;}
                        if($tw == '2'){$model->tw_2=$tw;}
                        if($tw == '3'){$model->tw_3=$tw;}
                        if($tw == '4'){$model->tw_4=$tw;}
                        $model->save();
                    }else{
                        $status = Deptstatus::find()->where([
                            'tahun' => $session['deptPeriodValue'],
                            'unit_id' => $row['unit_id'],
                        ])->one();
        
                        $model = Deptstatus::findOne($status['id']);
                        $model->modul_4 = substr($id,-1);
                        $model->tahun = $session['deptPeriodValue'];
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
        return $this->redirect(array('list', 'period'=>$session['deptPeriodValue']));    
    }

    public function actionLockreal($id,$tw)
    {
        $session = Yii::$app->session;

        $status = Deptstatus::find()->where([
            'tahun' => $session['deptPeriodValue'],
            'unit_id' => substr($id,0,-1),
        ])->count();

        if($status=="0") {
            $model = new Deptstatus(); //$params
            $model->modul_4 = substr($id,-1);
            $model->tahun = $session['deptPeriodValue'];
            $model->unit_id = substr($id,0,-1);
            if($tw == '1'){$model->tw_1=$tw;}
            if($tw == '2'){$model->tw_2=$tw;}
            if($tw == '3'){$model->tw_3=$tw;}
            if($tw == '4'){$model->tw_4=$tw;}
            $model->save();
        }else{
            $status = Deptstatus::find()->where([
                'tahun' => $session['deptPeriodValue'],
                'unit_id' => substr($id,0,-1),
            ])->one();

            $model = Deptstatus::findOne($status['id']);

            if($model->modul_4 === substr($id,-1)){
                $model->modul_4 = null;
                $model->tahun = $session['deptPeriodValue'];
                $model->unit_id = substr($id,0,-1);
                if($tw == '1'){$model->tw_1=null;}
                if($tw == '2'){$model->tw_2=null;}
                if($tw == '3'){$model->tw_3=null;}
                if($tw == '4'){$model->tw_4=null;}
                $model->save();
            }else{
                $model->modul_4 = substr($id,-1);
                $model->tahun = $session['deptPeriodValue'];
                $model->unit_id = substr($id,0,-1);
                if($tw == '1'){$model->tw_1=$tw;}
                if($tw == '2'){$model->tw_2=$tw;}
                if($tw == '3'){$model->tw_3=$tw;}
                if($tw == '4'){$model->tw_4=$tw;}
                $model->save();
            }
        }

        return $this->redirect(array('list', 'period'=>$session['deptPeriodValue']));    
    }

    public function actionRekapKomponenDetail($id)
    {
        $session = Yii::$app->session;
        $session['komponen'] = $id;
        
        $query = 'SELECT u.id, g.nama_program, s.nama_kegiatan, v.id id_sub, v.nama_sub_kegiatan, IFNULL(a.bentuk_kegiatan, v.nama_sub_kegiatan) bentuk_kegiatan, 
        a.indikator_hasil, a.target_hasil, a.indikator_keluaran, a.target_keluaran, c.kode,
        c.nama_rekening, e.rincian, e.vol_1, e.satuan_1, 
        IFNULL(e.vol_2,"") vol_2, IFNULL(e.satuan_2,"") satuan_2, 
        IFNULL(e.vol_3,"") vol_3, IFNULL(e.satuan_3,"") satuan_3,
        IFNULL(e.vol_4,"") vol_4, IFNULL(e.satuan_4,"") satuan_4,
        vol_1*IFNULL(vol_2,1)*IFNULL(vol_3,1)*IFNULL(vol_4,1) vol, 
        e.unit_cost, e.jumlah
        FROM dept_sub_activity_detail e
        LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
        LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
        LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
        LEFT JOIN dept_program g ON g.id=s.dept_program_id
        LEFT JOIN dept_period p ON p.id=a.dept_period_id
        LEFT JOIN account c ON c.id=e.account_id
        LEFT JOIN unit u ON u.id=p.unit_id
        WHERE p.tahun="'.$session['deptPeriodValue'].'" 
        AND g.id = "'.$id.'"
        ORDER BY g.id, s.id, v.id, a.id';

        $session['qrydetail'] = $query;

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model = $dataProvider->getModels();

        return $this->render('detail_komponen', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'namaUnit' => Yii::$app->user->identity->alias
        ]);

        // return $session['qrydetail'];
    }

    public function actionExportxlskomponen()
    {
        $session = Yii::$app->session;
        $period = $session['deptPeriodValue'];

        Yii::$app->db->createCommand('DELETE FROM export_program WHERE username=:username AND period=:periodValue ')
        ->bindValue(':username', Yii::$app->user->identity->unit_id)
        ->bindValue(':periodValue', $period)
        ->execute();

        $data = Yii::$app->db->createCommand($session['qrydetail'])
        ->queryAll();

        $rinci = '';
        $komponen = '';
        $kegiatan = '';
        $bentuk = '';
        foreach ($data as $row) {
            $exportprogram =  new Exportprogram();
   
            $exportprogram->nama_program=$row['nama_program'];
            
            if ($komponen !== $row['nama_kegiatan']) {
                $exportprogram->nama_pelayanan=$row['nama_kegiatan']; $komponen = $row['nama_kegiatan'];
            }

            if ($kegiatan !== $row['nama_sub_kegiatan']) {
                $exportprogram->nama_kegiatan=$row['nama_sub_kegiatan']; 
                
                $jumlahSub = Yii::$app->db->createCommand('SELECT v.id, IFNULL(SUM(e.jumlah),0) jml_sub FROM dept_sub_activity_detail e
                LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
                LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
                LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
                LEFT JOIN dept_period p ON p.id=a.dept_period_id
                WHERE v.id=:id')
                ->bindValue(':id', $row['id_sub'])
                ->queryAll();

                foreach($jumlahSub as $jmlsub);
                $exportprogram->jumlah_awal = $jmlsub['jml_sub'];

                $kegiatan = $row['nama_sub_kegiatan'];
            }

            if ($bentuk !== $row['bentuk_kegiatan']) {
                $exportprogram->bentuk_kegiatan=$row['bentuk_kegiatan']; $bentuk = $row['bentuk_kegiatan'];
                $exportprogram->sasaran=$row['indikator_hasil'];
                $exportprogram->target=$row['target_hasil'];
                $exportprogram->lokasi=$row['indikator_keluaran'];
                $exportprogram->pelaksana=$row['target_keluaran'];
            }

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

        $filename = 'export_rekap_komponen_'.$period.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function actionRekapDpaDetail($id)
    {
        $session = Yii::$app->session;
        $session['dpa'] = $id;
        
        $query = 'SELECT u.id, g.nama_program, s.nama_kegiatan, v.id id_sub, v.nama_sub_kegiatan, IFNULL(a.bentuk_kegiatan, v.nama_sub_kegiatan) bentuk_kegiatan, 
        a.indikator_hasil, a.target_hasil, a.indikator_keluaran, a.target_keluaran, a.dpa_id, c.kode,
        c.nama_rekening, e.rincian, e.vol_1, e.satuan_1, 
        IFNULL(e.vol_2,"") vol_2, IFNULL(e.satuan_2,"") satuan_2, 
        IFNULL(e.vol_3,"") vol_3, IFNULL(e.satuan_3,"") satuan_3,
        IFNULL(e.vol_4,"") vol_4, IFNULL(e.satuan_4,"") satuan_4,
        vol_1*IFNULL(vol_2,1)*IFNULL(vol_3,1)*IFNULL(vol_4,1) vol, 
        e.unit_cost, e.jumlah
        FROM dept_sub_activity_detail e
        LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
        LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
        LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
        LEFT JOIN dept_program g ON g.id=s.dept_program_id
        LEFT JOIN dept_period p ON p.id=a.dept_period_id
        LEFT JOIN account c ON c.id=e.account_id
        LEFT JOIN unit u ON u.id=p.unit_id
        WHERE p.tahun="'.$session['deptPeriodValue'].'" 
        AND a.dpa_id = "'.$id.'"
        ORDER BY g.id, s.id, v.id, a.id';

        $session['qrydetail'] = $query;

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model = $dataProvider->getModels();

        return $this->render('detail_dpa', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'namaUnit' => Yii::$app->user->identity->alias
        ]);

        // return $session['qrydetail'];
    }

    public function actionExportxlsdpa()
    {
        $session = Yii::$app->session;
        $period = $session['deptPeriodValue'];

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
            
            if ($komponen !== $row['nama_kegiatan']) {
                $exportprogram->nama_pelayanan=$row['nama_kegiatan']; $komponen = $row['nama_kegiatan'];
            }

            if ($kegiatan !== $row['nama_sub_kegiatan']) {
                $exportprogram->nama_kegiatan=$row['nama_sub_kegiatan']; 
                
                $jumlahSub = Yii::$app->db->createCommand('SELECT v.id, IFNULL(SUM(e.jumlah),0) jml_sub FROM dept_sub_activity_detail e
                LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
                LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
                LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
                LEFT JOIN dept_period p ON p.id=a.dept_period_id
                WHERE v.id=:id AND a.dpa_id=:dpaId')
                ->bindValue(':id', $row['id_sub'])
                ->bindValue(':dpaId', $row['dpa_id'])
                ->queryAll();

                foreach($jumlahSub as $jmlsub);
                $exportprogram->jumlah_awal = $jmlsub['jml_sub'];

                $kegiatan = $row['nama_sub_kegiatan'];
            }

            if ($bentuk !== $row['bentuk_kegiatan']) {
                $exportprogram->bentuk_kegiatan=$row['bentuk_kegiatan']; $bentuk = $row['bentuk_kegiatan'];
                $exportprogram->sasaran=$row['indikator_hasil'];
                $exportprogram->target=$row['target_hasil'];
                $exportprogram->lokasi=$row['indikator_keluaran'];
                $exportprogram->pelaksana=$row['target_keluaran'];
            }

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

        $filename = 'export_rekap_dpa_'.$period.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function actionLock($id)
    {
        $session = Yii::$app->session;

        if(substr($id,-1)==='P'){
            $status = Deptstatus::find()->where([
                'tahun' => $session['deptPeriodValue'],
                'unit_id' => substr($id, 0, -1),
            ])->count();

            if($status=="0") {
                $model = new Deptstatus(); //$params
                $model->modul_1 = substr($id,-1);
                $model->tahun = $session['deptPeriodValue'];
                $model->unit_id = substr($id, 0, -1);
                $model->save();
            }else{
                $status = Deptstatus::find()->where([
                    'tahun' => $session['deptPeriodValue'],
                    'unit_id' => substr($id, 0, -1),
                ])->one();

                $model = Deptstatus::findOne($status['id']);

                if($model->modul_1 === substr($id,-1)){
                    $model->modul_1 = null;
                    $model->tahun = $session['deptPeriodValue'];
                    $model->unit_id = substr($id, 0, -1);
                    $model->save();
                }else{
                    $model->modul_1 = substr($id,-1);
                    $model->tahun = $session['deptPeriodValue'];
                    $model->unit_id = substr($id, 0, -1);
                    $model->save();
                }
            }
        }

        if(substr($id,-1)==='G'){
            $status = Deptstatus::find()->where([
                'tahun' => $session['deptPeriodValue'],
                'unit_id' => substr($id, 0, -1),
            ])->count();

            if($status=="0") {
                $model = new Deptstatus($params);
                $model->modul_2 = substr($id,-1);
                $model->tahun = $session['deptPeriodValue'];
                $model->unit_id = substr($id, 0, -1);
                $model->save();
            }else{
                $status = Deptstatus::find()->where([
                    'tahun' => $session['deptPeriodValue'],
                    'unit_id' => substr($id, 0, -1),
                ])->one();

                $model = Deptstatus::findOne($status['id']);

                if($model->modul_2 === substr($id,-1)){
                    $model->modul_2 = null;
                    $model->tahun = $session['deptPeriodValue'];
                    $model->unit_id = substr($id, 0, -1);
                    $model->save();
                }else{
                    $model->modul_2 = substr($id,-1);
                    $model->tahun = $session['deptPeriodValue'];
                    $model->unit_id = substr($id, 0, -1);
                    $model->save();
                }
            }
        }

        if(substr($id,-1)==='R'){
            $status = Deptstatus::find()->where([
                'tahun' => $session['deptPeriodValue'],
                'unit_id' => substr($id,0,11),
            ])->count();

            if($status=="0") {
                $model = new Deptstatus($params);
                $model->modul_3 = substr($id,-1);
                $model->tahun = $session['deptPeriodValue'];
                $model->unit_id = substr($id, 0, -1);
                $model->save();
            }else{
                $status = Deptstatus::find()->where([
                    'tahun' => $session['deptPeriodValue'],
                    'unit_id' => substr($id, 0, -1),
                ])->one();

                $model = Deptstatus::findOne($status['id']);

                if($model->modul_3 === substr($id,-1)){
                    $model->modul_3 = null;
                    $model->tahun = $session['deptPeriodValue'];
                    $model->unit_id = substr($id, 0, -1);
                    $model->save();
                }else{
                    $model->modul_3 = substr($id,-1);
                    $model->tahun = $session['deptPeriodValue'];
                    $model->unit_id = substr($id, 0, -1);
                    $model->save();
                }
            }
        }

        if(substr($id,-1)==='L'){
            $status = Deptstatus::find()->where([
                'tahun' => $session['deptPeriodValue'],
                'unit_id' => substr($id, 0, -1),
            ])->count();

            if($status=="0") {
                $model = new Deptstatus($params);
                $model->modul_4 = substr($id,-1);
                $model->tahun = $session['deptPeriodValue'];
                $model->unit_id = substr($id, 0, -1);
                $model->save();
            }else{
                $status = Deptstatus::find()->where([
                    'tahun' => $session['deptPeriodValue'],
                    'unit_id' => substr($id, 0, -1),
                ])->one();

                $model = Deptstatus::findOne($status['id']);

                if($model->modul_4 === substr($id,-1)){
                    $model->modul_4 = null;
                    $model->tahun = $session['deptPeriodValue'];
                    $model->unit_id = substr($id, 0, -1);
                    $model->save();
                }else{
                    $model->modul_4 = substr($id,-1);
                    $model->tahun = $session['deptPeriodValue'];
                    $model->unit_id = substr($id, 0, -1);
                    $model->save();
                }
            }
        }

        return $this->redirect(array('list', 'period'=>$session['deptPeriodValue']));    
    }

    public function actionLockall($id)
    {
        $session = Yii::$app->session;

        $data = Yii::$app->db->createCommand('select id unit_id from unit where mid(id,1,1) <> "1" AND id <> "DINKES"')
        ->queryAll();

        if(!empty($data))
        {
            foreach($data as $row)
            {
                if(substr($id,-1)==='P'){
                    $status = Deptstatus::find()->where([
                        'tahun' => $session['deptPeriodValue'],
                        'unit_id' => $row['unit_id'],
                    ])->count();

                    if($status=="0") {
                        $model = new Deptstatus(); //$params
                        $model->modul_1 = substr($id,-1);
                        $model->tahun = $session['deptPeriodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }else{
                        $status = Deptstatus::find()->where([
                            'tahun' => $session['deptPeriodValue'],
                            'unit_id' => $row['unit_id'],
                        ])->one();
        
                        $model = Deptstatus::findOne($status['id']);
                        $model->modul_1 = substr($id,-1);
                        $model->tahun = $session['deptPeriodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }
                }

                if(substr($id,-1)==='G'){
                    $status = Deptstatus::find()->where([
                        'tahun' => $session['deptPeriodValue'],
                        'unit_id' => $row['unit_id'],
                    ])->count();

                    if($status=="0") {
                        $model = new Deptstatus($params);
                        $model->modul_2 = substr($id,-1);
                        $model->tahun = $session['deptPeriodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }else{
                        $status = Deptstatus::find()->where([
                            'tahun' => $session['deptPeriodValue'],
                            'unit_id' => $row['unit_id'],
                        ])->one();
        
                        $model = Deptstatus::findOne($status['id']);
                        $model->modul_2 = substr($id,-1);
                        $model->tahun = $session['deptPeriodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }
                }

                if(substr($id,-1)==='R'){
                    $status = Deptstatus::find()->where([
                        'tahun' => $session['deptPeriodValue'],
                        'unit_id' => $row['unit_id'],
                    ])->count();

                    if($status=="0") {
                        $model = new Deptstatus($params);
                        $model->modul_3 = substr($id,-1);
                        $model->tahun = $session['deptPeriodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }else{
                        $status = Deptstatus::find()->where([
                            'tahun' => $session['deptPeriodValue'],
                            'unit_id' => $row['unit_id'],
                        ])->one();
        
                        $model = Deptstatus::findOne($status['id']);
                        $model->modul_3 = substr($id,-1);
                        $model->tahun = $session['deptPeriodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }
                }

                if(substr($id,-1)==='L'){
                    $status = Deptstatus::find()->where([
                        'tahun' => $session['deptPeriodValue'],
                        'unit_id' => $row['unit_id'],
                    ])->count();

                    if($status=="0") {
                        $model = new Deptstatus($params);
                        $model->modul_4 = substr($id,-1);
                        $model->tahun = $session['deptPeriodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }else{
                        $status = Deptstatus::find()->where([
                            'tahun' => $session['deptPeriodValue'],
                            'unit_id' => $row['unit_id'],
                        ])->one();
        
                        $model = Deptstatus::findOne($status['id']);
                        $model->modul_4 = substr($id,-1);
                        $model->tahun = $session['deptPeriodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }
                }
            }
        }
        return $this->redirect(array('list', 'period'=>$session['deptPeriodValue']));  
    }

    public function actionUnlockall($id)
    {
        $session = Yii::$app->session;
        $data = Yii::$app->db->createCommand('select id unit_id from unit where mid(id,1,1) <> "1" AND id <> "DINKES"')
        ->queryAll();

        if(!empty($data))
        {
            foreach($data as $row)
            {
                if(substr($id,-1)==='P'){
                    $status = Deptstatus::find()->where([
                        'tahun' => $session['deptPeriodValue'],
                        'unit_id' => $row['unit_id'],
                    ])->count();

                    if($status=="0") {
                        $model = new Deptstatus(); //$params
                        $model->modul_1 = null;
                        $model->tahun = $session['deptPeriodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }else{
                        $status = Deptstatus::find()->where([
                            'tahun' => $session['deptPeriodValue'],
                            'unit_id' => $row['unit_id'],
                        ])->one();
        
                        $model = Deptstatus::findOne($status['id']);
                        $model->modul_1 = null;
                        $model->tahun = $session['deptPeriodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }
                }

                if(substr($id,-1)==='G'){
                    $status = Deptstatus::find()->where([
                        'tahun' => $session['deptPeriodValue'],
                        'unit_id' => $row['unit_id'],
                    ])->count();

                    if($status=="0") {
                        $model = new Deptstatus(); //$params
                        $model->modul_2 = null;
                        $model->tahun = $session['deptPeriodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }else{
                        $status = Deptstatus::find()->where([
                            'tahun' => $session['deptPeriodValue'],
                            'unit_id' => $row['unit_id'],
                        ])->one();
        
                        $model = Deptstatus::findOne($status['id']);
                        $model->modul_2 = null;
                        $model->tahun = $session['deptPeriodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }
                }

                if(substr($id,-1)==='R'){
                    $status = Deptstatus::find()->where([
                        'tahun' => $session['deptPeriodValue'],
                        'unit_id' => $row['unit_id'],
                    ])->count();

                    if($status=="0") {
                        $model = new Deptstatus(); //$params
                        $model->modul_3 = null;
                        $model->tahun = $session['deptPeriodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }else{
                        $status = Deptstatus::find()->where([
                            'tahun' => $session['deptPeriodValue'],
                            'unit_id' => $row['unit_id'],
                        ])->one();
        
                        $model = Deptstatus::findOne($status['id']);
                        $model->modul_3 = null;
                        $model->tahun = $session['deptPeriodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }
                }

                if(substr($id,-1)==='L'){
                    $status = Deptstatus::find()->where([
                        'tahun' => $session['deptPeriodValue'],
                        'unit_id' => $row['unit_id'],
                    ])->count();

                    if($status=="0") {
                        $model = new Deptstatus(); //$params
                        $model->modul_4 = null;
                        $model->tahun = $session['deptPeriodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }else{
                        $status = Deptstatus::find()->where([
                            'tahun' => $session['deptPeriodValue'],
                            'unit_id' => $row['unit_id'],
                        ])->one();
        
                        $model = Deptstatus::findOne($status['id']);
                        $model->modul_4 = null;
                        $model->tahun = $session['deptPeriodValue'];
                        $model->unit_id = $row['unit_id'];
                        $model->save();
                    }
                }
            }
        }
        return $this->redirect(array('list', 'period'=>$session['deptPeriodValue']));   
    }

    public function actionGetRealisasi($unit_id,$mo)
    {
        $session = Yii::$app->session;
        $unit = Unit::findOne($unit_id);
        $session['unitId'] = $unit_id;
        $session['mo'] = $mo;

        if($mo == '0'){
            $real = Yii::$app->db->createCommand('SELECT g.nama_program, v.nama_sub_kegiatan, IFNULL(SUM(e.jumlah),0) poa, 0 realisasi_lalu, IFNULL(realisasi.jumlah,0) realisasi, 
            SUBSTRING(IFNULL(cast(IFNULL(realisasi.jumlah,0)/SUM(IFNULL(e.jumlah,0))*100 as char),0),1,5) prosentase 
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_program g ON g.id=s.dept_program_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN
            (
                SELECT r.dept_sub_activity_id, dept_period_id, SUM(r.jumlah) jumlah 
                FROM dept_real r
                group BY r.dept_sub_activity_id 
            ) realisasi ON realisasi.dept_sub_activity_id=v.id
            WHERE p.tahun=:tahun AND p.unit_id=:user
            group BY v.id
            ORDER BY g.id, v.id')
            ->bindValue(':tahun', $session['deptPeriodValue'])
            ->bindValue(':user',$unit_id)
            ->queryAll();
        }elseif($mo == '1'){
            $real = Yii::$app->db->createCommand('SELECT g.nama_program, v.nama_sub_kegiatan, IFNULL(SUM(e.jumlah),0) poa, 0 realisasi_lalu, IFNULL(realisasi.jumlah,0) realisasi, 
            SUBSTRING(IFNULL(CAST((IFNULL(realisasi_lalu.jumlah,0)+IFNULL(realisasi.jumlah,0))/SUM(IFNULL(e.jumlah,0))*100 as char),0),1,5) prosentase 
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_program g ON g.id=s.dept_program_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN
            (
                SELECT r.dept_sub_activity_id, dept_period_id, SUM(r.jumlah) jumlah 
                FROM dept_real r WHERE r.bulan>=1 AND r.bulan<=3
                group BY r.dept_sub_activity_id 
            ) realisasi_lalu ON realisasi_lalu.dept_sub_activity_id=v.id
            LEFT JOIN
            (
                SELECT r.dept_sub_activity_id, dept_period_id, SUM(r.jumlah) jumlah 
                FROM dept_real r WHERE r.bulan>=1 AND r.bulan<=3
                group BY r.dept_sub_activity_id 
            ) realisasi ON realisasi.dept_sub_activity_id=v.id
            WHERE p.tahun=:tahun AND p.unit_id=:user
            group BY v.id
            ORDER BY g.id, v.id')
            ->bindValue(':tahun', $session['deptPeriodValue'])
            ->bindValue(':user',$unit_id)
            ->queryAll();
        }elseif($mo == '2'){
            $real = Yii::$app->db->createCommand('SELECT g.nama_program, v.nama_sub_kegiatan, IFNULL(SUM(e.jumlah),0) poa, IFNULL(realisasi_lalu.jumlah,0) realisasi_lalu, IFNULL(realisasi.jumlah,0) realisasi, 
            SUBSTRING(IFNULL(CAST((IFNULL(realisasi_lalu.jumlah,0)+IFNULL(realisasi.jumlah,0))/SUM(IFNULL(e.jumlah,0))*100 as char),0),1,5) prosentase 
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_program g ON g.id=s.dept_program_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN
            (
                SELECT r.dept_sub_activity_id, dept_period_id, SUM(r.jumlah) jumlah 
                FROM dept_real r WHERE r.bulan>=1 AND r.bulan<=3
                group BY r.dept_sub_activity_id 
            ) realisasi_lalu ON realisasi_lalu.dept_sub_activity_id=v.id
            LEFT JOIN
            (
                SELECT r.dept_sub_activity_id, dept_period_id, SUM(r.jumlah) jumlah 
                FROM dept_real r WHERE r.bulan>=3 AND r.bulan<=6
                group BY r.dept_sub_activity_id 
            ) realisasi ON realisasi.dept_sub_activity_id=v.id
            WHERE p.tahun=:tahun AND p.unit_id=:user
            group BY v.id
            ORDER BY g.id, v.id')
            ->bindValue(':tahun', $session['deptPeriodValue'])
            ->bindValue(':user',$unit_id)
            ->queryAll();
        }elseif($mo == '3'){
            $real = Yii::$app->db->createCommand('SELECT g.nama_program, v.nama_sub_kegiatan, IFNULL(SUM(e.jumlah),0) poa, IFNULL(realisasi_lalu.jumlah,0) realisasi_lalu, IFNULL(realisasi.jumlah,0) realisasi, 
            SUBSTRING(IFNULL(CAST((IFNULL(realisasi_lalu.jumlah,0)+IFNULL(realisasi.jumlah,0))/SUM(IFNULL(e.jumlah,0))*100 as char),0),1,5) prosentase 
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_program g ON g.id=s.dept_program_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN
            (
                SELECT r.dept_sub_activity_id, dept_period_id, SUM(r.jumlah) jumlah 
                FROM dept_real r WHERE r.bulan>=1 AND r.bulan<=6
                group BY r.dept_sub_activity_id 
            ) realisasi_lalu ON realisasi_lalu.dept_sub_activity_id=v.id
            LEFT JOIN
            (
                SELECT r.dept_sub_activity_id, dept_period_id, SUM(r.jumlah) jumlah 
                FROM dept_real r WHERE r.bulan>=6 AND r.bulan<=9
                group BY r.dept_sub_activity_id 
            ) realisasi ON realisasi.dept_sub_activity_id=v.id
            WHERE p.tahun=:tahun AND p.unit_id=:user
            group BY v.id
            ORDER BY g.id, v.id')
            ->bindValue(':tahun', $session['deptPeriodValue'])
            ->bindValue(':user',$unit_id)
            ->queryAll();
        }elseif($mo == '4'){
            $real = Yii::$app->db->createCommand('SELECT g.nama_program, v.nama_sub_kegiatan, IFNULL(SUM(e.jumlah),0) poa, IFNULL(realisasi_lalu.jumlah,0) realisasi_lalu, IFNULL(realisasi.jumlah,0) realisasi, 
            SUBSTRING(IFNULL(CAST((IFNULL(realisasi_lalu.jumlah,0)+IFNULL(realisasi.jumlah,0))/SUM(IFNULL(e.jumlah,0))*100 as char),0),1,5) prosentase 
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_program g ON g.id=s.dept_program_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN
            (
                SELECT r.dept_sub_activity_id, dept_period_id, SUM(r.jumlah) jumlah 
                FROM dept_real r WHERE r.bulan>=1 AND r.bulan<=9
                group BY r.dept_sub_activity_id 
            ) realisasi_lalu ON realisasi_lalu.dept_sub_activity_id=v.id
            LEFT JOIN
            (
                SELECT r.dept_sub_activity_id, dept_period_id, SUM(r.jumlah) jumlah 
                FROM dept_real r WHERE r.bulan>=9 AND r.bulan<=12
                group BY r.dept_sub_activity_id 
            ) realisasi ON realisasi.dept_sub_activity_id=v.id
            WHERE p.tahun=:tahun AND p.unit_id=:user
            group BY v.id
            ORDER BY g.id, v.id')
            ->bindValue(':tahun', $session['deptPeriodValue'])
            ->bindValue(':user',$unit_id)
            ->queryAll();
        }

        return $this->render('real', [
            'data' => $real,
            'mo' => $mo,
            'puskesmas' => $unit->puskesmas
        ]);
    }

    public function actionExportRealisasi()
    {
        $session = Yii::$app->session;
        $id = $session['mo'];
        if($id == 0){$tribulan = 'TAHUNAN';}
        if($id == 1){$tribulan = 'TRIBULAN I';}
        if($id == 2){$tribulan = 'TRIBULAN II';}
        if($id == 3){$tribulan = 'TRIBULAN III';}
        if($id == 4){$tribulan = 'TRIBULAN IV';}

        $session = Yii::$app->session;
        $period = $session['deptPeriodValue'];

        $unit = Unit::findOne($session['unitId']);
        
        // $spreadsheet = new Spreadsheet();

        $inputFileName = Yii::getAlias('@app/views/exportaccount').'/_export_realisasi_dinas.xlsx';

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

        $activeSheet->setCellValue('A'.'1', 'LAPORAN REALIASASI '.$tribulan.' BOK LABUPATEN TAHUN ' .$period);
        $activeSheet->setCellValue('A'.'2', 'SEKSI ' .strtoupper($unit->puskesmas));

        $baseRow=6;
        $firstData=6;

        $bidang = '';
        $isianbidang = '';

        if($id == '0'){
            $real = Yii::$app->db->createCommand('SELECT g.nama_program, v.nama_sub_kegiatan, IFNULL(SUM(e.jumlah),0) poa, 0 realisasi_lalu, IFNULL(realisasi.jumlah,0) realisasi, 
            SUBSTRING(IFNULL(cast(IFNULL(realisasi.jumlah,0)/SUM(IFNULL(e.jumlah,0))*100 as char),0),1,5) prosentase 
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_program g ON g.id=s.dept_program_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN
            (
                SELECT r.dept_sub_activity_id, dept_period_id, SUM(r.jumlah) jumlah 
                FROM dept_real r
                group BY r.dept_sub_activity_id 
            ) realisasi ON realisasi.dept_sub_activity_id=v.id
            WHERE p.tahun=:tahun AND p.unit_id=:user
            group BY v.id
            ORDER BY g.id, v.id')
            ->bindValue(':tahun', $session['deptPeriodValue'])
            ->bindValue(':user', $session['unitId'])
            ->queryAll();
        }elseif($id == '1'){
            $real = Yii::$app->db->createCommand('SELECT g.nama_program, v.nama_sub_kegiatan, IFNULL(SUM(e.jumlah),0) poa, 0 realisasi_lalu, IFNULL(realisasi.jumlah,0) realisasi, 
            SUBSTRING(IFNULL(CAST((IFNULL(realisasi_lalu.jumlah,0)+IFNULL(realisasi.jumlah,0))/SUM(IFNULL(e.jumlah,0))*100 as char),0),1,5) prosentase 
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_program g ON g.id=s.dept_program_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN
            (
                SELECT r.dept_sub_activity_id, dept_period_id, SUM(r.jumlah) jumlah 
                FROM dept_real r WHERE r.bulan>=1 AND r.bulan<=3
                group BY r.dept_sub_activity_id 
            ) realisasi_lalu ON realisasi_lalu.dept_sub_activity_id=v.id
            LEFT JOIN
            (
                SELECT r.dept_sub_activity_id, dept_period_id, SUM(r.jumlah) jumlah 
                FROM dept_real r WHERE r.bulan>=1 AND r.bulan<=3
                group BY r.dept_sub_activity_id 
            ) realisasi ON realisasi.dept_sub_activity_id=v.id
            WHERE p.tahun=:tahun AND p.unit_id=:user
            group BY v.id
            ORDER BY g.id, v.id')
            ->bindValue(':tahun', $session['deptPeriodValue'])
            ->bindValue(':user',$session['unitId'])
            ->queryAll();
        }elseif($id == '2'){
            $real = Yii::$app->db->createCommand('SELECT g.nama_program, v.nama_sub_kegiatan, IFNULL(SUM(e.jumlah),0) poa, IFNULL(realisasi_lalu.jumlah,0) realisasi_lalu, IFNULL(realisasi.jumlah,0) realisasi, 
            SUBSTRING(IFNULL(CAST((IFNULL(realisasi_lalu.jumlah,0)+IFNULL(realisasi.jumlah,0))/SUM(IFNULL(e.jumlah,0))*100 as char),0),1,5) prosentase 
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_program g ON g.id=s.dept_program_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN
            (
                SELECT r.dept_sub_activity_id, dept_period_id, SUM(r.jumlah) jumlah 
                FROM dept_real r WHERE r.bulan>=1 AND r.bulan<=3
                group BY r.dept_sub_activity_id 
            ) realisasi_lalu ON realisasi_lalu.dept_sub_activity_id=v.id
            LEFT JOIN
            (
                SELECT r.dept_sub_activity_id, dept_period_id, SUM(r.jumlah) jumlah 
                FROM dept_real r WHERE r.bulan>=3 AND r.bulan<=6
                group BY r.dept_sub_activity_id 
            ) realisasi ON realisasi.dept_sub_activity_id=v.id
            WHERE p.tahun=:tahun AND p.unit_id=:user
            group BY v.id
            ORDER BY g.id, v.id')
            ->bindValue(':tahun', $session['deptPeriodValue'])
            ->bindValue(':user',$session['unitId'])
            ->queryAll();
        }elseif($id == '3'){
            $real = Yii::$app->db->createCommand('SELECT g.nama_program, v.nama_sub_kegiatan, IFNULL(SUM(e.jumlah),0) poa, IFNULL(realisasi_lalu.jumlah,0) realisasi_lalu, IFNULL(realisasi.jumlah,0) realisasi, 
            SUBSTRING(IFNULL(CAST((IFNULL(realisasi_lalu.jumlah,0)+IFNULL(realisasi.jumlah,0))/SUM(IFNULL(e.jumlah,0))*100 as char),0),1,5) prosentase 
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_program g ON g.id=s.dept_program_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN
            (
                SELECT r.dept_sub_activity_id, dept_period_id, SUM(r.jumlah) jumlah 
                FROM dept_real r WHERE r.bulan>=1 AND r.bulan<=6
                group BY r.dept_sub_activity_id 
            ) realisasi_lalu ON realisasi_lalu.dept_sub_activity_id=v.id
            LEFT JOIN
            (
                SELECT r.dept_sub_activity_id, dept_period_id, SUM(r.jumlah) jumlah 
                FROM dept_real r WHERE r.bulan>=6 AND r.bulan<=9
                group BY r.dept_sub_activity_id 
            ) realisasi ON realisasi.dept_sub_activity_id=v.id
            WHERE p.tahun=:tahun AND p.unit_id=:user
            group BY v.id
            ORDER BY g.id, v.id')
            ->bindValue(':tahun', $session['deptPeriodValue'])
            ->bindValue(':user',$session['unitId'])
            ->queryAll();
        }elseif($id == '4'){
            $real = Yii::$app->db->createCommand('SELECT g.nama_program, v.nama_sub_kegiatan, IFNULL(SUM(e.jumlah),0) poa, IFNULL(realisasi_lalu.jumlah,0) realisasi_lalu, IFNULL(realisasi.jumlah,0) realisasi, 
            SUBSTRING(IFNULL(CAST((IFNULL(realisasi_lalu.jumlah,0)+IFNULL(realisasi.jumlah,0))/SUM(IFNULL(e.jumlah,0))*100 as char),0),1,5) prosentase 
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_program g ON g.id=s.dept_program_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN
            (
                SELECT r.dept_sub_activity_id, dept_period_id, SUM(r.jumlah) jumlah 
                FROM dept_real r WHERE r.bulan>=1 AND r.bulan<=9
                group BY r.dept_sub_activity_id 
            ) realisasi_lalu ON realisasi_lalu.dept_sub_activity_id=v.id
            LEFT JOIN
            (
                SELECT r.dept_sub_activity_id, dept_period_id, SUM(r.jumlah) jumlah 
                FROM dept_real r WHERE r.bulan>=9 AND r.bulan<=12
                group BY r.dept_sub_activity_id 
            ) realisasi ON realisasi.dept_sub_activity_id=v.id
            WHERE p.tahun=:tahun AND p.unit_id=:user
            group BY v.id
            ORDER BY g.id, v.id')
            ->bindValue(':tahun', $session['deptPeriodValue'])
            ->bindValue(':user',$session['unitId'])
            ->queryAll();
        }

        $totalpoa = 0;
        $totalrealisasilalu = 0;
        $totalrealisasi = 0;
        foreach($real as $rowdata) {
            if($isianbidang == ''){
                $bidang = $rowdata['nama_program'];
            }elseif($isianbidang == $rowdata['nama_program']){
                $bidang = '';
            }else{
                $bidang = $rowdata['nama_program'];
            }

            $activeSheet
                ->setCellValue('A'.$baseRow, $baseRow-5)
                ->setCellValue('B'.$baseRow, $bidang)
                ->setCellValue('C'.$baseRow, $rowdata['nama_sub_kegiatan'])
                ->setCellValue('D'.$baseRow, $rowdata['poa'])
                ->setCellValue('E'.$baseRow, $rowdata['realisasi_lalu'])
                ->setCellValue('F'.$baseRow, $rowdata['realisasi'])
                ->setCellValue('G'.$baseRow, $rowdata['prosentase']);
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':G' .$baseRow)->applyFromArray($styleArray);
                $isianbidang = $rowdata['nama_program'];
                $totalpoa = $totalpoa + $rowdata['poa'];
                $totalrealisasilalu = $totalrealisasilalu + $rowdata['realisasi_lalu'];
                $totalrealisasi = $totalrealisasi + $rowdata['realisasi'];
                $baseRow++;
        }

        if(!empty($rowdata)){
            $lastData=$baseRow-1;
            $activeSheet->setCellValue('A'.$baseRow, 'TOTAL');
            $spreadsheet->getActiveSheet()->mergeCells('A'.$baseRow. ':C' .$baseRow);
            $activeSheet->getStyle('A'.$baseRow. ':C' .$baseRow)->getAlignment()->setHorizontal('center'); 
            $activeSheet->getStyle('A'.$baseRow. ':C' .$baseRow)->getAlignment()->setWrapText(true);
            $activeSheet->setCellValue('D'.$baseRow, '=SUM(D' .$firstData. ':D' .$lastData. ')');
            $activeSheet->setCellValue('E'.$baseRow, '=SUM(E' .$firstData. ':E' .$lastData. ')');
            $activeSheet->setCellValue('F'.$baseRow, '=SUM(F' .$firstData. ':F' .$lastData. ')');
            $activeSheet->setCellValue('G'.$baseRow, ($totalrealisasilalu + $totalrealisasi)/$totalpoa*100);
            $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':G' .$baseRow)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':G' .$baseRow)->getFont()->setBold(true);
        }

        $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_realisasi_bok_kabupaten_'.strtolower($unit->puskesmas).'_'.strtolower($tribulan).'_'.$period.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function actionDetailpoa($p)
    {
        // $POST_VARIABLE=Yii::$app->request->post('Deptperiod');
        // $period = $POST_VARIABLE['tahun'];

        $session = Yii::$app->session;
        // if(!isset($period)){
        //     $period = $session['deptPeriodValue'];
        // }else{
        //     $session['deptPeriodValue'] = $period;
        // }
        $period = $session['deptPeriodValue'];

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

        $query = 'SELECT e.id, f.id perfomance_id, r.id realization_id, r.dept_sub_activity_detail_id, 
        g.nama_program, s.nama_kegiatan, v.nama_sub_kegiatan,  
        a.id dept_sub_activity_data_id, IFNULL(a.bentuk_kegiatan, v.nama_sub_kegiatan) bentuk_kegiatan,
        a.indikator_hasil, CASE WHEN MID(a.target_hasil,1,1) = "" THEN "Tidak ada" ELSE a.target_hasil END target_hasil, a.indikator_keluaran, a.target_keluaran, 
        c.nama_rekening, e.rincian, e.vol_1, e.satuan_1, 
        IFNULL(e.vol_2,"") vol_2, IFNULL(e.satuan_2,"") satuan_2, 
        IFNULL(e.vol_3,"") vol_3, IFNULL(e.satuan_3,"") satuan_3,
        IFNULL(e.vol_4,"") vol_4, IFNULL(e.satuan_4,"") satuan_4,
        vol_1*IFNULL(vol_2,1)*IFNULL(vol_3,1)*IFNULL(vol_4,1) vol, 
        e.unit_cost, e.jumlah, IFNULL(reallalu.jml,0) jml_real_lalu, IFNULL(r.jumlah,0) jml_real, 
        ifnull(ROUND((IFNULL(reallalu.jml,0)+IFNULL(r.jumlah,0))/e.jumlah*100,2),0) persen
        FROM dept_sub_activity_detail e
        LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
        LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
        LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
        LEFT JOIN dept_program g ON g.id=s.dept_program_id
        LEFT JOIN dept_period p ON p.id=a.dept_period_id
        LEFT JOIN account c ON c.id=e.account_id
        LEFT JOIN
        (
            SELECT z.dept_sub_activity_detail_id, SUM(z.jumlah) jml FROM dept_realization z 
            WHERE z.triwulan < "'.$p.'"
            GROUP BY z.dept_sub_activity_detail_id
        ) reallalu ON reallalu.dept_sub_activity_detail_id=e.id
        LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan="'.$p.'"
        LEFT JOIN dept_perfomance f ON f.dept_sub_activity_data_id=a.id AND f.triwulan="'.$p.'"
        WHERE p.unit_id="'.Yii::$app->user->identity->unit_id.'" AND p.tahun="'.$period.'"
        ORDER BY g.id, s.id, v.id, a.id';

        $session['qryreal'] = $query;

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model = $dataProvider->getModels();

        $unit = Unit::findOne(Yii::$app->user->identity->unit_id);

        $ceksp2d = Yii::$app->db->createCommand('SELECT IFNULL(SUM(s.jumlah),0) total FROM dept_sp2d s WHERE YEAR(s.tanggal)=:tahun')
        ->bindValue(':tahun', $period)
        ->queryAll();

        foreach($ceksp2d as $sp2d){
            $session['sp2d'] = $sp2d['total'];
        }

        $ceksp2dbln = Yii::$app->db->createCommand('SELECT IFNULL(SUM(s.jumlah),0) total FROM dept_sp2d s WHERE YEAR(s.tanggal)=:tahun AND MONTH(s.tanggal) >= :bulan1 AND MONTH(s.tanggal) <= :bulan2')
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
        
        FROM dept_sub_activity_detail e
        LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
        LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
        LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
        LEFT JOIN dept_period p ON p.id=a.dept_period_id
        LEFT JOIN unit u ON u.id=p.unit_id
        LEFT JOIN dept_status t ON t.unit_id=p.unit_id AND t.tahun=:tahun
        WHERE p.tahun=:tahun AND p.unit_id=:unit
        GROUP BY p.unit_id, p.pagu')
        ->bindValue(':unit', Yii::$app->user->identity->unit_id)
        ->bindValue(':tahun', $period)
        ->queryAll();

        // foreach($cekstsreal as $ceksts){
        //     if($p == 1){$stsreal = $ceksts['status_real_tw1'];}
        //     if($p == 2){$stsreal = $ceksts['status_real_tw2'];}
        //     if($p == 3){$stsreal = $ceksts['status_real_tw3'];}
        //     if($p == 4){$stsreal = $ceksts['status_real_tw4'];}
        // }

        if(!empty($cekstsreal))
        foreach($cekstsreal as $ceksts){
            if($p == 1){$stsreal = $ceksts['status_real_tw1'];}
            if($p == 2){$stsreal = $ceksts['status_real_tw2'];}
            if($p == 3){$stsreal = $ceksts['status_real_tw3'];}
            if($p == 4){$stsreal = $ceksts['status_real_tw4'];}
        }else{
            $stsreal = 0;
        }

        return $this->render('realization', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'id' => Yii::$app->user->identity->unit_id,
            'namaUnit' => $unit->puskesmas,
            'triwulan' => $triwulan,
            'twprev' => $twprev,
            'stsreal' => $stsreal
        ]);
    }

    public function actionDetailpoaadm($p,$unit_id,$sd)
    {
        $POST_VARIABLE=Yii::$app->request->post('Deptperiod');
        $period = $POST_VARIABLE['tahun'];

        $session = Yii::$app->session;
        if(!isset($period)){
            $period = $session['deptPeriodValue'];
        }else{
            $session['deptPeriodValue'] = $period;
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

        if($sd == ''){
            $query = 'SELECT e.id, f.id perfomance_id, r.id realization_id, r.dept_sub_activity_detail_id, 
            g.nama_program, s.nama_kegiatan, v.nama_sub_kegiatan,  
            a.id dept_sub_activity_data_id, IFNULL(a.bentuk_kegiatan, v.nama_sub_kegiatan) bentuk_kegiatan, sd.nama sumber_dana,
            a.indikator_hasil, CASE WHEN MID(a.target_hasil,1,1) = "" THEN "Tidak ada" ELSE a.target_hasil END target_hasil, a.indikator_keluaran, a.target_keluaran, 
            e.t1, e.t2, e.t3, e.t4,
            c.nama_rekening, e.rincian, e.vol_1, e.satuan_1, 
            IFNULL(e.vol_2,"") vol_2, IFNULL(e.satuan_2,"") satuan_2, 
            IFNULL(e.vol_3,"") vol_3, IFNULL(e.satuan_3,"") satuan_3,
            IFNULL(e.vol_4,"") vol_4, IFNULL(e.satuan_4,"") satuan_4,
            vol_1*IFNULL(vol_2,1)*IFNULL(vol_3,1)*IFNULL(vol_4,1) vol, 
            e.unit_cost, e.jumlah, IFNULL(reallalu.jml,0) jml_real_lalu, IFNULL(r.jumlah,0) jml_real, 
            ifnull(ROUND((IFNULL(reallalu.jml,0)+IFNULL(r.jumlah,0))/e.jumlah*100,2),0) persen
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_program g ON g.id=s.dept_program_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN account c ON c.id=e.account_id
            LEFT JOIN
            (
                SELECT z.dept_sub_activity_detail_id, SUM(z.jumlah) jml FROM dept_realization z 
                WHERE z.triwulan < "'.$p.'"
                GROUP BY z.dept_sub_activity_detail_id
            ) reallalu ON reallalu.dept_sub_activity_detail_id=e.id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan="'.$p.'"
            LEFT JOIN dept_perfomance f ON f.dept_sub_activity_data_id=a.id AND f.triwulan="'.$p.'"
            LEFT JOIN sumber_dana sd ON sd.id=e.sumber_dana_id
            WHERE p.unit_id="'.$unit_id.'" AND p.tahun="'.$period.'"
            ORDER BY g.id, s.id, v.id, a.id';
        }else{
            $query = 'SELECT e.id, f.id perfomance_id, r.id realization_id, r.dept_sub_activity_detail_id, 
            g.nama_program, s.nama_kegiatan, v.nama_sub_kegiatan,  
            a.id dept_sub_activity_data_id, IFNULL(a.bentuk_kegiatan, v.nama_sub_kegiatan) bentuk_kegiatan, sd.nama sumber_dana,
            a.indikator_hasil, CASE WHEN MID(a.target_hasil,1,1) = "" THEN "Tidak ada" ELSE a.target_hasil END target_hasil, a.indikator_keluaran, a.target_keluaran, 
            e.t1, e.t2, e.t3, e.t4,
            c.nama_rekening, e.rincian, e.vol_1, e.satuan_1, 
            IFNULL(e.vol_2,"") vol_2, IFNULL(e.satuan_2,"") satuan_2, 
            IFNULL(e.vol_3,"") vol_3, IFNULL(e.satuan_3,"") satuan_3,
            IFNULL(e.vol_4,"") vol_4, IFNULL(e.satuan_4,"") satuan_4,
            vol_1*IFNULL(vol_2,1)*IFNULL(vol_3,1)*IFNULL(vol_4,1) vol, 
            e.unit_cost, e.jumlah, IFNULL(reallalu.jml,0) jml_real_lalu, IFNULL(r.jumlah,0) jml_real, 
            ifnull(ROUND((IFNULL(reallalu.jml,0)+IFNULL(r.jumlah,0))/e.jumlah*100,2),0) persen
            FROM dept_sub_activity_detail e
            LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
            LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
            LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
            LEFT JOIN dept_program g ON g.id=s.dept_program_id
            LEFT JOIN dept_period p ON p.id=a.dept_period_id
            LEFT JOIN account c ON c.id=e.account_id
            LEFT JOIN
            (
                SELECT z.dept_sub_activity_detail_id, SUM(z.jumlah) jml FROM dept_realization z 
                WHERE z.triwulan < "'.$p.'"
                GROUP BY z.dept_sub_activity_detail_id
            ) reallalu ON reallalu.dept_sub_activity_detail_id=e.id
            LEFT JOIN dept_realization r ON r.dept_sub_activity_detail_id=e.id AND r.triwulan="'.$p.'"
            LEFT JOIN dept_perfomance f ON f.dept_sub_activity_data_id=a.id AND f.triwulan="'.$p.'"
            LEFT JOIN sumber_dana sd ON sd.id=e.sumber_dana_id
            WHERE p.unit_id="'.$unit_id.'" AND p.tahun="'.$period.'" AND e.sumber_dana_id="'.$sd.'"
            ORDER BY g.id, s.id, v.id, a.id';
        }

        $session['sumberdana'] = $sd;

        $session['qryreal'] = $query;

        $dataProvider = new SqlDataProvider([
            'sql' => $query,
            'pagination' => false
        ]);

        $model = $dataProvider->getModels();

        $unit = Unit::findOne($unit_id);

        $ceksp2d = Yii::$app->db->createCommand('SELECT IFNULL(SUM(s.jumlah),0) total FROM dept_sp2d s WHERE YEAR(s.tanggal)=:tahun')
        ->bindValue(':tahun', $period)
        ->queryAll();

        foreach($ceksp2d as $sp2d){
            $session['sp2d'] = $sp2d['total'];
        }

        $ceksp2dbln = Yii::$app->db->createCommand('SELECT IFNULL(SUM(s.jumlah),0) total FROM dept_sp2d s WHERE YEAR(s.tanggal)=:tahun AND MONTH(s.tanggal) >= :bulan1 AND MONTH(s.tanggal) <= :bulan2')
        ->bindValue(':bulan1', $b1)
        ->bindValue(':bulan2', $b2)
        ->bindValue(':tahun', $period)
        ->queryAll();

        foreach($ceksp2dbln as $sp2dbln){
            $session['sp2dBln'] = $sp2dbln['total'];
        }

        if($sp2dbln['total'] == 0){
            // Yii::$app->session->setFlash('danger', "SP2D belum dientri.");
            $session['sp2dStatus'] = false;
        }else{
            // Yii::$app->session->setFlash('success', "Total SP2D Triwulan ".$r." Rp. ".number_format($sp2dbln['total'],0,',','.'));
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
        
        FROM dept_sub_activity_detail e
        LEFT JOIN dept_sub_activity_data a ON a.id=e.dept_sub_activity_data_id
        LEFT JOIN dept_sub_activity v ON v.id=a.dept_sub_activity_id
        LEFT JOIN dept_activity s ON s.id=v.dept_activity_id
        LEFT JOIN dept_period p ON p.id=a.dept_period_id
        LEFT JOIN unit u ON u.id=p.unit_id
        LEFT JOIN dept_status t ON t.unit_id=p.unit_id AND t.tahun=:tahun
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

        return $this->render('realizationadm', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'id' => $unit_id,
            'namaUnit' => $unit->puskesmas,
            'triwulan' => $triwulan,
            'twprev' => $twprev,
            'stsreal' => $stsreal
        ]);
    }

    public function actionPostTw($id,$tw,$val)
    {
        if($val == 'true'){$value='1';}else{$value='0';}
        if($tw == '1'){
            $update = Yii::$app->db->createCommand('update dept_sub_activity_detail set t1=:val where id=:id')
            ->bindValue(':id',$id)
            ->bindValue(':val',$value)
            ->execute();
        }
        if($tw == '2'){
            $update = Yii::$app->db->createCommand('update dept_sub_activity_detail set t2=:val where id=:id')
            ->bindValue(':id',$id)
            ->bindValue(':val',$value)
            ->execute();
        }
        if($tw == '3'){
            $update = Yii::$app->db->createCommand('update dept_sub_activity_detail set t3=:val where id=:id')
            ->bindValue(':id',$id)
            ->bindValue(':val',$value)
            ->execute();
        }
        if($tw == '4'){
            $update = Yii::$app->db->createCommand('update dept_sub_activity_detail set t4=:val where id=:id')
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

        $activeSheet->setCellValue('A'.'1', 'DATA REALISASI POA TW '.$session['triwulan'].' '.strtoupper(Yii::$app->user->identity->alias));
        $activeSheet->setCellValue('A'.'2', 'PERIODE ' .$session['deptPeriodValue']);
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
            if($komponen == $rowdata['nama_kegiatan']){
                $komponen = '';
            }else{
                $komponen = $rowdata['nama_kegiatan'];
            }

            if($subkomponen == $rowdata['nama_sub_kegiatan']){
                $subkomponen = '';
            }else{
                $subkomponen = $rowdata['nama_sub_kegiatan'];
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

            $komponen = $rowdata['nama_kegiatan'];
            $subkomponen = $rowdata['nama_sub_kegiatan'];
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

    public function actionRekapSp2d($id)
    {
        if($id == 1){$twawal=1;$twakhir=3;}
        if($id == 2){$twawal=4;$twakhir=6;}
        if($id == 3){$twawal=7;$twakhir=9;}
        if($id == 4){$twawal=10;$twakhir=12;}

        $session = Yii::$app->session;
        $rekap = 'SELECT DATE_FORMAT(s.tanggal,"%d-%m-%Y") tanggal, s.no_sp2d, s.jenis_spm, s.uraian, s.jumlah FROM dept_sp2d s
        WHERE YEAR(s.tanggal)='.$session['deptPeriodValue'].' AND MONTH(s.tanggal)>='.$twawal.' AND MONTH(s.tanggal)<='.$twakhir.'
        ORDER BY s.tanggal';

        $session['qrysp2d'] = $rekap;

        $dataProvider = new SqlDataProvider([
            'sql' => $rekap,
            'pagination' => false
        ]);

        return $this->render('rekapsp2d',[
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionExportRekapSp2d()
    {
        $session = Yii::$app->session;
        $period = $session['deptPeriodValue'];

        $data = Yii::$app->db->createCommand($session['qrysp2d'])
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

        $activeSheet->setCellValue('A'.'1', 'REKAP SP2D');
        $activeSheet->setCellValue('A'.'2', 'PERIODE ' .$session['deptPeriodValue']);
        $baseRow=5;

        $activeSheet
            ->setCellValue('A'.'4', 'NO')
            ->setCellValue('B'.'4', 'TANGGAL')
            ->setCellValue('C'.'4', 'NO SP2D')
            ->setCellValue('D'.'4', 'JENIS SPM')
            ->setCellValue('E'.'4', 'URAIAN')
            ->setCellValue('F'.'4', 'JUMLAH');

        $pkm = '';
        $jml = 0;
        $row = 1;

        foreach($data as $rowdata) {
            $activeSheet->setCellValue('A'.$baseRow, $row);

            $activeSheet
            ->setCellValue('B'.$baseRow, $rowdata['tanggal'])
            ->setCellValue('C'.$baseRow, $rowdata['no_sp2d'])
            ->setCellValue('D'.$baseRow, $rowdata['jenis_spm'])
            ->setCellValue('E'.$baseRow, $rowdata['uraian'])
            ->setCellValue('F'.$baseRow, $rowdata['jumlah']);

            $jml = $jml + $rowdata['jumlah'];
            $baseRow = $baseRow + 1;
            $row++;
        }

        $activeSheet->setCellValue('E'.$baseRow, 'Total')
        ->setCellValue('F'.$baseRow, $jml);

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_rekap_sp2d_'.$period.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
