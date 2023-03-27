<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Bok;
use app\models\Deptgroupsp2d;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\Deptprogram */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="deptprogram-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'bok_id')->dropDownList( ArrayHelper::map(Bok::find()->all(),'id','keterangan'),['prompt'=>'Pilih'])->label('Jenis POA') ?>
    
    <?= $form->field($model, 'kode_rekening')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'nama_program')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'pagu')->textInput() ?>

    <?= $form->field($model, 'dept_group_sp2d_id_1')->dropDownList( ArrayHelper::map(Deptgroupsp2d::find()->all(),'id','nama'),['prompt'=>'Pilih'])->label('Kelompok SP2D 1') ?>

    <?= $form->field($model, 'dept_group_sp2d_id_2')->dropDownList( ArrayHelper::map(Deptgroupsp2d::find()->all(),'id','nama'),['prompt'=>'Pilih'])->label('Kelompok SP2D 2') ?>

    <?= $form->field($model, 'aktif')->checkBox([ 'label' => 'Aktif']) ?>

    <div class="form-group">
        <?= Html::submitButton('Simpan', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
