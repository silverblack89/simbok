<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Deptrealization */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="deptrealization-form">

    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Nominal Realisasi</h3>
        </div>
        <div class="panel-body">
            <?php $form = ActiveForm::begin(['id' => 'deptrealization-form', 'options' => ['autocomplete' => 'off']]); ?>

            <!-- <?= $form->field($model, 'triwulan')->textInput() ?>

            <?= $form->field($model, 'tahun')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'dept_sub_activity_detail_id')->textInput() ?> -->

            <?= $form->field($model, 'jml_poa')->hiddenInput()->label(false) ?>

            <?= $form->field($model, 'sp2d')->hiddenInput()->label(false) ?>

            <?= $form->field($model, 'realisasi_lalu')->hiddenInput()->label(false) ?>

            <?= $form->field($model, 'total_realisasi')->hiddenInput()->label(false) ?>

            <?= $form->field($model, 'jumlah')->textInput()->widget(\yii\widgets\MaskedInput::className(), [
            'options' => ['readOnly' => false],
            'clientOptions' => ['alias' => 'decimal', 'groupSeparator' => '.', 'radixPoint' => ',', 'autoGroup' => true,]
            ]); ?>

            <div class="form-group">
                <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> Simpan', ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<?php
$js=<<< JS
    $('#deptrealization-form').on('beforeSubmit', function (e) {

        var sp2d = parseInt(document.getElementById("deptrealization-sp2d").value);
        var prev_real = parseInt(document.getElementById("deptrealization-realisasi_lalu").value);
        var real = parseInt(document.getElementById("deptrealization-total_realisasi").value);
        var jml_poa =  parseInt(document.getElementById("deptrealization-jml_poa").value);
        var jumlah = parseInt(document.getElementById("deptrealization-jumlah").value.replaceAll(".","")) || 0;
        var form = $(this);
        var formData = form.serialize();

        if (jumlah+prev_real > jml_poa){
            alert('Input realisasi melebihi POA yang sudah dientri.');
            return false;
        }else{
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
            return true;
        }

        if (real+jumlah > sp2d){
            alert('Total realisasi melebihi Total SP2D yang sudah dientri.');
            return false;
        }else{
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
            return true;
        }
    }).on('submit', function(e){
        e.preventDefault();
   });
JS;
$this->registerJs($js, yii\web\View::POS_HEAD);
?>
