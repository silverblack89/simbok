<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Deptfinancialrealization */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="deptfinancialrealization-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'dept_sub_activity_detail_id')->textInput() ?>

    <?= $form->field($model, 'dept_sub_activity_detail_ubah_id')->textInput() ?>

    <?= $form->field($model, 'bulan')->textInput() ?>

    <?= $form->field($model, 'realisasi_vol_1')->textInput() ?>

    <?= $form->field($model, 'realisasi_satuan_1')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'realisasi_vol_2')->textInput() ?>

    <?= $form->field($model, 'realisasi_satuan_2')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'realisasi_vol_3')->textInput() ?>

    <?= $form->field($model, 'realisasi_satuan_3')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'realisasi_vol_4')->textInput() ?>

    <?= $form->field($model, 'realisasi_satuan_4')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'realisasi_unit_cost')->textInput() ?>

    <?= $form->field($model, 'realisasi_jumlah')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
