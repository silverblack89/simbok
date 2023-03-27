<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Deptsp2dSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="deptsp2d-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'tanggal') ?>

    <?= $form->field($model, 'dept_group_sp2d_id') ?>

    <?= $form->field($model, 'no_sp2d') ?>

    <?= $form->field($model, 'jenis_spm') ?>

    <?php // echo $form->field($model, 'uraian') ?>

    <?php // echo $form->field($model, 'jumlah') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
