<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\web\Session;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $model app\models\Activitydetail */

$this->title = $session['activityDetailAccount'];
$this->params['breadcrumbs'][] = ['label' => $session['periodValue']. $session['poaLabel'], 'url' => ['period/create', 'p' => 'def']];
$this->params['breadcrumbs'][] = ['label' => 'Data Upaya Program', 'url' => ['program/list']];
$this->params['breadcrumbs'][] = ['label' => 'Data Pelayanan', 'url' => ['service/list', 'id' => $session['programId']]];
$this->params['breadcrumbs'][] = ['label' => 'Data Kegiatan', 'url' => ['activity/list', 'id' => $session['serviceId']]];
$this->params['breadcrumbs'][] = ['label' => 'Bentuk Kegiatan', 'url' => ['activitydata/list', 'id' => $session['activityId']]];
$this->params['breadcrumbs'][] = ['label' => $session['activityName'], 'url' => ['activitydata/view', 'id' => $session['activityDataId']]];
$this->params['breadcrumbs'][] = 'Data Realisasi Kegiatan';
\yii\web\YiiAsset::register($this);
?>
<div class="activitydetail-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <!-- <?= Html::a('<span class="glyphicon glyphicon-pencil"></span> Ubah', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
        <?= Html::a('<span class="glyphicon glyphicon-remove"></span> Hapus', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Apakah Anda yakin akan menghapus data ini?',
                'method' => 'post',
            ],
        ]) ?> -->
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            // 'id',
            // 'activity_data_id',
            // [
            //     'attribute' => 'activity_data_id',
            //     'value' => $model->activityData->bentuk_kegiatan, // or use 'usertable.name'
            // ],
            // 'account_id',
            // [
            //     'label'  => 'Rekening',
            //     'attribute' => 'account_id',
            //     'value' => $model->account->nama_rekening, // or use 'usertable.name'
            // ],
            // 'rincian',
            // 'vol_1',
            [
                'attribute' => 'vol_1',
                'value' => $model->vol_1. ' '.$model->satuan_1,
            ],
            // 'vol_2',
            [
                'label' => 'Volume 2 (Optional)',
                'attribute' => 'vol_2',
                'value' => $model->vol_2. ' '.$model->satuan_2,
            ],
            // 'unit_cost',
            [
                'attribute' =>'unit_cost',
                'format'=>['decimal',0]
            ],
            // 'jumlah',
            [
                'attribute' =>'jumlah',
                'format'=>['decimal',0]
            ],
            // 'jan',
            // 'feb',
            // 'mar',
            // 'apr',
            // 'mei',
            // 'jun',
            // 'jul',
            // 'agu',
            // 'sep',
            // 'okt',
            // 'nov',
            // 'des',
        ],
    ]) ?>

    <?php 
        Modal::begin([
                'header'=>'<h4>Realisasi Keuangan</h4>',
                'id'=>'modal',
                'size'=>'modal-lg',
            ]);
        echo "<div id='modalContent'></div>";
        Modal::end();
    ?>

    <p>
        <?= Html::a('<span class="glyphicon glyphicon-plus"></span> Tambah Realisasi', ['financialrealization/create', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
        <!-- <?= Html::button('<span class="glyphicon glyphicon-plus"></span> Tambah Realisasi', ['value' => Url::to(['financialrealization/create', 'id' => $model->id]), 'title' => 'Tambah Realisasi Keuangan', 'class' => 'showModalButton btn btn-info']); ?> -->
    </p>

    <div style="overflow-x:auto;">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            // 'activity_detail_id',
            // 'bulan',
            [
                'label' => 'Bulan',
                'attribute' =>'bulan',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-left', 'style' => 'width: 10%'],
                'value' => function($model){
                    if($model->bulan == '1'){
                       return 'Januari';
                    }
                    if($model->bulan == '2'){
                       return 'Februari';
                    }
                    if($model->bulan == '3'){
                       return 'Maret';
                    }
                    if($model->bulan == '4'){
                       return 'April';
                    }
                    if($model->bulan == '5'){
                       return 'Mei';
                    }
                    if($model->bulan == '6'){
                       return 'Juni';
                    }
                    if($model->bulan == '7'){
                       return 'Juli';
                    }
                    if($model->bulan == '8'){
                       return 'Agustus';
                    }
                    if($model->bulan == '9'){
                       return 'September';
                    }
                    if($model->bulan == '10'){
                       return 'Oktober';
                    }
                    if($model->bulan == '11'){
                       return 'November';
                    }
                    if($model->bulan == '12'){
                       return 'Desember';
                    }
                  }
            ],
            // 'realisasi_vol_1',
            [
                'attribute' =>'realisasi_vol_1',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right', 'style' => 'width: 10%'],
                'format'=>['decimal',0]
            ],
            [
                'label' => 'Satuan',
                'attribute' =>'realisasi_satuan_1',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-left', 'style' => 'width: 10%'],
            ],
            // 'realisasi_vol_2',
            [
                'attribute' =>'realisasi_vol_2',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right', 'style' => 'width: 10%'],
                'format'=>['decimal',0]
            ],
            [
                'label' => 'Satuan',
                'attribute' =>'realisasi_satuan_2',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-left', 'style' => 'width: 10%'],
            ],
            // 'realisasi_unit_cost',
            [
                'label' => 'Realisasi Biaya (Unit Cost)',
                'attribute' =>'realisasi_unit_cost',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right', 'style' => 'width: 10%'],
                'format'=>['decimal',0]
            ],
            // 'realisasi_jumlah',
            [
                'label' => 'Jumlah',
                'attribute' =>'realisasi_jumlah',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right', 'style' => 'width: 10%'],
                'format'=>['decimal',0]
            ],

            // ['class' => 'yii\grid\ActionColumn'],
            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'width: 5%'],
                'template' => '{delete}',
                'buttons' => [
                    'delete' => function ($url, $model, $session) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span> Hapus', ['financialrealization/delete', 'id' => $model->id], ['class'=>'btn btn-xs btn-danger custom_button', 
                        'data' => [
                            'confirm' => 'Apakah Anda yakin akan menghapus data ini?',
                            'method' => 'post',
                        ],
                    ]);
                    },
                    'update' => function ($url, $model) {
                        // return Html::a('<span class="glyphicon glyphicon-pencil"></span> Ubah', array('financialrealization/update', 'id'=>$model->id, 'real' => false), ['class'=>'btn btn-xs btn-warning custom_button']);
                        return Html::button('<span class="glyphicon glyphicon-pencil"></span> Ubah', ['value' => Url::to(['financialrealization/update', 'id' => $model->id]), 'title' => 'Tambah Realisasi Keuangan', 'class' => 'showModalButton btn btn-xs btn-warning custom_button']);
                    },
                ]
            ],
        ],
    ]); ?>
    </div>

</div>
