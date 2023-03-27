<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Activity */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="activity-form">

    <?php $form = ActiveForm::begin(); ?>

    <!-- <?= $form->field($model, 'service_id')->textInput() ?> -->

    <?= $form->field($model, 'nama_kegiatan')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'aktif')->checkBox([ 'label' => 'Aktif']) ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model, 'status')->dropDownList(['Wajib' => 'Wajib', 'Pilihan' => 'Pilihan'], ['class'=>'form-control'])->label('Status') ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Simpan', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
