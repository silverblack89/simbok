<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\Session;
use yii\helpers\Url;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Deptreal */
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

<div class="deptreal-form">
    <div class="panel panel-primary">
        <div class="panel-heading">
                <h3 class="panel-title">Detail Realisasi</h3>
        </div>
        <div class="panel-body">

            <?php $form = ActiveForm::begin(['options' => ['autocomplete' => 'off', 'id' => 'deptreal-form']]); ?>

            <!-- <?= $form->field($model, 'dept_sub_activity_id')->textInput() ?> -->

            <!-- <?= $form->field($model, 'dept_period_id')->textInput() ?> -->

            <tr>
            <td style="text-align:right;border:1px solid grey;">
            <?= $form->field($model, 'sisa_sp2d')->textInput(['maxlength' => true])->label('SISA SP2D ('.$grupLabel.')')->widget(\yii\widgets\MaskedInput::className(), [
                'options' => ['placeholder' => '', 'disabled' => true],
                'clientOptions' => [
                'alias' => 'decimal',
                'groupSeparator' => '.',
                'radixPoint' => ',',
                'autoGroup' => true,
                'removeMaskOnSubmit' => false
                ]]); 
            ?>
            </td>
            </tr>

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
                            // $session['bulan']=>['Selected'=>true],
                    ],
                    // 'onchange'=>'window.location=window.origin+"'.Url::to(['month-total']).'?bulan="+$(this).val()', //window.origin+"'.Url::to(['month-total', 'tahun' => $tahun]).'"&bulan=$(this).val()
                    'class'=>'form-control'
                ]
                )->label('Bulan')
            ?>

            <?= $form->field($model, 'jumlah')->textInput(['maxlength' => true])->label('Jumlah')->widget(\yii\widgets\MaskedInput::className(), [
                'options' => ['placeholder' => ''],
                'clientOptions' => [
                'alias' => 'decimal',
                'groupSeparator' => '.',
                'radixPoint' => ',',
                'autoGroup' => true,
                'removeMaskOnSubmit' => false
                ]]); 
            ?>

            <!-- <?= $form->field($model, 'modified_at')->textInput() ?> -->

            <div class="form-group">
                <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> Simpan', ['class' => 'btn btn-success pull-right']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
