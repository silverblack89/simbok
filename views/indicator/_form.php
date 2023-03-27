<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Program;

/* @var $this yii\web\View */
/* @var $model app\models\Indicator */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="indicator-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'nama_indikator')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'data_komulatif')->checkBox([ 'label' => 'Data komulatif']) ?>

    <div class="form-group">
        <?= Html::submitButton('Simpan', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
