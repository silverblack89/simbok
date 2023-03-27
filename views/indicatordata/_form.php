<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Indicator;
use app\models\Period;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Indicatordata */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="indicatordata-form">

    <?php $form = ActiveForm::begin([
                'options' => [
                    'id' => 'create-indicatordata-form'
                ]
    ]); ?>

    <!-- <?= $form->field($model, 'indicator_id')->dropDownList( ArrayHelper::map(Indicator::find()->where(['in','program_id',$session['programId']])->all(),'id','nama_indikator'),['id' => 'indicator','prompt'=>'Pilih Indikator'])
    ->label('Indikator Program') ?> -->

    <!-- <?= $form->field($model, 'period_id')->textInput() ?>  -->

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

    <div class="row">
        <div class="col-md-1">
            <!-- <?= $form->field($model, 'kinerja')->textInput(['maxlength' => true, 'id' => 'kinerja'])->label('Kinerja %') //, 'onchange' => 'document.location.href = "http://localhost/poa/web/index.php?r=indicatordata%2Fprocess-max"', ?> -->
            <?= $form->field($model, 'kinerja')->textInput(['maxlength' => true, 'id' => 'kinerja'])->label('Kinerja %')->widget(\yii\widgets\MaskedInput::className(), [
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'allowMinus'=>false,
                            // 'groupSize'=>3,
                            // 'groupSeparator' => '.',
                            'radixPoint' => '.',
                            // 'autoGroup' => true,
                            // 'removeMaskOnSubmit' => true
                            ]]); ?>
        </div>
    </div>

    <!-- <p class="text-danger text-left">*) <strong>Kinerja</strong> diisi sampai dengan bulan terkait.</P> -->

    <div class="form-group">
        <?php if($session['status']!=='disabled'){ ?>
            <?= Html::submitButton('Simpan', ['class' => 'btn btn-success', 'id' => 'simpan']) ?>
        <?php } ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
// $script = <<< JS
//     alert('ok');
// JS;
// $this->registerJs($script);

// $this->registerJs('
//     $("#simpan").on("click", function (e) {
//         var x = document.getElementById("indicator").value;
//         var y = document.getElementById("kinerja").value;
//         baseUrl = window.origin;
//         // alert(x);

//         createCookie("module", x, "1");
//         createCookie("kinerja", y, "1");

//         var link = baseUrl+"'.Url::to(['process-max']).'";
        
//         $.get(link, function(data) {
//             alert(data);
//         });

//         // alert(link);

//         // Function to create the cookie 
//         function createCookie(name, value, days) { 
//             var expires; 
            
//             if (days) { 
//                 var date = new Date(); 
//                 date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000)); 
//                 expires = "; expires=" + date.toGMTString(); 
//             } 
//             else { 
//                 expires = ""; 
//             } 
            
//             document.cookie = escape(name) + "=" +  
//                 escape(value) + expires + "; path=/"; 
//         } 
//     });
// ');
?>