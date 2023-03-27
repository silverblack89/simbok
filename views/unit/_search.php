<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\UnitSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="unit-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'puskesmas') ?>

    <?= $form->field($model, 'kecamatan') ?>

    <?= $form->field($model, 'kepala') ?>

    <?= $form->field($model, 'jabatan_kepala') ?>

    <?php // echo $form->field($model, 'nip_kepala') ?>

    <?php // echo $form->field($model, 'petugas') ?>

    <?php // echo $form->field($model, 'jabatan_petugas') ?>

    <?php // echo $form->field($model, 'nip_petugas') ?>

    <?php // echo $form->field($model, 'jenis_puskesmas') ?>

    <?php // echo $form->field($model, 'telepon_puskesmas') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
