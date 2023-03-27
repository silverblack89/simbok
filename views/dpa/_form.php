<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Deptsubactivity;
use yii\helpers\ArrayHelper;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Dpa */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="dpa-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'dept_sub_activity_id')->dropDownList( ArrayHelper::map(Deptsubactivity::find()
                                                                                        ->select('dept_sub_activity.*')
                                                                                        ->leftJoin('dept_activity', '`dept_activity`.`id` = `dept_sub_activity`.`dept_activity_id`')
                                                                                        ->leftJoin('dept_program', '`dept_program`.`id` = `dept_activity`.`dept_program_id`')
                                                                                        ->where(['dept_program.tahun' => $session['dpaYear']])
                                                                                        ->andWhere(['dept_program.bok_id' => 6])
                                                                                        ->all(),'id','nama_sub_kegiatan'),['id'=> 'select', 'prompt'=>'Pilih'])->label('Sub Kegiatan DPA') ?>

    <?= $form->field($model, 'tahun')->hiddenInput(['maxlength' => true])->label(false) ?>

    <?= $form->field($model, 'nama')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'keterangan')->hiddenInput(['maxlength' => true])->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton('Simpan', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$js=<<< JS
    $("#select").on("change", function (e) {
        document.getElementById("dpa-keterangan").value = this.options[this.selectedIndex].text;
    });
JS;
$this->registerJs($js, yii\web\View::POS_READY);
// $this->registerJs($js, yii\web\View::POS_HEAD);
?>
