<?php

namespace app\controllers;

use Yii;
use app\models\Ukm;
use app\models\UkmSearch;
use app\models\Unit;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\SqlDataProvider;
use yii\web\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

/**
 * UkmController implements the CRUD actions for Ukm model.
 */
class UkmController extends Controller
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
     * Lists all Ukm models.
     * @return mixed
     */
    public function actionIndex($tahun)
    {
        $session = Yii::$app->session;
        unset($session['bulan']);
        unset($session['sp2d_bulanan']);

        unset($session['re_1']);
        unset($session['re_2']);
        unset($session['re_3']);
        unset($session['re_4']);
        unset($session['re_5']);
        unset($session['re_6']);
        unset($session['re_7']);
        unset($session['re_8']);
        unset($session['re_9']);
        unset($session['re_10']);
        unset($session['re_11']);
        unset($session['re_12']);
        unset($session['re_13']);
        unset($session['re_14']);
        unset($session['re_15']);
        unset($session['re_16']);
        unset($session['re_17']);
        unset($session['re_18']);
        unset($session['re_19']);
        unset($session['re_20']);
        unset($session['re_21']);
        unset($session['re_22']);
        unset($session['re_23']);
        unset($session['re_24']);
        unset($session['re_25']);
        unset($session['re_26']);
        unset($session['re_27']);
        unset($session['re_28']);
        unset($session['re_29']);
        unset($session['re_30']);

        $session['tahun'] = $tahun;
        $session['periodValue'] = $tahun;
        unset($session['dblbln']);

        $searchModel = new UkmSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['unit_id' => Yii::$app->user->identity->unit_id, 'tahun' => $tahun]);
        $dataProvider->query->orderBy(['bulan'=>SORT_DESC, 'tahun'=>SORT_DESC]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'tahun' => $tahun
        ]);
    }

    /**
     * Displays a single Ukm model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id, $bln)
    {
        $session = Yii::$app->session; 

        if($bln == 1){$bulan = 'Januari';}
        if($bln == 2){$bulan = 'Februari';}
        if($bln == 3){$bulan = 'Maret';}
        if($bln == 4){$bulan = 'April';}
        if($bln == 5){$bulan = 'Mei';}
        if($bln == 6){$bulan = 'Juni';}
        if($bln == 7){$bulan = 'Juli';}
        if($bln == 8){$bulan = 'Agustus';}
        if($bln == 9){$bulan = 'September';}
        if($bln == 10){$bulan = 'Oktober';}
        if($bln == 11){$bulan = 'November';}
        if($bln == 12){$bulan = 'Desember';}

        $real = Yii::$app->db->createCommand('SELECT bd.jenis, bd.bd_desk bidang, uk.uk_desk upaya, MID(uk.uk_nama,4,2) no, IFNULL(p.jumlah,0) jumlah,
            c.jml_ke, c.jml_confirm, c.tenaga_tracer, c.tenaga_surveilans FROM uk_label uk
            LEFT JOIN bd_label bd ON bd.id=uk.bd_id
            LEFT JOIN uk_pagu p ON p.uk_id=uk.id AND p.unit_id=:unit
            LEFT JOIN capout c ON c.unit_id=:unit AND MID(c.nomor,1,4)=:tahun AND MID(c.nomor,5,2)=MID(uk.uk_nama,4,2) AND c.bulan=:bulan
            WHERE uk.tahun=:tahun AND bd.jenis = "COVID"
            ORDER BY uk.id')
        ->bindValue(':bulan', $bln)
        ->bindValue(':tahun', $session['tahun'])
        ->bindValue(':unit', Yii::$app->user->identity->unit_id)
        ->queryAll();

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('view', [
                'model' => $this->findModel($id),
                'data' => $real,
                'bln' => $bln,
                'bulan' => $bulan
            ]);
        }else{
            return $this->render('view', [
                'model' => $this->findModel($id),
                'data' => $real,
                'bln' => $bln,
                'bulan' => $bulan
            ]);
        }
    }

    /**
     * Creates a new Ukm model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */


    public function actionMonthTotal($bulan)
    {
        // return $bulan;
        $session = Yii::$app->session;
        $session['bulan'] = $bulan;

        $cek_sp2d_bulanan = Yii::$app->db->createCommand('SELECT IFNULL(SUM(s.jumlah),0) AS jumlah FROM sp2d s WHERE MONTH(s.tanggal)=:bulan AND YEAR(s.tanggal)=:tahun AND unit_id=:unit')
        ->bindValue(':bulan', $bulan)
        ->bindValue(':tahun', $session['tahun'])
        ->bindValue(':unit', Yii::$app->user->identity->unit_id)
        ->queryAll();

        $cek_ukm = Ukm::find()->where(['bulan' => $bulan, 'tahun' => $session['tahun'], 'unit_id' => Yii::$app->user->identity->unit_id])->all();

        if (!empty($cek_ukm)){
            $session['dblbln'] = true;
            Yii::$app->session->setFlash('danger', "Bulan yang dipilih sudah dientri sebelumnya.");
        }else{
            $session['dblbln'] = false;
        }

        foreach($cek_sp2d_bulanan as $sp2d_bulanan){
            unset($session['sp2d_bulanan']);

            unset($session['re_1']);
            unset($session['re_2']);
            unset($session['re_3']);
            unset($session['re_4']);
            unset($session['re_5']);
            unset($session['re_6']);
            unset($session['re_7']);
            unset($session['re_8']);
            unset($session['re_9']);
            unset($session['re_10']);
            unset($session['re_11']);
            unset($session['re_12']);
            unset($session['re_13']);
            unset($session['re_14']);
            unset($session['re_15']);
            unset($session['re_16']);
            unset($session['re_17']);
            unset($session['re_18']);
            unset($session['re_19']);
            unset($session['re_20']);
            unset($session['re_21']);
            unset($session['re_22']);
            unset($session['re_23']);
            unset($session['re_24']);
            unset($session['re_25']);
            unset($session['re_26']);
            unset($session['re_27']);
            unset($session['re_28']);
            unset($session['re_29']);
            unset($session['re_30']);

            $session['sp2d_bulanan'] = $sp2d_bulanan['jumlah'];
        }

        return $this->redirect(['create', 'tahun' => $session['tahun']]);
    }
    
    public function actionCreate($tahun)
    {
        $session = Yii::$app->session;
        $session['tahun'] = $tahun;

        $model = new Ukm();
        $model->tahun = $tahun;
        $model->unit_id = Yii::$app->user->identity->unit_id;

        $i = 1;
        for ($i=1; $i<=30; $i++){

            $re = 're_' .$i;
            if ($session[$re] == null){
                $model->$re = 0;
            }else{
                $model->$re = str_replace(".", "", $session[$re]);
            }
        }

        $model->total_sp2d_bulanan = $session['sp2d_bulanan'];

        $qList_uk = 'SELECT uk.*, bd.jenis, bd.bd_desk, IFNULL(p.jumlah,0) jumlah, MID(uk.uk_nama,4,2) no, uk.co_1, uk.co_2, uk.co_3, uk.co_4 FROM uk_label uk
        LEFT JOIN bd_label bd ON bd.id=uk.bd_id
        LEFT JOIN uk_pagu p ON p.uk_id=uk.id AND p.unit_id="'.Yii::$app->user->identity->unit_id.'"
        WHERE uk.tahun="'.$tahun.'"
        ORDER BY uk.id';

        $dpList_uk = new SqlDataProvider([
            'sql' => $qList_uk,
            'pagination' => false
        ]);

        $mList_uk = $dpList_uk->getModels();

        foreach($mList_uk as $list);

        $cek_sp2d = Yii::$app->db->createCommand('SELECT sum(s.jumlah) total_sp2d, 
        SUM(CASE WHEN MONTH(s.tanggal)=1 THEN TRUE ELSE FALSE END) jan,
        SUM(CASE WHEN MONTH(s.tanggal)=2 THEN TRUE ELSE FALSE END) feb,
        SUM(CASE WHEN MONTH(s.tanggal)=3 THEN TRUE ELSE FALSE END) mar,
        SUM(CASE WHEN MONTH(s.tanggal)=4 THEN TRUE ELSE FALSE END) apr,
        SUM(CASE WHEN MONTH(s.tanggal)=5 THEN TRUE ELSE FALSE END) mei,
        SUM(CASE WHEN MONTH(s.tanggal)=6 THEN TRUE ELSE FALSE END) jun,
        SUM(CASE WHEN MONTH(s.tanggal)=7 THEN TRUE ELSE FALSE END) jul,
        SUM(CASE WHEN MONTH(s.tanggal)=8 THEN TRUE ELSE FALSE END) agu,
        SUM(CASE WHEN MONTH(s.tanggal)=9 THEN TRUE ELSE FALSE END) sep,
        SUM(CASE WHEN MONTH(s.tanggal)=10 THEN TRUE ELSE FALSE END) okt,
        SUM(CASE WHEN MONTH(s.tanggal)=11 THEN TRUE ELSE FALSE END) nov,
        SUM(CASE WHEN MONTH(s.tanggal)=12 THEN TRUE ELSE FALSE END) des
        FROM sp2d s
        WHERE YEAR(s.tanggal)=:tahun AND unit_id=:unit')
        ->bindValue(':tahun', $tahun)
        ->bindValue(':unit', Yii::$app->user->identity->unit_id)
        ->queryAll();

        foreach($cek_sp2d as $cek_sp2d);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'tahun' => $tahun]);
        }

        return $this->render('create', [
            'model' => $model,
            'listuk' => $mList_uk,
            'list' => $list,
            'tahun' => $tahun,
            'jan' => $cek_sp2d['jan'],
            'feb' => $cek_sp2d['feb'],
            'mar' => $cek_sp2d['mar'],
            'apr' => $cek_sp2d['apr'],
            'mei' => $cek_sp2d['mei'],
            'jun' => $cek_sp2d['jun'],
            'jul' => $cek_sp2d['jul'],
            'agu' => $cek_sp2d['agu'],
            'sep' => $cek_sp2d['sep'],
            'okt' => $cek_sp2d['okt'],
            'nov' => $cek_sp2d['nov'],
            'des' => $cek_sp2d['des'],
        ]);
    }

    /**
     * Updates an existing Ukm model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $session = Yii::$app->session;
        $session['dblbln'] = false;
        $model = $this->findModel($id);
        $session['bulan'] = $model->bulan;

        $i = 1;
        for ($i=1; $i<=30; $i++){

            $re = 're_' .$i;
            if ($model->$re == null){
                $model->$re = 0;
            }else{
                if ($session[$re] !== null){
                    $model->$re = str_replace(".", "", $session[$re]);
                }
            }
        }

        $cek_sp2d_bulanan = Yii::$app->db->createCommand('SELECT IFNULL(SUM(s.jumlah),0) AS jumlah FROM sp2d s WHERE MONTH(s.tanggal)=:bulan AND YEAR(s.tanggal)=:tahun AND unit_id=:unit')
        ->bindValue(':bulan', $model->bulan)
        ->bindValue(':tahun', $model->tahun)
        ->bindValue(':unit', $model->unit_id)
        ->queryAll();

        foreach($cek_sp2d_bulanan as $sp2d_bulanan){
            $model->total_sp2d_bulanan = $sp2d_bulanan['jumlah'];
        }

        $qList_uk = 'SELECT uk.*, bd.jenis, bd.bd_desk, IFNULL(p.jumlah,0) jumlah, MID(uk.uk_nama,4,2) no, uk.co_1, uk.co_2, uk.co_3, uk.co_4 FROM uk_label uk
        LEFT JOIN bd_label bd ON bd.id=uk.bd_id
        LEFT JOIN uk_pagu p ON p.uk_id=uk.id AND p.unit_id="'.Yii::$app->user->identity->unit_id.'"
        WHERE uk.tahun="'.$model->tahun.'"
        ORDER BY uk.id';

        $dpList_uk = new SqlDataProvider([
            'sql' => $qList_uk,
            'pagination' => false
        ]);

        $mList_uk = $dpList_uk->getModels();

        foreach($mList_uk as $list);

        $cek_sp2d = Yii::$app->db->createCommand('SELECT 
        SUM(CASE WHEN u.bulan=1 THEN TRUE ELSE FALSE END) jan,
        SUM(CASE WHEN u.bulan=2 THEN TRUE ELSE FALSE END) feb,
        SUM(CASE WHEN u.bulan=3 THEN TRUE ELSE FALSE END) mar,
        SUM(CASE WHEN u.bulan=4 THEN TRUE ELSE FALSE END) apr,
        SUM(CASE WHEN u.bulan=5 THEN TRUE ELSE FALSE END) mei,
        SUM(CASE WHEN u.bulan=6 THEN TRUE ELSE FALSE END) jun,
        SUM(CASE WHEN u.bulan=7 THEN TRUE ELSE FALSE END) jul,
        SUM(CASE WHEN u.bulan=8 THEN TRUE ELSE FALSE END) agu,
        SUM(CASE WHEN u.bulan=9 THEN TRUE ELSE FALSE END) sep,
        SUM(CASE WHEN u.bulan=10 THEN TRUE ELSE FALSE END) okt,
        SUM(CASE WHEN u.bulan=11 THEN TRUE ELSE FALSE END) nov,
        SUM(CASE WHEN u.bulan=12 THEN TRUE ELSE FALSE END) des
        FROM ukm u
        WHERE u.bulan=:bulan AND u.tahun=:tahun AND u.unit_id=:unit')
        ->bindValue(':bulan', $model->bulan)
        ->bindValue(':tahun', $model->tahun)
        ->bindValue(':unit', $model->unit_id)
        ->queryAll();

        foreach($cek_sp2d as $cek_sp2d);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'tahun' => $model->tahun]);
        }

        return $this->render('update', [
            'model' => $model,
            'listuk' => $mList_uk,
            'tahun' => $model->tahun,
            'jan' => $cek_sp2d['jan'],
            'feb' => $cek_sp2d['feb'],
            'mar' => $cek_sp2d['mar'],
            'apr' => $cek_sp2d['apr'],
            'mei' => $cek_sp2d['mei'],
            'jun' => $cek_sp2d['jun'],
            'jul' => $cek_sp2d['jul'],
            'agu' => $cek_sp2d['agu'],
            'sep' => $cek_sp2d['sep'],
            'okt' => $cek_sp2d['okt'],
            'nov' => $cek_sp2d['nov'],
            'des' => $cek_sp2d['des'],
        ]);
    }

    /**
     * Deletes an existing Ukm model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $session = Yii::$app->session;
        $this->findModel($id)->delete();

        return $this->redirect(['index', 'tahun' => $session['tahun']]);
    }

    /**
     * Finds the Ukm model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Ukm the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Ukm::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionGetReal($no, $bulan, $pagu)
    {
        $session = Yii::$app->session;

        $real = Yii::$app->db->createCommand('SELECT * FROM ukm u WHERE u.bulan=:bulan AND u.tahun=:tahun AND u.unit_id=:unit')
        ->bindValue(':bulan', $bulan)
        ->bindValue(':tahun', $session['tahun'])
        ->bindValue(':unit', Yii::$app->user->identity->unit_id)
        ->queryAll();

        foreach($real as $datareal);

        $re = 're_' .$no;
        
        if($pagu > 0 && $datareal[$re] > 0){
            $session['prosentase'] = number_format($datareal[$re] / $pagu * 100,2,",",".");
        }else{
            $session['prosentase'] = '0';
        }

        return number_format($datareal[$re],0,"",".");
    }

    public function actionExportRealisasi($bln)
    {
        if($bln == 1){$bulan = 'Januari';}
        if($bln == 2){$bulan = 'Februari';}
        if($bln == 3){$bulan = 'Maret';}
        if($bln == 4){$bulan = 'April';}
        if($bln == 5){$bulan = 'Mei';}
        if($bln == 6){$bulan = 'Juni';}
        if($bln == 7){$bulan = 'Juli';}
        if($bln == 8){$bulan = 'Agustus';}
        if($bln == 9){$bulan = 'September';}
        if($bln == 10){$bulan = 'Oktober';}
        if($bln == 11){$bulan = 'November';}
        if($bln == 12){$bulan = 'Desember';}

        $session = Yii::$app->session;
        $period = $session['periodValue'];

        $unit = Unit::findOne(Yii::$app->user->identity->unit_id);

        $data = Yii::$app->db->createCommand('SELECT bd.jenis, bd.bd_desk bidang, uk.uk_desk upaya, MID(uk.uk_nama,4,2) no, IFNULL(p.jumlah,0) jumlah, 
            c.jml_ke, c.jml_confirm, c.tenaga_tracer, c.tenaga_surveilans FROM uk_label uk
            LEFT JOIN bd_label bd ON bd.id=uk.bd_id
            LEFT JOIN uk_pagu p ON p.uk_id=uk.id AND p.unit_id=:unit
            LEFT JOIN capout c ON c.unit_id=:unit AND MID(c.nomor,1,4)=:tahun AND MID(c.nomor,5,2)=MID(uk.uk_nama,4,2) AND c.bulan=:bulan
            WHERE uk.tahun=:tahun AND bd.jenis = "COVID"
            ORDER BY uk.id')
        ->bindValue(':bulan', $bln)
        ->bindValue(':tahun', $session['tahun'])
        ->bindValue(':unit', Yii::$app->user->identity->unit_id)
        ->queryAll();
        
        // $spreadsheet = new Spreadsheet();

        $inputFileName = Yii::getAlias('@app/views/exportaccount').'/_export_covid.xlsx';

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

        $activeSheet->setCellValue('A'.'1', 'LAPORAN REALIASASI BULANAN BOK COVID PUSKESMAS TAHUN ' .$period);
        $activeSheet->setCellValue('A'.'2', 'PUSKESMAS ' .strtoupper($unit->puskesmas). ' BULAN '.strtoupper($bulan));

        $baseRow=6;
        $firstData=6;

        $activeSheet
            ->setCellValue('A'.'5', 'NO')
            ->setCellValue('B'.'5', 'BIDANG')
            ->setCellValue('C'.'5', 'UPAYA KESEHATAN')
            ->setCellValue('D'.'5', 'PAGU')
            ->setCellValue('E'.'5', 'REALISASI')
            ->setCellValue('F'.'5', '%');

        $bidang = '';
        $isianbidang = '';

        foreach($data as $rowdata) {

            $real = Yii::$app->db->createCommand('SELECT * FROM ukm u WHERE u.bulan=:bulan AND u.tahun=:tahun AND u.unit_id=:unit')
                ->bindValue(':bulan', $bln)
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', Yii::$app->user->identity->unit_id)
                ->queryAll();
            
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
                ->setCellValue('A'.$baseRow, $baseRow-5)
                ->setCellValue('B'.$baseRow, $bidang)
                ->setCellValue('C'.$baseRow, $rowdata['upaya'])
                ->setCellValue('D'.$baseRow, $rowdata['jumlah'])
                ->setCellValue('E'.$baseRow, $datareal[$re])
                ->setCellValue('F'.$baseRow, $session['prosentase'])
                ->setCellValue('G'.$baseRow, $rowdata['jml_ke'])
                ->setCellValue('H'.$baseRow, $rowdata['jml_confirm'])
                ->setCellValue('I'.$baseRow, $rowdata['tenaga_tracer'])
                ->setCellValue('J'.$baseRow, $rowdata['tenaga_surveilans']);
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':J' .$baseRow)->applyFromArray($styleArray);
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
            $activeSheet->setCellValue('G'.$baseRow, '=SUM(G' .$firstData. ':G' .$lastData. ')');
            $activeSheet->setCellValue('H'.$baseRow, '=SUM(H' .$firstData. ':H' .$lastData. ')');
            $activeSheet->setCellValue('I'.$baseRow, '=SUM(I' .$firstData. ':I' .$lastData. ')');
            $activeSheet->setCellValue('J'.$baseRow, '=SUM(J' .$firstData. ':J' .$lastData. ')');
            $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':J' .$baseRow)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':J' .$baseRow)->getFont()->setBold(true);
        }

        $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_realisasi_bok_covid_'.strtolower($unit->puskesmas).'_'.strtolower($bulan).'_'.$period.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function actionExportRealisasiAll()
    {
        $session = Yii::$app->session;
        // $bln = $session['mo'];
        // if($bln == 1){$bulan = 'Januari';}
        // if($bln == 2){$bulan = 'Februari';}
        // if($bln == 3){$bulan = 'Maret';}
        // if($bln == 4){$bulan = 'April';}
        // if($bln == 5){$bulan = 'Mei';}
        // if($bln == 6){$bulan = 'Juni';}
        // if($bln == 7){$bulan = 'Juli';}
        // if($bln == 8){$bulan = 'Agustus';}
        // if($bln == 9){$bulan = 'September';}
        // if($bln == 10){$bulan = 'Oktober';}
        // if($bln == 11){$bulan = 'November';}
        // if($bln == 12){$bulan = 'Desember';}

        $id = $session['mo'];
        if($id == 0){$tribulan = 'TAHUNAN';}
        if($id == 1){$tribulan = 'TRIBULAN I';}
        if($id == 2){$tribulan = 'TRIBULAN II';}
        if($id == 3){$tribulan = 'TRIBULAN III';}
        if($id == 4){$tribulan = 'TRIBULAN IV';}

        $period = $session['periodValue'];

        $unit = Unit::findOne($session['unitId']);

        if($id == '0'){
            $data = Yii::$app->db->createCommand('SELECT bd.jenis, bd.bd_desk bidang, uk.uk_desk upaya, MID(uk.uk_nama,4,2) no, SUM(IFNULL(p.jumlah,0)) jumlah, 
                c.jml_ke, c.jml_confirm, c.tenaga_tracer, c.tenaga_surveilans FROM uk_label uk
                LEFT JOIN bd_label bd ON bd.id=uk.bd_id
                LEFT JOIN uk_pagu p ON p.uk_id=uk.id AND p.unit_id=:unit
                LEFT JOIN capout c ON c.unit_id=:unit AND MID(c.nomor,1,4)=:tahun AND MID(c.nomor,5,2)=MID(uk.uk_nama,4,2)
                WHERE uk.tahun=:tahun AND bd.jenis = "COVID"
                GROUP BY uk.id ORDER BY uk.id')
            // ->bindValue(':bulan', $bln)
            ->bindValue(':tahun', $session['tahun'])
            ->bindValue(':unit', $session['unitId'])
            ->queryAll();
        }elseif($id == '1'){
            $data = Yii::$app->db->createCommand('SELECT bd.jenis, bd.bd_desk bidang, uk.uk_desk upaya, MID(uk.uk_nama,4,2) no, SUM(IFNULL(p.jumlah,0)) jumlah, 
                c.jml_ke, c.jml_confirm, c.tenaga_tracer, c.tenaga_surveilans FROM uk_label uk
                LEFT JOIN bd_label bd ON bd.id=uk.bd_id
                LEFT JOIN uk_pagu p ON p.uk_id=uk.id AND p.unit_id=:unit
                LEFT JOIN capout c ON c.unit_id=:unit AND MID(c.nomor,1,4)=:tahun AND MID(c.nomor,5,2)=MID(uk.uk_nama,4,2) AND c.bulan>=1 AND c.bulan<=3
                WHERE uk.tahun=:tahun AND bd.jenis = "COVID"
                GROUP BY uk.id ORDER BY uk.id')
            // ->bindValue(':bulan', $bln)
            ->bindValue(':tahun', $session['tahun'])
            ->bindValue(':unit', $session['unitId'])
            ->queryAll();
        }elseif($id == '2'){
            $data = Yii::$app->db->createCommand('SELECT bd.jenis, bd.bd_desk bidang, uk.uk_desk upaya, MID(uk.uk_nama,4,2) no, SUM(IFNULL(p.jumlah,0)) jumlah, 
                c.jml_ke, c.jml_confirm, c.tenaga_tracer, c.tenaga_surveilans FROM uk_label uk
                LEFT JOIN bd_label bd ON bd.id=uk.bd_id
                LEFT JOIN uk_pagu p ON p.uk_id=uk.id AND p.unit_id=:unit
                LEFT JOIN capout c ON c.unit_id=:unit AND MID(c.nomor,1,4)=:tahun AND MID(c.nomor,5,2)=MID(uk.uk_nama,4,2) AND u.bulan>3 AND u.bulan<=6
                WHERE uk.tahun=:tahun AND bd.jenis = "COVID"
                GROUP BY uk.id ORDER BY uk.id')
            // ->bindValue(':bulan', $bln)
            ->bindValue(':tahun', $session['tahun'])
            ->bindValue(':unit', $session['unitId'])
            ->queryAll();
        }elseif($id == '3'){
            $data = Yii::$app->db->createCommand('SELECT bd.jenis, bd.bd_desk bidang, uk.uk_desk upaya, MID(uk.uk_nama,4,2) no, SUM(IFNULL(p.jumlah,0)) jumlah, 
                c.jml_ke, c.jml_confirm, c.tenaga_tracer, c.tenaga_surveilans FROM uk_label uk
                LEFT JOIN bd_label bd ON bd.id=uk.bd_id
                LEFT JOIN uk_pagu p ON p.uk_id=uk.id AND p.unit_id=:unit
                LEFT JOIN capout c ON c.unit_id=:unit AND MID(c.nomor,1,4)=:tahun AND MID(c.nomor,5,2)=MID(uk.uk_nama,4,2) AND c.bulan>6 AND c.bulan<=9
                WHERE uk.tahun=:tahun AND bd.jenis = "COVID"
                GROUP BY uk.id ORDER BY uk.id')
            // ->bindValue(':bulan', $bln)
            ->bindValue(':tahun', $session['tahun'])
            ->bindValue(':unit', $session['unitId'])
            ->queryAll();
        }elseif($id == '4'){
            $data = Yii::$app->db->createCommand('SELECT bd.jenis, bd.bd_desk bidang, uk.uk_desk upaya, MID(uk.uk_nama,4,2) no, SUM(IFNULL(p.jumlah,0)) jumlah, 
                c.jml_ke, c.jml_confirm, c.tenaga_tracer, c.tenaga_surveilans FROM uk_label uk
                LEFT JOIN bd_label bd ON bd.id=uk.bd_id
                LEFT JOIN uk_pagu p ON p.uk_id=uk.id AND p.unit_id=:unit
                LEFT JOIN capout c ON c.unit_id=:unit AND MID(c.nomor,1,4)=:tahun AND MID(c.nomor,5,2)=MID(uk.uk_nama,4,2) AND c.bulan>9 AND c.bulan<=12
                WHERE uk.tahun=:tahun AND bd.jenis = "COVID"
                GROUP BY uk.id ORDER BY uk.id')
            // ->bindValue(':bulan', $bln)
            ->bindValue(':tahun', $session['tahun'])
            ->bindValue(':unit', $session['unitId'])
            ->queryAll();
        }
        
        // $spreadsheet = new Spreadsheet();

        $inputFileName = Yii::getAlias('@app/views/exportaccount').'/_export_covid.xlsx';

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

        $activeSheet->setCellValue('A'.'1', 'LAPORAN REALIASASI '.strtoupper($tribulan).' BOK COVID PUSKESMAS TAHUN ' .$period);
        $activeSheet->setCellValue('A'.'2', 'PUSKESMAS ' .strtoupper($unit->puskesmas));

        $baseRow=6;
        $firstData=6;

        $activeSheet
            ->setCellValue('A'.'5', 'NO')
            ->setCellValue('B'.'5', 'BIDANG')
            ->setCellValue('C'.'5', 'UPAYA KESEHATAN')
            ->setCellValue('D'.'5', 'PAGU')
            ->setCellValue('E'.'5', 'REALISASI')
            ->setCellValue('F'.'5', '%');

        $bidang = '';
        $isianbidang = '';

        foreach($data as $rowdata) {

            if($id == '0'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                SUM(u.re_1) re_1, SUM(u.re_2) re_2, SUM(u.re_3) re_3, SUM(u.re_4) re_4, SUM(u.re_5) re_5, 
                SUM(u.re_6) re_6, SUM(u.re_7) re_7, SUM(u.re_8) re_8, SUM(u.re_9) re_9, SUM(u.re_10) re_10, 
                SUM(u.re_11) re_11, SUM(u.re_12) re_12, SUM(u.re_13) re_13, SUM(u.re_14) re_14, SUM(u.re_15) re_15, 
                SUM(u.re_16) re_16, SUM(u.re_17) re_17, SUM(u.re_18) re_18, SUM(u.re_19) re_19, SUM(u.re_20) re_20, 
                SUM(u.re_21) re_21, SUM(u.re_22) re_22, SUM(u.re_23) re_23, SUM(u.re_24) re_24, SUM(u.re_25) re_25,
                SUM(u.re_26) re_26, SUM(u.re_27) re_27, SUM(u.re_28) re_28, SUM(u.re_29) re_29, SUM(u.re_30) re_30 FROM ukm u WHERE u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', $session['unitId'])
                ->queryAll();
            }elseif($id == '1'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                SUM(u.re_1) re_1, SUM(u.re_2) re_2, SUM(u.re_3) re_3, SUM(u.re_4) re_4, SUM(u.re_5) re_5, 
                SUM(u.re_6) re_6, SUM(u.re_7) re_7, SUM(u.re_8) re_8, SUM(u.re_9) re_9, SUM(u.re_10) re_10, 
                SUM(u.re_11) re_11, SUM(u.re_12) re_12, SUM(u.re_13) re_13, SUM(u.re_14) re_14, SUM(u.re_15) re_15, 
                SUM(u.re_16) re_16, SUM(u.re_17) re_17, SUM(u.re_18) re_18, SUM(u.re_19) re_19, SUM(u.re_20) re_20, 
                SUM(u.re_21) re_21, SUM(u.re_22) re_22, SUM(u.re_23) re_23, SUM(u.re_24) re_24, SUM(u.re_25) re_25,
                SUM(u.re_26) re_26, SUM(u.re_27) re_27, SUM(u.re_28) re_28, SUM(u.re_29) re_29, SUM(u.re_30) re_30 FROM ukm u WHERE u.bulan>=1 AND u.bulan<=3 AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', $session['unitId'])
                ->queryAll();
            }elseif($id == '2'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                SUM(u.re_1) re_1, SUM(u.re_2) re_2, SUM(u.re_3) re_3, SUM(u.re_4) re_4, SUM(u.re_5) re_5, 
                SUM(u.re_6) re_6, SUM(u.re_7) re_7, SUM(u.re_8) re_8, SUM(u.re_9) re_9, SUM(u.re_10) re_10, 
                SUM(u.re_11) re_11, SUM(u.re_12) re_12, SUM(u.re_13) re_13, SUM(u.re_14) re_14, SUM(u.re_15) re_15, 
                SUM(u.re_16) re_16, SUM(u.re_17) re_17, SUM(u.re_18) re_18, SUM(u.re_19) re_19, SUM(u.re_20) re_20, 
                SUM(u.re_21) re_21, SUM(u.re_22) re_22, SUM(u.re_23) re_23, SUM(u.re_24) re_24, SUM(u.re_25) re_25,
                SUM(u.re_26) re_26, SUM(u.re_27) re_27, SUM(u.re_28) re_28, SUM(u.re_29) re_29, SUM(u.re_30) re_30 FROM ukm u WHERE u.bulan>3 AND u.bulan<=6 AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', $session['unitId'])
                ->queryAll();
            }elseif($id == '3'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                SUM(u.re_1) re_1, SUM(u.re_2) re_2, SUM(u.re_3) re_3, SUM(u.re_4) re_4, SUM(u.re_5) re_5, 
                SUM(u.re_6) re_6, SUM(u.re_7) re_7, SUM(u.re_8) re_8, SUM(u.re_9) re_9, SUM(u.re_10) re_10, 
                SUM(u.re_11) re_11, SUM(u.re_12) re_12, SUM(u.re_13) re_13, SUM(u.re_14) re_14, SUM(u.re_15) re_15, 
                SUM(u.re_16) re_16, SUM(u.re_17) re_17, SUM(u.re_18) re_18, SUM(u.re_19) re_19, SUM(u.re_20) re_20, 
                SUM(u.re_21) re_21, SUM(u.re_22) re_22, SUM(u.re_23) re_23, SUM(u.re_24) re_24, SUM(u.re_25) re_25,
                SUM(u.re_26) re_26, SUM(u.re_27) re_27, SUM(u.re_28) re_28, SUM(u.re_29) re_29, SUM(u.re_30) re_30 FROM ukm u WHERE u.bulan>6 AND u.bulan<=9 AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', $session['unitId'])
                ->queryAll();
            }elseif($id == '4'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                SUM(u.re_1) re_1, SUM(u.re_2) re_2, SUM(u.re_3) re_3, SUM(u.re_4) re_4, SUM(u.re_5) re_5, 
                SUM(u.re_6) re_6, SUM(u.re_7) re_7, SUM(u.re_8) re_8, SUM(u.re_9) re_9, SUM(u.re_10) re_10, 
                SUM(u.re_11) re_11, SUM(u.re_12) re_12, SUM(u.re_13) re_13, SUM(u.re_14) re_14, SUM(u.re_15) re_15, 
                SUM(u.re_16) re_16, SUM(u.re_17) re_17, SUM(u.re_18) re_18, SUM(u.re_19) re_19, SUM(u.re_20) re_20, 
                SUM(u.re_21) re_21, SUM(u.re_22) re_22, SUM(u.re_23) re_23, SUM(u.re_24) re_24, SUM(u.re_25) re_25,
                SUM(u.re_26) re_26, SUM(u.re_27) re_27, SUM(u.re_28) re_28, SUM(u.re_29) re_29, SUM(u.re_30) re_30 FROM ukm u WHERE u.bulan>9 AND u.bulan<=12 AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', $session['unitId'])
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
                ->setCellValue('A'.$baseRow, $baseRow-5)
                ->setCellValue('B'.$baseRow, $bidang)
                ->setCellValue('C'.$baseRow, $rowdata['upaya'])
                ->setCellValue('D'.$baseRow, $rowdata['jumlah'])
                ->setCellValue('E'.$baseRow, $datareal[$re])
                ->setCellValue('F'.$baseRow, $session['prosentase'])
                ->setCellValue('G'.$baseRow, $rowdata['jml_ke'])
                ->setCellValue('H'.$baseRow, $rowdata['jml_confirm'])
                ->setCellValue('I'.$baseRow, $rowdata['tenaga_tracer'])
                ->setCellValue('J'.$baseRow, $rowdata['tenaga_surveilans']);
                $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':J' .$baseRow)->applyFromArray($styleArray);
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
            $activeSheet->setCellValue('G'.$baseRow, '=SUM(G' .$firstData. ':G' .$lastData. ')');
            $activeSheet->setCellValue('H'.$baseRow, '=SUM(H' .$firstData. ':H' .$lastData. ')');
            $activeSheet->setCellValue('I'.$baseRow, '=SUM(I' .$firstData. ':I' .$lastData. ')');
            $activeSheet->setCellValue('J'.$baseRow, '=SUM(J' .$firstData. ':J' .$lastData. ')');
            $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':J' .$baseRow)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle('A'.$baseRow. ':J' .$baseRow)->getFont()->setBold(true);
        }

        $spreadsheet->getActiveSheet()->getProtection()->setSheet(true);

        $writer = new Xlsx($spreadsheet);

        $filename = 'export_realisasi_bok_covid_'.strtolower($unit->puskesmas).'_'.strtolower($tribulan).'_'.$period.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function actionExportRealisasiUkm($id)
    {
        if($id == 0){$tribulan = 'TAHUNAN';}
        if($id == 1){$tribulan = 'TRIBULAN I';}
        if($id == 2){$tribulan = 'TRIBULAN II';}
        if($id == 3){$tribulan = 'TRIBULAN III';}
        if($id == 4){$tribulan = 'TRIBULAN IV';}

        $session = Yii::$app->session;
        $period = $session['periodValue'];

        $unit = Unit::findOne(Yii::$app->user->identity->unit_id);

        $data = Yii::$app->db->createCommand('SELECT bd.jenis, bd.bd_desk bidang, uk.uk_desk upaya, MID(uk.uk_nama,4,2) no, IFNULL(p.jumlah,0) jumlah FROM uk_label uk
                LEFT JOIN bd_label bd ON bd.id=uk.bd_id
                LEFT JOIN uk_pagu p ON p.uk_id=uk.id AND p.unit_id=:unit
                WHERE uk.tahun=:tahun AND bd.jenis = "UKM"
                ORDER BY uk.id')
            ->bindValue(':tahun', $session['tahun'])
            ->bindValue(':unit', Yii::$app->user->identity->unit_id)
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

        $activeSheet->setCellValue('A'.'1', 'LAPORAN REALIASASI '.$tribulan.' BOK UKM PUSKESMAS PER BIDANG UPAYA TAHUN ' .$period);
        $activeSheet->setCellValue('A'.'2', 'PUSKESMAS ' .strtoupper($unit->puskesmas));

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

            if($id == '0'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                SUM(u.re_1) re_1, SUM(u.re_2) re_2, SUM(u.re_3) re_3, SUM(u.re_4) re_4, SUM(u.re_5) re_5, 
                SUM(u.re_6) re_6, SUM(u.re_7) re_7, SUM(u.re_8) re_8, SUM(u.re_9) re_9, SUM(u.re_10) re_10, 
                SUM(u.re_11) re_11, SUM(u.re_12) re_12, SUM(u.re_13) re_13, SUM(u.re_14) re_14, SUM(u.re_15) re_15, 
                SUM(u.re_16) re_16, SUM(u.re_17) re_17, SUM(u.re_18) re_18, SUM(u.re_19) re_19, SUM(u.re_20) re_20, 
                SUM(u.re_21) re_21, SUM(u.re_22) re_22, SUM(u.re_23) re_23, SUM(u.re_24) re_24, SUM(u.re_25) re_25,
                SUM(u.re_26) re_26, SUM(u.re_27) re_27, SUM(u.re_28) re_28, SUM(u.re_29) re_29, SUM(u.re_30) re_30 FROM ukm u WHERE u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', Yii::$app->user->identity->unit_id)
                ->queryAll();
            }elseif($id == '1'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                SUM(u.re_1) re_1, SUM(u.re_2) re_2, SUM(u.re_3) re_3, SUM(u.re_4) re_4, SUM(u.re_5) re_5, 
                SUM(u.re_6) re_6, SUM(u.re_7) re_7, SUM(u.re_8) re_8, SUM(u.re_9) re_9, SUM(u.re_10) re_10, 
                SUM(u.re_11) re_11, SUM(u.re_12) re_12, SUM(u.re_13) re_13, SUM(u.re_14) re_14, SUM(u.re_15) re_15, 
                SUM(u.re_16) re_16, SUM(u.re_17) re_17, SUM(u.re_18) re_18, SUM(u.re_19) re_19, SUM(u.re_20) re_20, 
                SUM(u.re_21) re_21, SUM(u.re_22) re_22, SUM(u.re_23) re_23, SUM(u.re_24) re_24, SUM(u.re_25) re_25,
                SUM(u.re_26) re_26, SUM(u.re_27) re_27, SUM(u.re_28) re_28, SUM(u.re_29) re_29, SUM(u.re_30) re_30 FROM ukm u WHERE u.bulan>=1 AND u.bulan<=3 AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', Yii::$app->user->identity->unit_id)
                ->queryAll();
            }elseif($id == '2'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                SUM(u.re_1) re_1, SUM(u.re_2) re_2, SUM(u.re_3) re_3, SUM(u.re_4) re_4, SUM(u.re_5) re_5, 
                SUM(u.re_6) re_6, SUM(u.re_7) re_7, SUM(u.re_8) re_8, SUM(u.re_9) re_9, SUM(u.re_10) re_10, 
                SUM(u.re_11) re_11, SUM(u.re_12) re_12, SUM(u.re_13) re_13, SUM(u.re_14) re_14, SUM(u.re_15) re_15, 
                SUM(u.re_16) re_16, SUM(u.re_17) re_17, SUM(u.re_18) re_18, SUM(u.re_19) re_19, SUM(u.re_20) re_20, 
                SUM(u.re_21) re_21, SUM(u.re_22) re_22, SUM(u.re_23) re_23, SUM(u.re_24) re_24, SUM(u.re_25) re_25,
                SUM(u.re_26) re_26, SUM(u.re_27) re_27, SUM(u.re_28) re_28, SUM(u.re_29) re_29, SUM(u.re_30) re_30 FROM ukm u WHERE u.bulan>3 AND u.bulan<=6 AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', Yii::$app->user->identity->unit_id)
                ->queryAll();
            }elseif($id == '3'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                SUM(u.re_1) re_1, SUM(u.re_2) re_2, SUM(u.re_3) re_3, SUM(u.re_4) re_4, SUM(u.re_5) re_5, 
                SUM(u.re_6) re_6, SUM(u.re_7) re_7, SUM(u.re_8) re_8, SUM(u.re_9) re_9, SUM(u.re_10) re_10, 
                SUM(u.re_11) re_11, SUM(u.re_12) re_12, SUM(u.re_13) re_13, SUM(u.re_14) re_14, SUM(u.re_15) re_15, 
                SUM(u.re_16) re_16, SUM(u.re_17) re_17, SUM(u.re_18) re_18, SUM(u.re_19) re_19, SUM(u.re_20) re_20, 
                SUM(u.re_21) re_21, SUM(u.re_22) re_22, SUM(u.re_23) re_23, SUM(u.re_24) re_24, SUM(u.re_25) re_25,
                SUM(u.re_26) re_26, SUM(u.re_27) re_27, SUM(u.re_28) re_28, SUM(u.re_29) re_29, SUM(u.re_30) re_30 FROM ukm u WHERE u.bulan>6 AND u.bulan<=9 AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', Yii::$app->user->identity->unit_id)
                ->queryAll();
            }elseif($id == '4'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                SUM(u.re_1) re_1, SUM(u.re_2) re_2, SUM(u.re_3) re_3, SUM(u.re_4) re_4, SUM(u.re_5) re_5, 
                SUM(u.re_6) re_6, SUM(u.re_7) re_7, SUM(u.re_8) re_8, SUM(u.re_9) re_9, SUM(u.re_10) re_10, 
                SUM(u.re_11) re_11, SUM(u.re_12) re_12, SUM(u.re_13) re_13, SUM(u.re_14) re_14, SUM(u.re_15) re_15, 
                SUM(u.re_16) re_16, SUM(u.re_17) re_17, SUM(u.re_18) re_18, SUM(u.re_19) re_19, SUM(u.re_20) re_20, 
                SUM(u.re_21) re_21, SUM(u.re_22) re_22, SUM(u.re_23) re_23, SUM(u.re_24) re_24, SUM(u.re_25) re_25,
                SUM(u.re_26) re_26, SUM(u.re_27) re_27, SUM(u.re_28) re_28, SUM(u.re_29) re_29, SUM(u.re_30) re_30 FROM ukm u WHERE u.bulan>9 AND u.bulan<=12 AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', Yii::$app->user->identity->unit_id)
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

        $filename = 'export_realisasi_bok_ukm_'.strtolower($unit->puskesmas).'_'.strtolower($tribulan).'_'.$period.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function actionExportRealisasiUkmAll()
    {
        $session = Yii::$app->session;
        $id = $session['mo'];
        if($id == 0){$tribulan = 'TAHUNAN';}
        if($id == 1){$tribulan = 'TRIBULAN I';}
        if($id == 2){$tribulan = 'TRIBULAN II';}
        if($id == 3){$tribulan = 'TRIBULAN III';}
        if($id == 4){$tribulan = 'TRIBULAN IV';}

        $session = Yii::$app->session;
        $period = $session['periodValue'];

        $unit = Unit::findOne($session['unitId']);

        $data = Yii::$app->db->createCommand('SELECT bd.jenis, bd.bd_desk bidang, uk.uk_desk upaya, MID(uk.uk_nama,4,2) no, IFNULL(p.jumlah,0) jumlah FROM uk_label uk
                LEFT JOIN bd_label bd ON bd.id=uk.bd_id
                LEFT JOIN uk_pagu p ON p.uk_id=uk.id AND p.unit_id=:unit
                WHERE uk.tahun=:tahun AND bd.jenis = "UKM"
                ORDER BY uk.id')
            ->bindValue(':tahun', $session['tahun'])
            ->bindValue(':unit', $session['unitId'])
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

        $activeSheet->setCellValue('A'.'1', 'LAPORAN REALIASASI '.$tribulan.' BOK UKM PUSKESMAS PER BIDANG UPAYA TAHUN ' .$period);
        $activeSheet->setCellValue('A'.'2', 'PUSKESMAS ' .strtoupper($unit->puskesmas));

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

            if($id == '0'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                SUM(u.re_1) re_1, SUM(u.re_2) re_2, SUM(u.re_3) re_3, SUM(u.re_4) re_4, SUM(u.re_5) re_5, 
                SUM(u.re_6) re_6, SUM(u.re_7) re_7, SUM(u.re_8) re_8, SUM(u.re_9) re_9, SUM(u.re_10) re_10, 
                SUM(u.re_11) re_11, SUM(u.re_12) re_12, SUM(u.re_13) re_13, SUM(u.re_14) re_14, SUM(u.re_15) re_15, 
                SUM(u.re_16) re_16, SUM(u.re_17) re_17, SUM(u.re_18) re_18, SUM(u.re_19) re_19, SUM(u.re_20) re_20, 
                SUM(u.re_21) re_21, SUM(u.re_22) re_22, SUM(u.re_23) re_23, SUM(u.re_24) re_24, SUM(u.re_25) re_25,
                SUM(u.re_26) re_26, SUM(u.re_27) re_27, SUM(u.re_28) re_28, SUM(u.re_29) re_29, SUM(u.re_30) re_30 FROM ukm u WHERE u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', $session['unitId'])
                ->queryAll();
            }elseif($id == '1'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                SUM(u.re_1) re_1, SUM(u.re_2) re_2, SUM(u.re_3) re_3, SUM(u.re_4) re_4, SUM(u.re_5) re_5, 
                SUM(u.re_6) re_6, SUM(u.re_7) re_7, SUM(u.re_8) re_8, SUM(u.re_9) re_9, SUM(u.re_10) re_10, 
                SUM(u.re_11) re_11, SUM(u.re_12) re_12, SUM(u.re_13) re_13, SUM(u.re_14) re_14, SUM(u.re_15) re_15, 
                SUM(u.re_16) re_16, SUM(u.re_17) re_17, SUM(u.re_18) re_18, SUM(u.re_19) re_19, SUM(u.re_20) re_20, 
                SUM(u.re_21) re_21, SUM(u.re_22) re_22, SUM(u.re_23) re_23, SUM(u.re_24) re_24, SUM(u.re_25) re_25,
                SUM(u.re_26) re_26, SUM(u.re_27) re_27, SUM(u.re_28) re_28, SUM(u.re_29) re_29, SUM(u.re_30) re_30 FROM ukm u WHERE u.bulan>=1 AND u.bulan<=3 AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', $session['unitId'])
                ->queryAll();
            }elseif($id == '2'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                SUM(u.re_1) re_1, SUM(u.re_2) re_2, SUM(u.re_3) re_3, SUM(u.re_4) re_4, SUM(u.re_5) re_5, 
                SUM(u.re_6) re_6, SUM(u.re_7) re_7, SUM(u.re_8) re_8, SUM(u.re_9) re_9, SUM(u.re_10) re_10, 
                SUM(u.re_11) re_11, SUM(u.re_12) re_12, SUM(u.re_13) re_13, SUM(u.re_14) re_14, SUM(u.re_15) re_15, 
                SUM(u.re_16) re_16, SUM(u.re_17) re_17, SUM(u.re_18) re_18, SUM(u.re_19) re_19, SUM(u.re_20) re_20, 
                SUM(u.re_21) re_21, SUM(u.re_22) re_22, SUM(u.re_23) re_23, SUM(u.re_24) re_24, SUM(u.re_25) re_25,
                SUM(u.re_26) re_26, SUM(u.re_27) re_27, SUM(u.re_28) re_28, SUM(u.re_29) re_29, SUM(u.re_30) re_30 FROM ukm u WHERE u.bulan>3 AND u.bulan<=6 AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', $session['unitId'])
                ->queryAll();
            }elseif($id == '3'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                SUM(u.re_1) re_1, SUM(u.re_2) re_2, SUM(u.re_3) re_3, SUM(u.re_4) re_4, SUM(u.re_5) re_5, 
                SUM(u.re_6) re_6, SUM(u.re_7) re_7, SUM(u.re_8) re_8, SUM(u.re_9) re_9, SUM(u.re_10) re_10, 
                SUM(u.re_11) re_11, SUM(u.re_12) re_12, SUM(u.re_13) re_13, SUM(u.re_14) re_14, SUM(u.re_15) re_15, 
                SUM(u.re_16) re_16, SUM(u.re_17) re_17, SUM(u.re_18) re_18, SUM(u.re_19) re_19, SUM(u.re_20) re_20, 
                SUM(u.re_21) re_21, SUM(u.re_22) re_22, SUM(u.re_23) re_23, SUM(u.re_24) re_24, SUM(u.re_25) re_25,
                SUM(u.re_26) re_26, SUM(u.re_27) re_27, SUM(u.re_28) re_28, SUM(u.re_29) re_29, SUM(u.re_30) re_30 FROM ukm u WHERE u.bulan>6 AND u.bulan<=9 AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', $session['unitId'])
                ->queryAll();
            }elseif($id == '4'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                SUM(u.re_1) re_1, SUM(u.re_2) re_2, SUM(u.re_3) re_3, SUM(u.re_4) re_4, SUM(u.re_5) re_5, 
                SUM(u.re_6) re_6, SUM(u.re_7) re_7, SUM(u.re_8) re_8, SUM(u.re_9) re_9, SUM(u.re_10) re_10, 
                SUM(u.re_11) re_11, SUM(u.re_12) re_12, SUM(u.re_13) re_13, SUM(u.re_14) re_14, SUM(u.re_15) re_15, 
                SUM(u.re_16) re_16, SUM(u.re_17) re_17, SUM(u.re_18) re_18, SUM(u.re_19) re_19, SUM(u.re_20) re_20, 
                SUM(u.re_21) re_21, SUM(u.re_22) re_22, SUM(u.re_23) re_23, SUM(u.re_24) re_24, SUM(u.re_25) re_25,
                SUM(u.re_26) re_26, SUM(u.re_27) re_27, SUM(u.re_28) re_28, SUM(u.re_29) re_29, SUM(u.re_30) re_30 FROM ukm u WHERE u.bulan>9 AND u.bulan<=12 AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', $session['unitId'])
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

        $filename = 'export_realisasi_bok_ukm_'.strtolower($unit->puskesmas).'_'.strtolower($tribulan).'_'.$period.'.xlsx';

        header('Content-Description: File Transfer');   
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function actionLaporanUkm($id)
    {
        $session = Yii::$app->session;
        if($id == 0){$tribulan = 'Tahunan';}
        if($id == 1){$tribulan = 'Tribulan I';}
        if($id == 2){$tribulan = 'Tribulan II';}
        if($id == 3){$tribulan = 'Tribulan III';}
        if($id == 4){$tribulan = 'Tribulan IV';}

        $real = Yii::$app->db->createCommand('SELECT bd.jenis, bd.bd_desk bidang, uk.uk_desk upaya, MID(uk.uk_nama,4,2) no, IFNULL(p.jumlah,0) jumlah FROM uk_label uk
            LEFT JOIN bd_label bd ON bd.id=uk.bd_id
            LEFT JOIN uk_pagu p ON p.uk_id=uk.id AND p.unit_id=:unit
            WHERE uk.tahun=:tahun AND bd.jenis = "UKM"
            ORDER BY uk.id')
        ->bindValue(':tahun', $session['tahun'])
        ->bindValue(':unit', Yii::$app->user->identity->unit_id)
        ->queryAll();

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('ukm', [
                'data' => $real,
                'id' => $id,
                'tribulan' => $tribulan
            ]);
        }else{
            return $this->render('ukm', [
                'data' => $real,
                'id' => $id,
                'tribulan' => $tribulan
            ]);
        }
    }

    public function actionLaporanUkmAll($unit_id,$id,$mo)
    {
        if($id==0){ 
            $bok = 'UKM';
        }else{
            $bok = 'COVID';
        }

        $unit = Unit::findOne($unit_id);

        $session = Yii::$app->session;
        $session['tahun'] = $session['periodValue'];
        $session['unitId'] = $unit_id;
        $session['id'] = $id;
        $session['mo'] = $mo;
        $real = Yii::$app->db->createCommand('SELECT bd.jenis, bd.bd_desk bidang, uk.uk_desk upaya, MID(uk.uk_nama,4,2) no, IFNULL(p.jumlah,0) jumlah FROM uk_label uk
            LEFT JOIN bd_label bd ON bd.id=uk.bd_id
            LEFT JOIN uk_pagu p ON p.uk_id=uk.id AND p.unit_id=:unit
            WHERE uk.tahun=:tahun AND bd.jenis =:bok
            ORDER BY uk.id')
        ->bindValue(':bok', $bok)
        ->bindValue(':tahun', $session['periodValue'])
        ->bindValue(':unit', $unit_id)
        ->queryAll();

        return $this->render('ukmall', [
            'data' => $real,
            'mo' => $mo,
            'puskesmas' => $unit->puskesmas
        ]);

    }

    public function actionGetRealUkm($no, $id, $pagu)
    {
        $session = Yii::$app->session;

        if(Yii::$app->user->identity->unit_id == 'DINKES'){
            $unit = $session['unitId'];
        }else{
            $unit = Yii::$app->user->identity->unit_id;
        }

        if($session['id'] == 0) {
            if($id == '0'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                IFNULL(SUM(u.re_1),0) re_1, IFNULL(SUM(u.re_2),0) re_2, IFNULL(SUM(u.re_3),0) re_3, IFNULL(SUM(u.re_4),0) re_4, IFNULL(SUM(u.re_5),0) re_5, 
                IFNULL(SUM(u.re_6),0) re_6, IFNULL(SUM(u.re_7),0) re_7, IFNULL(SUM(u.re_8),0) re_8, IFNULL(SUM(u.re_9),0) re_9, IFNULL(SUM(u.re_10),0) re_10, 
                IFNULL(SUM(u.re_11),0) re_11, IFNULL(SUM(u.re_12),0) re_12, IFNULL(SUM(u.re_13),0) re_13, IFNULL(SUM(u.re_14),0) re_14, IFNULL(SUM(u.re_15),0) re_15, 
                IFNULL(SUM(u.re_16),0) re_16, IFNULL(SUM(u.re_17),0) re_17, IFNULL(SUM(u.re_18),0) re_18, IFNULL(SUM(u.re_19),0) re_19, IFNULL(SUM(u.re_20),0) re_20, 
                IFNULL(SUM(u.re_21),0) re_21, IFNULL(SUM(u.re_22),0) re_22, IFNULL(SUM(u.re_23),0) re_23, IFNULL(SUM(u.re_24),0) re_24, IFNULL(SUM(u.re_25),0) re_25,
                IFNULL(SUM(u.re_26),0) re_26, IFNULL(SUM(u.re_27),0) re_27, IFNULL(SUM(u.re_28),0) re_28, IFNULL(SUM(u.re_29),0) re_29, IFNULL(SUM(u.re_30),0) re_30 
                FROM ukm u WHERE u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', $unit)
                ->queryAll();
            }elseif($id == '1'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                IFNULL(SUM(u.re_1),0) re_1, IFNULL(SUM(u.re_2),0) re_2, IFNULL(SUM(u.re_3),0) re_3, IFNULL(SUM(u.re_4),0) re_4, IFNULL(SUM(u.re_5),0) re_5, 
                IFNULL(SUM(u.re_6),0) re_6, IFNULL(SUM(u.re_7),0) re_7, IFNULL(SUM(u.re_8),0) re_8, IFNULL(SUM(u.re_9),0) re_9, IFNULL(SUM(u.re_10),0) re_10, 
                IFNULL(SUM(u.re_11),0) re_11, IFNULL(SUM(u.re_12),0) re_12, IFNULL(SUM(u.re_13),0) re_13, IFNULL(SUM(u.re_14),0) re_14, IFNULL(SUM(u.re_15),0) re_15, 
                IFNULL(SUM(u.re_16),0) re_16, IFNULL(SUM(u.re_17),0) re_17, IFNULL(SUM(u.re_18),0) re_18, IFNULL(SUM(u.re_19),0) re_19, IFNULL(SUM(u.re_20),0) re_20, 
                IFNULL(SUM(u.re_21),0) re_21, IFNULL(SUM(u.re_22),0) re_22, IFNULL(SUM(u.re_23),0) re_23, IFNULL(SUM(u.re_24),0) re_24, IFNULL(SUM(u.re_25),0) re_25,
                IFNULL(SUM(u.re_26),0) re_26, IFNULL(SUM(u.re_27),0) re_27, IFNULL(SUM(u.re_28),0) re_28, IFNULL(SUM(u.re_29),0) re_29, IFNULL(SUM(u.re_30),0) re_30 
                FROM ukm u WHERE u.bulan>=1 AND u.bulan<=3 AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', $unit)
                ->queryAll();
            }elseif($id == '2'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                IFNULL(SUM(u.re_1),0) re_1, IFNULL(SUM(u.re_2),0) re_2, IFNULL(SUM(u.re_3),0) re_3, IFNULL(SUM(u.re_4),0) re_4, IFNULL(SUM(u.re_5),0) re_5, 
                IFNULL(SUM(u.re_6),0) re_6, IFNULL(SUM(u.re_7),0) re_7, IFNULL(SUM(u.re_8),0) re_8, IFNULL(SUM(u.re_9),0) re_9, IFNULL(SUM(u.re_10),0) re_10, 
                IFNULL(SUM(u.re_11),0) re_11, IFNULL(SUM(u.re_12),0) re_12, IFNULL(SUM(u.re_13),0) re_13, IFNULL(SUM(u.re_14),0) re_14, IFNULL(SUM(u.re_15),0) re_15, 
                IFNULL(SUM(u.re_16),0) re_16, IFNULL(SUM(u.re_17),0) re_17, IFNULL(SUM(u.re_18),0) re_18, IFNULL(SUM(u.re_19),0) re_19, IFNULL(SUM(u.re_20),0) re_20, 
                IFNULL(SUM(u.re_21),0) re_21, IFNULL(SUM(u.re_22),0) re_22, IFNULL(SUM(u.re_23),0) re_23, IFNULL(SUM(u.re_24),0) re_24, IFNULL(SUM(u.re_25),0) re_25,
                IFNULL(SUM(u.re_26),0) re_26, IFNULL(SUM(u.re_27),0) re_27, IFNULL(SUM(u.re_28),0) re_28, IFNULL(SUM(u.re_29),0) re_29, IFNULL(SUM(u.re_30),0) re_30 
                FROM ukm u WHERE u.bulan>3 AND u.bulan<=6 AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', $unit)
                ->queryAll();
            }elseif($id == '3'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                IFNULL(SUM(u.re_1),0) re_1, IFNULL(SUM(u.re_2),0) re_2, IFNULL(SUM(u.re_3),0) re_3, IFNULL(SUM(u.re_4),0) re_4, IFNULL(SUM(u.re_5),0) re_5, 
                IFNULL(SUM(u.re_6),0) re_6, IFNULL(SUM(u.re_7),0) re_7, IFNULL(SUM(u.re_8),0) re_8, IFNULL(SUM(u.re_9),0) re_9, IFNULL(SUM(u.re_10),0) re_10, 
                IFNULL(SUM(u.re_11),0) re_11, IFNULL(SUM(u.re_12),0) re_12, IFNULL(SUM(u.re_13),0) re_13, IFNULL(SUM(u.re_14),0) re_14, IFNULL(SUM(u.re_15),0) re_15, 
                IFNULL(SUM(u.re_16),0) re_16, IFNULL(SUM(u.re_17),0) re_17, IFNULL(SUM(u.re_18),0) re_18, IFNULL(SUM(u.re_19),0) re_19, IFNULL(SUM(u.re_20),0) re_20, 
                IFNULL(SUM(u.re_21),0) re_21, IFNULL(SUM(u.re_22),0) re_22, IFNULL(SUM(u.re_23),0) re_23, IFNULL(SUM(u.re_24),0) re_24, IFNULL(SUM(u.re_25),0) re_25,
                IFNULL(SUM(u.re_26),0) re_26, IFNULL(SUM(u.re_27),0) re_27, IFNULL(SUM(u.re_28),0) re_28, IFNULL(SUM(u.re_29),0) re_29, IFNULL(SUM(u.re_30),0) re_30 
                FROM ukm u WHERE u.bulan>6 AND u.bulan<=9 AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', $unit)
                ->queryAll();
            }elseif($id == '4'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                IFNULL(SUM(u.re_1),0) re_1, IFNULL(SUM(u.re_2),0) re_2, IFNULL(SUM(u.re_3),0) re_3, IFNULL(SUM(u.re_4),0) re_4, IFNULL(SUM(u.re_5),0) re_5, 
                IFNULL(SUM(u.re_6),0) re_6, IFNULL(SUM(u.re_7),0) re_7, IFNULL(SUM(u.re_8),0) re_8, IFNULL(SUM(u.re_9),0) re_9, IFNULL(SUM(u.re_10),0) re_10, 
                IFNULL(SUM(u.re_11),0) re_11, IFNULL(SUM(u.re_12),0) re_12, IFNULL(SUM(u.re_13),0) re_13, IFNULL(SUM(u.re_14),0) re_14, IFNULL(SUM(u.re_15),0) re_15, 
                IFNULL(SUM(u.re_16),0) re_16, IFNULL(SUM(u.re_17),0) re_17, IFNULL(SUM(u.re_18),0) re_18, IFNULL(SUM(u.re_19),0) re_19, IFNULL(SUM(u.re_20),0) re_20, 
                IFNULL(SUM(u.re_21),0) re_21, IFNULL(SUM(u.re_22),0) re_22, IFNULL(SUM(u.re_23),0) re_23, IFNULL(SUM(u.re_24),0) re_24, IFNULL(SUM(u.re_25),0) re_25,
                IFNULL(SUM(u.re_26),0) re_26, IFNULL(SUM(u.re_27),0) re_27, IFNULL(SUM(u.re_28),0) re_28, IFNULL(SUM(u.re_29),0) re_29, IFNULL(SUM(u.re_30),0) re_30 
                FROM ukm u WHERE u.bulan>9 AND u.bulan<=12 AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', $unit)
                ->queryAll();
            }
        }else{
            // $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
            // IFNULL(SUM(u.re_1),0) re_1, IFNULL(SUM(u.re_2),0) re_2, IFNULL(SUM(u.re_3),0) re_3, IFNULL(SUM(u.re_4),0) re_4, IFNULL(SUM(u.re_5),0) re_5, 
            // IFNULL(SUM(u.re_6),0) re_6, IFNULL(SUM(u.re_7),0) re_7, IFNULL(SUM(u.re_8),0) re_8, IFNULL(SUM(u.re_9),0) re_9, IFNULL(SUM(u.re_10),0) re_10, 
            // IFNULL(SUM(u.re_11),0) re_11, IFNULL(SUM(u.re_12),0) re_12, IFNULL(SUM(u.re_13),0) re_13, IFNULL(SUM(u.re_14),0) re_14, IFNULL(SUM(u.re_15),0) re_15, 
            // IFNULL(SUM(u.re_16),0) re_16, IFNULL(SUM(u.re_17),0) re_17, IFNULL(SUM(u.re_18),0) re_18, IFNULL(SUM(u.re_19),0) re_19, IFNULL(SUM(u.re_20),0) re_20, 
            // IFNULL(SUM(u.re_21),0) re_21, IFNULL(SUM(u.re_22),0) re_22, IFNULL(SUM(u.re_23),0) re_23, IFNULL(SUM(u.re_24),0) re_24, IFNULL(SUM(u.re_25),0) re_25,
            // IFNULL(SUM(u.re_26),0) re_26, IFNULL(SUM(u.re_27),0) re_27, IFNULL(SUM(u.re_28),0) re_28, IFNULL(SUM(u.re_29),0) re_29, IFNULL(SUM(u.re_30),0) re_30 
            // FROM ukm u WHERE u.bulan=:bulan AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
            // ->bindValue(':bulan', $id)
            // ->bindValue(':tahun', $session['tahun'])
            // ->bindValue(':unit', $unit)
            // ->queryAll();

            if($id == '0'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                IFNULL(SUM(u.re_1),0) re_1, IFNULL(SUM(u.re_2),0) re_2, IFNULL(SUM(u.re_3),0) re_3, IFNULL(SUM(u.re_4),0) re_4, IFNULL(SUM(u.re_5),0) re_5, 
                IFNULL(SUM(u.re_6),0) re_6, IFNULL(SUM(u.re_7),0) re_7, IFNULL(SUM(u.re_8),0) re_8, IFNULL(SUM(u.re_9),0) re_9, IFNULL(SUM(u.re_10),0) re_10, 
                IFNULL(SUM(u.re_11),0) re_11, IFNULL(SUM(u.re_12),0) re_12, IFNULL(SUM(u.re_13),0) re_13, IFNULL(SUM(u.re_14),0) re_14, IFNULL(SUM(u.re_15),0) re_15, 
                IFNULL(SUM(u.re_16),0) re_16, IFNULL(SUM(u.re_17),0) re_17, IFNULL(SUM(u.re_18),0) re_18, IFNULL(SUM(u.re_19),0) re_19, IFNULL(SUM(u.re_20),0) re_20, 
                IFNULL(SUM(u.re_21),0) re_21, IFNULL(SUM(u.re_22),0) re_22, IFNULL(SUM(u.re_23),0) re_23, IFNULL(SUM(u.re_24),0) re_24, IFNULL(SUM(u.re_25),0) re_25,
                IFNULL(SUM(u.re_26),0) re_26, IFNULL(SUM(u.re_27),0) re_27, IFNULL(SUM(u.re_28),0) re_28, IFNULL(SUM(u.re_29),0) re_29, IFNULL(SUM(u.re_30),0) re_30 
                FROM ukm u WHERE u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', $unit)
                ->queryAll();
            }elseif($id == '1'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                IFNULL(SUM(u.re_1),0) re_1, IFNULL(SUM(u.re_2),0) re_2, IFNULL(SUM(u.re_3),0) re_3, IFNULL(SUM(u.re_4),0) re_4, IFNULL(SUM(u.re_5),0) re_5, 
                IFNULL(SUM(u.re_6),0) re_6, IFNULL(SUM(u.re_7),0) re_7, IFNULL(SUM(u.re_8),0) re_8, IFNULL(SUM(u.re_9),0) re_9, IFNULL(SUM(u.re_10),0) re_10, 
                IFNULL(SUM(u.re_11),0) re_11, IFNULL(SUM(u.re_12),0) re_12, IFNULL(SUM(u.re_13),0) re_13, IFNULL(SUM(u.re_14),0) re_14, IFNULL(SUM(u.re_15),0) re_15, 
                IFNULL(SUM(u.re_16),0) re_16, IFNULL(SUM(u.re_17),0) re_17, IFNULL(SUM(u.re_18),0) re_18, IFNULL(SUM(u.re_19),0) re_19, IFNULL(SUM(u.re_20),0) re_20, 
                IFNULL(SUM(u.re_21),0) re_21, IFNULL(SUM(u.re_22),0) re_22, IFNULL(SUM(u.re_23),0) re_23, IFNULL(SUM(u.re_24),0) re_24, IFNULL(SUM(u.re_25),0) re_25,
                IFNULL(SUM(u.re_26),0) re_26, IFNULL(SUM(u.re_27),0) re_27, IFNULL(SUM(u.re_28),0) re_28, IFNULL(SUM(u.re_29),0) re_29, IFNULL(SUM(u.re_30),0) re_30 
                FROM ukm u WHERE u.bulan>=1 AND u.bulan<=3 AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', $unit)
                ->queryAll();
            }elseif($id == '2'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                IFNULL(SUM(u.re_1),0) re_1, IFNULL(SUM(u.re_2),0) re_2, IFNULL(SUM(u.re_3),0) re_3, IFNULL(SUM(u.re_4),0) re_4, IFNULL(SUM(u.re_5),0) re_5, 
                IFNULL(SUM(u.re_6),0) re_6, IFNULL(SUM(u.re_7),0) re_7, IFNULL(SUM(u.re_8),0) re_8, IFNULL(SUM(u.re_9),0) re_9, IFNULL(SUM(u.re_10),0) re_10, 
                IFNULL(SUM(u.re_11),0) re_11, IFNULL(SUM(u.re_12),0) re_12, IFNULL(SUM(u.re_13),0) re_13, IFNULL(SUM(u.re_14),0) re_14, IFNULL(SUM(u.re_15),0) re_15, 
                IFNULL(SUM(u.re_16),0) re_16, IFNULL(SUM(u.re_17),0) re_17, IFNULL(SUM(u.re_18),0) re_18, IFNULL(SUM(u.re_19),0) re_19, IFNULL(SUM(u.re_20),0) re_20, 
                IFNULL(SUM(u.re_21),0) re_21, IFNULL(SUM(u.re_22),0) re_22, IFNULL(SUM(u.re_23),0) re_23, IFNULL(SUM(u.re_24),0) re_24, IFNULL(SUM(u.re_25),0) re_25,
                IFNULL(SUM(u.re_26),0) re_26, IFNULL(SUM(u.re_27),0) re_27, IFNULL(SUM(u.re_28),0) re_28, IFNULL(SUM(u.re_29),0) re_29, IFNULL(SUM(u.re_30),0) re_30 
                FROM ukm u WHERE u.bulan>3 AND u.bulan<=6 AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', $unit)
                ->queryAll();
            }elseif($id == '3'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                IFNULL(SUM(u.re_1),0) re_1, IFNULL(SUM(u.re_2),0) re_2, IFNULL(SUM(u.re_3),0) re_3, IFNULL(SUM(u.re_4),0) re_4, IFNULL(SUM(u.re_5),0) re_5, 
                IFNULL(SUM(u.re_6),0) re_6, IFNULL(SUM(u.re_7),0) re_7, IFNULL(SUM(u.re_8),0) re_8, IFNULL(SUM(u.re_9),0) re_9, IFNULL(SUM(u.re_10),0) re_10, 
                IFNULL(SUM(u.re_11),0) re_11, IFNULL(SUM(u.re_12),0) re_12, IFNULL(SUM(u.re_13),0) re_13, IFNULL(SUM(u.re_14),0) re_14, IFNULL(SUM(u.re_15),0) re_15, 
                IFNULL(SUM(u.re_16),0) re_16, IFNULL(SUM(u.re_17),0) re_17, IFNULL(SUM(u.re_18),0) re_18, IFNULL(SUM(u.re_19),0) re_19, IFNULL(SUM(u.re_20),0) re_20, 
                IFNULL(SUM(u.re_21),0) re_21, IFNULL(SUM(u.re_22),0) re_22, IFNULL(SUM(u.re_23),0) re_23, IFNULL(SUM(u.re_24),0) re_24, IFNULL(SUM(u.re_25),0) re_25,
                IFNULL(SUM(u.re_26),0) re_26, IFNULL(SUM(u.re_27),0) re_27, IFNULL(SUM(u.re_28),0) re_28, IFNULL(SUM(u.re_29),0) re_29, IFNULL(SUM(u.re_30),0) re_30 
                FROM ukm u WHERE u.bulan>6 AND u.bulan<=9 AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', $unit)
                ->queryAll();
            }elseif($id == '4'){
                $real = Yii::$app->db->createCommand('SELECT u.unit_id, 
                IFNULL(SUM(u.re_1),0) re_1, IFNULL(SUM(u.re_2),0) re_2, IFNULL(SUM(u.re_3),0) re_3, IFNULL(SUM(u.re_4),0) re_4, IFNULL(SUM(u.re_5),0) re_5, 
                IFNULL(SUM(u.re_6),0) re_6, IFNULL(SUM(u.re_7),0) re_7, IFNULL(SUM(u.re_8),0) re_8, IFNULL(SUM(u.re_9),0) re_9, IFNULL(SUM(u.re_10),0) re_10, 
                IFNULL(SUM(u.re_11),0) re_11, IFNULL(SUM(u.re_12),0) re_12, IFNULL(SUM(u.re_13),0) re_13, IFNULL(SUM(u.re_14),0) re_14, IFNULL(SUM(u.re_15),0) re_15, 
                IFNULL(SUM(u.re_16),0) re_16, IFNULL(SUM(u.re_17),0) re_17, IFNULL(SUM(u.re_18),0) re_18, IFNULL(SUM(u.re_19),0) re_19, IFNULL(SUM(u.re_20),0) re_20, 
                IFNULL(SUM(u.re_21),0) re_21, IFNULL(SUM(u.re_22),0) re_22, IFNULL(SUM(u.re_23),0) re_23, IFNULL(SUM(u.re_24),0) re_24, IFNULL(SUM(u.re_25),0) re_25,
                IFNULL(SUM(u.re_26),0) re_26, IFNULL(SUM(u.re_27),0) re_27, IFNULL(SUM(u.re_28),0) re_28, IFNULL(SUM(u.re_29),0) re_29, IFNULL(SUM(u.re_30),0) re_30 
                FROM ukm u WHERE u.bulan>9 AND u.bulan<=12 AND u.tahun=:tahun AND u.unit_id=:unit GROUP BY u.unit_id')
                ->bindValue(':tahun', $session['tahun'])
                ->bindValue(':unit', $unit)
                ->queryAll();
            }
        }

        if (!empty($real)){
            foreach($real as $datareal);

            $re = 're_' .$no;
            
            if($pagu > 0 && $datareal[$re] > 0){
                $session['prosentase'] = number_format($datareal[$re] / $pagu * 100,2,",",".");
            }else{
                $session['prosentase'] = '0';
            }

            // return number_format($datareal[$re],0,"",".");
            return $datareal[$re];
        }else{
            $session['prosentase'] = '0';
            // return number_format(0,0,"",".");
            return 0;
        }
    }
}
