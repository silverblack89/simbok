<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use yii\web\Session;
use app\models\Deptgroupsp2d;
use app\models\Dpa;

/* @var $this yii\web\View */
/* @var $model app\models\Deptsp2d */
/* @var $form yii\widgets\ActiveForm */

$session = Yii::$app->session;
?>

<div class="deptsp2d-form">

<div class="panel panel-primary">
        <div class="panel-heading">
                <h3 class="panel-title">Detail SP2D</h3>
        </div>
        <div class="panel-body">
            <?php $form = ActiveForm::begin(['options' => ['id' => 'deptsp2d-form', 'autocomplete' => 'off']]); ?>

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
            
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'dept_group_sp2d_id')->dropDownList( ArrayHelper::map(Deptgroupsp2d::find()->all(),'id','nama'),['prompt'=>'Pilih'])->label('Kel.SP2D') ?>
                </div>
                <div class="col-md-8">
                    <?= $form->field($model, 'no_sp2d')->textInput(['maxlength' => true, 'autocomplete' => 'off'])->label('Nomor SP2D') ?>
                </div>
            </div>

            <?= $form->field($model, 'dpa_id')->dropDownList( ArrayHelper::map(Dpa::find()->where(['tahun' => $session['deptPeriodValue']])->all(),'id','nama'),['prompt'=>'Pilih'])->label('Sub Kegiatan') ?>

            <?= $form->field($model, 'jenis_spm')->dropDownList(['Langsung (LS)' => 'Langsung (LS)', 'Ganti Uang Persediaan (GU)' => 'Ganti Uang Persediaan (GU)'], ['class'=>'form-control'])->label('Jenis SPM') ?>

            <?= $form->field($model, 'uraian')->textarea(['maxlength' => true, 'rows' => 4, 'autocomplete' => 'off']) ?>

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
