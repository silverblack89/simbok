<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\UkmSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ukm-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'triwulan') ?>

    <?= $form->field($model, 'tahun') ?>

    <?= $form->field($model, 'unit_id') ?>

    <?= $form->field($model, 'uk_1') ?>

    <?php // echo $form->field($model, 're_1') ?>

    <?php // echo $form->field($model, 'uk_2') ?>

    <?php // echo $form->field($model, 're_2') ?>

    <?php // echo $form->field($model, 'uk_3') ?>

    <?php // echo $form->field($model, 're_3') ?>

    <?php // echo $form->field($model, 'uk_4') ?>

    <?php // echo $form->field($model, 're_4') ?>

    <?php // echo $form->field($model, 'uk_5') ?>

    <?php // echo $form->field($model, 're_5') ?>

    <?php // echo $form->field($model, 'uk_6') ?>

    <?php // echo $form->field($model, 're_6') ?>

    <?php // echo $form->field($model, 'uk_7') ?>

    <?php // echo $form->field($model, 're_7') ?>

    <?php // echo $form->field($model, 'uk_8') ?>

    <?php // echo $form->field($model, 're_8') ?>

    <?php // echo $form->field($model, 'uk_9') ?>

    <?php // echo $form->field($model, 're_9') ?>

    <?php // echo $form->field($model, 'uk_10') ?>

    <?php // echo $form->field($model, 're_10') ?>

    <?php // echo $form->field($model, 'uk_11') ?>

    <?php // echo $form->field($model, 're_11') ?>

    <?php // echo $form->field($model, 'uk_12') ?>

    <?php // echo $form->field($model, 're_12') ?>

    <?php // echo $form->field($model, 'uk_13') ?>

    <?php // echo $form->field($model, 're_13') ?>

    <?php // echo $form->field($model, 'uk_14') ?>

    <?php // echo $form->field($model, 're_14') ?>

    <?php // echo $form->field($model, 'uk_15') ?>

    <?php // echo $form->field($model, 're_15') ?>

    <?php // echo $form->field($model, 'uk_16') ?>

    <?php // echo $form->field($model, 're_16') ?>

    <?php // echo $form->field($model, 'uk_17') ?>

    <?php // echo $form->field($model, 're_17') ?>

    <?php // echo $form->field($model, 'uk_18') ?>

    <?php // echo $form->field($model, 're_18') ?>

    <?php // echo $form->field($model, 'uk_19') ?>

    <?php // echo $form->field($model, 're_19') ?>

    <?php // echo $form->field($model, 'uk_20') ?>

    <?php // echo $form->field($model, 're_20') ?>

    <?php // echo $form->field($model, 'uk_21') ?>

    <?php // echo $form->field($model, 're_21') ?>

    <?php // echo $form->field($model, 'uk_22') ?>

    <?php // echo $form->field($model, 're_22') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
