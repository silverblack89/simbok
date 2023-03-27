<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\Session;
use yii\helpers\ArrayHelper;
use app\models\Satuan;

/* @var $this yii\web\View */
/* @var $model app\models\Deptperfomance */
/* @var $form yii\widgets\ActiveForm */

$session = Yii::$app->session;
?>

<div class="deptperfomance-form">
    <?php $form = ActiveForm::begin(['id' => 'deptperfomance-form', 'options' => ['autocomplete' => 'off']]); ?>
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Informasi Target Awal</h3>
        </div>
        <div class="panel-body">
            <h3><span class="label label-danger"><?php echo $target ?></span></h3>
            <!-- <?= $form->field($model, 'triwulan')->textInput() ?>

            <?= $form->field($model, 'tahun')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'dept_sub_activity_data_id')->textInput() ?> -->

            <div class="row">
                <div class="col-sm-5">
                    <?= $form->field($model, 'target_awal')->textInput(['maxlength' => true])->widget(\yii\widgets\MaskedInput::className(), [
                        'clientOptions' => [
                        'alias' => 'decimal',
                        'groupSeparator' => '.',
                        'radixPoint' => ',',
                        'autoGroup' => true
                    ]])->label('Target'); ?>
                </div>
                <div class="col-sm-7">
                    <?= $form->field($model, 'satuan_awal')->dropDownList( ArrayHelper::map(Satuan::find()->orderBy('nama')->all(),'nama','nama'),['prompt'=>'Pilih'])->label('Satuan') ?>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Realisasi Kinerja</h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-5">
                    <?= $form->field($model, 'target_real')->textInput(['maxlength' => true])->widget(\yii\widgets\MaskedInput::className(), [
                        'clientOptions' => [
                        'alias' => 'decimal',
                        'groupSeparator' => '.',
                        'radixPoint' => ',',
                        'autoGroup' => true
                    ]])->label('Realisasi'); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> Simpan', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$script = <<< JS
$('#deptperfomance-form').on('beforeSubmit', function(e) {
    var form = $(this);
    var formData = form.serialize();

    $.ajax({
        url: form.attr("action"),
        type: form.attr("method"),
        data: formData,
        success: function (data) {
            $("#modal").modal('hide');
            $.pjax.reload({container: '#real', timeout:false});
        },
        error: function () {
            alert("Something went wrong");
        }
    });

}).on('submit', function(e){
    e.preventDefault();
});
JS;

$this->registerJs($script);
?>
