<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Unit;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'alias')->textInput(['maxlength' => true]) ?>

    <!-- <?= $form->field($model, 'auth_key')->textInput(['maxlength' => true]) ?> -->

    <?= $form->field($model, 'password_hash')->passwordInput(['value'=>'']) ?>

    <!-- <?= $form->field($model, 'password_reset_token')->textInput(['maxlength' => true]) ?> -->

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'unit_id')->dropDownList( ArrayHelper::map(Unit::find()->all(),'id','puskesmas'),['prompt'=>'Pilih Unit'])->label('Unit') ?>

    <?= $form->field($model, 'status')->dropDownList([ '10' => 'Aktif', '0' => 'Non Aktif']) ?>

    <!-- <?= $form->field($model, 'create_at')->textInput() ?>

    <?= $form->field($model, 'update_at')->textInput() ?> -->

    <div class="form-group">
        <?= Html::submitButton('Simpan', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
