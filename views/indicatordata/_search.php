<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\IndicatordataSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="indicatordata-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'indicator_id') ?>

    <?= $form->field($model, 'bulan') ?>

    <?= $form->field($model, 'kinerja') ?>

    <!-- <?= $form->field($model, 'keuangan') ?> -->

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
