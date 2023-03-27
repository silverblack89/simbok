<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Uklabel */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="uklabel-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'uk_nama')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uk_desk')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tahun')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bd_id')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
