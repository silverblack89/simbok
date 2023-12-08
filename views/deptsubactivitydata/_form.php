<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Dpa;
use app\models\Satuan;
use app\models\DeptPeriod;
use yii\helpers\ArrayHelper;
use yii\web\Session;
use yii\grid\GridView;
use yii\bootstrap\Button;
use yii\helpers\Url;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Deptsubactivitydata */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="deptsubactivitydata-form">
    <?php if($modul == 'usr'){ ?>
        <?php $form = ActiveForm::begin(['options' => ['id' => 'deptsubactivitydata-form', 'autocomplete' => 'off']]); ?>
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Ubah Seksi</h3>
            </div>
            <div class="panel-body">
                <!-- <?= $form->field($model, 'dept_sub_activity_id')->hiddenInput()->label(false) ?> -->
                <?= $form->field($model, 'dept_period_id')->dropDownList( ArrayHelper::map(DeptPeriod::find()->where(['tahun' => $session['deptPeriodValue']])->all(),'id','unit_id'))->label('Seksi') ?>
                <div class="form-group">
                    <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> Simpan', ['class' => 'btn btn-success']) ?>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    <?php }else{ ?>
        <?php if($modul !== 'new'){ ?>
            <?php if($modul == 'select'){ ?>
                <?php $form = ActiveForm::begin(['options' => ['id' => 'deptsubactivitydata-form', 'autocomplete' => 'off']]); ?>

                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo $title ?></h3>
                    </div>
                    <div class="panel-body">
                        <?= $form->field($model, 'dept_sub_activity_id')->hiddenInput()->label(false) ?>

                        <?= $form->field($model, 'deptProgramId')->textInput(['maxlength' => true, 'disabled' => true])->label('Rinci Menu Kegiatan') ?>

                        <?= $form->field($model, 'deptActivityId')->textInput(['maxlength' => true, 'disabled' => true])->label('Komponen') ?>

                        <?= $form->field($model, 'deptSubActivityId')->textInput(['maxlength' => true, 'disabled' => true])->label('Kegiatan') ?>

                        <div class="form-group">
                            <?php if($session['status_poa']!=='disabled'){ ?>
                                <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> Simpan', ['class' => 'btn btn-success', 'data' => [
                                'confirm' => 'Apakah anda yakin akan memindahkan data kegiatan beserta detailnya?'
                                ]]) ?>
                            <?php
                            }else{
                                if($session['revisi_poa'] == 1){ ?>
                                    <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> Simpan', ['class' => 'btn btn-success', 'data' => [
                                    'confirm' => 'Apakah anda yakin akan memindahkan data kegiatan beserta detailnya?'
                                    ]]) ?>
                            <?php } 
                            } ?>
                        </div>
                    </div>
                </div>

                <?php ActiveForm::end(); ?>
            <?php }else{ ?>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'summary' => '',
                    // 'filterModel' => $searchModel,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],

                        'nama',

                        // ['class' => 'yii\grid\ActionColumn'],
                        ['class' => 'yii\grid\ActionColumn',
                            'contentOptions' => ['style' => 'width: 7%'],
                            'template' => '{back} {next}',
                            'buttons' => [
                                'next' => function ($url, $model2, $session) {
                                    $session = Yii::$app->session;
                                    // return Html::a('<span class="glyphicon glyphicon-check"></span> Proses', array('deptsubactivitydata/update', 'id'=>$session['deptSubActivityDataId'], 'mid' => $model2['id'], 'modul' => $session['modul']), ['class'=>'btn btn-xs btn-success custom_button']);
                                    return Html::button('<span class="glyphicon glyphicon-check"></span> Proses', 
                                    ['value' => Url::to(['deptsubactivitydata/update', 'id'=>$session['deptSubActivityDataId'], 'mid' => $model2['id'], 'modul' => $session['modul']]), 'title' => 'Pindah Data', 'class' => 'showModalButton btn btn-xs btn-success']);
                                },
                            ]
                        ],
                    ],
                ]); ?>
            <?php } ?>
        <?php } ?>

        <?php if($modul == 'new'){ ?>
        
        <?php $form = ActiveForm::begin(['options' => ['id' => 'deptsubactivitydata-form', 'autocomplete' => 'off']]); ?>

            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo $title ?></h3>
                </div>
                <div class="panel-body">
                    <!-- <?= $form->field($model, 'dept_sub_activity_id')->textInput() ?>

                    <?= $form->field($model, 'dept_period_id')->textInput() ?> -->

                    <?php if(substr($session['sumberDana'],0,3) == 'DAK') { ?>
                        <?= $form->field($model, 'dpa_id')->dropDownList( ArrayHelper::map(Dpa::find()->where(['>','id',1])->andWhere(['tahun' => $session['deptPeriodValue']])->all(),'id','nama'),['prompt'=>'Pilih'])->label('Sub Menu Kegiatan DPA') ?>
                    <?php } ?>
                    <?= $form->field($model, 'bentuk_kegiatan')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'indikator_hasil')->textInput(['maxlength' => true]) ?>

                    <?php if(!empty($model->target_hasil)){ ?>
                        <?= $form->field($model, 'target_hasil')->textInput(['maxlength' => true, 'disabled' => true]) ?>
                    <?php } ?>

                    <div class="row">
                        <div class="col-sm-7">
                        <?= $form->field($model, 'target')->textInput(['maxlength' => true])->widget(\yii\widgets\MaskedInput::className(), [
                            'clientOptions' => [
                            'alias' => 'decimal',
                            'groupSeparator' => '.',
                            'radixPoint' => ',',
                            'autoGroup' => true
                        ]]); ?>
                        </div>
                        <div class="col-sm-5">
                            <?= $form->field($model, 'satuan')->dropDownList( ArrayHelper::map(Satuan::find()->orderBy('nama')->all(),'nama','nama'),['prompt'=>'Pilih'])->label('Satuan') ?>
                        </div>
                    </div>

                    <!-- <?= $form->field($model, 'indikator_keluaran')->textInput(['maxlength' => true]) ?>
                    
                    <?= $form->field($model, 'target_keluaran')->textInput(['maxlength' => true]) ?> -->

                    <div class="form-group">
                        <?php if($session['status_poa']!=='disabled'){ ?>
                            <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> Simpan', ['class' => 'btn btn-success']) ?>
                        <?php
                        }else{
                            if($session['revisi_poa'] == 1){ ?>
                                <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> Simpan', ['class' => 'btn btn-success']) ?>
                        <?php } 
                        } ?>
                    </div>
                </div>
            </div>  

        <?php ActiveForm::end(); ?>

        <?php } ?>
    <?php } ?>

</div>

<?php
    $js=<<< JS
    $('#deptsubactivitydata-form').on('beforeSubmit', function (e) {
        var form = $(this);
        var formData = form.serialize();

        $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: formData,
            success: function (data) {
                $("#modal").modal('hide');
                $.pjax.reload({container: '#week-all', timeout:false});
            },
            error: function () {
                // alert("Something went wrong");
            }
        });
        return true;
    }).on('submit', function(e){
        e.preventDefault();
   });
JS;
$this->registerJs($js, yii\web\View::POS_HEAD);
?>
