<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\DeptdatarealSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="deptdatareal-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'dept_sub_activity_detail_id') ?>

    <?= $form->field($model, 'tanggal') ?>

    <?= $form->field($model, 'nomor') ?>

    <?= $form->field($model, 'jumlah') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
