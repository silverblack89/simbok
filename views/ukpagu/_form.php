<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Uklabel;
use yii\helpers\ArrayHelper;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Ukpagu */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ukpagu-form">

<div class="panel panel-primary">
        <div class="panel-heading">
                <h3 class="panel-title">Detail Data</h3>
        </div>
        <div class="panel-body">
            <?php $form = ActiveForm::begin(['options' => ['autocomplete' => 'off']]); ?>

            <?= $form->field($model, 'uk_id')->dropDownList( ArrayHelper::map(Uklabel::find()->where(['tahun' => $session['tahun']])->all(),'id','uk_desk'),['prompt'=>'Pilih'])->label('Upaya Kesehatan') ?>

            <?= $form->field($model, 'unit_id')->hiddenInput(['maxlength' => true])->label(false) ?>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'jumlah')->widget(\yii\widgets\MaskedInput::class, [
                        'clientOptions' => [
                            'alias' =>  'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true,
                        ],

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
