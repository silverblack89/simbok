<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Sp2d */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sp2d-form">

    <div class="panel panel-primary">
        <div class="panel-heading">
                <h3 class="panel-title">Detail Data</h3>
        </div>
        <div class="panel-body">
            <?php $form = ActiveForm::begin(['id' => 'sp2d-form', 'options' => ['autocomplete' => 'off']]); ?>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'tanggal')->widget(DatePicker::classname(), [
                        'removeButton' => false,
                        'pluginOptions' => [
                            'format' => 'dd-mm-yyyy',
                            'todayHighlight' => true,
                            'autoclose'=>true
                        ],
                        'options' => ['placeholder' => 'dd-mm-yyyy', 'autocomplete' => 'off'],
                    ]);
                    ?>
                </div>
            </div>

            <?= $form->field($model, 'no_sp2d')->textInput(['maxlength' => true, 'autocomplete' => 'off'])->label('Nomor SP2D') ?>

            <?= $form->field($model, 'jenis_spm')->dropDownList(['Langsung (LS)' => 'Langsung (LS)', 'Ganti Uang Persediaan (GU)' => 'Ganti Uang Persediaan (GU)'], ['class'=>'form-control'])->label('Jenis SPM') ?>

            <?= $form->field($model, 'uraian')->textarea(['maxlength' => true, 'autocomplete' => 'off']) ?>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'jumlah')->textInput(['autocomplete' => 'off'])->widget(\yii\widgets\MaskedInput::className(), [
                        'clientOptions' => ['alias' => 'decimal', 'groupSeparator' => '.', 'radixPoint' => ',', 'autoGroup' => true,]
                    ]); ?>
                </div>
            </div>

            <div class="form-group">
                <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> Simpan', ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>
