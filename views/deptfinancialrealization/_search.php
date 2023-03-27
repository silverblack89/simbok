<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\DeptfinancialrealizationSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="deptfinancialrealization-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'dept_sub_activity_detail_id') ?>

    <?= $form->field($model, 'dept_sub_activity_detail_ubah_id') ?>

    <?= $form->field($model, 'bulan') ?>

    <?= $form->field($model, 'realisasi_vol_1') ?>

    <?php // echo $form->field($model, 'realisasi_satuan_1') ?>

    <?php // echo $form->field($model, 'realisasi_vol_2') ?>

    <?php // echo $form->field($model, 'realisasi_satuan_2') ?>

    <?php // echo $form->field($model, 'realisasi_vol_3') ?>

    <?php // echo $form->field($model, 'realisasi_satuan_3') ?>

    <?php // echo $form->field($model, 'realisasi_vol_4') ?>

    <?php // echo $form->field($model, 'realisasi_satuan_4') ?>

    <?php // echo $form->field($model, 'realisasi_unit_cost') ?>

    <?php // echo $form->field($model, 'realisasi_jumlah') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
