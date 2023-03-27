<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Profile */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="profile-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'nama')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'alamat')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'kota_kab')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'provinsi')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'telepon')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'kepala')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'jabatan_kepala')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nip_kepala')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sekretaris')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'jabatan_sekretaris')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nip_sekretaris')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Simpan', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
