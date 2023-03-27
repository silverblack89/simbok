<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\FinancialrealizationSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="financialrealization-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'activity_detail_id') ?>

    <?= $form->field($model, 'bulan') ?>

    <?= $form->field($model, 'realisasi_vol_1') ?>

    <?= $form->field($model, 'realisasi_vol_2') ?>

    <?php // echo $form->field($model, 'realisasi_unit_cost') ?>

    <?php // echo $form->field($model, 'realisasi_jumlah') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
