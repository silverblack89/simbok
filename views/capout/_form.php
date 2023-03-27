<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Capout */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="capout-form">

<div class="panel panel-primary">
        <div class="panel-heading">
                <h3 class="panel-title">Capaian Output</h3>
        </div>
        <div class="panel-body">

            <?php $form = ActiveForm::begin(); ?>

            <!-- <?= $form->field($model, 'nomor')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'unit_id')->textInput(['maxlength' => true]) ?> 

            <?= $form->field($model, 'bulan')->textInput() ?> -->

            <div class="row">
                <div class="col-md-12">

                <?php if ($co1 == 1){ ?>
                    <?= $form->field($model, 'jml_ke')->widget(\yii\widgets\MaskedInput::class, [
                        'clientOptions' => [
                            'alias' =>  'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true,
                            'removeMaskOnSubmit' => true
                        ],
                    ])->label('Jml Kontak erat yg dilacak (Bulan ini)'); ?>
                    <?php } ?>
                </div>
                <div class="col-md-12">
                    <?php if ($co2 == 1){ ?>
                    <?= $form->field($model, 'jml_confirm')->widget(\yii\widgets\MaskedInput::class, [
                        'clientOptions' => [
                            'alias' =>  'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true,
                            'removeMaskOnSubmit' => true
                        ],
                    ])->label('Jml orang Terkonfirmasi yg di pantau (bulan ini)'); ?>
                    <?php } ?>
                </div>
                <div class="col-md-12">
                    <?php if ($co3 == 1){ ?>
                    <?= $form->field($model, 'tenaga_tracer')->widget(\yii\widgets\MaskedInput::class, [
                        'clientOptions' => [
                            'alias' =>  'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true,
                            'removeMaskOnSubmit' => true
                        ],
                    ])->label('Tenaga Tracer (Kondisi sampai bulan ini)'); ?>
                    <?php } ?>
                </div>
                <div class="col-md-12">
                    <?php if ($co4 == 1){ ?>
                    <?= $form->field($model, 'tenaga_surveilans')->widget(\yii\widgets\MaskedInput::class, [
                        'clientOptions' => [
                            'alias' =>  'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true,
                            'removeMaskOnSubmit' => true
                        ],
                    ])->label('Tenaga surveilans/Pengolah data (Kondisi sampai bulan ini)'); ?>
                    <?php } ?>
                </div>
            </div>

            <div class="form-group">
                <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> Simpan', ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
