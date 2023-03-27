<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Account;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Activitydetailubah */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="activitydetailubah-form">

    <?php $form = ActiveForm::begin(['options' => ['autocomplete' => 'off']]); ?>

    <?php
        if($real == 0){
            $disabled = false;
        }else{
            $disabled = true;
        }
    ?>
    
    <div class="row">
        <div class="col-md-9">
            <?= $form->field($model, 'account_id')->dropDownList( ArrayHelper::map(Account::find()->all(),'id','nama_rekening'),['prompt'=>'Pilih Rekening', 'disabled' => $disabled])->label('Jenis Rekening') ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'total_poa')->textInput(['maxlength' => true])->label('Total POA Perubahan')->widget(\yii\widgets\MaskedInput::className(), [
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
        <div class="col-md-2">
            <?= $form->field($model, 'vol_1')->textInput(['maxlength' => true])->label('Volume 1')->widget(\yii\widgets\MaskedInput::className(), [
                    'options' => ['disabled' => $disabled],
                    'clientOptions' => [
                    'alias' => 'decimal',
                    'groupSeparator' => '.',
                    'radixPoint' => ',',
                    'autoGroup' => true,
                    ]]); ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'satuan_1')->textInput(['maxlength' => true, 'disabled' => $disabled])->label('Satuan 1') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'vol_2')->textInput(['maxlength' => true])->label('Volume 2 (Optional)')->widget(\yii\widgets\MaskedInput::className(), [
                    'options' => ['disabled' => $disabled],
                    'clientOptions' => [
                    'alias' => 'decimal',
                    'groupSeparator' => '.',
                    'radixPoint' => ',',
                    'autoGroup' => true
                    ]]); ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'satuan_2')->textInput(['maxlength' => true, 'disabled' => $disabled])->label('Satuan 2 (Optional)') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'unit_cost')->textInput(['maxlength' => true])->label('Biaya (Unit Cost)')->widget(\yii\widgets\MaskedInput::className(), [
                    'options' => ['disabled' => $disabled],
                    'clientOptions' => [
                    'alias' => 'decimal',
                    'groupSeparator' => '.',
                    'radixPoint' => ',',
                    'autoGroup' => true
                    ]]); ?>
        </div>
    </div>

    <!-- <?= $form->field($model, 'jumlah')->textInput(['maxlength' => true]) ?> -->
    
    <?php if($real == false) { ?>
    <H4><span class="label label-default">Pelaksanaan Kegiatan</span></H4>
    <div class="row">
        <div class="col-xs-1">
            <?= $form->field($model, 'jan')->checkBox([ 'label' => 'Jan']) ?>
        </div>
        <div class="col-xs-1">
            <?= $form->field($model, 'feb')->checkBox([ 'label' => 'Feb']) ?>
        </div>
        <div class="col-xs-1">
            <?= $form->field($model, 'mar')->checkBox([ 'label' => 'Mar']) ?>
        </div>
        <div class="col-xs-1">
            <?= $form->field($model, 'apr')->checkBox([ 'label' => 'Apr']) ?>
        </div>
        <div class="col-xs-1">
            <?= $form->field($model, 'mei')->checkBox([ 'label' => 'Mei']) ?>
        </div>
        <div class="col-xs-1">
            <?= $form->field($model, 'jun')->checkBox([ 'label' => 'Jun']) ?>
        </div>
        <div class="col-xs-1">
            <?= $form->field($model, 'jul')->checkBox([ 'label' => 'Jul']) ?>
        </div>
        <div class="col-xs-1">
            <?= $form->field($model, 'agu')->checkBox([ 'label' => 'Agu']) ?>
        </div>
        <div class="col-xs-1">
            <?= $form->field($model, 'sep')->checkBox([ 'label' => 'Sep']) ?>
        </div>
        <div class="col-xs-1">
            <?= $form->field($model, 'okt')->checkBox([ 'label' => 'Okt']) ?>
        </div>
        <div class="col-xs-1">
            <?= $form->field($model, 'nov')->checkBox([ 'label' => 'Nov']) ?>
        </div>
        <div class="col-xs-1">
            <?= $form->field($model, 'des')->checkBox([ 'label' => 'Des']) ?>
        </div>
    </div>
    <?php } ?>

    <div class="form-group">
        <?php if($session['status_poa']!=='disabled'){ ?>
            <?php if($real == true) { ?>
                
            <?php }else{ ?>
                <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> Simpan', ['class' => 'btn btn-success']) ?>
            <?php } ?>
        <?php } else {
            if($session['revisi_poa'] == 1){ ?>
                <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> Simpan', ['class' => 'btn btn-success']) ?>
        <?php } 
        } ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>