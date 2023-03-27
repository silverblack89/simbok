<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Indicator */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Bulan';
$this->params['breadcrumbs'][] = ['label' => $period, 'url' => ['period/create']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="option-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'bulan')->dropDownList(['1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
            '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
            '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember', ]
            ,[
                'id' => 'select', 
                'prompt'=>'Pilih Bulan', 
            ]
            )
            ?>
        </div>
    </div>

    <div class="form-group">
        <!-- <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?> -->
        <?= Html::a(' Proses', Url::to(['exportbln']), ['id' => 'proses', 'data-method' => 'POST', 'class'=>'btn btn-info', 'style' => 'display: inline-block']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$this->registerJs('
$("#proses").on("click", function (e) {
    baseUrl = window.origin;
    var bulan=document.getElementById("select").value;  
    var bulannya = $("#select option:selected").text();
    createCookie("bulan", bulan, "1");
    createCookie("bulannya", bulannya.toUpperCase(), "1");
    // alert(bulan);
    // alert(bulannya);

    var link = baseUrl+"'.Url::to(['export-bln']).'";
    
    $.get(link, function(data) {
        // alert(data)
    });

    // alert(link);

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
?>
