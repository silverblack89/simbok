<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\Session;
use yii\grid\GridView;
use yii\bootstrap\Button;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\Activitydatasub;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Activitydata */
/* @var $form yii\widgets\ActiveForm */

if($session['status_poa']=='disabled'){
    $disabled = true;
}else{
    $disabled = false;
}
?>

<div class="activitydata-form">
    <?php if($modul !== 'new'){ ?>
        <?php if($modul == 'select'){ ?>
            <?php $form = ActiveForm::begin(['id' => 'activitydata-form', 'options' => ['autocomplete' => 'off']]); ?>
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo $title ?></h3>
                    </div>
                    <div class="panel-body">
                        <?= $form->field($model, 'activity_id')->hiddenInput()->label(false) ?>

                        <?= $form->field($model, 'programId')->textInput(['maxlength' => true, 'disabled' => true])->label('Data Upaya Program') ?>

                        <?= $form->field($model, 'serviceId')->textInput(['maxlength' => true, 'disabled' => true])->label('Data Pelayanan') ?>

                        <?= $form->field($model, 'activityId')->textInput(['maxlength' => true, 'disabled' => true])->label('Data Kegiatan') ?>

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
                                // return Html::a('<span class="glyphicon glyphicon-check"></span> Proses', array('activitydata/update', 'id'=>$session['activityDataId'], 'mid' => $model2['id'], 'modul' => $session['modul']), ['class'=>'btn btn-xs btn-success custom_button']);
                                return Html::button('<span class="glyphicon glyphicon-check"></span> Proses', 
                                ['value' => Url::to(['activitydata/update', 'id'=>$session['activityDataId'], 'mid' => $model2['id'], 'modul' => $session['modul']]), 'title' => 'Pindah Data', 'class' => 'showModalButton btn btn-xs btn-success']);
                            },
                        ]
                    ],
                ],
            ]); ?>
        <?php } ?>
    <?php } ?>

    <?php if($modul == 'new'){ ?>
    
    <?php $form = ActiveForm::begin([
        'id' => 'activitydata-form']); ?>

        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo $title ?></h3>
            </div>
            <div class="panel-body">
                <!-- <?= $form->field($model, 'activity_id')->textInput() ?>

                <?= $form->field($model, 'period_id')->textInput() ?> -->

                <?= $form->field($model, 'activity_data_sub_id')->dropDownList( ArrayHelper::map(Activitydatasub::find()->where(['tahun' => $session['periodValue']])->all(),'id','nama'),['prompt'=>'Pilih'])->label('Sub Kegiatan') ?>

                <?= $form->field($model, 'bentuk_kegiatan')->textInput(['maxlength' => true, 'disabled' => $disabled]) ?>

                <?= $form->field($model, 'sasaran')->textInput(['maxlength' => true, 'disabled' => $disabled]) ?>

                <?= $form->field($model, 'target')->textInput(['maxlength' => true, 'disabled' => $disabled]) ?>

                <?= $form->field($model, 'lokasi')->textInput(['maxlength' => true, 'disabled' => $disabled]) ?>

                <?= $form->field($model, 'pelaksana')->textInput(['maxlength' => true, 'disabled' => $disabled]) ?>

                <div class="form-group">
                    <?php if($session['status_poa']!=='disabled2'){ ?>
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

</div>
