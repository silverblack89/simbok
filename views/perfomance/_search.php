<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PerfomanceSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="perfomance-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'triwulan') ?>

    <?= $form->field($model, 'tahun') ?>

    <?= $form->field($model, 'activity_data_id') ?>

    <?= $form->field($model, 'target_awal') ?>

    <?php // echo $form->field($model, 'satuan_awal') ?>

    <?php // echo $form->field($model, 'target_real') ?>

    <?php // echo $form->field($model, 'satuan_real') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
