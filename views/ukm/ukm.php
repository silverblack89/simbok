<?php

use yii\helpers\Html;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Ukm */

$this->title = 'Laporan BOK UKM ' .$tribulan;
$this->params['breadcrumbs'][] = ['label' => $session['periodValue'], 'url' => ['period/create', 'p' => 'def']];
$this->params['breadcrumbs'][] = ['label' => 'Realisasi', 'url' => ['index', 'tahun' => $session['tahun']]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

?>
<div class="ukm-view">
    <div class="row">
        <div class="col-sm-2 pull-right">
            <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export Excel', ['export-realisasi-ukm', 'id' => $id], ['class' => 'btn btn-success pull-right', 'target' => '_blank']) ?>
        </div>
    </div>

    <div style="overflow-x:auto; margin-top:10px">
    <table id="tabel1" class="table table-bordered">
        <thead>
            <tr>
                <th style="text-align:center;border:1px solid grey">NO</th>
                <th style="text-align:center;border:1px solid grey">BIDANG</th>
                <th style="text-align:center;border:1px solid grey">UPAYA KESEHATAN</th>
                <th style="text-align:center;border:1px solid grey">PAGU</th>
                <th style="text-align:center;border:1px solid grey">REALISASI</th>
                <th style="text-align:center;border:1px solid grey">%</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $total=0;
                $no=1;
                foreach($data as $datareal){ 
                    $real = Yii::$app->runAction('ukm/get-real-ukm', ['id' => $id, 'no' => $datareal['no'], 'pagu' => $datareal['jumlah']]);   
                ?>

                <tr>
                    <td style="text-align:center;border:1px solid grey;"><?php echo $no ?></td>
                    <td style="text-align:left;border:1px solid grey;"><?= $datareal['bidang'] ?></td>
                    <td style="text-align:left;border:1px solid grey;"><?= $datareal['upaya'] ?></td>


                    <td style="text-align:right;border:1px solid grey;">
                        <H5><?= number_format($datareal['jumlah'],0,"",".") ?></H5>
                    </td>

                    <td style="text-align:right;border:1px solid grey;">
                        <H5><?php echo number_format($real,0,",",".") ?></H5>
                    </td>

                    <td style="text-align:right;border:1px solid grey;">
                        <H5><?php echo $session['prosentase']; ?></H5>
                    </td>
                </tr>
            <?php 
                $total = $total+$real;
                $no = $no+1;
                } 
            ?>
            <tr>
                <td colspan=4 style="text-align:center;border:1px solid grey;">Total Realisasi</td>
                <td style="text-align:right;border:1px solid grey;"><?php echo number_format($total,0,",",".") ?></td>
                <td style="text-align:left;border:1px solid grey;"></td>
            </tr>
        </tbody>
    </table>
    </div>
</div>

<?php
    echo '
    <script type="text/javascript">
        
    </script>';

$js=<<< JS
    $(document).ready(function () {	
        $('#tabel1').each(function () {
            var Column_number_to_Merge = 2
 
            // Previous_TD holds the first instance of same td. Initially first TD=null.
            var Previous_TD = null;
            var i = 1;
            $('tbody',this).find('tr').each(function () {
                // find the correct td of the correct column
                // we are considering the table column 1, You can apply on any table column
                var Current_td = $(this).find('td:nth-child(' + Column_number_to_Merge + ')');
                 
                if (Previous_TD == null) {
                    // for first row
                    Previous_TD = Current_td;
                    i = 1;
                } 
                else if (Current_td.text() == Previous_TD.text()) {
                    // the current td is identical to the previous row td
                    // remove the current td
                    Current_td.remove();
                    // increment the rowspan attribute of the first row td instance
                    Previous_TD.attr('rowspan', i + 1);
                    i = i + 1;
                } 
                else {
                    // means new value found in current td. So initialize counter variable i
                    Previous_TD = Current_td;
                    i = 1;
                }
            });
        });		
    });
JS;
$this->registerJs($js, yii\web\View::POS_READY);
// $this->registerJs($js, yii\web\View::POS_HEAD);
?>
