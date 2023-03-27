<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Account;
use app\models\Satuan;
use app\models\Sumberdana;
use yii\web\Session;
use yii\helpers\Url;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Deptsubactivitydetail */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="deptsubactivitydetail-form">

    <?php $form = ActiveForm::begin(['options' => ['id' => 'deptsubactivitydetail-form', 'autocomplete' => 'off']]); ?>

    <?php
        $real = false;

        if($real == 0){
            $disabled = false;
        }else{
            $disabled = true;
        }
    ?>

    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo $title ?></h3>
        </div>
        <div class="panel-body">

            <?= $form->field($model, 'dept_sub_activity_data_id')->hiddenInput()->label(false) ?>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'sumber_dana_id')->dropDownList( ArrayHelper::map(Sumberdana::find()->where($sd)->all(),'id','nama'), ['prompt'=>'Pilih'])->label('Sumber Dana') ?>
                </div>
                <!-- <div class="col-md-4 pull-right">
                    <?= $form->field($model, 'total_poa')->textInput(['maxlength' => true])->label('Total POA')->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['disabled' => true],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                            ]]); ?>
                </div>   -->
            </div>

            <div class="row">
                <div class="col-md-8">
                    <?php if(!empty($akun)){ ?>
                        <?= $form->field($model, 'account_id')->dropDownList( ArrayHelper::map(Account::find()->where('id IN('.$akun.')')->orderBy('nama_rekening')->all(),'id','nama_rekening'),['prompt'=>'Pilih Rekening', 'disabled' => $disabled])->label('Jenis Rekening') ?>
                    <?php }else{ ?>
                        <?= $form->field($model, 'account_id')->dropDownList( ArrayHelper::map(Account::find()->orderBy('nama_rekening')->all(),'id','nama_rekening'),['prompt'=>'Pilih Rekening', 'disabled' => $disabled])->label('Jenis Rekening') ?>
                    <?php } ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'rincian')->textInput(['maxlength' => true, 'placeholder' => 'rincian rekening']) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'vol_1')->textInput(['maxlength' => true])->label('Volume 1')->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'vol1', 'disabled' => $disabled, 'placeholder' => 'orang'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true,
                            ]]); ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'satuan_1')->dropDownList( ArrayHelper::map(Satuan::find()->orderBy('nama')->all(),'nama','nama'),['prompt'=>'Pilih Satuan', 'disabled' => $disabled])->label('Satuan 1') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'vol_2')->textInput(['maxlength' => true])->label('Volume 2')->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'vol2', 'disabled' => $disabled, 'placeholder' => 'jam'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                            ]]); ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'satuan_2')->dropDownList( ArrayHelper::map(Satuan::find()->orderBy('nama')->all(),'nama','nama'),['prompt'=>'Pilih Satuan', 'disabled' => $disabled])->label('Satuan 2') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'vol_3')->textInput(['maxlength' => true])->label('Volume 3')->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'vol3', 'disabled' => $disabled, 'placeholder' => 'hari'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true,
                            ]]); ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'satuan_3')->dropDownList( ArrayHelper::map(Satuan::find()->orderBy('nama')->all(),'nama','nama'),['prompt'=>'Pilih Satuan', 'disabled' => $disabled])->label('Satuan 3') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'vol_4')->textInput(['maxlength' => true])->label('Volume 4')->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'vol4', 'disabled' => $disabled, 'placeholder' => 'kali'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true,
                            ]]); ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'satuan_4')->dropDownList( ArrayHelper::map(Satuan::find()->orderBy('nama')->all(),'nama','nama'),['prompt'=>'Pilih Satuan', 'disabled' => $disabled])->label('Satuan 4') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'unit_cost')->textInput(['maxlength' => true])->label('Harga Satuan')->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'harga', 'disabled' => $disabled],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                            ]]); ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'total')->textInput(['maxlength' => true])->label('Total')->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'total', 'disabled' => true],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                            ]]); ?>
                </div>
            </div>

            <!-- <?= $form->field($model, 'jumlah')->textInput() ?> -->

            <?php if($real == false) { ?>
            <H4><span class="label label-default">Pelaksanaan Kegiatan</span></H4>
            <div class="row">
                <div class="col-xs-3">
                    <?= $form->field($model, 'tw1')->checkBox([ 'label' => 'Triwulan I']) ?>
                </div>
                <div class="col-xs-3">
                    <?= $form->field($model, 'tw2')->checkBox([ 'label' => 'Triwulan II']) ?>
                </div>
                <div class="col-xs-3">
                    <?= $form->field($model, 'tw3')->checkBox([ 'label' => 'Triwulan III']) ?>
                </div>
                <div class="col-xs-3">
                    <?= $form->field($model, 'tw4')->checkBox([ 'label' => 'Triwulan IV']) ?>
                </div>
            </div>
            <?php } ?>

            <div class="form-group">
                <?php if($session['status_poa']!=='disabled'){ ?>
                    <?php if($real == true) { ?>
                        
                    <?php }else{ ?>
                        <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> Simpan', ['class' => 'btn btn-success']) ?>
                    <?php } ?>
                <?php } else {
                    if($session['revisi_poa'] == 1){ ?>
                        <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> Simpan', ['class' => 'btn btn-success']) ?>
                <?php } 
                } ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$this->registerJs('
    $("#vol1").on("change", function (e) {
        createCookie("vol1", $("#vol1").val(), "1");
        createCookie("vol2", $("#vol2").val(), "1"); 
        createCookie("vol3", $("#vol3").val(), "1"); 
        createCookie("vol4", $("#vol4").val(), "1"); 
        createCookie("harga", $("#harga").val(), "1"); 
        baseUrl = window.origin;
        var link = baseUrl+"'.Url::to(['get-total']).'";
        $.get(link, function(data) {
            $( "input#total" ).val( data );
        });
    });

    $("#vol2").on("change", function (e) {
        createCookie("vol1", $("#vol1").val(), "1");
        createCookie("vol2", $("#vol2").val(), "1"); 
        createCookie("vol3", $("#vol3").val(), "1"); 
        createCookie("vol4", $("#vol4").val(), "1"); 
        createCookie("harga", $("#harga").val(), "1"); 
        baseUrl = window.origin;
        var link = baseUrl+"'.Url::to(['get-total']).'";
        $.get(link, function(data) {
            $( "input#total" ).val( data );
        });
    });

    $("#vol3").on("change", function (e) {
        createCookie("vol1", $("#vol1").val(), "1");
        createCookie("vol2", $("#vol2").val(), "1"); 
        createCookie("vol3", $("#vol3").val(), "1"); 
        createCookie("vol4", $("#vol4").val(), "1"); 
        createCookie("harga", $("#harga").val(), "1"); 
        baseUrl = window.origin;
        var link = baseUrl+"'.Url::to(['get-total']).'";
        $.get(link, function(data) {
            $( "input#total" ).val( data );
        });
    });

    $("#vol4").on("change", function (e) {
        createCookie("vol1", $("#vol1").val(), "1");
        createCookie("vol2", $("#vol2").val(), "1"); 
        createCookie("vol3", $("#vol3").val(), "1"); 
        createCookie("vol4", $("#vol4").val(), "1"); 
        createCookie("harga", $("#harga").val(), "1"); 
        baseUrl = window.origin;
        var link = baseUrl+"'.Url::to(['get-total']).'";
        $.get(link, function(data) {
            $( "input#total" ).val( data );
        });
    });

    $("#harga").on("change", function (e) {
        createCookie("vol1", $("#vol1").val(), "1");
        createCookie("vol2", $("#vol2").val(), "1"); 
        createCookie("vol3", $("#vol3").val(), "1"); 
        createCookie("vol4", $("#vol4").val(), "1"); 
        createCookie("harga", $("#harga").val(), "1"); 
        baseUrl = window.origin;
        var link = baseUrl+"'.Url::to(['get-total']).'";
        $.get(link, function(data) {
            $( "input#total" ).val( data );
        });
    });

    // Function to create the cookie 
    function createCookie(name, value, days) { 
        var expires; 
        
        if (days) { 
            var date = new Date(); 
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000)); 
            expires = "; expires=" + date.toGMTString(); 
        } 
        else { 
            expires = ""; 
        } 
        
        document.cookie = escape(name) + "=" +  
            escape(value) + expires + "; path=/"; 
    } 
');
?>
