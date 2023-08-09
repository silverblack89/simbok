<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Account;
use app\models\Satuan;
use yii\web\Session;
use yii\helpers\Url;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Activitydetail */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="activitydetail-form">

    <?php $form = ActiveForm::begin(['id' => 'activitydetail-form', 'options' => ['autocomplete' => 'off']]); ?>

    <?php
        if($real == 0){
            if($session['status_poa']!=='disabled'){
                $disabled = false;
            }else{
                $disabled = true;
            }
        }else{
            $disabled = true;
        }
    ?>

    <div class="panel panel-primary">   
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo $title ?></h3>
        </div>
        <div class="panel-body">
            <!-- <?= $form->field($model, 'activity_data_id')->textInput() ?> -->
            
            <div class="row">
                <div class="col-md-7">
                    <?php if(!empty($akun)){ ?>
                        <?= $form->field($model, 'account_id')->dropDownList( ArrayHelper::map(Account::find()->where('id IN('.$akun.')')->orderBy('nama_rekening')->all(),'id','nama_rekening'),['prompt'=>'Pilih Rekening', 'disabled' => $disabled, 'readonly' => $disabled])->label('Jenis Rekening') ?>
                    <?php }else{ ?>
                        <?= $form->field($model, 'account_id')->dropDownList( ArrayHelper::map(Account::find()->orderBy('nama_rekening')->all(),'id','nama_rekening'),['prompt'=>'Pilih Rekening', 'disabled' => $disabled, 'readonly' => $disabled])->label('Jenis Rekening') ?>
                    <?php } ?>
                </div>
                <!-- <div class="col-md-4">
                    <?= $form->field($model, 'total_pagu')->textInput(['maxlength' => true])->label('Total Pagu')->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id'=> 'pagu', 'disabled' => true],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                            ]]); ?>
                </div>   -->

                <div class="col-md-5">
                    <?= $form->field($model, 'rincian')->textInput(['disabled' => $disabled, 'maxlength' => true, 'placeholder' => 'rincian belanja']) ?>
                </div>  
                <!-- <div class="col-md-4">
                    <?= $form->field($model, 'total_poa')->textInput(['maxlength' => true])->label('Total POA')->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id'=> 'poa', 'disabled' => true],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                            ]]); ?>
                </div>   -->
            </div>

            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'vol_1')->textInput(['maxlength' => true])->label('Volume 1')->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'vol1', 'readonly' => $disabled, 'placeholder' => 'orang'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true,
                            ]]); ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'satuan_1')->dropDownList( ArrayHelper::map(Satuan::find()->orderBy('nama')->all(),'nama','nama'),['prompt'=>'Pilih Satuan', 'disabled' => $disabled, 'readonly' => $disabled])->label('Satuan 1') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'vol_2')->textInput(['maxlength' => true])->label('Volume 2')->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'vol2', 'readonly' => $disabled, 'placeholder' => 'jam'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                            ]]); ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'satuan_2')->dropDownList( ArrayHelper::map(Satuan::find()->orderBy('nama')->all(),'nama','nama'),['prompt'=>'Pilih Satuan', 'disabled' => $disabled, 'readonly' => $disabled])->label('Satuan 2') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'vol_3')->textInput(['maxlength' => true])->label('Volume 3')->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'vol3', 'readonly' => $disabled, 'placeholder' => 'hari'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true,
                            ]]); ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'satuan_3')->dropDownList( ArrayHelper::map(Satuan::find()->orderBy('nama')->all(),'nama','nama'),['prompt'=>'Pilih Satuan', 'disabled' => $disabled, 'readonly' => $disabled])->label('Satuan 3') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'vol_4')->textInput(['maxlength' => true])->label('Volume 4')->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'vol4', 'readonly' => $disabled, 'placeholder' => 'kali'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true,
                            ]]); ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'satuan_4')->dropDownList( ArrayHelper::map(Satuan::find()->orderBy('nama')->all(),'nama','nama'),['prompt'=>'Pilih Satuan', 'disabled' => $disabled, 'readonly' => $disabled])->label('Satuan 4') ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($model, 'unit_cost')->textInput(['maxlength' => true])->label('Harga Satuan')->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'harga', 'readonly' => $disabled, 'placeholder' => 'harga satuan'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                            ]]); ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($model, 'total')->textInput(['maxlength' => true])->label('Total')->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'total', 'readonly' => $disabled],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                            ]]); ?>
                </div>
            </div>

            <!-- <?= $form->field($model, 'jumlah')->textInput(['maxlength' => true]) ?> -->
        </div>
    </div>

    <?php if($real == false) { ?>

        <!-- Bulan Rencana Kegiatan -->
            
        <div class="panel panel-info">   
            <div class="panel-heading">
                <h3 class="panel-title">Rencana Anggaran Kas</h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-2">
                        <!-- <?= $form->field($model, 'jan')->checkBox([ 'label' => 'Jan']) ?> -->
                        <?= $form->field($model, 'jan_val')->textInput(['maxlength' => true])->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'jan_val'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                        ]]); ?>
                    </div>
                    <div class="col-md-2">
                        <!-- <?= $form->field($model, 'feb')->checkBox([ 'label' => 'Feb']) ?> -->
                        <?= $form->field($model, 'feb_val')->textInput(['maxlength' => true])->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'feb_val'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                        ]]); ?>
                    </div>
                    <div class="col-md-2">
                        <!-- <?= $form->field($model, 'mar')->checkBox([ 'label' => 'Mar']) ?> -->
                        <?= $form->field($model, 'mar_val')->textInput(['maxlength' => true])->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'mar_val'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                        ]]); ?>
                    </div>
                    <div class="col-md-2">
                        <!-- <?= $form->field($model, 'apr')->checkBox([ 'label' => 'Apr']) ?> -->
                        <?= $form->field($model, 'apr_val')->textInput(['maxlength' => true])->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'apr_val'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                        ]]); ?>
                    </div>
                    <div class="col-md-2">
                        <!-- <?= $form->field($model, 'mei')->checkBox([ 'label' => 'Mei']) ?> -->
                        <?= $form->field($model, 'mei_val')->textInput(['maxlength' => true])->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'mei_val'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                        ]]); ?>
                    </div>
                    <div class="col-md-2">
                        <!-- <?= $form->field($model, 'jun')->checkBox([ 'label' => 'Jun']) ?> -->
                        <?= $form->field($model, 'jun_val')->textInput(['maxlength' => true])->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'jun_val'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                        ]]); ?>
                    </div>
                    <div class="col-md-2">
                        <!-- <?= $form->field($model, 'jul')->checkBox([ 'label' => 'Jul']) ?> -->
                        <?= $form->field($model, 'jul_val')->textInput(['maxlength' => true])->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'jul_val'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                        ]]); ?>
                    </div>
                    <div class="col-md-2">
                        <!-- <?= $form->field($model, 'agu')->checkBox([ 'label' => 'Agu']) ?> -->
                        <?= $form->field($model, 'agu_val')->textInput(['maxlength' => true])->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'agu_val'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                        ]]); ?>
                    </div>
                    <div class="col-md-2">
                        <!-- <?= $form->field($model, 'sep')->checkBox([ 'label' => 'Sep']) ?> -->
                        <?= $form->field($model, 'sep_val')->textInput(['maxlength' => true])->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'sep_val'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                        ]]); ?>
                    </div>
                    <div class="col-md-2">
                        <!-- <?= $form->field($model, 'okt')->checkBox([ 'label' => 'Okt']) ?> -->
                        <?= $form->field($model, 'okt_val')->textInput(['maxlength' => true])->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'okt_val'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                        ]]); ?>
                    </div>
                    <div class="col-md-2">
                        <!-- <?= $form->field($model, 'nov')->checkBox([ 'label' => 'Nov']) ?> -->
                        <?= $form->field($model, 'nov_val')->textInput(['maxlength' => true])->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'nov_val'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                        ]]); ?>
                    </div>
                    <div class="col-md-2">
                        <!-- <?= $form->field($model, 'des')->checkBox([ 'label' => 'Des']) ?> -->
                        <?= $form->field($model, 'des_val')->textInput(['maxlength' => true])->widget(\yii\widgets\MaskedInput::className(), [
                            'options' => ['id' => 'des_val'],
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                        ]]); ?>
                    </div>
                </div>
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

            <!-- TAMBAHAN RAK -->
            <?php if($session['status_poa']=='disabled'){ ?>
                <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> Simpan', ['class' => 'btn btn-success']) ?>
            <?php } ?>
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

        var linkpoa = baseUrl+"'.Url::to(['get-poa']).'";
        $.get(linkpoa, function(data){
            $( "input#poa" ).val( data );
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

        var linkpoa = baseUrl+"'.Url::to(['get-poa']).'";
        $.get(linkpoa, function(data){
            $( "input#poa" ).val( data );
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

        var linkpoa = baseUrl+"'.Url::to(['get-poa']).'";
        $.get(linkpoa, function(data){
            $( "input#poa" ).val( data );
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

        var linkpoa = baseUrl+"'.Url::to(['get-poa']).'";
        $.get(linkpoa, function(data){
            $( "input#poa" ).val( data );
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

        var linkpoa = baseUrl+"'.Url::to(['get-poa']).'";
        $.get(linkpoa, function(data){
            $( "input#poa" ).val( data );
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

$js=<<< JS
    $('#activitydetail-form').on('beforeSubmit', function (e) {
        var total =  parseInt(document.getElementById("total").value.replaceAll(".","")) || 0;
        var jan_val = parseInt(document.getElementById("jan_val").value.replaceAll(".","")) || 0;
        var feb_val = parseInt(document.getElementById("feb_val").value.replaceAll(".","")) || 0;
        var mar_val = parseInt(document.getElementById("mar_val").value.replaceAll(".","")) || 0;
        var apr_val = parseInt(document.getElementById("apr_val").value.replaceAll(".","")) || 0;
        var mei_val = parseInt(document.getElementById("mei_val").value.replaceAll(".","")) || 0;
        var jun_val = parseInt(document.getElementById("jun_val").value.replaceAll(".","")) || 0;
        var jul_val = parseInt(document.getElementById("jul_val").value.replaceAll(".","")) || 0;
        var agu_val = parseInt(document.getElementById("agu_val").value.replaceAll(".","")) || 0;
        var sep_val = parseInt(document.getElementById("sep_val").value.replaceAll(".","")) || 0;
        var okt_val = parseInt(document.getElementById("okt_val").value.replaceAll(".","")) || 0;
        var nov_val = parseInt(document.getElementById("nov_val").value.replaceAll(".","")) || 0;
        var des_val = parseInt(document.getElementById("des_val").value.replaceAll(".","")) || 0;

        var form = $(this);
        var formData = form.serialize();

        if(jan_val+feb_val+mar_val+apr_val+mei_val+jun_val+jul_val+agu_val+sep_val+okt_val+nov_val+des_val !== total){
            alert('Total RPK bulanan tidak sama dengan total kegiatan.');
            return false;
        }else{
            $.ajax({
                url: form.attr("action"),
                type: form.attr("method"),
                data: formData,
                success: function (data) {
                    $("#modal").modal('hide');
                    $.pjax.reload({container: '#detail', timeout:false});
                },
                error: function () {
                    // alert("Something went wrong");
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