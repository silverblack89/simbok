<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\Session;
use yii\bootstrap\Modal;
use yii\helpers\Url;

$session = Yii::$app->session;

/* @var $this yii\web\View */
/* @var $searchModel app\models\IndicatordataSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Data Realisasi';
$this->params['breadcrumbs'][] = ['label' => $session['periodValue'], 'url' => ['period/create']];
$this->params['breadcrumbs'][] = ['label' => 'Data Upaya Program', 'url' => ['program/list']];
$this->params['breadcrumbs'][] = ['label' => 'Data Indikator', 'url' => ['indicator/list', 'id' => $session['programId']]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="indicatordata-index">

    <h1><?= Html::encode($this->title.' '.$session['indicatorName']) ?></h1>

    <p>
      <?php if($session['status']!=='disabled'){ ?>
        <?= Html::a('Tambah Realisasi', ['create', 'id' => $id], ['class' => 'btn btn-success']) ?>
        <!-- <?= Html::button('<span class="glyphicon glyphicon-plus"></span> Tambah Realisasi', ['value' => Url::to(['create', 'id' => $id]), 'title' => 'Tambah Realisasi Kinerja', 'class' => 'showModalButton btn btn-success']); ?> -->
      <?php } ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            // 'indicator_id',
            // [
            //     'attribute' => 'indicator',
            //     'label' => 'Nama Indikator',
            //     'value' => 'indicator.nama_indikator'
            // ],
            // 'bulan',
            [
                'attribute' => 'bulan',
                'enableSorting' => false,
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
            // 'kinerja',
            [
                'label' => 'Kinerja (%)',
                'attribute' =>'kinerja',
                'enableSorting' => false,
                'contentOptions' => ['class' => 'col-lg-1 text-right'],
                'format'=>['decimal',2]
            ],
            // 'keuangan',
            // [
            //     'label' => 'Keuangan (Rp)',
            //     'attribute' =>'keuangan',
            //     'enableSorting' => false,
            //     'contentOptions' => ['class' => 'col-lg-1 text-right'],
            //     'format'=>['decimal',0]
            // ],

            // ['class' => 'yii\grid\ActionColumn'],
            ['class' => 'yii\grid\ActionColumn',
                'contentOptions' => ['style' => 'width: 13%'],
                'template' => '{update} {delete}',
                'buttons' => [
                    'delete' => function ($url, $model, $session) {
                        $session = Yii::$app->session;
                        if($session['status']!=='disabled'){
                           return Html::a('<span class="glyphicon glyphicon-trash"></span> Hapus', ['indicatordata/delete', 'id' => $model->id], ['class'=>'btn btn-xs btn-danger custom_button', 
                           'data' => [
                               'confirm' => 'Apakah Anda yakin akan menghapus data ini?',
                               'method' => 'post',
                           ],
                        ]);
                        }
                    },
                    'update' => function ($url, $model, $session) {
                        $session = Yii::$app->session;
                        if($session['status']!=='disabled'){
                           return Html::a('<span class="glyphicon glyphicon-pencil"></span> Ubah', array('indicatordata/update', 'id'=>$model->id), ['class'=>'btn btn-xs btn-warning custom_button']);
                        }
                    },
                ]
            ],
        ],
    ]); ?>


</div>
