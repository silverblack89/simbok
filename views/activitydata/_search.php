<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ActivitydataSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="activitydata-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'activity_id') ?>

    <?= $form->field($model, 'period_id') ?>

    <?= $form->field($model, 'sasaran') ?>

    <?= $form->field($model, 'target') ?>

    <?php // echo $form->field($model, 'lokasi') ?>

    <?php // echo $form->field($model, 'pelaksana') ?>

    <?php // echo $form->field($model, 'periode') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
