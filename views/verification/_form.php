<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Verification */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="verification-form">

    <?php $form = ActiveForm::begin([
        'id' => 'verification-form']); 
    ?>

    <?= $form->field($model, 'unit_id')->hiddenInput(['maxlength' => true])->label(false) ?>

    <?php if (Yii::$app->user->identity->unit_id == 'DINKES'){ ?>
    <div style="overflow-x:auto;">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            // 'filterModel' => $searchModel,
            'id' => 'GridView',
            'options' => ['style' => 'font-size:13px;'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                // [
                //     'attribute' => 'nama_kegiatan',
                //     'label' => 'Jenis',
                // ],
                [
                    'attribute' => 'bentuk_kegiatan',
                    'label' => 'Kegiatan',
                ],
                'sasaran',
                // 'target',
                'lokasi',
                // 'pelaksana',
                [
                    'attribute' => 'nama_rekening',
                    'label' => 'Rekening',
                ],
                [
                    'label' => 'Vol1',
                    'attribute' =>'vol_1',
                    'enableSorting' => false,
                    'contentOptions' => ['class' => 'col-lg-1 text-right'],
                    'format'=>['decimal',0]
                ],
                [
                    'attribute' => 'satuan_1',
                    'label' => 'Satuan',
                    'contentOptions' => ['style' => 'width: 5%']
                ],
                [
                    'label' => 'Vol2',
                    'attribute' =>'vol_2',
                    'enableSorting' => false,
                    'contentOptions' => ['class' => 'col-lg-1 text-right'],
                    'format'=>['decimal',0]
                ],
                [
                    'attribute' => 'satuan_2',
                    'label' => 'Satuan',
                    'contentOptions' => ['style' => 'width: 5%']
                ],
                [
                    'label' => 'Biaya',
                    'attribute' =>'unit_cost',
                    'enableSorting' => false,
                    'contentOptions' => ['class' => 'col-lg-1 text-right'],
                    'format'=>['decimal',0]
                ],
                [
                    'label' => 'Jumlah',
                    'attribute' =>'jumlah',
                    'enableSorting' => false,
                    'contentOptions' => ['class' => 'col-lg-1 text-right'],
                    'format'=>['decimal',0]
                ],

                // ['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
    </div>
    <?php } ?>
    
    <div class="form-group">
    <?php if($kunci == 1){ ?>
        <?php if($status == 'verified'){ ?>
            <?= Html::a('<span class="glyphicon glyphicon-remove"></span> Batal Verifikasi', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Apakah Anda yakin akan membatalkan verifikasi data ini?',
                'method' => 'post',
            ],
            ]) ?>
        <?php }else{ ?>
            <?php if($status == 'revision'){ ?>
                <?php if($revised == 1){ ?>
                <?= $form->field($model, 'catatan')->textarea(['rows' => '2', 'disabled' => true])->label('Catatan Revisi') ?>
                <?= Html::a('<span class="glyphicon glyphicon-pencil"></span> Sudah direvisi', array('verification/update', 'id' => $session['verifId'], 'revisi' => 1, 'revised' => 1), [
                    'class' => 'btn btn-warning',
                    'data' => [
                        'method' => 'post',
                    ],
                    ]) ?>
                <?php }else{ ?>
                    <?= $form->field($model, 'catatan')->textarea(['rows' => '2'])->label('Catatan Revisi') ?>
                    <?= Html::a('<span class="glyphicon glyphicon-ok"></span> Verifikasi', array('verification/update', 'id' => $session['verifId'], 'revisi' => 0, 'revised' => 0), [
                    'class' => 'btn btn-success',
                    'data' => [
                        'method' => 'post',
                    ],
                    ]) ?>
                <?php } ?>
            <?php }else{ ?>
                <?php if ($dataProvider->totalCount > 0) {?>
                    <?= $form->field($model, 'catatan')->textarea(['rows' => '2'])->label('Catatan Revisi') ?>
                    <?= Html::submitButton('<span class="glyphicon glyphicon-ok"></span> Verifikasi', ['class' => 'btn btn-success']) ?>
                    <?= Html::a('<span class="glyphicon glyphicon-pencil"></span> Revisi', array('verification/create', 'id' => $id, 'revisi' => 1, 'revised' => 0), [
                    'class' => 'btn btn-warning',
                    'data' => [
                        'method' => 'post',
                    ],
                    ]) ?>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    <?php } ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
