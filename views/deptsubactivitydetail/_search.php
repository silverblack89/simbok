<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\DeptsubactivitydetailSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="deptsubactivitydetail-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'dept_sub_activity_data_id') ?>

    <?= $form->field($model, 'account_id') ?>

    <?= $form->field($model, 'vol_1') ?>

    <?= $form->field($model, 'satuan_1') ?>

    <?php // echo $form->field($model, 'vol_2') ?>

    <?php // echo $form->field($model, 'satuan_2') ?>

    <?php // echo $form->field($model, 'unit_cost') ?>

    <?php // echo $form->field($model, 'jumlah') ?>

    <?php // echo $form->field($model, 'tw1') ?>

    <?php // echo $form->field($model, 'tw2') ?>

    <?php // echo $form->field($model, 'tw3') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
