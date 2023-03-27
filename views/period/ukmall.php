<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\web\Session;
use yii\helpers\Url;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Ukm */

if($session['id'] == '0'){
    $this->title = 'Laporan BOK UKM Tribulan';
}else{
    $this->title = 'Laporan BOK Covid Bulanan';
}
$this->params['breadcrumbs'][] = ['label' => 'Data POA Puskesmas', 'url' => ['period/list', 'period' => $session['periodValue']]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

?>
<div class="ukmall-view">
    <div class="row">
        <div class="col-sm-4">
            <?php if ($session['id'] == '0') {?>
                <?= Html::dropDownList('tahun', null, [ '1' => 'Tribulan I', '2' => 'Tribulan II', '3' => 'Tribulan III', '4' => 'Tribulan IV'],
                [
                    // 'prompt'=>'Pilih Periode',
                    'options'=>[$session['mo']=>['Selected'=>true]],
                    // 'style' => 'width:80px; margin-right:5px !important;', 
                    'onchange'=>'
                        $.pjax.reload({
                            url: "'.Url::to(['period/laporan-ukm-all', 'id' => $session['id']]).'&mo="+$(this).val(),
                            container: "#pjax-ukmall",
                            timeout: 1000,
                        });',
                    'class'=>'form-control']) 
                ?>
            <?php }else{ ?>
                <?= Html::dropDownList('tahun', null, ['1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
                '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
                '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember', ],
                [
                    // 'prompt'=>'Pilih Periode',
                    'options'=>[$session['mo']=>['Selected'=>true]],
                    // 'style' => 'width:80px; margin-right:5px !important;', 
                    'onchange'=>'
                        $.pjax.reload({
                            url: "'.Url::to(['period/laporan-ukm-all', 'id' => $session['id']]).'&mo="+$(this).val(),
                            container: "#pjax-ukmall",
                            timeout: 1000,
                        });',
                    'class'=>'form-control']) 
                ?>
            <?php } ?>
        </div>
        <div class="col-sm-2 pull-right">
            <?php if ($session['id'] == '0') {?>
                <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export Excel', ['export-realisasi-ukm-all'], ['class' => 'btn btn-success pull-right', 'target' => '_blank']) ?>
            <?php }else{ ?>
                <?= Html::a('<span class="glyphicon glyphicon-export"></span> Export Excel', ['export-realisasi-all'], ['class' => 'btn btn-success pull-right', 'target' => '_blank']) ?>
            <?php } ?>
        </div>
    </div>

    <?php Pjax::begin(['id' => 'pjax-ukmall']) ?>
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
                $no=1;
                foreach($data as $datareal){ ?>

                <tr>
                    <td style="text-align:center;border:1px solid grey;"><?php echo $no ?></td>
                    <td style="text-align:left;border:1px solid grey;"><?= $datareal['bidang'] ?></td>
                    <td style="text-align:left;border:1px solid grey;"><?= $datareal['upaya'] ?></td>


                    <td style="text-align:right;border:1px solid grey;">
                        <H5><?= number_format($datareal['jumlah'],0,"",".") ?></H5>
                    </td>

                    <td style="text-align:right;border:1px solid grey;">
                        <H5><?php echo Yii::$app->runAction('period/get-real-ukm', ['id' => $mo, 'no' => $datareal['no'], 'pagu' => $datareal['jumlah']]); ?></H5>
                    </td>

                    <td style="text-align:right;border:1px solid grey;">
                        <H5><?php echo $session['prosentase']; ?></H5>
                    </td>
                </tr>
            <?php 
                $no = $no+1;
                } 
            ?>
        </tbody>
    </table>
    </div>
    
</div>

<?php
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

<?php Pjax::end() ?>
