<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Ukm */
/* @var $form yii\widgets\ActiveForm */

if($jan>=1){$jan = false;$jan_style = '';}else{$jan = true;$jan_style = 'color:silver';}
if($feb>=1){$feb = false;$feb_style = '';}else{$feb = true;$feb_style = 'color:silver';}
if($mar>=1){$mar = false;$mar_style = '';}else{$mar = true;$mar_style = 'color:silver';}
if($apr>=1){$apr = false;$apr_style = '';}else{$apr = true;$apr_style = 'color:silver';}
if($mei>=1){$mei = false;$mei_style = '';}else{$mei = true;$mei_style = 'color:silver';}
if($jun>=1){$jun = false;$jun_style = '';}else{$jun = true;$jun_style = 'color:silver';}
if($jul>=1){$jul = false;$jul_style = '';}else{$jul = true;$jul_style = 'color:silver';}
if($agu>=1){$agu = false;$agu_style = '';}else{$agu = true;$agu_style = 'color:silver';}
if($sep>=1){$sep = false;$sep_style = '';}else{$sep = true;$sep_style = 'color:silver';}
if($okt>=1){$okt = false;$okt_style = '';}else{$okt = true;$okt_style = 'color:silver';}
if($nov>=1){$nov = false;$nov_style = '';}else{$nov = true;$nov_style = 'color:silver';}
if($des>=1){$des = false;$des_style = '';}else{$des = true;$des_style = 'color:silver';}

?>

<div class="ukm-form" id="ukmform">
<div class="panel panel-primary">
        <div class="panel-heading">
                <h3 class="panel-title">Detail Data</h3>
        </div>
        <div class="panel-body">

            <?php $form = ActiveForm::begin(['options' => ['autocomplete' => 'off', 'id' => 'ukm-form']]); ?>

            <div class="row" style="margin-bottom:-15px;">
                <div class="col-md-2">
                    <?= $form->field($model, 'bulan')->dropDownList(['1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
                        '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
                        '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember', ]
                        ,[
                            'id' => 'select', 
                            'prompt'=>'Pilih Bulan', 
                            // 'disabled'=>$disabled,
                            'options'=>['1'=>['disabled'=>$jan, 'style' => $jan_style],
                                    '2'=>['disabled'=>$feb, 'style' => $feb_style],
                                    '3'=>['disabled'=>$mar, 'style' => $mar_style],
                                    '4'=>['disabled'=>$apr, 'style' => $apr_style],
                                    '5'=>['disabled'=>$mei, 'style' => $mei_style],
                                    '6'=>['disabled'=>$jun, 'style' => $jun_style],
                                    '7'=>['disabled'=>$jul, 'style' => $jul_style],
                                    '8'=>['disabled'=>$agu, 'style' => $agu_style],
                                    '9'=>['disabled'=>$sep, 'style' => $sep_style],
                                    '10'=>['disabled'=>$okt, 'style' => $okt_style],
                                    '11'=>['disabled'=>$nov, 'style' => $nov_style],
                                    '12'=>['disabled'=>$des, 'style' => $des_style],
                                    $session['bulan']=>['Selected'=>true],
                            ],
                            'onchange'=>'window.location=window.origin+"'.Url::to(['month-total']).'?bulan="+$(this).val()', //window.origin+"'.Url::to(['month-total', 'tahun' => $tahun]).'"&bulan=$(this).val()
                            'class'=>'form-control'
                        ]
                        )->label(false)
                    ?>
                </div>

                <div class="col-md-2">
                    <h4>Total SP2D bulanan</h4>
                </div>

                <div class="col-md-2">
                    <td style="text-align:right;border:1px solid grey;">
                        <?= $form->field($model, 'total_sp2d_bulanan')->widget(\yii\widgets\MaskedInput::class, [
                            'clientOptions' => [
                                'alias' =>  'numeric',
                                'groupSeparator' => '.',
                                'groupSize'=>3,
                                'radixPoint' => ',',
                                'autoGroup' => true,
                                'removeMaskOnSubmit' => true,
                            ],
                            'options' => ['disabled' => true,]
                        ])->label(false); ?>
                    </td>
                </div>
            </div>
            
            <?= $form->field($model, 'tahun')->hiddenInput(['maxlength' => true])->label(false) ?>

            <?= $form->field($model, 'unit_id')->hiddenInput(['maxlength' => true])->label(false) ?>

            <div class="table-responsive" style="margin-top:20px">
                <table id="tabel1" class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="text-align:center;border:1px solid grey">NO</th>
                            <th style="text-align:center;border:1px solid grey">BIDANG</th>
                            <th style="text-align:center;border:1px solid grey">UPAYA KESEHATAN</th>
                            <th style="text-align:center;border:1px solid grey">PAGU</th>
                            <th style="text-align:center;border:1px solid grey">REALISASI</th>
                            <!-- <th style="text-align:center;border:1px solid grey">%</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $no=1;
                            foreach($listuk as $datauk){ ?>

                            <tr>
                                <td style="text-align:center;border:1px solid grey;"><?php echo $no ?></td>
                                <td style="text-align:left;border:1px solid grey;"><?= $datauk['bd_desk'] ?></td>
                                <td style="text-align:left;border:1px solid grey;">
                                    <?= $datauk['uk_desk'] ?>

                                    <?php if($datauk['jenis'] == 'COVID'){ ?>
                                        <!-- <?= Html::a('<span class="glyphicon glyphicon-plus"></span> ', ['capout/confirm', 'no' => $datauk['no'], 'co1' => $datauk['co_1'], 'co2' => $datauk['co_2'], 'co3' => $datauk['co_3'], 'co4' => $datauk['co_4']], ['class' => 'btn btn-link pull-right']) ?> -->
                                        <?= Html::button('<span class="glyphicon glyphicon-stats"></span> Output', ['value' => Url::to(['capout/confirm', 'no' => $datauk['no'], 'co1' => $datauk['co_1'], 'co2' => $datauk['co_2'], 'co3' => $datauk['co_3'], 'co4' => $datauk['co_4']]), 'class' => 'showModalButton btn btn-primary pull-right']) ?>
                                    <?php } ?>
                                </td>


                                <td style="text-align:right;border:1px solid grey;">
                                    <H5><?= number_format($datauk['jumlah'],0,"",".") ?></H5>
                                </td>
                                
                                <td style="text-align:right;border:1px solid grey;">
                                    <?= $form->field($model, 're_'.substr($datauk['uk_nama'],3,2))->widget(\yii\widgets\MaskedInput::class, [
                                        'clientOptions' => [
                                            'alias' =>  'decimal',
                                            'groupSeparator' => '.',
                                            'radixPoint' => ',',
                                            'autoGroup' => true,
                                            'removeMaskOnSubmit' => false
                                        ],
                                        // 'options' => [
                                        //     'value' => 0,
                                        // ]
                                    ])->label(false); ?>
                                </td>
                            </tr>
                        <?php 
                            $no = $no+1;
                            } 
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="form-group">
                <!-- <?= Html::button('<span class="glyphicon glyphicon-check"></span> Validasi', ['value' => Url::to(['ukm/validate']), 'class' => 'showModalButton btn btn-primary']) ?> -->
                <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> Simpan', ['class' => 'btn btn-success pull-right']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?php 
    Modal::begin([
            // 'header'=>'',
            'id'=>'modal',
            'size'=>'modal-sm',
            'clientOptions' => ['backdrop' => 'dinamis', 'keyboard' => FALSE],
            // 'footer' => ''
        ]);
    echo "<div id='modalContent'></div>";
    Modal::end();
?>

</div>

<?php
    echo '
    <script type="text/javascript">
        var sp2d='.$model->total_sp2d_bulanan.';
        var dblbln="'.$session['dblbln'].'";

    </script>';

    $this->registerJs('
        $("#ukmform").on("shown.bs.modal", function (e) {
            if (document.getElementById("ukm-re_1") !== null) createCookie("re1",document.getElementById("ukm-re_1").value, "1");
            if (document.getElementById("ukm-re_2") !== null) createCookie("re2",document.getElementById("ukm-re_2").value, "1");
            if (document.getElementById("ukm-re_3") !== null) createCookie("re3",document.getElementById("ukm-re_3").value, "1");
            if (document.getElementById("ukm-re_4") !== null) createCookie("re4",document.getElementById("ukm-re_4").value, "1");
            if (document.getElementById("ukm-re_5") !== null) createCookie("re5",document.getElementById("ukm-re_5").value, "1");

            if (document.getElementById("ukm-re_6") !== null) createCookie("re6",document.getElementById("ukm-re_6").value, "1");
            if (document.getElementById("ukm-re_7") !== null) createCookie("re7",document.getElementById("ukm-re_7").value, "1");
            if (document.getElementById("ukm-re_8") !== null) createCookie("re8",document.getElementById("ukm-re_8").value, "1");
            if (document.getElementById("ukm-re_9") !== null) createCookie("re9",document.getElementById("ukm-re_9").value, "1");
            if (document.getElementById("ukm-re_10") !== null) createCookie("re10",document.getElementById("ukm-re_10").value, "1");

            if (document.getElementById("ukm-re_11") !== null) createCookie("re11",document.getElementById("ukm-re_11").value, "1");
            if (document.getElementById("ukm-re_12") !== null) createCookie("re12",document.getElementById("ukm-re_12").value, "1");
            if (document.getElementById("ukm-re_13") !== null) createCookie("re13",document.getElementById("ukm-re_13").value, "1");
            if (document.getElementById("ukm-re_14") !== null) createCookie("re14",document.getElementById("ukm-re_14").value, "1");
            if (document.getElementById("ukm-re_15") !== null) createCookie("re15",document.getElementById("ukm-re_15").value, "1");

            if (document.getElementById("ukm-re_16") !== null) createCookie("re16",document.getElementById("ukm-re_16").value, "1");
            if (document.getElementById("ukm-re_17") !== null) createCookie("re17",document.getElementById("ukm-re_17").value, "1");
            if (document.getElementById("ukm-re_18") !== null) createCookie("re18",document.getElementById("ukm-re_18").value, "1");
            if (document.getElementById("ukm-re_19") !== null) createCookie("re19",document.getElementById("ukm-re_19").value, "1");
            if (document.getElementById("ukm-re_20") !== null) createCookie("re20",document.getElementById("ukm-re_20").value, "1");
            
            if (document.getElementById("ukm-re_21") !== null) createCookie("re21",document.getElementById("ukm-re_21").value, "1");
            if (document.getElementById("ukm-re_22") !== null) createCookie("re22",document.getElementById("ukm-re_22").value, "1");
            if (document.getElementById("ukm-re_23") !== null) createCookie("re23",document.getElementById("ukm-re_23").value, "1");
            if (document.getElementById("ukm-re_24") !== null) createCookie("re24",document.getElementById("ukm-re_24").value, "1");
            if (document.getElementById("ukm-re_25") !== null) createCookie("re25",document.getElementById("ukm-re_25").value, "1");

            if (document.getElementById("ukm-re_26") !== null) createCookie("re26",document.getElementById("ukm-re_26").value, "1");
            if (document.getElementById("ukm-re_27") !== null) createCookie("re27",document.getElementById("ukm-re_27").value, "1");
            if (document.getElementById("ukm-re_28") !== null) createCookie("re28",document.getElementById("ukm-re_28").value, "1");
            if (document.getElementById("ukm-re_29") !== null) createCookie("re29",document.getElementById("ukm-re_29").value, "1");
            if (document.getElementById("ukm-re_30") !== null) createCookie("re30",document.getElementById("ukm-re_30").value, "1");

            baseUrl = window.origin;
            var link = baseUrl+"'.Url::to(['capout/temp']).'";

            $.get(link);

            // Function to create the cookie 
            function createCookie(name, value, days) { 
                var expires; 
                
                if (days) { 
                    var date = new Date(); 
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000)); 
                    expires = "; expires=" + date.toGMTString(); 
                } 
                else { 
                    expires = ""; 
                } 
                
                document.cookie = escape(name) + "=" +  
                    escape(value) + expires + "; path=/"; 
            } 
        });
    ');

$js=<<< JS
    $("#kecamatan").on("change", function (e) {
        createCookie("idkec", $(this).val(), "1");

        baseUrl = window.origin;
        var link = baseUrl+"'.Url::to(['get-kelurahan']).'";

        $.get(link, function(data) {
            if(data=="true"){
                document.getElementById("desa").disabled = true;
            }else{
                document.getElementById("desa").disabled = false;
            }
        });

        // Function to create the cookie 
        function createCookie(name, value, days) { 
            var expires; 
            
            if (days) { 
                var date = new Date(); 
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000)); 
                expires = "; expires=" + date.toGMTString(); 
            } 
            else { 
                expires = ""; 
            } 
            
            document.cookie = escape(name) + "=" +  
                escape(value) + expires + "; path=/"; 
        } 
    });

    $(document).ready(function () {	
        $('#tabel1').each(function () {
            var Column_number_to_Merge = 2;
 
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

    $('#ukm-form').on('beforeSubmit', function (e) {
        if (dblbln == true){
            alert('Bulan yang dipilih sudah dientri sebelumnya.');
            return false;
        }
        var re1 = parseInt(document.getElementById("ukm-re_1").value.replaceAll(".","")) || 0;
        var re2 = parseInt(document.getElementById("ukm-re_2").value.replaceAll(".","")) || 0;
        var re3 = parseInt(document.getElementById("ukm-re_3").value.replaceAll(".","")) || 0;
        var re4 = parseInt(document.getElementById("ukm-re_4").value.replaceAll(".","")) || 0;
        var re5 = parseInt(document.getElementById("ukm-re_5").value.replaceAll(".","")) || 0;
        var re6 = parseInt(document.getElementById("ukm-re_6").value.replaceAll(".","")) || 0;
        var re7 = parseInt(document.getElementById("ukm-re_7").value.replaceAll(".","")) || 0;
        var re8 = parseInt(document.getElementById("ukm-re_8").value.replaceAll(".","")) || 0;
        var re9 = parseInt(document.getElementById("ukm-re_9").value.replaceAll(".","")) || 0;
        var re10 = parseInt(document.getElementById("ukm-re_10").value.replaceAll(".","")) || 0;
        var re11 = parseInt(document.getElementById("ukm-re_11").value.replaceAll(".","")) || 0;
        var re12 = parseInt(document.getElementById("ukm-re_12").value.replaceAll(".","")) || 0;
        var re13 = parseInt(document.getElementById("ukm-re_13").value.replaceAll(".","")) || 0;
        var re14 = parseInt(document.getElementById("ukm-re_14").value.replaceAll(".","")) || 0;
        var re15 = parseInt(document.getElementById("ukm-re_15").value.replaceAll(".","")) || 0;
        var re16 = parseInt(document.getElementById("ukm-re_16").value.replaceAll(".","")) || 0;
        var re17 = parseInt(document.getElementById("ukm-re_17").value.replaceAll(".","")) || 0;
        var re18 = parseInt(document.getElementById("ukm-re_18").value.replaceAll(".","")) || 0;
        var re19 = parseInt(document.getElementById("ukm-re_19").value.replaceAll(".","")) || 0;
        var re20 = parseInt(document.getElementById("ukm-re_20").value.replaceAll(".","")) || 0;
        var re21 = parseInt(document.getElementById("ukm-re_21").value.replaceAll(".","")) || 0;
        var re22 = parseInt(document.getElementById("ukm-re_22").value.replaceAll(".","")) || 0;
        var re23 = parseInt(document.getElementById("ukm-re_23").value.replaceAll(".","")) || 0;
        var re24 = parseInt(document.getElementById("ukm-re_24").value.replaceAll(".","")) || 0;
        var re25 = parseInt(document.getElementById("ukm-re_25").value.replaceAll(".","")) || 0;
        var re26 = parseInt(document.getElementById("ukm-re_26").value.replaceAll(".","")) || 0;
        var re27 = parseInt(document.getElementById("ukm-re_27").value.replaceAll(".","")) || 0;
        // var re28 = parseInt(document.getElementById("ukm-re_28").value.replaceAll(".","")) || 0;
        // var re29 = parseInt(document.getElementById("ukm-re_29").value.replaceAll(".","")) || 0;
        // var re30 = parseInt(document.getElementById("ukm-re_30").value.replaceAll(".","")) || 0;
        var realisasi = re1+re2+re3+re4+re5+re6+re7+re8+re9+re10+re11+re12+re13+re14+re15+re16+re17+re18+re19+re20+re21+re22+re23+re24+re25+re26+re27;
        
        if (realisasi > sp2d){
            alert('Total realisasi ('+realisasi.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")+') tidak boleh melebihi Total SP2D yang dientri');
            return false;
        }else if(realisasi < sp2d){
            alert('Total realisasi ('+realisasi.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")+') tidak boleh kurang dari Total SP2D yang dientri');
            return false;
        }else{
            return true;
        }
    });
JS;
$this->registerJs($js, yii\web\View::POS_READY);
// $this->registerJs($js, yii\web\View::POS_HEAD);
?>
