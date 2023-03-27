<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\Session;
use yii\grid\GridView;
use yii\bootstrap\Button;
use yii\helpers\Url;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Activitydata */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="activitydataubah-form">
    <?php $form = ActiveForm::begin(['options' => ['autocomplete' => 'off']]); ?>

    <!-- <?= $form->field($model, 'activity_id')->textInput() ?>

    <?= $form->field($model, 'period_id')->textInput() ?> -->

    <?= $form->field($model, 'bentuk_kegiatan')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sasaran')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'target')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'lokasi')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'pelaksana')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> Simpan', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
