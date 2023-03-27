<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ActivitydetailSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="activitydetail-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'activity_data_id') ?>

    <?= $form->field($model, 'account_id') ?>

    <?= $form->field($model, 'rincian') ?>

    <?= $form->field($model, 'vol_1') ?>

    <?php // echo $form->field($model, 'vol_2') ?>

    <?php // echo $form->field($model, 'unit_cost') ?>

    <?php // echo $form->field($model, 'jumlah') ?>

    <?php // echo $form->field($model, 'jan') ?>

    <?php // echo $form->field($model, 'feb') ?>

    <?php // echo $form->field($model, 'mar') ?>

    <?php // echo $form->field($model, 'apr') ?>

    <?php // echo $form->field($model, 'mei') ?>

    <?php // echo $form->field($model, 'jun') ?>

    <?php // echo $form->field($model, 'jul') ?>

    <?php // echo $form->field($model, 'agu') ?>

    <?php // echo $form->field($model, 'sep') ?>

    <?php // echo $form->field($model, 'okt') ?>

    <?php // echo $form->field($model, 'nov') ?>

    <?php // echo $form->field($model, 'des') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
