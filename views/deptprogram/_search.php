<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\DeptprogramSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="deptprogram-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'kode_rekening') ?>

    <?= $form->field($model, 'nama_program') ?>

    <?= $form->field($model, 'tahun') ?>

    <?= $form->field($model, 'pagu') ?>

    <?php // echo $form->field($model, 'aktif') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
