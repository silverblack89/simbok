<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Deptactivity */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="deptactivity-form">

    <?php $form = ActiveForm::begin(); ?>

    <!-- <?= $form->field($model, 'dept_program_id')->textInput() ?> -->

    <?= $form->field($model, 'kode_rekening')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nama_kegiatan')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'pagu')->textInput() ?>

    <?= $form->field($model, 'aktif')->checkBox(['label' => 'Aktif']) ?>

    <div class="form-group">
        <?= Html::submitButton('Simpan', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
