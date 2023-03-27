<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\DeptsubactivitydataSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="deptsubactivitydata-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'dept_sub_activity_id') ?>

    <?= $form->field($model, 'dept_period_id') ?>

    <?= $form->field($model, 'bentuk_kegiatan') ?>

    <?= $form->field($model, 'indikator_hasil') ?>

    <?php // echo $form->field($model, 'target_hasil') ?>

    <?php // echo $form->field($model, 'indikator_keluaran') ?>

    <?php // echo $form->field($model, 'target_keluaran') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
