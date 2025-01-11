<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Deptdatareal */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="deptdatareal-form">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo $title ?></h3>
        </div>
        <div class="panel-body">
            <?php $form = ActiveForm::begin(['id' => 'deptdatareal-form', 'options' => ['autocomplete' => 'off']]); ?>

            <?= $form->field($model, 'dept_sub_activity_detail_id')->hiddenInput()->label(false) ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'tanggal')->widget(DatePicker::classname(), [
                        'removeButton' => false,
                        'pluginOptions' => [
                            'format' => 'dd-mm-yyyy',
                            'todayHighlight' => true,
                            'autoclose'=>true
                        ]
                        ])->label('Tanggal');
                    ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'sisa_pagu')->textInput(['maxlength' => true])->label('Sisa Pagu')->widget(\yii\widgets\MaskedInput::className(), [
                        'options' => ['id' => 'sisapagu', 'disabled' => true, 'readonly' => true],
                        'clientOptions' => [
                        'alias' => 'decimal',
                        'groupSeparator' => '.',
                        'radixPoint' => ',',
                        'autoGroup' => true
                        ]]); ?>
                </div>
            </div>

            <?= $form->field($model, 'nomor')->textInput(['maxlength' => true])->label('Nomor SP2D') ?>

            <div class="row">
                <div class="col-md-6"></div>
                <div class="col-md-6">
                    <?= $form->field($model, 'jumlah')->textInput(['maxlength' => true])->label('Jumlah Realisasi')->widget(\yii\widgets\MaskedInput::className(), [
                        'options' => ['id' => 'jumlah', 'readonly' => false],
                        'clientOptions' => [
                        'alias' => 'decimal',
                        'groupSeparator' => '.',
                        'radixPoint' => ',',
                        'autoGroup' => true
                        ]]); ?>
                 </div>
            </div>

            <div class="form-group">
                <?= Html::button('<span class="glyphicon glyphicon-triangle-left"></span> Kembali', ['value' => Url::to(['/deptdatareal', 'id' => $id]), 'class' => 'showModalButton btn btn-primary']) ?>
                <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> Simpan', ['class' => 'btn btn-success pull-right']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<?php
    Modal::begin([
        // 'header'=>'<h4>Detail Kegiatan</h4>', 
        'id'=>'modal',
        'size'=>'modal-md',
        'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE],
        // 'footer' => ''
    ]);
    echo "<div id='modalContent'></div>";
    Modal::end();
?>

<?php
$js=<<< JS
    $('#deptdatareal-form').on('beforeSubmit', function (e) {
        var sisapagu =  parseInt(document.getElementById("sisapagu").value.replaceAll(".","")) || 0;
        // var vol_1 = parseInt(document.getElementById("vol1").value.replaceAll(".","")) || 0;
        // var vol_2 = parseInt(document.getElementById("vol2").value.replaceAll(".","")) || 0;
        // var hrg_sat = parseInt(document.getElementById("hrgsat").value.replaceAll(".","")) || 0;
        var jumlah = parseInt(document.getElementById("jumlah").value.replaceAll(".","")) || 0;

        var form = $(this);
        var formData = form.serialize();

        if(jumlah > sisapagu){
            alert('Jumlah realisasi melebihi jumlah pagu kegiatan.');
            return false;
        }else{
            $.ajax({
                url: form.attr("action"),
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    $("#modal").modal('hide');
                    $.pjax.reload({container: '#datareal', timeout:false});
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

   $(document).on('ready pjax:success', function() {
        $('.pjax-delete-link').on('click', function(e) {
            e.preventDefault();
            var deleteUrl = $(this).attr('delete-url');
            var pjaxContainer = $(this).attr('pjax-container');
            var result = confirm('Delete this item, are you sure?');                                
            if(result) {
                $.ajax({
                    url: deleteUrl,
                    type: 'post',
                    error: function(xhr, status, error) {
                        alert('There was an error with your request.' + xhr.responseText);
                    }
                }).done(function(data) {
                    $("#modal").modal('hide');
                    $.pjax.reload({container: '#datareal', timeout:false});
                });
            }
        });

    });
JS;
$this->registerJs($js, yii\web\View::POS_HEAD);
?>
