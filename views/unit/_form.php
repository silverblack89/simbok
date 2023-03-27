<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Unit */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="unit-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php if (Yii::$app->user->identity->username == 'admin'){ ?>
        
        <div style="margin-bottom:25px;">

            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'id')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'puskesmas')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'kecamatan')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
        </div>

    <?php } ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'kepala')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'jabatan_kepala')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'nip_kepala')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'petugas')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'jabatan_petugas')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'nip_petugas')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div style="margin-top:25px;">
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'jenis_puskesmas')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'telepon_puskesmas')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Simpan', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$js=<<< JS
$(".alert").animate({opacity: 1.0}, 3000).fadeOut("slow");
JS;
$this->registerJs($js, yii\web\View::POS_READY);
?>
