<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Financialrealization */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="financialrealization-form">

    <?php $form = ActiveForm::begin([
                'options' => [
                    'id' => 'create-financialrealization-form',
                    'autocomplete' => 'off'
                ]
    ]); ?>

    <!-- <?= $form->field($model, 'activity_detail_id')->textInput() ?>
    <?= $form->field($model, 'activity_detail_ubah_id')->textInput() ?> -->

    <?php
        if($session['jan']==1){$jan = true;$jan_style = 'color:silver';}else{$jan = false;$jan_style = '';}
        if($session['feb']==1){$feb = true;$feb_style = 'color:silver';}else{$feb = false;$feb_style = '';}
        if($session['mar']==1){$mar = true;$mar_style = 'color:silver';}else{$mar = false;$mar_style = '';}
        if($session['apr']==1){$apr = true;$apr_style = 'color:silver';}else{$apr = false;$apr_style = '';}
        if($session['mei']==1){$mei = true;$mei_style = 'color:silver';}else{$mei = false;$mei_style = '';}
        if($session['jun']==1){$jun = true;$jun_style = 'color:silver';}else{$jun = false;$jun_style = '';}
        if($session['jul']==1){$jul = true;$jul_style = 'color:silver';}else{$jul = false;$jul_style = '';}
        if($session['agu']==1){$agu = true;$agu_style = 'color:silver';}else{$agu = false;$agu_style = '';}
        if($session['sep']==1){$sep = true;$sep_style = 'color:silver';}else{$sep = false;$sep_style = '';}
        if($session['okt']==1){$okt = true;$okt_style = 'color:silver';}else{$okt = false;$okt_style = '';}
        if($session['nov']==1){$nov = true;$nov_style = 'color:silver';}else{$nov = false;$nov_style = '';}
        if($session['des']==1){$des = true;$des_style = 'color:silver';}else{$des = false;$des_style = '';}
    ?>

    <div class="row">
        <div class="col-md-9">
            <?= $form->field($model, 'bulan')->dropDownList(['1' => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
                    '5' => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
                    '9' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember', ]
                    ,[
                        'id' => 'select', 
                        'prompt'=>'Pilih Bulan', 
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
                        ],
                    ])
            ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'total_realisasi')->textInput(['maxlength' => true])->label('Total Realisasi')->widget(\yii\widgets\MaskedInput::className(), [
                    'options' => ['disabled' => true],
                    'clientOptions' => [
                    'alias' => 'decimal',
                    'groupSeparator' => '.',
                    'radixPoint' => ',',
                    'autoGroup' => true
                    ]]); ?>
        </div>  
    </div>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'realisasi_vol_1')->textInput(['maxlength' => true])->label('Realisasi Volume 1')->widget(\yii\widgets\MaskedInput::className(), [
                    // 'options' => ['disabled' => $disabled],
                    'clientOptions' => [
                    'alias' => 'decimal',
                    'groupSeparator' => '.',
                    'radixPoint' => ',',
                    'autoGroup' => true,
                    ]]); ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'realisasi_satuan_1')->textInput(['maxlength' => true, 'disabled' => true])->label('Satuan 1') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'realisasi_vol_2')->textInput(['maxlength' => true])->label('Realisasi Volume 2')->widget(\yii\widgets\MaskedInput::className(), [
                    // 'options' => ['disabled' => $disabled],
                    'clientOptions' => [
                    'alias' => 'decimal',
                    'groupSeparator' => '.',
                    'radixPoint' => ',',
                    'autoGroup' => true
                    ]]); ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'realisasi_satuan_2')->textInput(['maxlength' => true, 'disabled' => true])->label('Satuan 2') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'realisasi_unit_cost')->textInput(['maxlength' => true])->label('Realisasi Biaya (Unit Cost)')->widget(\yii\widgets\MaskedInput::className(), [
                    // 'options' => ['disabled' => $disabled],
                    'clientOptions' => [
                    'alias' => 'decimal',
                    'groupSeparator' => '.',
                    'radixPoint' => ',',
                    'autoGroup' => true
                    ]]); ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> Simpan', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
