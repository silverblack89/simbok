<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Account;

/* @var $this yii\web\View */
/* @var $model app\models\Deptaccountaccess */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="deptaccountaccess-form">
    <?php $form = ActiveForm::begin(['id' => 'deptaccountaccess-form', 'options' => ['autocomplete' => 'off', 'data-pjax' => true]]); ?>
    <div class="row">
        <div class="col-sm-10" style="margin-top:-15px">
            <?= $form->field($model, 'dept_sub_activity_id')->hiddenInput()->label(false) ?>
            <?= $form->field($model, 'account_id')->dropDownList( ArrayHelper::map(Account::find()->orderBy('nama_rekening')->all(),'id','nama_rekening'),['prompt'=>'Pilih Rekening'])->label(false) ?>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                <?= Html::submitButton('<span class="glyphicon glyphicon-plus"></span> Tambah', ['class' => 'btn btn-success pull-right']) ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php
$script = <<< JS
$('#deptaccountaccess-form').on('beforeSubmit', function(e) {
    var form = $(this);
    var formData = form.serialize();

    $.ajax({
        url: form.attr("action"),
        type: form.attr("method"),
        data: formData,
        success: function (data) {
            $.pjax.reload({container: '#deptaccountaccess', timeout:false});
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
