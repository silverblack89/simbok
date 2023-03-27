<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Service */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="service-form">

    <?php $form = ActiveForm::begin(); ?>

    <!-- <?= $form->field($model, 'program_id')->textInput() ?> -->

    <?= $form->field($model, 'nama_pelayanan')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'aktif')->checkBox([ 'label' => 'Aktif']) ?>

    <div class="form-group">
        <?= Html::submitButton('Simpan', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
